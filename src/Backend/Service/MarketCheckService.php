<?php

namespace App\Backend\Service;

use App\Backend\Exception\CarApiException;
use App\Backend\Model\Car\BodyType;
use App\Backend\Model\Car\Drivetrain;
use App\Backend\Model\Car\FuelType;
use App\Backend\Model\Car\Transmission;
use App\Backend\Model\Car\VehicleType;
use App\Backend\Model\MarketCheck\MarketCheckVinDecoderModel;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Config\ConfigInterface;
use PHPCurl\CurlWrapper\CurlInterface;
use Yiisoft\Injector\Injector;
use Yiisoft\Translator\TranslatorInterface;

final class MarketCheckService extends AbstractService
{
    public function __construct(
        protected CurlInterface $curl,
        protected ConfigInterface $config,
        protected Aliases $aliases,
        protected Injector $injector,
    ) {
        parent::__construct($injector);
    }





    protected function getCarDataByVinCode(
        string $vinCode,
        CarMakeService $carMakeService,
        CarModelService $carModelService,
        TranslatorInterface $translator
    ): object {
        $model = MarketCheckVinDecoderModel::findOne(['vinCode' => $vinCode]);

        if ($model) {
            $response = (object)$model->responseData;
        } else {
            $vinDecoderApi = new VinDecoderApi($this->curl, $this->config, $this->aliases);
            $response = $vinDecoderApi->getCarDataByVinCode($vinCode);
        }

        if (!$response->httpCode) {
            throw new CarApiException($translator->translate("Invalid CAR API URL or no connection"), 404);
        }

        if (!$model && isset($response->is_valid)) {
            // Ex: {"code": 422, "message": "Unable to decode 2C3CCAG27NH154692", "httpCode": 422, "is_valid": false}
            // Ex: {"is_valid": true, "httpCode": 200, "make": "Ford", "trim": "SE", "year": 2012, "doors": 4, "model": "Focus", "engine": "2.0L I4", ...}
            $model = new MarketCheckVinDecoderModel();
            $model->vinCode = $vinCode;
            $model->responseData = $response;
            $model->save();
        }

        if ($response->is_valid == false || $response->httpCode == 422) {
            $message = $response->message ?? $translator->translate("Unable to decode {vinCode}", ["vinCode" => $vinCode]);
            // Ex: {"code": 422, "message": "Unable to decode 2C3CCAG27NH154692", "httpCode": 422, "is_valid": false}
            // Ex: {"is_valid":false,"decode_mode":"squishVIN","year":2012,"make":"Ford","model":"Focus","trim":"SE","body_type":"Sedan", ...}
            throw new CarApiException($message, 422);
        }

        $make = $carMakeService->findByName($response->make);
        $model = $carModelService->findByName($response->model);
        $returnData = new \stdClass();
        $returnData->vinCode = $vinCode;
        $returnData->makeId = $make?->id;
        $returnData->modelId = $model?->id;
        $returnData->trim = $response->trim;
        $returnData->year = $response->year;
        $returnData->bodyType = $this->convertBodyTypesValues($response->body_type)?->value;
        $returnData->mileage = $response->highway_mpg ?? null;
        $returnData->vehicleType = $this->convertVehicleTypeValues($response->vehicle_type)?->value;
        $returnData->transmission = $this->convertTransmissionValues($response->transmission)?->value;
        $returnData->drivetrain = $this->convertDrivetrainValues($response->drivetrain)?->value;
        $returnData->fuelType = $this->convertFuelTypeValues($response->fuel_type)?->value;
        $returnData->engine = $response->engine_size ?? null;
        $returnData->engineType = $response->engine ?? null;
        $returnData->cylinders = $response->cylinders ?? null;
        $returnData->doors = $response->doors ?? null;
        $returnData->madeIn = $response->made_in ?? null;

        return $returnData;
    }

