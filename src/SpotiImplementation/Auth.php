<?php

namespace App\SpotiImplementation;

use \App\SpotifyWebAPI\Session;

class Auth
{
    CONST CLIENT_ID     = '0526d569b3284692b26e01909d76b53d'; // Your client id

    public static function spotiInit()
    {
        $session = new \App\SpotifyWebAPI\Session(
            static::CLIENT_ID,
            static::getSecret(),
            static::getRedirectUri()
        );
        $options = [
            'scope' => [
                'playlist-read-private',
                'user-read-private',
                'user-modify-playback-state',
                'playlist-modify-public',
                'playlist-modify-private'
            ],
        ];

        return $session->getAuthorizeUrl($options);
    }

    public static function spotiCallback()
    {
        $session = new \App\SpotifyWebAPI\Session(
            static::CLIENT_ID,
            static::getSecret(),
            static::getRedirectUri()
        );

        // Request a access token using the code from Spotify
        $session->requestAccessToken($_GET['code']);

        \App\SpotiImplementation\Tools::saveApiSession($session);
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
