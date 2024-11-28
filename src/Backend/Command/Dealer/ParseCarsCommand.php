<?php

namespace App\Backend\Command\Dealer;

use App\Backend\Model\Car\BodyType;
use App\Backend\Model\Car\CarMakeModel;
use App\Backend\Model\Car\CarModel;
use App\Backend\Model\Car\CarModelModel;
use App\Backend\Model\Car\CarStatus;
use App\Backend\Model\Car\Condition;
use App\Backend\Model\Car\Drivetrain;
use App\Backend\Model\Car\ExtColor;
use App\Backend\Model\Car\FuelType;
use App\Backend\Model\Car\IntColor;
use App\Backend\Model\Car\Transmission;
use App\Backend\Model\Car\VehicleType;
use App\Backend\Model\Dealer\DealerCarParserProgressModel;
use App\Backend\Model\Dealer\DealerModel;
use App\Backend\Service\CarService;
use HttpSoft\Message\StreamFactory;
use HttpSoft\Message\UploadedFileFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Aliases\Aliases;

class ParseCarsCommand extends Command
{
    public function __construct(
        private readonly CarService $carService,
        private readonly LoggerInterface $logger,
        private readonly Aliases $aliases,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription("Parse dealer cars from https://www.autotrader.ca");
    }





    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dealers = DealerModel::find()
            ->where(['is not', 'dealer.autotraderUrl', null])
            ->all();

        if (empty($dealers)) {
            throw new \RuntimeException("Dealers with the specified Autotrader URL were not found");
        }

        foreach ($dealers as $dealer) {
            try {
                $this->print("Processing dealer #{$dealer->id}");
                $this->parseDealerCars($dealer);
                $this->print("Finished dealer #{$dealer->id}");
            } catch (\Throwable $e) {
                $this->print("Unable to parse dealer #{$dealer->id}. Error: {$e->getMessage()}");
                $this->logger->error("Unable to parse dealer #{$dealer->id} cars. Error:");
                $this->logger->error($e);
            }
        }