    protected function getAllMakes(): array
    {
        $countries = ["us", "ca"];
        $inventoryApi = new InventoryApi($this->curl, $this->config, $this->aliases);
        $makes = [];

        foreach ($countries as $country) {
            $result = $inventoryApi->getMakes($country);

            if ($result) {
                /* Ex:
                {
                    "num_found": 546892,
                    "listings": [],
                    "facets": {
                                "make": [
                                        {
                                            "item": "Ford",
                                            "count": 80852
                                        },
                                        {
                                            "item": "Chevrolet",
                                            "count": 42887
                                        },
                                        .....
                                ]
                    }
                }
                */
                $facets = $result->facets;

                foreach ($facets->make as $make) {
                    $makes[] = $make->item;
                }
            }
        }

        $makes = array_unique($makes);
        sort($makes);

        return $makes;
    }

    protected function getModels(
        string $make
    ): array {
        $inventoryApi = new InventoryApi($this->curl, $this->config, $this->aliases);
        $models = [];
        $result = $inventoryApi->getModels($make);

        if ($result) {
            /* Ex:
            {
                "num_found": 546892,
                "listings": [],
                "facets": {
                            "model": [
                                        {
                                            "item": "XT5",
                                            "count": 14150
                                        },
                                        {
                                            "item": "Escalade",
                                            "count": 11794
                                        },
                                        .....
                            ]

                }
            }
            */
            $facets = $result->facets;

            foreach ($facets->model as $model) {
                $models[] = $model->item;
            }
        }

        $models = array_unique($models);
        sort($models);

        return $models;
    }





    private function convertBodyTypesValues(string $bodyType): ?BodyType
    {
        return match ($bodyType) {
            "SUV" => BodyType::Suv,
            "Sedan" => BodyType::Sedan,
            "Pickup" => BodyType::Pickup,
            "Coupe", "Convertible", "Micro Car", "Targa" => BodyType::Coupe,
            "Wagon", "Combi", "Commercial Wagon", "Hatchback" => BodyType::Wagon,
            "Cargo Van", "Chassis Cab", "Chassis Cowl", "Cutaway" => BodyType::Truck,
            "Van", "Passenger Van", "Car Van" => BodyType::Van,
            "Minivan", "Mini Mpv" => BodyType::Minivan,
            default => null
        };
    }

    private function convertTransmissionValues(string $transmission): ?Transmission
    {
        return match ($transmission) {
            "Automatic" => Transmission::Automatic,
            "CVT" => Transmission::Variator,
            "Manual" => Transmission::Manual,
            "Robot" => Transmission::Robot,
            default => null
        };
    }

    private function convertDrivetrainValues(string $drivetrain): ?Drivetrain
    {
        return match ($drivetrain) {
            "4WD" => Drivetrain::FourWD,
            "FWD" => Drivetrain::FWD,
            "RWD" => Drivetrain::RWD,
            "RWD;4WD" => Drivetrain::RWD4WD,
            default => null
        };
    }

    private function convertFuelTypeValues(string $fuelType): ?FuelType
    {
        return match ($fuelType) {
            "Diesel" => FuelType::Diesel,
            "Biodiesel" => FuelType::Biodiesel,
            "E85", "E85 / Premium Unleaded", "E85 / Unleaded", "E85 / Unleaded; Unleaded","E85 / Unleaded; Unleaded / E85" => FuelType::E85,
            "M85 / Unleaded" => FuelType::M85,
            "Electric", "Electric / E85", "Electric / Hydrogen", "Electric / Premium Unleaded", "Electric / Unleaded" => FuelType::Electric,
            "Compressed Natural Gas", "Compressed Natural Gas / Lpg", "Compressed Natural Gas / Unleaded" => FuelType::Gas,
            "Hydrogen" => FuelType::Hydrogen,
            "Unleaded", "Unleaded / E85", "Unleaded / Electric", "Unleaded / Natural Gas", "Unleaded / Premium Unleaded" => FuelType::Unleaded,
            "Premium Unleaded", "Premium Unleaded / E85", "Premium Unleaded / Natural Gas", "Premium Unleaded / Unleaded" => FuelType::Premium,

            default => null
        };
    }

    private function convertVehicleTypeValues(string $vehicleType): ?VehicleType
    {
        return match ($vehicleType) {
            "Car" => VehicleType::Car,
            "Truck" => VehicleType::Truck,
            default => null
        };
    }
}
