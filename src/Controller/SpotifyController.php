<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use \App\SpotiImplementation\Auth as SpotiAuth;

class SpotifyController extends AbstractController
{
    /**
     * @Route("/spotiAuth", name="spoti_auth")
     */
    public function connectAction(Request $request, ClientRegistry $clientRegistry): RedirectResponse
    {
        SpotiAuth::setUrlAfterAuth($request->headers->get('referer'));
        return $clientRegistry
            ->getClient('spotify') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect([
                'playlist-modify-public',
                'playlist-modify-private',
                'user-follow-read',
                'user-modify-playback-state', // pour ajouter dans la queue
                'user-read-playback-state', // pour avoir la liste des devices dispo
            ], []);
    }

    /**
     * @Route("/spotiCallback", name="spoti_callback")
     */
    public function spotiCallback(ClientRegistry $clientRegistry): void
    {
        // Toute la logique se trouve dans SpotifyAuthenticator
    }

    /**
     * @Route("/logout", name="logout", methods={"GET"})
     * @throws \Exception
     */
    public function logout(): never
    {
        // controller can be blank: it will never be executed!
        throw new \RuntimeException('Don\'t forget to activate logout in security.yaml');
    }
}
