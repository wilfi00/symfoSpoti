<?php

namespace App\SpotiImplementation;

use Symfony\Component\HttpFoundation\Session\Session;

class Tools
{
    const SESSION_ARTISTSELECTION = 'artist_selection';

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

    protected static function getSecret()
    {
        return $_ENV['SPOTIFY_API_SECRET'];
    }

    protected static function getRedirectUri()
    {
        return $_ENV['SPOTIFY_REDIRECT_URI'];
    }
}
