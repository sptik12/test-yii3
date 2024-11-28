<?php

namespace App\Backend\Service;

use App\Backend\Model\Dealer\DealerModel;
use App\Backend\Model\Dealer\DealerStatus;
use App\Backend\Model\Province;
use App\Backend\Model\User\Role;
use App\Backend\Model\User\Status;
use App\Backend\Search\DealerSearch;
use App\Backend\Component\Notificator\Notificator;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Files\FileHelper;
use Yiisoft\Injector\Injector;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

final class DealerService extends AbstractService
{
    public function __construct(
        protected UrlGeneratorInterface $urlGenerator,
        protected TranslatorInterface $translator,
        protected ConfigInterface $config,
        protected Aliases $aliases,
        protected Injector $injector,
    ) {
        parent::__construct($injector);
    }

    /**
     * Find
     */
    public function findById(string $id): ?DealerModel
    {
        return DealerModel::findOne($id);
    }


    public function getPossibleStatuses(): array
    {
        $res = [];

        foreach (DealerStatus::cases() as $dealerStatus) {
            if ($dealerStatus != DealerStatus::Deleted) {
                $res[] = $dealerStatus;
            }
        }

        return $res;
    }

    /**
     * Count
     */
    protected function getAccountManagerDealersCount(
        int $userId,
        DealerSearch $dealerSearch
    ): int {
        return $dealerSearch->getTotalRecords(filters: ["accountManager" => $userId]);
    }



    /**
     * Search
     */
    protected function searchDealers(
        array $filters,
        DealerSearch $dealerSearch
    ): object {
        $items = [];
        $totalCount = $dealerSearch->getTotalRecords(
            filters: $filters,
        );

        if ($totalCount) {
            $items = $dealerSearch->search(
                fields: ["dealer.*"],
                filters: $filters,
                joinsWith: ['accountManager']
            );

            foreach ($items as &$dealer) {
                $dealer = $this->hydrateToListCard($dealer);
            }
        }

        return (object)compact("items", "totalCount");
    }

    protected function searchDealersForList(
        array $filters,
        DealerSearch $dealerSearch
    ): array {
        $items = $dealerSearch->search(
            fields: ["dealer.id", "dealer.name"],
            filters: $filters,
            sort: 'dealer.name'
        );

        foreach ($items as &$dealer) {
            $dealer = $this->hydrateModelToObject($dealer);
        }

        return $items;
    }


    /**
     * Methods with validation
     */
    protected function getDealer(
        int $id,
    ): object {
        $dealer = $this->findById($id);
        $dealer = $this->hydrateToDealerCard($dealer);

        return $dealer;
    }

    protected function createDealershipFromArray(
        array $requestData,
        UserService $userService,
        GeoService $geoService
    ): bool {
        $requestDataDealership = $requestData["requestDataDealership"];

        // method called to create dealer from admin part, so it's status already active
        $requestDataDealership['status'] = DealerStatus::Active->value;
        $dealerModel = $this->createDealer($requestDataDealership);
        $geoService->setDealerGeoData($dealerModel->id);

        // create dealer owner and assign to dealer role
        $requestDataUser = $requestData["requestDataUser"];
        $requestDataUser["dealerId"] = $dealerModel->id;
        $requestDataUser["role"] = Role::DealerPrimary;
        $userModel = $userService->createUserAndAssignToDealer($requestDataUser);
        $geoService->setUserGeoData($userModel->id);

        return true;
    }

