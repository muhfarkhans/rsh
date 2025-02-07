<?php

namespace App\Constants;

class PaymentMethod
{
    const CASH = 'cash';
    const QRIS = 'qris';
    const WAITING_FOR_PAYMENT = 'waiting_for_payment';

    public static function getLabels()
    {
        return [
                // self::WAITING_FOR_PAYMENT => 'Waiting For Payment',
            self::CASH => 'Cash',
            self::QRIS => 'Qris',
        ];
    }

    public static function getLabel($status)
    {
        return self::getLabels()[$status];
    }
}