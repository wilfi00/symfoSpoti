<?php

session_start();

class Tools
{
    CONST CLIENT_ID     = '0526d569b3284692b26e01909d76b53d'; // Your client id
    CONST CLIENT_SECRET = '89a3ad66196346a6a3abef6f75855ee1'; // Your secret
    CONST REDIRECT_URI  = 'http://localhost/prout/Callback'; // Your redirect uri

    const SESSION_ACCESSTOKEN  = 'access_token';
    const SESSION_REFRESHTOKEN = 'refresh_token';
    const SESSION_APISESSION   = 'api_session';

    public static function getCurrentToken()
    {
        return $_SESSION[static::SESSION_ACCESSTOKEN];
    }

    public static function getRefreshToken()
    {
        return $_SESSION[static::SESSION_REFRESHTOKEN];
    }

    public static function setCurrentToken($token)
    {
        $_SESSION[static::SESSION_ACCESSTOKEN] = $token;
    }

    public static function setRefreshToken($token)
    {
        $_SESSION[static::SESSION_REFRESHTOKEN] = $token;
    }

    public static function saveApiSession(SpotifyWebAPI\Session $session)
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
