<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\Genre;
use App\Entity\Recommendation;
use App\Entity\Track;
use App\Services\RecommendationsService;
use App\Services\SearchSongService;
use App\SpotiImplementation\Request as SpotiRequest;
use App\SpotiImplementation\Save as SpotiSave;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class RecommendationsController extends AbstractController
{
    /**
     * @Route("/recommendations", name="recommendations")
     * @param Security $security
     * @param SpotiRequest $spotiRequest
     * @return Response
     */
    public function displayRecommendations(Security $security, SpotiRequest $spotiRequest): Response
    {
        $playlists = [];
        if ($security->isGranted('ROLE_SPOTIFY')) {
            $playlists = $spotiRequest->getUserPlaylistsForModaleSelection();
        }

        return $this->render('pages/recommendations.html.twig', [
            'playlists' => $playlists,
        ]);
    }

    /**
     * @Route("/makeRecommendations", name="makeRecommendations")
     * @param Request $request
     * @param SessionInterface $session
     * @param RecommendationsService $recommendationsService
     * @return Response
     * @throws Exception
     */
    public function makeRecommendations(Request $request, SessionInterface $session, RecommendationsService $recommendationsService): Response
    {
        $artists = [];
        $genres = [];
        $tracks = [];

        $seeds = array_filter($request->request->all(), function($key) {
            return strpos($key, 'seeds') === 0;
        }, ARRAY_FILTER_USE_KEY);

        foreach ($seeds as $seed) {
            if ($seed[1] === Artist::TYPE) {
                $artists[] = $seed[0];
            }
            if ($seed[1] === Track::TYPE) {
                $tracks[] = $seed[0];
            }
            if ($seed[1] === Genre::TYPE) {
                $genres[] = $seed[0];
            }
        }

        if (empty($artists) && empty($tracks) && empty($genres)) {
            return $this->render('spotiTemplates/_songs_recommendations.html.twig', [
                'songs' => []
            ]);
        }

        $tracks = $recommendationsService->getRecommendations(
            $artists,
            $genres,
            $tracks,
            (float) $request->get('acousticness') / 100,
            (float) $request->get('danceability') / 100,
//            (int) $request->get('duration'),
            (float) $request->get('energy') / 100,
            (float) $request->get('instrumentalness') / 100,
            (float) $request->get('liveness') / 100,
//            (float) $request->get('loudness'),
//            (int) $request->get('mode'),
//            (int) $request->get('popularity'),
            (float) $request->get('speechiness') / 100,
//            (float) $request->get('tempo'),
            (float) $request->get('valence') / 100
        );

        return $this->render('spotiTemplates/_songs_recommendations.html.twig', [
            'songs' => $tracks
        ]);
    }

    /**
     * @Route("/searchForSeeds", name="searchForSeeds")
     * @param Request $request
     * @param SearchSongService $searchSongService
     * @return Response
     */
    public function searchForSeeds(Request $request, SearchSongService $searchSongService): Response
    {
        $searchLabel = $request->get('seed-search');
        $tracks = $searchSongService->search(Track::TYPE, $searchLabel);
        $artists = $searchSongService->search(Artist::TYPE, $searchLabel);
        $genres = $searchSongService->search(Recommendation::GENRE_TYPE, $searchLabel);

        return $this->render('spotiTemplates/_search_result.html.twig', [
            'tracks' => array_slice($tracks, 0, 4),
            'artists' => array_slice($artists, 0, 4),
            'genres' => array_slice($genres, 0, 4),
        ]);
    }

    /**
     * @Route("/saveTracksFromRecommendations", name="save_tracks_from_recommendtaions")
     * @param LoggerInterface $logger
     * @param Request $request
     * @param SpotiRequest $spotiRequest
     * @param Security $security
     * @return RedirectResponse
     */
    public function saveTracksFromRecommendations(LoggerInterface $logger, Request $request, SpotiRequest $spotiRequest, Security $security): RedirectResponse
    {
        $data = [
            'saveOption'       => $request->request->get('saveOption'),
            'tracks'           => json_decode($request->request->get('tracks'), true),
            'playlistName'     => $request->request->get('playlistName'),
            'existingPlaylist' => $request->request->get('existingPlaylist'),
        ];

        $spotiSave = new SpotiSave(
            $logger,
            $spotiRequest,
            $data['saveOption'],
            $data['tracks'],
            $data['playlistName'],
            $data['existingPlaylist'],
        );
        $success = $spotiSave->save();

        return $this->redirect($this->generateUrl('recommendations', ['success' => $success]));
    }
}
