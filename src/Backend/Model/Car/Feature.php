<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum Feature: string
{
    case BackupCamera = "backupCamera";
    case AllowWheels = "allowWheels";
    case Bluetooth = "bluetooth";
    case HeatedSeats = "heatedSeats";
    case CarPlay = "carPlay";
    case NavigationSystem = "navigationSystem";
    case AndroidAuto = "androidAuto";
    case RemoteStart = "remoteStart";
    case SunroofMoonroof = "sunroof/moonroof";
    case BlindSpotMonitoring = "blindSpotMonitoring";
    case LeatherSeats = "leatherSeats";
    case AdaptiveCruiseControl = "adaptiveCruiseControl";
    case ParkingSensors = "parkingSensors";
    case ThirdRowSeating = "thirdRowSeating";
    case QuickOrderPackage = "quickOrderPackage";
    case ConveniencePackage = "conveniencePackage";
    case SteelWheels = "steelWheels";
    case PremiumPackage = "premiumPackage";
    case TowPackage = "towPackage";
    case MultiZoneClimateControl = "multiZoneClimateControl";
    case AppearancePackage = "appearancePackage";
    case PreferredPackage = "preferredPackage";
    case TechnologyPackage = "technologyPackage";
    case AdaptiveSuspension = "adaptiveSuspension";
    case PowerPackage = "powerPackage";
    case SuspensionPackage = "suspensionPackage";
    case OffRoadPackage = "offRoadPackage";
    case TrailerPackage = "trailerPackage";
    case HeatPackage = "heatPackage";
    case SportPackage = "sportPackage";
    case CargoPackage = "cargoPackage";
    case LEPackage = "lePackage";
    case ChromeWheels = "chromeWheels";
    case StoragePackage = "storagePackage";
    case SafetyPackage = "safetyPackage";
    case LuxuryEquipmentGroup302A = "302aLuxuryEquipmentGroup";
    case LuxuryEquipmentGroup502A = "502aLuxuryEquipmentGroup";
    case Wheel5 = "5thWheel";
    case UtilityPackage = "utilityPackage";
    case ValuePackage = "valuePackage";
    case DriverAssistancePackage = "driverAssistancePackage";
    case CustomerPreferredPackage = "customerPreferredPackage";
    case XLTPackage = "xltPackage";
    case LTPackage = "ltPackage";
    case DualRearWheels = "dualRearWheels";
    case MidEquipmentGroup301A = "301aMidEquipmentGroup";
    case MidEquipmentGroup501A = "501aMidEquipmentGroup";
    case LSPackage = "lsPackage";
    case PerformancePackage = "performancePackage";
    case UpgradePackage = "upgradePackage";
    case Lifted = "lifted";
    case ElitePackage = "elitePackage";
    case SLineSportPackage = "sLineSportPackage";
    case ColdWeatherPackage = "coldWeatherPackage";

    public static function fromName(string $name): ?static
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        return null;
    }

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::BackupCamera => "Backup Camera",
            self::AllowWheels => "Allow Wheels",
            self::Bluetooth => "Bluetooth",
            self::HeatedSeats => "Heated Seats",
            self::CarPlay => "CarPlay",
            self::NavigationSystem => "Navigation System",
            self::AndroidAuto => "Android Auto",
            self::RemoteStart => "Remote Start",
            self::SunroofMoonroof => "Sunroof/Moonroof",
            self::BlindSpotMonitoring => "Blind Spot Monitoring",
            self::LeatherSeats => "Leather Seats",
            self::AdaptiveCruiseControl => "Adaptive Cruise Control",
            self::ParkingSensors => "Parking Sensors",
            self::ThirdRowSeating => "Third Row Seating",
            self::QuickOrderPackage => "Quick Order Package",
            self::ConveniencePackage => "Convenience Package",
            self::SteelWheels => "Steel Wheels",
            self::PremiumPackage => "Premium Package",
            self::TowPackage => "Tow Package",
            self::MultiZoneClimateControl => "Multi Zone Climate Control",
            self::AppearancePackage => "Appearance Package",
            self::PreferredPackage => "Preferred Package",
            self::TechnologyPackage => "Technology Package",
            self::AdaptiveSuspension => "Adaptive Suspension",
            self::PowerPackage => "Power Package",
            self::SuspensionPackage => "Suspension Package",
            self::OffRoadPackage => "Off Road Package",
            self::TrailerPackage => "Trailer Package",
            self::HeatPackage => "Heat Package",
            self::SportPackage => "Sport Package",
            self::CargoPackage => "Cargo Package",
            self::LEPackage => "LE Package",
            self::ChromeWheels => "Chrome Wheels",
            self::StoragePackage => "Storage Package",
            self::SafetyPackage => "Safety Package",
            self::LuxuryEquipmentGroup302A => "302A Luxury Equipment Group",
            self::LuxuryEquipmentGroup502A => "502A Luxury Equipment Group",
            self::Wheel5 => "5th Wheel",
            self::UtilityPackage => "Utility Package",
            self::ValuePackage => "Value Package",
            self::DriverAssistancePackage => "Driver Assistance Package",
            self::CustomerPreferredPackage => "Customer Preferred Package",
            self::XLTPackage => "XLT Package",
            self::LTPackage => "LT Package",
            self::DualRearWheels => "Dual Rear Wheels",
            self::MidEquipmentGroup301A => "301A Mid Equipment Group",
            self::MidEquipmentGroup501A => "501A Mid Equipment Group",
            self::LSPackage => "LS Package",
            self::PerformancePackage => "Performance Package",
            self::UpgradePackage => "Upgrade Package",
            self::Lifted => "Lifted",
            self::ElitePackage => "Elite Package",
            self::SLineSportPackage => "S Line Sport Package",
            self::ColdWeatherPackage => "Cold Weather Package"
        };

        return $translator->translate($title);
    }
}
