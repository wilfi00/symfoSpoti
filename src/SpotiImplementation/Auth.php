<?php

namespace App\SpotiImplementation;

use Symfony\Component\HttpFoundation\Session\Session;
use \App\SpotiImplementation\Request as SpotiRequest;
use SpotifyWebAPI\Session as SpotiSession;
use App\Entity\User;

class Auth
{
    const CALLBACK_URL  = 'callback_url';
    const CALLBACK_DATA = 'callback_data';
    
    protected static function makeSpotiSession()
    {
        return new SpotiSession(
            static::getClientId(),
            static::getSecret(),
            static::getRedirectUri()
        );
    }

    public static function makeBasicAuth(): SpotiSession
    {
        $session = static::makeSpotiSession();
        $session->requestCredentialsToken();
        
        return $session;
    }
    
    public static function makeUserAuth(User $user): SpotiSession
    {
        $session = static::makeSpotiSession();
        $session->setAccessToken($user->getAccessToken());
        $session->setRefreshToken($user->getRefreshToken());
        
        return $session;
    }
    
    public static function setUrlAfterAuth($url, Session $session = null)
    {
        if ($session === null) {
            $session = new Session();
        }

        $session->set(static::CALLBACK_URL, $url);
    }
    
    public static function getUrlAfterAuth($defaultUrl, Session $session = null)
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
    
    protected static function getSecret()
    {
        return $_ENV['SPOTIFY_API_SECRET'];
    }
    
    protected static function getClientId()
    {
        return $_ENV['SPOTIFY_CLIENT_ID'];
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
