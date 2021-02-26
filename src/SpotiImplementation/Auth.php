<?php

namespace App\SpotiImplementation;

use Symfony\Component\HttpFoundation\Session\Session;
use \App\SpotiImplementation\Request as SpotiRequest;
use SpotifyWebAPI\Session as SpotiSession;

class Auth
{
    const CLIENT_ID               = '0526d569b3284692b26e01909d76b53d'; // Your client id
    const SESSION_APISESSION      = 'api_session';
    const SESSION_BASICAPISESSION = 'api_basic_session';
    const SESSION_INFORMATION     = 'spotify_user_information';
    const CALLBACK_URL            = 'callback_url';
    const CALLBACK_DATA           = 'callback_data';

    public static function makeBasicAuth()
    {
        $session = new SpotiSession(
            '0526d569b3284692b26e01909d76b53d',
            static::getSecret(),
            static::getRedirectUri()
        );
        $session->requestCredentialsToken();

        // Sauvegarde
        static::saveBasicApiSession($session);
    }

    public static function spotiInit()
    {
        $session = new SpotiSession(
            static::CLIENT_ID,
            static::getSecret(),
            static::getRedirectUri()
        );
        $options = [
            'scope' => [
                'playlist-modify-public',
                'playlist-modify-private',
                'user-follow-read',
                'user-modify-playback-state', // pour ajouter dans la queue
                'user-read-playback-state', // pour avoir la liste des devices dispo
            ],
        ];

        return $session->getAuthorizeUrl($options);
    }

    public static function spotiCallback()
    {
        $session = new SpotiSession(
            static::CLIENT_ID,
            static::getSecret(),
            static::getRedirectUri()
        );

        // Request a access token using the code from Spotify
        $session->requestAccessToken($_GET['code']);

        $sessionPhp = static::saveApiSession($session);
        static::saveUserSession($sessionPhp);
    }

    public static function isUserAuthenticated($session = null)
    {
        if ($session === null) {
            $session = new Session();
        }

        $apiSession = static::getApiSession($session);

        // Si on a bien une session
        if ($apiSession) {
            // Et si la session n'est pas expiré
            if (!static::isSessionExpired($apiSession)) {
                // L'utilisateur est bien authentifié
                return true;
            }
        }

        // Sinon ça veut dire qu'il ne l'est pas
        $session->set(static::CALLBACK_URL, Tools::getCurrentUrl());
        return false;
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

    public static function getApiSession($session = null)
    {
        if ($session === null) {
            $session = new Session();
        }
        $apiSession = $session->get(static::SESSION_APISESSION);
        if ($apiSession === null) {
            return $apiSession;
        }

        return unserialize($session->get(static::SESSION_APISESSION));
    }

    public static function saveApiSession($sessionValue)
    {
        $session = new Session();
        $session->set(static::SESSION_APISESSION, serialize($sessionValue));
        return $session;
    }

    protected static function saveUserSession($session)
    {
        if (static::isUserAuthenticated($session)) {
            $request  = SpotiRequest::factory();
            $session->set(static::SESSION_INFORMATION, serialize($request->getUserInformations()));
        }
    }
    
    public static function getBasicApiSession($session = null)
    {
        if ($session === null) {
            $session = new Session();
        }

        $basicApiSession = $session->get(static::SESSION_BASICAPISESSION);

        // Si jamais on a rien, alors on joue l'authentification
        if ($basicApiSession === null) {
            static::makeBasicAuth();
        }

        $basicApiSession = unserialize($session->get(static::SESSION_BASICAPISESSION));

        // Si jamais la session est expiré alors on joue l'authentification
        if (static::isSessionExpired($basicApiSession)) {
            static::makeBasicAuth();
        }

        return unserialize($session->get(static::SESSION_BASICAPISESSION));
    }

    public static function saveBasicApiSession($sessionValue)
    {
        $session = new Session();
        $session->set(static::SESSION_BASICAPISESSION, serialize($sessionValue));
        // ici stocker les infos de l'utilisateur courant : https://developer.spotify.com/documentation/web-api/reference/users-profile/
    }

    protected static function getSecret()
    {
        return $_ENV['SPOTIFY_API_SECRET'];
    }

    protected static function getRedirectUri()
    {
        return $_ENV['SPOTIFY_REDIRECT_URI'];
    }

    protected static function isSessionExpired($session)
    {
        return $session->getTokenExpiration() <= time();
    }
}
