<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use \App\SpotiImplementation\Auth as SpotiAuth;
use \App\SpotiImplementation\Request as SpotiRequest;

class AppExtension extends AbstractExtension
{
    protected $session;
    protected $spotiRequest;
    
    public function __construct(SessionInterface $session) 
    {
        $this->session = $session;
        $this->spotiRequest  = SpotiRequest::factory();
    }
    
    public function getFunctions()
    {
        return [
            new TwigFunction('isConnected', [$this, 'isUserConnectedToSpotify']),
            new TwigFunction('isOneDeviceActive', [$this, 'isOneDeviceActiveorAvailable']),
        ];
    }

    public function isUserConnectedToSpotify()
    {
        return SpotiAuth::isUserAuthenticated($this->session);
    }
    
    public function isOneDeviceActiveorAvailable()
    {
        return $this->spotiRequest->isThereOneAvailableDevice();
    }
}