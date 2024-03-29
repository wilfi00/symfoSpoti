<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use \App\SpotiImplementation\Request as SpotiRequest;
use Symfony\Component\Security\Core\Security;

class AppExtension extends AbstractExtension
{
    public function __construct(protected SpotiRequest $spotiRequest, protected Security $security)
    {
    }
    
    public function getFunctions()
    {
        return [
            new TwigFunction('isConnected', [$this, 'isUserConnectedToSpotify']),
            new TwigFunction('isOneDeviceActive', [$this, 'isOneDeviceActiveorAvailable']),
        ];
    }

    public function isUserConnectedToSpotify(): bool
    {
        return $this->security->isGranted('ROLE_SPOTIFY');
    }
    
    public function isOneDeviceActiveorAvailable(): bool
    {
        return $this->spotiRequest->isThereOneAvailableDevice();
    }
}