    protected function updateDealerFromArray(
        array $requestData,
        GeoService $geoService,
        CarService $carService
    ): ?object {
        $requestData = (object)$requestData;
        $dealer = $this->findById($requestData->id);
        $dealer->name = $requestData->name;
        $dealer->accountManagerId = $requestData->accountManagerId;
        $dealer->businessNumber = $requestData->businessNumber;
        $dealer->phone = $requestData->phone;
        $dealer->website = $requestData->website;
        $dealer->address = $requestData->address;
        $dealer->postalCode = $requestData->postalCode;
        $dealer->province = $requestData->province;
        $dealer->googleMapsBusinessUrl = $requestData->googleMapsBusinessUrl ?? $dealer->googleMapsBusinessUrl;
        $dealer->googleMapsReviewsUrl = $requestData->googleMapsReviewsUrl ?? $dealer->googleMapsReviewsUrl;

        if (!empty($requestData->googleMapsBusinessUrl)) {
            $reviewsInfo = $this->parseReviewsInfoFromGoogleMaps($dealer->id, $dealer->googleMapsBusinessUrl);
            $dealer->reviewsRating = $reviewsInfo->rating;
            $dealer->reviewsCount = $reviewsInfo->count;
        }

        $dealer->save();
        $dealer = $geoService->setDealerGeoData($requestData->id);

        $carService->updateCarsLocationDataForDealer($dealer->id);

        $dealer = $this->hydrateToDealerCard($dealer);

        return $dealer;
    }

    protected function approveDealerFromArray(
        array $requestData,
        UserDealerPositionService $userDealerPositionService,
        ConfigInterface $config,
        Notificator $notificator,
    ): bool {
        $requestData = (object)$requestData;
        $dealerId = $requestData->dealerId;
        $dealerModel = $this->findById($dealerId);

        if ($dealerModel) {
            $dealerModel->status = DealerStatus::Active->value;
            $dealerModel->save();

            // notify primary dealers
            $primaryDealers = $userDealerPositionService->search(
                filters: [
                    'dealer' => $dealerId,
                    'role' => Role::DealerPrimary->value
                ],
                joinsWith: ['user']
            );
            $fromEmail = $config->get('params')['app']['defaultFromEmail'];
            $appName =  $config->get('params')['app']['name'];

            foreach ($primaryDealers as $primaryDealer) {
                if ($primaryDealer->status == Status::Active->value) {
                    $notificator->push(
                        from: $fromEmail,
                        to: $primaryDealer->email,
                        subject: $appName . " " . $this->translator->translate("dealer approved"),
                        content: [
                            'template' => "dealer-approved",
                            'vars' => [
                                'username' => $primaryDealer->username,
                            ],
                        ],
                        event: "Dealer approved",
                        lang: $this->translator->getLocale()
                    );
                }
            }
        }

        return true;
    }

    protected function suspendDealerFromArray(
        array $requestData,
    ): bool {
        $requestData = (object)$requestData;
        $dealerId = $requestData->dealerId;
        $dealerModel = $this->findById($dealerId);

        if ($dealerModel) {
            $dealerModel->status = DealerStatus::Disabled->value;
            $dealerModel->save();
        }

        return true;
    }

    protected function unsuspendDealerFromArray(
        array $requestData,
    ): bool {
        $requestData = (object)$requestData;
        $dealerId = $requestData->dealerId;
        $dealerModel = $this->findById($dealerId);

        if ($dealerModel) {
            $dealerModel->status = DealerStatus::Active->value;
            $dealerModel->save();
        }

        return true;
    }

    protected function assignAccountManagersFromArray(
        array $requestData,
    ): bool {
        $requestData = (object)$requestData;
        $accountManagerId = $requestData->accountManagerId;
        $dealersIds = $requestData->dealersIds;

        if ($dealersIds) {
            DealerModel::updateAllRecords(['accountManagerId' => $accountManagerId], ['id' => $dealersIds]);
        }

        return true;
    }

    protected function uploadDealerLogoFromArray(
        array $requestData,
        array $files
    ): ?object {
        $requestData = (object)$requestData;
        $dealerId = $requestData->dealerId;

        if ($files) {
            $dealer = $this->findById($dealerId);
            $dealerPath = $this->getDealerPath($dealer->id);
            $file = $files[0];
            $extension =  pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
            $fileName = "logo.{$extension}";
            $filePath = "{$dealerPath}/{$fileName}";
            $file->moveTo("{$filePath}");
            $dealer->logo = $this->getDealerFileUrl($dealer->id, $fileName);
            $dealer->save();
        }

        $dealer = $this->hydrateToDealerCard($dealer);

        return $dealer;
    }

