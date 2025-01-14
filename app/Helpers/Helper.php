<?php

namespace App\Helpers;

class Helper
{
    public static function rupiah($number)
    {
        $string = "Rp " . number_format($number, 2, ',', '.');
        return $string;
    }
}