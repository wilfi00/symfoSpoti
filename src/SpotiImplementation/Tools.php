<?php

namespace App\SpotiImplementation;

use Symfony\Component\HttpFoundation\Session\Session;

class Tools
{
    final const SESSION_ARTISTSELECTION = 'artist_selection';
    final const SESSION_DISCOVERSELECTION = 'discover_selection';

    public static function generateRandomCharacter()
    {
        $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        //Get the length of the string.
        $stringLength = strlen($string);

        //Generate a random index based on the string in question.
        $randomIndex = random_int(0, $stringLength - 1);

        return $string[$randomIndex];
    }

    public static function saveArtistSelectionInSession(array $artist): void
    {
        $session = new Session();

        $artists = static::getArtistsSelectionInSession();
        if ($artists === null) {
            $artists = [];
        }

        array_unshift($artists, $artist);

        $session->set(static::SESSION_ARTISTSELECTION, $artists);
    }

    public static function deleteArtistSelectionInSession($artistId): void
    {
        $selection = static::getArtistsSelectionInSession();

        foreach ($selection as $key => $artist) {
            if ($artist['id'] === $artistId) {
                unset($selection[$key]);
                static::emptyArtistSelectionInSession();

                foreach ($selection as $artistSelected) {
                    if (is_array($artistSelected)) {
                        static::saveArtistSelectionInSession($artistSelected);
                    }
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

    public static function emptyArtistSelectionInSession(): void
    {
        $session = new Session();
        $session->remove(static::SESSION_ARTISTSELECTION);
    }
    
    public static function saveTracksInSession(Session $session, array $tracks): void
    {
        static::emptyTracksInSession();
        $session->set(static::SESSION_DISCOVERSELECTION, $tracks);
    }
    
    public static function getTracksInSession(Session $session)
    {
        return $session->get(static::SESSION_DISCOVERSELECTION);
    }
    
    public static function emptyTracksInSession(): void
    {
        $session = new Session();
        $session->remove(static::SESSION_DISCOVERSELECTION);
    }

    public static function isCurrentUserOwnerOfPlaylist($userId, $playlist): bool
    {
        return $playlist->owner->id === $userId;
    }

    public static function getCurrentUrl()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public static function formatStringForSpotify($string): string
    {
	    return '"' . $string . '"';
    }

    public static function formatInverseStringForSpotify($string)
    {
	return substr($string, 1, -1);
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