    protected function deleteDealerLogo(
        int $dealerId
    ): ?object {
        $dealer = $this->findById($dealerId);
        $dealer->logo = null;
        $dealer->save();
        $dealer = $this->hydrateToDealerCard($dealer);

        return $dealer;
    }






    /**
     * Other methods
     */

    protected function createDealer(
        array $data
    ): ?DealerModel {
        $data = (object)$data;
        $dealer = new DealerModel();
        $dealer->name = $data->name;
        $dealer->accountManagerId = $data->accountManagerId;
        $dealer->businessNumber = $data->businessNumber;
        $dealer->phone = $data->phone;
        $dealer->website = $data->website;
        $dealer->address = $data->address;
        $dealer->postalCode = $data->postalCode;
        $dealer->province = $data->province;
        $dealer->status = $data->status;
        $dealer->save();

        return $dealer;
    }





    private function hydrateToListCard(
        DealerModel $dealerModel
    ): object {
        $dealer = $this->hydrateModelToObject($dealerModel);
        $dealer->statusName = DealerStatus::tryFrom($dealerModel->status)?->title($this->translator);
        $dealer->approveUrl = $this->urlGenerator->generateAbsolute(
            'admin.approveDealer',
            [
                '_language' => $this->translator->getLocale(),
                'id' => $dealerModel->id
            ]
        );

        return $dealer;
    }

    private function hydrateToDealerCard(
        DealerModel $dealerModel
    ): object {
        $dealer = $this->hydrateModelToObject($dealerModel);
        $dealerProvince = Province::tryFromName($dealer?->province)?->title($this->translator);
        $dealer->statusName = DealerStatus::tryFrom($dealerModel->status)?->title($this->translator);
        $dealer->fullAddress = "{$dealer->address}, {$dealerProvince} {$dealer->postalCode}";
        $dealer->hasLogo = $dealer?->logo ? true : false;
        $dealer->logo = $dealer?->logo ?? $this->getDealerDefaultImage();

        return $dealer;
    }


    private function getDealerPath(int $dealerId): string
    {
        $path = $this->aliases->get("@uploads/dealers/{$dealerId}");
        FileHelper::ensureDirectory($path);

        return $path;
    }

    private function getDealerFileUrl(int $dealerId, ?string $fileName): ?string
    {
        if (!$fileName) {
            return null;
        }

        return "/uploads/dealers/{$dealerId}/{$fileName}";
    }

    private function getDealerDefaultImage(): string
    {
        return DealerModel::DEFAULT_IMAGE;
    }

    private function parseReviewsInfoFromGoogleMaps(int $dealerId, string $googleMapsBusinessUrl): object
    {
        if (!preg_match("/(0x[0-9a-zA-Z]+:0x[0-9a-zA-Z]+)!/i", $googleMapsBusinessUrl, $match)) {
            throw new \RuntimeException("Unable to parse Feature Id from Google Maps Business Url. Dealer #{$dealerId}. Url: {$googleMapsBusinessUrl}");
        }

        $featureId = $match[1];
        $content = file_get_contents("https://www.google.com/async/reviewDialog?hl=en_us&async=feature_id:{$featureId},next_page_token:,sort_by:qualityScore,start_index:,associated_topic:,_fmt:pc");

        // Rating
        if (!preg_match("/<g-review-stars>.*?aria-label=\"Rated\s+([0-9\.]+).*?\".*?>/i", $content, $match)) {
            throw new \RuntimeException("Unable to parse Reviews Rating from Google Maps. Dealer #{$dealerId}. Url: {$googleMapsBusinessUrl}");
        }

        $data['rating'] = $match[1];

        // Count
        if (!preg_match("/([0-9,]+)\s+reviews/i", $content, $match)) {
            throw new \RuntimeException("Unable to parse Reviews Count from Google Maps. Dealer #{$dealerId}. Url: {$googleMapsBusinessUrl}");
        }

        $data['count'] = $match[1];

        return (object)$data;
    }
}
