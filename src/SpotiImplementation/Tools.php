<?php

namespace App\SpotiImplementation;

use Symfony\Component\HttpFoundation\Session\Session;

class Tools
{
    const SESSION_APISESSION      = 'api_session';
    const SESSION_ARTISTSELECTION = 'artist_selection';
    const CALLBACK_URL            = 'callback_url';

    public static function getApiSession($session = null)
    {
        if ($session === null) {
            $session = new Session();
        }
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

    public static function saveArtistSelectionInSession($artist)
    {
        $session = new Session();

        $artists = static::getArtistsSelectionInSession();
        if ($artists === null) {
            $artists = [];
        }

        array_unshift($artists, $artist);

        $session->set(static::SESSION_ARTISTSELECTION, $artists);
    }

    public static function deleteArtistSelectionInSession($artistId)
    {
        $selection = static::getArtistsSelectionInSession();

        foreach ($selection as $key => $artist) {
            if ($artist['id'] === $artistId) {
                unset($selection[$key]);
                static::emptyArtistSelectionInSession();

                foreach ($selection as $artist) {
                    static::saveArtistSelectionInSession($artist);
                }
                return;
            }
        }
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

    public static function isCurrentUserOwnerOfPlaylist($userId, $playlist)
    {
        return $playlist->owner->id === $userId;
    }

    public static function isAuthenticated($session = null)
    {
        if ($session === null) {
            $session = new Session();
        }

        if (!static::getApiSession($session)) {
            $session->set(static::CALLBACK_URL, static::getCurrentUrl());
            return false;
        } else {
            return true;
        }
    }

    public static function getUrlAfterAuthentification($defaultUrl, $session = null)
    {
        if ($session === null) {
            $session = new Session();
        }

        $previousUrl = $session->remove(static::CALLBACK_URL);
        if (!empty($previousUrl)) {
            return $previousUrl;
        } else {
            return $defaultUrl;
        }
    }

    public static function getCurrentUrl()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public static function formatStringForSpotify($string)
    {
        return str_replace(' ', '+', $string);
    }

    public static function formatInverseStringForSpotify($string)
    {
        return str_replace('+', ' ', $string);
    }

    public static function addErrorProbability($nbArtists)
    {
        return round($nbArtists * 1.1);
    }

    /**
     * Retourne le nombre d'artistes que l'on devrait chercher en fonction du nombre de chansons voulus
     *
     * @return int Nb artistes
     */
    public static function getNbArtistsWeSouldGetByNbSongs($nbSongs)
    {
        $nbSongsPerArtists = 2;
        return round($nbSongs / $nbSongsPerArtists);
    }
}
