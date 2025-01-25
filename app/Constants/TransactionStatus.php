<?php

namespace App\Constants;

class TransactionStatus
{
    const WAITING_FOR_PAYMENT = 'waiting_for_payment';
    const EXPIRED = 'expired';
    const CANCEL = 'cancel';
    const PAID = 'paid';
    const UNPAID = 'unpaid';

    public static function getLabels()
    {
        return [
            self::WAITING_FOR_PAYMENT => 'Waiting For Payment',
                // self::EXPIRED => 'Expired',
            self::CANCEL => 'Cancel',
            self::PAID => 'Paid',
            // self::UNPAID => 'Unpaid',
        ];
    }

    public static function getLabel($status)
    {
        return self::getLabels()[$status];
    }
}