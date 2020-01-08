<?php

namespace App\SpotiImplementation;

use Symfony\Component\HttpFoundation\Session\Session;

class Tools
{
    const SESSION_APISESSION      = 'api_session';
    const SESSION_ARTISTSELECTION = 'artist_selection';

    public static function getApiSession()
    {
        $session = new Session();
        return unserialize($session->get(static::SESSION_APISESSION));
    }

    public static function saveApiSession($sessionValue)
    {
        $session = new Session();
        $session->set(static::SESSION_APISESSION, serialize($sessionValue));
    }

    public static function getApiSessionDeprecated()
    {
        return unserialize($_SESSION[static::SESSION_APISESSION]);
    }

    public static function saveApiSessionDeprecated($session)
    {
        $_SESSION[static::SESSION_APISESSION] = serialize($session);
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

    public static function saveArtistSelectionInSession($artistId)
    {
        $session = new Session();

        $artists = static::getArtistsSelectionInSession();
        if ($artists === null) {
            $artists = [];
        }

        $artists[] = $artistId;

        $session->set(static::SESSION_ARTISTSELECTION, $artists);
    }

    public static function getArtistsSelectionInSession($session = null)
    {
        if ($session === null) {
            $session = new Session();
        }

        return $session->get(static::SESSION_ARTISTSELECTION);
    }

    public static function emptyArtistSelectionInSession()
    {
        $session = new Session();
        $session->remove(static::SESSION_ARTISTSELECTION);
    }
}
