<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use \App\SpotiImplementation\Request as SpotiRequest;
use \App\SpotiImplementation\Auth as SpotiAuth;
use \App\SpotiImplementation\Tools as SpotiTools;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Form\Type\ArtistsType;

class DiscoverFromFollowedArtistsController extends AbstractController
{
    /**
     * @Route("/followedArtists", name="artists_followed")
     */
    public function index(Request $request, TranslatorInterface $translator)
    {
        $session = $request->getSession();

        if (!SpotiAuth::isUserAuthenticated($session)) {
            return $this->redirectToRoute('init');
        }
        
        $requestSpoti = SpotiRequest::factory();
        $artists      = $requestSpoti->getAllFollowedArtists();
        
        usort($artists, function($a, $b) {
            return strtolower($a->name) > strtolower($b->name);
        });
        dump($artists);
        
        return $this->render('pages/discover_from_followed_artists.html.twig', [
            'artists'    => $artists,
            'vueArtists' => $artists,
            'url'        => $this->generateUrl('generate_playlist_followed_artists'),
        ]);
    }
    
    /**
     * @Route("/generatePlaylistFollowedArtists", name="generate_playlist_followed_artists")
     */
    public function generatePlaylistFollowedArtists(Request $request)
    {
        $requestContent = json_decode($request->getContent(), true);
        $request       = SpotiRequest::factory();
        $tracksRequest = $request->getTopsTracksFromArtists(
            $requestContent['artists'], 
            $requestContent['nbTracks']
        );
        
        $request  = SpotiRequest::factory();
       // $playlist = $request->createNewPlaylist($requestContent['playlistName']);
        //$request->addTracksToPlaylist(array_keys($tracksRequest), $playlist->id);
        // Succès de l'opération, feedback vert \o/
        $success = true;
    }
}
