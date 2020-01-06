<?php

namespace App\SpotiImplementation;

use \App\SpotifyWebAPI\Session;

class Auth
{
    CONST CLIENT_ID     = '0526d569b3284692b26e01909d76b53d'; // Your client id
    CONST CLIENT_SECRET = '89a3ad66196346a6a3abef6f75855ee1'; // Your secret
    CONST REDIRECT_URI  = 'https://localhost:8000/spotiCallback'; // Your redirect uri

    public static function spotiInit()
    {
        $session = new \App\SpotifyWebAPI\Session(
            static::CLIENT_ID,
            static::CLIENT_SECRET,
            static::REDIRECT_URI
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
        dump('coucou1');
        $session = new \App\SpotifyWebAPI\Session(
            static::CLIENT_ID,
            static::CLIENT_SECRET,
            static::REDIRECT_URI
        );

        // Request a access token using the code from Spotify
        $session->requestAccessToken($_GET['code']);

        \App\SpotiImplementation\Tools::saveApiSession($session);
    }
}
