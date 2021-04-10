<?php

namespace App\Services;

class Utils
{
    public static function isJson($string): bool
    {
        json_decode($string, true);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}