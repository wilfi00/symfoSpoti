<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use App\SpotiImplementation\Auth as SpotiAuth;

class SpotifyAuthenticator extends OAuth2Authenticator
{
    private $clientRegistry;
    private $entityManager;
    private $router;
    private $credentials;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
	    $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'spoti_callback';
    }

    public function authenticate(Request $request): PassportInterface
    {
        $this->$credentials = $this->fetchAccessToken($this->clientRegistry->getClient('spotify'));
       
        return new SelfValidatingPassport(new UserBadge($this->$credentials, function() {
            $spotifyUser = $this->clientRegistry->getClient('spotify')->fetchUserFromToken($this->credentials);
      
            // Update user
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['uuid' => $spotifyUser->getId()]);
            if ($existingUser) {
                return $this->entityManager->getRepository(User::class)->updateFromSpotify($existingUser, $spotifyUser, $this->credentials);
            }
            
            // New user
            return $this->entityManager->getRepository(User::class)->createFromSpotify($spotifyUser, $this->credentials);
        }));
    }

    /**
     * @return OAuth2ClientInterface
     */
    private function getSpotifyClient()
    {
        return $this->clientRegistry
            // "spotify" is the key used in config/packages/knpu_oauth2_client.yaml
            ->getClient('spotify');
	}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): Response
    {
        // change "app_homepage" to some route in your app
        $targetUrl = $this->router->generate('discover');

        return new RedirectResponse(SpotiAuth::getUrlAfterAuth($targetUrl));
    
        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }
}