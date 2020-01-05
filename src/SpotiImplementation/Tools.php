<?php

namespace App\SpotiImplementation;

class Tools
{
    const SESSION_APISESSION   = 'api_session';

    public static function saveApiSession($session)
    {
        $_SESSION[static::SESSION_APISESSION] = serialize($session);
    }

    public static function getApiSession()
    {
        return unserialize($_SESSION[static::SESSION_APISESSION]);
    }

    public static function getRequiredFiles()
    {
        foreach (glob("SpotifyWebAPI/*.php") as $filename) {
            include_once $filename;
        }
        foreach (glob("Src/*.php") as $filename) {
            include_once $filename;
        }
    }

    public static function generateRandomCharacter()
    {
        $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        //Get the length of the string.
        $stringLength = strlen($string);

        //Generate a random index based on the string in question.
        $randomIndex = mt_rand(0, $stringLength - 1);

        return $string[$randomIndex];
    }
}
