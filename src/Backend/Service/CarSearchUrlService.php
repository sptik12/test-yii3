<?php

namespace App\Backend\Service;

use App\Backend\Model\Car\CarSearchUrlModel;
use App\Backend\Model\Car\CarSearchUrlStatus;
use App\Backend\Search\CarSearchUrlSearch;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Injector\Injector;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\Router\UrlGeneratorInterface;

final class CarSearchUrlService extends AbstractService
{
    public function __construct(
        protected CarSearchUrlSearch $carSearchUrlSearch,
        protected UrlGeneratorInterface $urlGenerator,
        protected TranslatorInterface $translator,
        protected ConfigInterface $config,
        protected Injector $injector
    ) {
        parent::__construct($injector);
    }


    /**
     * Find
     */
    public function findById(int $id): ?CarSearchUrlModel
    {
        return CarSearchUrlModel::findOne($id);
    }

    public function findByUrl(string $url, int $userId): ?CarSearchUrlModel
    {
        return CarSearchUrlModel::findOne(['userId' => $userId, 'url' => $url, 'status' => CarSearchUrlStatus::Active->value]);
    }

    public function getCarSearchUrlsCount(int $userId): int
    {
        return CarSearchUrlModel::find()->where(["userId" => $userId, 'status' => CarSearchUrlStatus::Active->value])->count();
    }





    protected function searchCarSearchUrls(
        array $filters,
        string $sort,
        string $sortOrder,
        int $page,
        int $perPage,
        CurrentUser $currentUser
    ): object {
        $filters = array_merge($filters, ["userId" => $currentUser->getId(), 'status' => CarSearchUrlStatus::Active->value]);
        $items = [];
        $totalCount = $this->carSearchUrlSearch->getTotalRecords(
            filters: $filters,
        );

        if ($totalCount) {
            $totalPages = ceil($totalCount / $perPage);

            if ($page > $totalPages) {
                $page = $totalPages;
            }

            $items = $this->carSearchUrlSearch->search(
                fields: ["carSearchUrl.*"],
                filters: $filters,
                joinsWith: [],
                sort: "{$sort} {$sortOrder}",
                perPage: $perPage,
                offset: ($page - 1) * $perPage,
            );

            foreach ($items as &$carSearchUrl) {
                $carSearchUrl = $this->hydrateToCarSearchUrlCard($carSearchUrl);
            }
        }

        return (object)compact("items", "totalCount", "page");
    }


    protected function addCarSearchUrlFromArray(
        array $requestData
    ): array {
        $requestData = (object)$requestData;
        $carSearchUrlModel = new CarSearchUrlModel();
        $carSearchUrlModel->title = $requestData->title;
        $carSearchUrlModel->url = $requestData->url;
        $carSearchUrlModel->userId = $requestData->userId;
        $carSearchUrlModel->filters = json_decode($requestData->filters);
        $carSearchUrlModel->save();
        $totalCount = $this->getCarSearchUrlsCount($carSearchUrlModel->userId);

        return ['id' => $carSearchUrlModel->id, 'totalCount' => $totalCount];
    }

    protected function updateCarSearchUrlFromArray(
        array $requestData
    ): int {
        $requestData = (object)$requestData;
        $carSearchUrlModel = $this->findById($requestData->id);
        $carSearchUrlModel->title = $requestData->title;
        $carSearchUrlModel->url = $requestData->url;
        $carSearchUrlModel->filters = json_decode($requestData->filters);
        $carSearchUrlModel->save();

        return $carSearchUrlModel->id;
    }

    protected function removeCarSearchUrlFromArray(
        array $requestData
    ): int {
        $requestData = (object)$requestData;
        $carSearchUrlModel = $this->findById($requestData->id);
        CarSearchUrlModel::deleteAllRecords(['id' => $requestData->id]);

        return $this->getCarSearchUrlsCount($carSearchUrlModel->userId);
    }

    protected function deleteCarSearchUrlFromArray(
        array $requestData
    ): int {
        $requestData = (object)$requestData;
        $carSearchUrlModel = $this->findById($requestData->id);
        CarSearchUrlModel::updateAllRecords(['status' => CarSearchUrlStatus::Deleted->value], ['id' => $requestData->id]);

        return $this->getCarSearchUrlsCount($carSearchUrlModel->userId);
    }

    protected function restoreCarSearchUrlFromArray(
        array $requestData
    ): int {
        $requestData = (object)$requestData;
        $carSearchUrlModel = $this->findById($requestData->id);
        CarSearchUrlModel::updateAllRecords(['status' => CarSearchUrlStatus::Active->value], ['id' => $requestData->id]);

        return $this->getCarSearchUrlsCount($carSearchUrlModel->userId);
    }

    protected function checkCarSearchUrlFromArray(
        array $requestData
    ): ?CarSearchUrlModel {
        $requestData = (object)$requestData;

        return $this->findByUrl($requestData->url, $requestData->userId);
    }

    protected function getCurrentUrlInCarSearchUrls(
        string $currentUrl,
        int $userId,
    ): ?object {
        $currentUrl = str_replace("-ajax", "", $currentUrl);
        $path = parse_url($currentUrl, PHP_URL_PATH);
        $query = parse_url($currentUrl, PHP_URL_QUERY);
        parse_str($query, $arrQuery);
        unset($arrQuery['page']);
        unset($arrQuery['perPage']);
        $query = http_build_query($arrQuery);
        $query = preg_replace_callback('/(\w+)%5B\d+%5D/', function ($matches) {
            return $matches[1] . '%5B%5D';
        }, $query);
        $url = "{$path}?{$query}";
        $carSearchUrlModel = $this->findByUrl($url, $userId);

        return $carSearchUrlModel ? $this->hydrateModelToObject($carSearchUrlModel) : null;
    }

    protected function clearDeletedCarSearchUrls()
    {
        CarSearchUrlModel::deleteAllRecords(['status' => CarSearchUrlStatus::Deleted->value]);
    }





    private function hydrateToCarSearchUrlCard(
        CarSearchUrlModel $carSearchUrlModel
    ): object {
        $carSearchUrl = $this->hydrateModelToObject($carSearchUrlModel);

        return $carSearchUrl;
    }

    private function extractQueryFiltersParameters(
        string $url
    ): array {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $unsetKeys = ["sort", "sortOrder", "page", "perPage"];

        foreach ($unsetKeys as $key) {
            if (array_key_exists($key, $query)) {
                unset($query[$key]);
            }
        }

        return $query;
    }
}
