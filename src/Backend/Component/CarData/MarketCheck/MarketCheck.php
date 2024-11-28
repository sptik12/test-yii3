<?php

namespace App\Backend\Component\CarData\MarketCheck;

use App\Backend\Component\CarData\CarData;
use App\Backend\Component\CarData\CarDataInterface;
use App\Backend\Component\CarData\CarMakeData;
use App\Backend\Component\CarData\CarModelData;
use App\Backend\Exception\CarApiException;
use App\Backend\Exception\ValidationException;
use App\Backend\Model\Car\BedSize;
use App\Backend\Model\Car\BodyType;
use App\Backend\Model\Car\CabinSize;
use App\Backend\Model\Car\Condition;
use App\Backend\Model\Car\Drivetrain;
use App\Backend\Model\Car\ExtColor;
use App\Backend\Model\Car\FuelType;
use App\Backend\Model\Car\IntColor;
use App\Backend\Model\Car\SafetyRating;
use App\Backend\Model\Car\Transmission;
use App\Backend\Model\Car\VehicleType;
use App\Backend\Model\MarketCheck\MarketCheckVinDecoderModel;
use App\Backend\Service\CarMakeService;
use App\Backend\Service\CarModelService;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Config\ConfigInterface;
use PHPCurl\CurlWrapper\CurlInterface;
use Yiisoft\Translator\TranslatorInterface;

final class MarketCheck implements CarDataInterface
{
    public function __construct(
        protected CarMakeService $carMakeService,
        protected CarModelService $carModelService,
        protected CurlInterface $curl,
        protected ConfigInterface $config,
        protected Aliases $aliases,
        protected TranslatorInterface $translator
    ) {
    }


    public function getCarDataByVinCode(
        string $vinCode
    ): CarData {
        $model = MarketCheckVinDecoderModel::findOne(['vinCode' => $vinCode]);

        if ($model) {
            $response = (object)$model->responseData;
        } else {
            $vinDecoderApi = new VinDecoderApi($this->curl, $this->config, $this->aliases);
            $response = $vinDecoderApi->getCarDataByVinCode($vinCode);
        }

        if (!$response->httpCode) {
            throw new CarApiException($this->translator->translate("Invalid MarketCheck API URL or no connection"), 404);
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
            $message = $response->message ?? $this->translator->translate("Unable to decode {vinCode}", ["vinCode" => $vinCode]);
            // Ex: {"code": 422, "message": "Unable to decode 2C3CCAG27NH154692", "httpCode": 422, "is_valid": false}
            // Ex: {"is_valid":false,"decode_mode":"squishVIN","year":2012,"make":"Ford","model":"Focus","trim":"SE","body_type":"Sedan", ...}
            throw new CarApiException($message, 422);
        }

        $make = $this->carMakeService->findByName($response->make);
        $model = $this->carModelService->findByName($response->model);

        // set default values for data that are present in api response
        $returnData = new CarData();
        $returnData->vinCode = $vinCode;
        $returnData->makeId = $make?->id;
        $returnData->modelId = $model?->id;
        $returnData->trim = $response?->trim ?? null;
        $returnData->year = $response?->year ?? null;
        $returnData->bodyType = $this->convertBodyTypesValues($response?->body_type ?? null)?->value;
        $returnData->mileage = $response?->highway_mpg ?? null;
        $returnData->vehicleType = $this->convertVehicleTypeValues($response?->vehicle_type ?? null)?->value;
        $returnData->transmission = $this->convertTransmissionValues($response?->transmission ?? null)?->value;
        $returnData->drivetrain = $this->convertDrivetrainValues($response?->drivetrain ?? null)?->value;
        $returnData->fuelType = $this->convertFuelTypeValues($response?->fuel_type ?? null)?->value;
        $returnData->engine = $response?->engine_size ?? null;
        $returnData->engineType = $response?->engine ?? null;
        $returnData->cylinders = $response?->cylinders ?? null;
        $returnData->doors = $response?->doors ?? null;
        $returnData->madeIn = $response?->made_in ?? null;

        // set default values for data that are not present in api response
        $returnData->condition = Condition::Used->value;
        $returnData->extColor = ExtColor::Unknown->value;
        $returnData->intColor = IntColor::Unknown->value;
        $returnData->safetyRating = SafetyRating::NoRating->value;
        $returnData->cabinSize = CabinSize::Unknown->value;
        $returnData->bedSize = BedSize::Unknown->value;
        $returnData->certifiedPreOwned = 0;
        $returnData->fuelEconomy = null;
        $returnData->co2 = null;
        $returnData->evBatteryRange = null;
        $returnData->evBatteryTime = null;
        $returnData->seats = null;
        $returnData->features = null;

        return $returnData;
    }

    public function getAllMakes(): array
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

    public function getModels(
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





    private function convertBodyTypesValues(?string $bodyType): ?BodyType
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

    private function convertTransmissionValues(?string $transmission): ?Transmission
    {
        return match ($transmission) {
            "Automatic" => Transmission::Automatic,
            "CVT" => Transmission::Variator,
            "Manual" => Transmission::Manual,
            "Robot" => Transmission::Robot,
            default => null
        };
    }

    private function convertDrivetrainValues(?string $drivetrain): ?Drivetrain
    {
        return match ($drivetrain) {
            "4WD" => Drivetrain::FourWD,
            "FWD" => Drivetrain::FWD,
            "RWD" => Drivetrain::RWD,
            "RWD;4WD" => Drivetrain::RWD4WD,
            default => null
        };
    }

    private function convertFuelTypeValues(?string $fuelType): ?FuelType
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

    private function convertVehicleTypeValues(?string $vehicleType): ?VehicleType
    {
        return match ($vehicleType) {
            "Car" => VehicleType::Car,
            "Truck" => VehicleType::Truck,
            default => VehicleType::Car
        };
    }
}
