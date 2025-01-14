<?php

namespace App\Helpers;

class FilamentHelper
{
    public static function textEntryExtraAttributes(): array
    {
        return [
            'class' => 'border p-2 border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700',
        ];
    }

    public static function textEntryExtraAttributesSuccess(): array
    {
        return [
            'class' => 'border p-2 border-green-200 rounded-lg bg-green-50 dark:bg-green-800 dark:border-green-700',
        ];
    }

    public static function textEntryExtraAttributesError(): array
    {
        return [
            'class' => 'border p-2 border-red-200 rounded-lg bg-red-50 dark:bg-red-800 dark:border-red-700',
        ];
    }

    public static function textEntryExtraAttributesProse(): array
    {
        return [
            'class' => 'border p-2 border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700 prose dark:prose-dark max-w-none',
        ];
    }
}
