<?php

namespace App\Frontend\Helper;

use Yiisoft\Config\ConfigInterface;

class FormatHelper
{
    public static function formatPhone($phone): string
    {
        return preg_replace("/[^0-9]/", "", $phone);
    }

    public static function formatDate(string $datetime, ConfigInterface $config)
    {
        $params = $config->get('params');
        $dateFormat = $params['formats']['dateFormat'];

        return date($dateFormat, strtotime($datetime));
    }

    public static function formatDateShort(string $datetime, ConfigInterface $config)
    {
        $params = $config->get('params');
        $dateFormat = $params['formats']['dateFormatShort'];

        return date($dateFormat, strtotime($datetime));
    }


    public static function formatDateLong(string $datetime, ConfigInterface $config)
    {
        $params = $config->get('params');
        $dateFormat = $params['formats']['dateFormatLong'];

        return date($dateFormat, strtotime($datetime));
    }

    public static function formatTime(string $datetime, ConfigInterface $config)
    {
        $params = $config->get('params');
        $dateFormat = $params['formats']['timeFormat'];

        return date($dateFormat, strtotime($datetime));
    }

    public static function formatDateTimeShort(string $datetime, ConfigInterface $config)
    {
        return self::formatDateShort($datetime, $config) . ' ' . self::formatTime($datetime, $config);
    }

    public static function formatMoney(ConfigInterface $config, $value, int $decimals = 2): string
    {
        $params = $config->get('params');
        $currency = $params['formats']['currentCurrency'];

        return $currency . number_format($value, $decimals);
    }

    public static function formatPercent($value)
    {
        return round($value, 2) . "%";
    }

    public static function formatRating($value)
    {
        return number_format($value, 1);
    }
}
