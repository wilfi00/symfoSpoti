<?php

namespace App\SpotiImplementation;

use \App\SpotifyWebAPI\Session as SpotiSession;
use Symfony\Component\HttpFoundation\Session\Session;

class Auth
{
    CONST CLIENT_ID               = '0526d569b3284692b26e01909d76b53d'; // Your client id
    const SESSION_APISESSION      = 'api_session';
    const SESSION_BASICAPISESSION = 'api_basic_session';
    const CALLBACK_URL            = 'callback_url';

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
                'playlist-read-private',
                'user-read-private',
                'playlist-modify-public',
                'playlist-modify-private'
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

        static::saveApiSession($session);
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

    public static function getApiSession($session = null)
    {
        if ($session === null) {
            $session = new Session();
        }
        $apiSession = $session->get(static::SESSION_APISESSION);
        if ($apiSession === null) {
            header(static::spotiInit());
            exit();
        }

        return unserialize($session->get(static::SESSION_APISESSION));
    }

    public static function saveApiSession($sessionValue)
    {
        $session = new Session();
        $session->set(static::SESSION_APISESSION, serialize($sessionValue));
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
        return unserialize($session->get(static::SESSION_BASICAPISESSION));
    }

    public static function saveBasicApiSession($sessionValue)
    {
        $session = new Session();
        $session->set(static::SESSION_BASICAPISESSION, serialize($sessionValue));
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
