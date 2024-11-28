<?php

namespace App\Backend\Model\User;

use Yiisoft\Translator\TranslatorInterface;

enum Role: string
{
    case AdminSuperAdmin = "admin.superAdmin";
    case AdminAccountManager = "admin.accountManager";
    case DealerPrimary = "dealer.primary";
    case DealerSalesManager = "dealer.salesManager";
    case Client = "client";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::AdminSuperAdmin => 'Super Admin',
            self::AdminAccountManager => 'Account Manager',
            self::DealerPrimary => 'Dealer Primary',
            self::DealerSalesManager => 'Dealer Sales Manager',
            self::Client => 'Client',
        };

        return $translator->translate($title);
    }

    public function isAdminRole(): bool
    {
        return match ($this) {
            self::AdminSuperAdmin => true,
            self::AdminAccountManager => true,
            self::DealerPrimary => false,
            self::DealerSalesManager => false,
            self::Client => false,
        };
    }

    public function isDealerRole(): bool
    {
        return match ($this) {
            self::AdminSuperAdmin => false,
            self::AdminAccountManager => false,
            self::DealerPrimary => true,
            self::DealerSalesManager => true,
            self::Client => false,
        };
    }
}
