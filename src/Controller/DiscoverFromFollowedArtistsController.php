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
use \App\SpotiImplementation\Save as SpotiSave;
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
        
        $genres    = [];
        $tmpGenres = [];
        foreach ($artists as $artist) {
            $artist->active = true;
            $tmpGenres = array_merge($tmpGenres, $artist->genres);
        }
        $tmpGenres = array_unique($tmpGenres);
        sort($tmpGenres);
        foreach ($tmpGenres as $key => $genre) {
            $genres[] = [
                'name'   => $genre,
                'id'     => $key,
                'active' => true,
            ];
        }
        
        $playlists = [];
        if (SpotiAuth::isUserAuthenticated($request->getSession())) {
            $requestSpoti  = SpotiRequest::factory();
            $playlists     = $requestSpoti->getUserPlaylistsForModaleSelection();
        }

        return $this->render('pages/discover_from_followed_artists.html.twig', [
            'artists'    => $artists,
            'vueArtists' => $artists,
            'genres'     => $genres,
            'saveUrl'    => $this->generateUrl('save_tracks_from_followed'),
            'playlists'  => $playlists,
            'url'        => $this->generateUrl('save_tracks_from_followed2'),
            'text'                => [
                'playlistSaveSucessFeedback' => $translator->trans('discover_playlistSaveSucessFeedback'),
                'feedbackError'              => $translator->trans('feedbackError'),
            ]
        ]);
    }
    
    /**
     * @Route("/saveTracksFromFollowed", name="save_tracks_from_followed")
     */
    public function saveTracksFromFollowed(Request $request)
    {
        // On part du principe que ça va échouer ;(
        $success = false;
        
        $data = [
            'saveOption'       => $request->request->get('saveOption'),
            'playlistName'     => $request->request->get('playlistName'),
            'existingPlaylist' => $request->request->get('existingPlaylist'),
            'artists'          => json_decode($request->request->get('artists')),
            'nbTracks'         => $request->request->get('nbTracks'),
        ];

        // Si l'utilisateur n'est pas logé sur spotify, on le fait
        $session = $request->getSession();
        if (!SpotiAuth::isUserAuthenticated($session)) {
            // On sauvegarde les datas post avant la redirection pour se connecter
            $session->set(SpotiAuth::CALLBACK_DATA, $data);
            return $this->redirect($this->generateUrl('init'), 301);
        }
        // Récupération des données si on vient de se logger
        if ($data['saveOption'] === null) {
            $data = $session->get(SpotiAuth::CALLBACK_DATA);
        }
        
        foreach ($data['artists'] as &$artist) {
            $artist = [
              'id'     => $artist
            ];
        }
        
        $request       = SpotiRequest::factory();
        $tracksRequest = $request->getTopsTracksFromArtists(
            $data['artists'], 
            $data['nbTracks']
        );
        
        $spotiSave = new SpotiSave(
            $data['saveOption'],
            array_keys($tracksRequest),
            $data['playlistName'],
            $data['existingPlaylist'],
        );
        $success = $spotiSave->save();

        return $this->redirect($this->generateUrl('artists_followed', ['success' => $success]));
    }
    
    /**
     * @Route("/saveTracksFromFollowed2", name="save_tracks_from_followed2")
     */
    public function saveTracksFromFollowed2(Request $request)
    {
        $requestContent = json_decode($request->getContent(), true);
        $request       = SpotiRequest::factory();
        $tracksRequest = $request->getTopsTracksFromArtists(
            $requestContent['artists'], 
            $requestContent['nbTracks']
        );
        
        $request  = SpotiRequest::factory();
        $playlist = $request->createNewPlaylist($requestContent['playlistName']);
        $request->addTracksToPlaylist(array_keys($tracksRequest), $playlist->id);
        
        $response = new Response();
        $response->setContent(json_encode([
            'success' => true,
        ]));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
