<?php

namespace App\Backend\Service;

use App\Backend\Component\GeoData\GeoDataInterface;
use App\Backend\Component\GeoData\GeoData;
use App\Backend\Exception\GeoCodeApiException;
use App\Backend\Model\Dealer\DealerModel;
use App\Backend\Model\User\UserModel;
use App\Backend\Model\Car\CarModel;
use App\Backend\Model\PostalCode\PostalCodeGeoDataModel;
use Yiisoft\Injector\Injector;

final class GeoService extends AbstractService
{
    public function __construct(
        protected GeoDataInterface $geoData,
        protected Injector $injector
    ) {
        parent::__construct($injector);
    }

    /**
     * Find
     */
    public function findPostalCodeGeoData(string $postalCode): ?PostalCodeGeoDataModel
    {
        return PostalCodeGeoDataModel::findOne(['postalCode' => $postalCode]);
    }





    protected function getGeoData(string $query, ?string $postalCode = null): ?GeoData
    {
        return $this->geoData->getGeoData($query, $postalCode);
    }

    protected function getGeoDataByPostalCode(string $postalCode): ?GeoData
    {
        return $this->geoData->getGeoDataByPostalCode($postalCode);
    }

    protected function setGeoDataForPostalCodeFromArray(
        array $requestData
    ): ?PostalCodeGeoDataModel {
        $requestData = (object)$requestData;
        $postalCode = str_replace(' ', '', $requestData->postalCode);

        if ($postalCode) {
            $postalCodeGeoDataModel = $this->findPostalCodeGeoData($postalCode);

            if (!$postalCodeGeoDataModel) {
                $geoData = $this->geoData->getGeoDataByPostalCode($postalCode);
                $postalCodeGeoDataModel = new PostalCodeGeoDataModel();
                $postalCodeGeoDataModel->postalCode = $postalCode;
                $postalCodeGeoDataModel->longitude = $geoData->longitude;
                $postalCodeGeoDataModel->latitude = $geoData->latitude;
                $postalCodeGeoDataModel->region = $geoData->region;
                $postalCodeGeoDataModel->province = $geoData->province;
                $postalCodeGeoDataModel->country = $geoData->country;
                $postalCodeGeoDataModel->save();
            }

            return $postalCodeGeoDataModel;
        }

        return null;
    }

    protected function getPostalCodeByGeoDataFromArray(
        array $requestData
    ): ?string {
        $requestData = (object)$requestData;

        return $this->geoData->getPostalCode($requestData->latitude, $requestData->longitude);
    }


    protected function fillDealersTableByGeoData(
        DealerService $dealerService
    ) {
        $data = $dealerService->searchDealers(filters: []);
        $dealers = $data->items;

        foreach ($dealers as $dealer) {
            try {
                $this->setDealerGeoData($dealer->id, $dealerService);
            } catch (GeoCodeApiException $e) {
            }

            usleep(500);
        }
    }

    protected function setDealerGeoData(
        int $dealerId,
        DealerService $dealerService
    ): ?DealerModel {
        $dealerModel = $dealerService->findById($dealerId);
        $query = "{$dealerModel->address}, {$dealerModel->province} {$dealerModel->postalCode}";
        $geoData = $this->geoData->getGeoData($query, $dealerModel->postalCode);
        $dealerModel->longitude = $geoData->longitude;
        $dealerModel->latitude = $geoData->latitude;
        $dealerModel->save();

        $this->setGeoDataForPostalCodeFromArray(["postalCode" => $dealerModel->postalCode]);

        return $dealerModel;
    }

    protected function fillUsersTableByGeoData(
        UserService $userService
    ) {
        $data = $userService->searchDealers(filters: []);
        $users = $data->items;

        foreach ($users as $user) {
            try {
                $this->setUserGeoData($user->id, $userService);
            } catch (GeoCodeApiException $e) {
            }

            usleep(500);
        }
    }

    protected function setUserGeoData(
        int $userId,
        UserService $userService
    ): ?UserModel {
        $userModel = $userService->findById($userId);

        if ($userModel->address || $userModel->province || $userModel->postalCode) {
            $query = "{$userModel->address}, {$userModel->province} {$userModel->postalCode}";
            $geoData = $this->geoData->getGeoData($query, $userModel->postalCode);
            $userModel->longitude = $geoData->longitude;
            $userModel->latitude = $geoData->latitude;
            $userModel->save();
        } else {
            $userModel->longitude = $userModel->latitude = null;
            $userModel->save();
        }

        return $userModel;
    }

    protected function setCarGeoData(
        int $carId,
        CarService $carService
    ): ?CarModel {
        $carModel = $carService->findById($carId);

        if ($carModel->address || $carModel->province || $carModel->postalCode) {
            $query = "{$carModel->address}, {$carModel->province} {$carModel->postalCode}";
            $geoData = $this->geoData->getGeoData($query, $carModel->postalCode);
            $carModel->longitude = $geoData->longitude;
            $carModel->latitude = $geoData->latitude;
            $carModel->save();
        } else {
            $carModel->longitude = $carModel->latitude = null;
            $carModel->save();
        }

        return $carModel;
    }
}
