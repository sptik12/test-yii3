<?php

namespace App\Frontend\Helper;

use Yiisoft\Translator\TranslatorInterface;

class Ancillary
{
    public static function mergeObjects(object $firstObj, object $secondObj): object
    {
        foreach ($secondObj as $name => $value) {
            $firstObj->{$name} = $value;
        }

        return $firstObj;
    }

    public static function forJs(object|array $var): string
    {
        $encoded = rawurlencode(json_encode($var));

        return "JSON.parse(decodeURIComponent('{$encoded}'))";
    }

    public static function selectedIf(?object $filledObject, string $field, mixed $value): string
    {
        return ($filledObject->{$field} ?? "") == $value ? "selected" : "";
    }

    public static function checkedIf(?object $filledObject, string $field): string
    {
        return ($filledObject->{$field} ?? 0) ? "checked" : "";
    }

    public static function classIf(object|array $filledObject, string|array $fields, string $className): string
    {
        $filledObject = (object)$filledObject;

        if (!is_array($fields)) {
            $fields = [$fields];
        }

        $res = "";

        foreach ($fields as $field) {
            if (!empty($filledObject->{$field})) {
                $res = $className;
                break;
            }
        }

        return $res;
    }

    public static function classNotIf(object|array $filledObject, string|array $fields, string $className): string
    {
        $res = self::classIf($filledObject, $fields, $className);

        return $res ? "" : $className;
    }

    public static function hasValueOrEmpty(object|array $filledObject, string $field, mixed $value): bool
    {
        $filledObject = (object)$filledObject;

        if (!property_exists($filledObject, $field)) {
            return true;
        }

        return $filledObject->{$field} == $value;
    }

    public static function getYearsOptions(): string
    {
        $result = "";

        for ($i = date("Y"); $i >= 1990; $i--) {
            $result .= "<option value='{$i}'>{$i}</option>";
        }

        return $result;
    }

    public static function getTermsOfUseLink(TranslatorInterface $translator): string
    {
        return "<a href='/terms' target='_blank'>{$translator->translate("Terms Of Use")}</a>";
    }

    public static function getPrivacyStatementLink(TranslatorInterface $translator): string
    {
        return "<a href='/privacy' target='_blank'>{$translator->translate("Privacy Statement")}</a>";
    }

    public static function getCarfaxLink(TranslatorInterface $translator, $label = 'here'): string
    {
        return "<a href='https://www.carfax.ca' target='_blank'>{$translator->translate($label)}</a>";
    }


    // tmp func
    public static function getTranslatedListFromArray(?array $items, TranslatorInterface $translator): ?string
    {
        return $items
            ? implode(", ", array_filter($items, fn($feature) => $translator->translate($feature)))
            : null;
    }
}
