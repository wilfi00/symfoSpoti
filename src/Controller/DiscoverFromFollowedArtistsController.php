<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\SpotiImplementation\Request as SpotiRequest;
use App\SpotiImplementation\Auth as SpotiAuth;
use App\SpotiImplementation\Save as SpotiSave;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Security;

class DiscoverFromFollowedArtistsController extends AbstractController
{
    /**
     * @Route("/followedArtistsNotConnected", name="artists_followed_not_connected")
     * @return RedirectResponse|Response
     */
    public function isNotConnected(Security $security): RedirectResponse|Response
    {
        if ($security->isGranted('ROLE_SPOTIFY')) {
            return $this->redirectToRoute('artists_followed');
        }
        return $this->render('pages/discover_from_followed_artists_not_connected.html.twig');
    }

    /**
     * @Route("/followedArtists", name="artists_followed")
     * @return RedirectResponse|Response
     */
    public function index(Request $request, TranslatorInterface $translator, SpotiRequest $spotiRequest, Security $security): RedirectResponse|Response
    {
        $session = $request->getSession();

        if (!$security->isGranted('ROLE_SPOTIFY')) {
           return $this->redirectToRoute('artists_followed_not_connected');
        }
        
        $artists      = $spotiRequest->getAllFollowedArtists();
        
        usort($artists, fn($a, $b) => strtolower($a->name) > strtolower($b->name));
        
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
        if ($security->isGranted('ROLE_SPOTIFY')) {
            $playlists     = $spotiRequest->getUserPlaylistsForModaleSelection();
        }

        return $this->render('pages/discover_from_followed_artists.html.twig', [
            'artists'    => $artists,
            'vueArtists' => $artists,
            'genres'     => $genres,
            'saveUrl'    => $this->generateUrl('save_tracks_from_followed'),
            'playlists'  => $playlists,
            'url'        => $this->generateUrl('save_tracks_from_followed2'),
            'success'    => $request->query->get('success'),
            'text'                => [
                'playlistSaveSucessFeedback' => $translator->trans('discover_playlistSaveSucessFeedback'),
                'feedbackError'              => $translator->trans('feedbackError'),
            ]
        ]);
    }

    /**
     * @Route("/saveTracksFromFollowed", name="save_tracks_from_followed")
     * @return RedirectResponse
     */
    public function saveTracksFromFollowed(LoggerInterface $logger, Request $request, SpotiRequest $spotiRequest, Security $security)
    {
        // On part du principe que ça va échouer ;(
        $success = false;
        
        $data = [
            'saveOption'       => $request->request->get('saveOption'),
            'playlistName'     => $request->request->get('playlistName'),
            'existingPlaylist' => $request->request->get('existingPlaylist'),
            'artists'          => json_decode($request->request->get('artists'), true, 512, JSON_THROW_ON_ERROR),
            'nbTracks'         => $request->request->get('nbTracks'),
        ];

        // Si l'utilisateur n'est pas logé sur spotify, on le fait
        $session = $request->getSession();
        if (!$security->isGranted('ROLE_SPOTIFY')) {
            // On sauvegarde les datas post avant la redirection pour se connecter
            $session->set(SpotiAuth::CALLBACK_DATA, $data);
            return $this->redirect($this->generateUrl('spoti_auth'), 301);
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
        
        $tracksRequest = $spotiRequest->getTopsTracksFromArtists(
            $data['artists'], 
            $data['nbTracks']
        );
        
        $spotiSave = new SpotiSave(
            $logger,
            $spotiRequest,
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
     * @return Response
     */
    public function saveTracksFromFollowed2(Request $request, SpotiRequest $spotiRequest)
    {
        $requestContent = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $tracksRequest = $spotiRequest->getTopsTracksFromArtists(
            $requestContent['artists'], 
            $requestContent['nbTracks']
        );
        
        $playlist = $spotiRequest->createNewPlaylist($requestContent['playlistName']);
        $spotiRequest->addTracksToPlaylist(array_keys($tracksRequest), $playlist->id);
        
        $response = new Response();
        $response->setContent(json_encode([
            'success' => true,
        ]));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