        return Command::SUCCESS;
    }





    private function parseDealerCars(DealerModel $dealer): void
    {
        $dealerCarUrls = $this->parseCarUrlsFromDealerInventoryPage($dealer->autotraderUrl);
        $carsTotal = count($dealerCarUrls);
        $this->print("Found {$carsTotal} cars");

        /* Create parser progress entry */
        $dealerCarParserProgress = new DealerCarParserProgressModel();
        $dealerCarParserProgress->dealerId = $dealer->id;
        $dealerCarParserProgress->carsTotal = $carsTotal;
        $dealerCarParserProgress->started = date("Y-m-d H:i:s");
        $dealerCarParserProgress->save();

        foreach ($dealerCarUrls as $index => $dealerCarUrl) {
            /* Parse car data */
            $car = $this->parseCarDataFromCarPage($dealerCarUrl, $index, $carsTotal);

            /* Save car to database */
            $carModel = $this->hydrateToCarModel($car, $dealer->id);
            $carModel->save();

            /* Upload car medias */
            foreach ($car->medias as $index => $media) {
                $pathInfo = pathinfo($media->photoViewerUrl);

                // Upload temporary file
                $tmpPath = $this->aliases->get("@runtime") . uniqid();
                copy($media->photoViewerUrl, $tmpPath);

                // Get filename and mime
                $mimeType = $this->getFileMimeType($tmpPath);
                $stream = (new StreamFactory())->createStreamFromFile($tmpPath);
                $uploadedFile = (new UploadedFileFactory())->createUploadedFile(
                    stream: $stream,
                    size: filesize($tmpPath),
                    clientFilename: $pathInfo['basename'],
                    clientMediaType: $mimeType
                );

                // Remove temporary file
                unlink($tmpPath);

                // Upload
                $this->carService->uploadMedia($carModel, $uploadedFile, isMain: $index == 0);
            }

            /* Update processed cars in parser progress */
            $dealerCarParserProgress->carsProcessed++;
            $dealerCarParserProgress->save();
        }

        /* Save finish time to parser progress */
        $dealerCarParserProgress->finished = date("Y-m-d H:i:s");
        $dealerCarParserProgress->save();
    }

    private function parseCarUrlsFromDealerInventoryPage(string $dealerInventoryPageUrl): array
    {
        $dealerInventoryPage = $this->request($dealerInventoryPageUrl);

        // <a _ngcontent-serverapp-c38="" class="cars-for-sale-big" href="https://www.autotrader.ca/a/toyota/tacoma/saint john/new brunswick/5_63822694_20130910105140504" data-adid="5-63822694">
        if (!preg_match_all("/class=\".*?cars-for-sale-big.*?\".*?href=\"(.*?)\".*?/i", $dealerInventoryPage, $match)) {
            throw new \RuntimeException("Dealer cars not found");
        }

        return $match[1] ?? [];
    }

    private function parseCarDataFromCarPage(string $carPageUrl, int $index, int $totalCars): object
    {
        $carNumber = $index + 1;
        $this->print("Processing car #{$carNumber} of {$totalCars}...");
        $carPage = $this->request(str_replace(" ", "+", $carPageUrl), true);

        // gtmManager.initializeDataLayer({"vehicle":{"adID":"5-63822694","category":"Cars, Trucks & SUVs","condition":"used",...})
        if (!preg_match("/gtmManager.initializeDataLayer\((.*?)\)/i", $carPage, $match)) {
            throw new \RuntimeException("Unable to parse car info");
        }

        $parsedCarData = json_decode($match[1]);
        $carData = [
            'url' => $carPageUrl,
            'make' => $parsedCarData->vehicle->make,
            'model' => $parsedCarData->vehicle->model,
            'year' => $parsedCarData->vehicle->year,
            'price' => $parsedCarData->vehicle->price,
            'condition' => $parsedCarData->vehicle->condition,
        ];

        // window['ngVdpModel'] = {"currentCulture":"en",...}
        if (!preg_match("/window\['ngVdpModel'\]\s*=\s*(.*?})\;/i", $carPage, $match)) {
            throw new \RuntimeException("Unable to parse car info");
        }

        $parsedCarData = json_decode($match[1]);
        $specs = $parsedCarData->specifications->specs;
        $carData['vinCode'] = $parsedCarData->adBasicInfo->vin;
        $carData['mileage'] = $parsedCarData->adBasicInfo->odometer;
        $carData['trim'] = $parsedCarData->hero->trim;
        $carData['bodyType'] = match ($parsedCarData->adBasicInfo->splashBodyType) {
            'crew cab' => BodyType::Truck->value,
            default => $parsedCarData->adBasicInfo->splashBodyType
        };
        $carData['vehicleType'] = match ($parsedCarData->adBasicInfo->microSite) {
            'cars' => VehicleType::Car->value,
            default => $parsedCarData->adBasicInfo->microSite
        };
        $carData['transmission'] = $this->fetchSpecValue($specs, "Transmission");
        $carData['drivetrain'] = $this->fetchSpecValue($specs, "Drivetrain");
        $carData['fuelType'] = $this->fetchSpecValue($specs, "Fuel Type");
        $carData['fuelEconomy'] = $this->fetchSpecValue($specs, "City Fuel Economy");
        $carData['engine'] = $this->fetchSpecValue($specs, "Engine");
        $carData['engineType'] = $this->fetchSpecValue($specs, "Engine");
        $carData['cylinder'] = $this->fetchSpecValue($specs, "Cylinder");
        $carData['doors'] = $this->fetchSpecValue($specs, "Doors");
        $carData['seats'] = $this->fetchSpecValue($specs, "Passengers");
        $carData['extColor'] = $this->fetchSpecValue($specs, "Exterior Colour");
        $carData['intColor'] = $this->fetchSpecValue($specs, "Interior Colour");
        $carData['features'] = $parsedCarData->featureHighlights->highlights ?? [];
        $carData['description'] = $parsedCarData->description->description[0]->description ?? null;
        $carData['stockNumber'] = $this->fetchSpecValue($specs, "Stock Number");
        $carData['medias'] = $parsedCarData->gallery->items ?? [];
        $this->print("Finished car #{$carNumber} of {$totalCars}!");

        return (object)$carData;
    }

    private function fetchSpecValue(array $specs, string $keyName): ?string
    {
        $value = null;

        foreach ($specs as $spec) {
            if ($spec->key == $keyName) {
                $value = $spec->value;
                break;
            }
        }

        return $value;
    }

    private function hydrateToCarModel(object $car, int $dealerId): CarModel
    {
        $carModel = new CarModel();
        $carModel->dealerId = $dealerId;
        $carModel->isParsed = 1;
        $carModel->sourceUrl = $car->url;
        $carModel->publicId = $this->carService->generatePublicId();
        $carModel->status = CarStatus::Draft->value;
        $carModel->vinCode = $car->vinCode;
        $carModel->condition = $this->hydrateField($car, "condition");
        $carModel->year = $car->year;
        $carModel->makeId = CarMakeModel::findOne(['name' => $car->make])?->id ?? null;
        $carModel->modelId = CarModelModel::findOne(['makeId' => $carModel->makeId, 'name' => $car->model])?->id ?? null;
        $carModel->mileage = $this->hydrateField($car, "mileage");
        $carModel->trim = $this->hydrateField($car, "trim");
        $carModel->bodyType = $this->hydrateField($car, "bodyType");
        $carModel->vehicleType = $this->hydrateField($car, "vehicleType");
        $carModel->transmission = $this->hydrateField($car, "transmission"); // TODO: robot detecting
        $carModel->drivetrain = $this->hydrateField($car, "drivetrain"); // TODO: check more possible drivetrains
        $carModel->fuelType = $this->hydrateField($car, "fuelType"); // TODO: add hybrid fuelType
        $carModel->fuelEconomy = $this->hydrateField($car, "fuelEconomy"); // TODO: check why fuelEconomy is sometimes empty
        $carModel->engine = $this->hydrateField($car, "engine");
        $carModel->engineType = $this->hydrateField($car, "engineType"); // TODO: check more possible drivetrains
        $carModel->cylinders = $car->cylinder;
        $carModel->doors = !empty($car->doors) ? $car->doors : null;
        $carModel->seats = !empty($car->seats) ? $car->seats : null;
        $carModel->extColor = $this->hydrateField($car, "extColor");
        $carModel->intColor = $this->hydrateField($car, "intColor");
        $carModel->features = !empty($car->features) ? $car->features : null;
        $carModel->description = !empty($car->description) ? $car->description : null;
        $carModel->price = !empty($car->price) ? (float)$car->price : null;

        // Check required fields
        $requiredFields = ['vinCode', 'makeId', 'modelId'];

        foreach ($requiredFields as $fieldName) {
            if (empty($carModel->{$fieldName})) {
                throw new \RuntimeException("Unable to parse '{$fieldName}' for car {$car->url}");
            }
        }

        return $carModel;
    }

    private function hydrateField(object $parsedCarData, string $fieldName): ?string
    {
        $value = $parsedCarData->{$fieldName};

        switch ($fieldName) {
            case "condition":
                return Condition::tryFrom(lcfirst($value))?->value ?? null;
            case "mileage":
                return $value != "" ? preg_replace("/[^0-9]/i", "", $value) : null;
            case "trim":
                preg_match("/^(.*?)(\s+\|.*)?$/i", $value, $match);

                return $match[1] ?? null;
            case "bodyType":
                $bodyType = null;

                if (!empty($value)) {
                    $bodyType = match ($value) {
                        'hatchback' => BodyType::Wagon->value,
                        default => BodyType::tryFrom($value)?->value ?? null
                    };
                }

                return $bodyType;
            case "vehicleType":
                return VehicleType::tryFrom($value)?->value ?? null;
            case "transmission":
                $transmission = null;

                if (!empty($value)) {
                    $value = strtolower($value);

                    if (str_contains($value, "auto")) {
                        $transmission = Transmission::Automatic->value;
                    } elseif (str_contains($value, "manual")) {
                        $transmission = Transmission::Manual->value;
                    } elseif (str_contains($value, "cvt")) {
                        $transmission = Transmission::Variator->value;
                    }
                }

                return $transmission;
            case "drivetrain":
                $drivetrain = null;

                if (!empty($value)) {
                    $value = strtolower($value);
                    $drivetrain = match ($value) {
                        '4x4' => Drivetrain::FourWD->value,
                        'awd' => Drivetrain::RWD4WD->value,
                        default => Drivetrain::tryFrom($value)?->value ?? null,
                    };
                }

                return $drivetrain;
            case "fuelType":
                $fuelType = null;

                if (!empty($value)) {
                    $value = strtolower($value);
                    $fuelType = match ($value) {
                        'gasoline hybrid' => FuelType::Gas->value, // TODO: add hybrid fuelType
                        default => FuelType::tryFrom($value)?->value ?? null,
                    };
                }

                return $fuelType;
            case "fuelEconomy":
                $fuelEconomy = null;

                if (!empty($value)) {
                    preg_match("/^([0-9]+\.[0-9]+)/i", $value, $match);
                    $fuelEconomy = !empty($match[1]) ? (float)$match[1] : null;
                }

                return $fuelEconomy;
            case "engine":
                $engine = null;

                if (!empty($value) && preg_match("/([0-9]+(\.([0-9]+))?)L/i", $value, $match)) {
                    $engine = (float)$match[1];
                }

                return $engine;
            case "engineType":
                $engineType = null;

                if (!empty($value) && str_contains($value, "Engine")) {
                    $engineType = $value;
                }

                return $engineType;
            case "extColor":
                $color = ExtColor::Unknown->value;

                if (!empty($value)) {
                    $value = strtolower($value);

                    foreach (ExtColor::cases() as $possibleColor) {
                        if (str_contains($value, $possibleColor->value)) {
                            $color = $possibleColor->value;
                        }
                    }
                }

                return $color;
            case "intColor":
                $color = IntColor::Unknown->value;

                if (!empty($value)) {
                    $value = strtolower($value);

                    foreach (IntColor::cases() as $possibleColor) {
                        if (str_contains($value, $possibleColor->value)) {
                            $color = $possibleColor->value;
                        }
                    }
                }

                return $color;
        }

        return $value;
    }

    private function getFileMimeType(string $pathToFile): string
    {
        $file = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($file, $pathToFile);
        finfo_close($file);

        return $mime;
    }

    private function request(string $url, bool $useShellCurl = false): mixed
    {
        return $useShellCurl ? shell_exec("curl -s '{$url}'") : file_get_contents($url);
    }

    private function print(string $message): void
    {
        echo $message . PHP_EOL;
    }
}
