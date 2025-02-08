<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class Helper
{
    public static function rupiah($number)
    {
        $string = "Rp " . number_format($number, 2, ',', '.');
        return $string;
    }

    public static function sanitizeBase64Image($imageBase64)
    {
        $imageBase64 = str_replace('data:image/png;base64,', '', $imageBase64);
        $imageBase64 = str_replace(' ', '+', $imageBase64);

        return $imageBase64;
    }


    public static function getFileAsBase64($filename)
    {
        $fileContent = Storage::disk('local')->get($filename);
        $base64String = base64_encode($fileContent);
        $mimeType = Storage::disk('local')->mimeType($filename);
        $base64WithPrefix = 'data:' . $mimeType . ';base64,' . $base64String;

        return $base64WithPrefix;
    }

    public static function deleteFileStorage($filename)
    {
        if (Storage::disk('local')->exists($filename)) {
            Storage::disk('local')->delete($filename);
        }
    }
}