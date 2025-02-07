<?php

namespace App\Constants;

class VisitStatus
{
    const REGISTER = 'register';
    const WAITING_FOR_CHECK = 'waiting_for_check';
    const WAITING_FOR_SERVICE = 'waiting_for_service';
    const ON_SERVICE = 'on_service';
    const WAITING_FOR_PAYMENT = 'waiting_for_payment';
    const DONE = 'done';

    public static function getLabels()
    {
        return [
            self::REGISTER => 'Register',
            self::WAITING_FOR_CHECK => 'Waiting For Check',
            self::WAITING_FOR_SERVICE => 'Waiting For Service',
            self::ON_SERVICE => 'On Service',
            self::WAITING_FOR_PAYMENT => 'Waiting For Payment',
            self::DONE => 'Done',
        ];
    }

    public static function getLabel($status)
    {
        return self::getLabels()[$status];
    }
}