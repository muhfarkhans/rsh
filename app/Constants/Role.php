<?php

namespace App\Constants;

class Role
{
    const SUPER_ADMIN = 'super_admin';
    const THERAPIST = 'therapist';
    const CASHIER = 'cashier';

    public static function getLabels()
    {
        return [
            self::SUPER_ADMIN => 'Super admin',
            self::THERAPIST => 'Therapist',
            self::CASHIER => 'Cashier',
        ];
    }

    public static function getLabel($status)
    {
        return self::getLabels()[$status];
    }
}