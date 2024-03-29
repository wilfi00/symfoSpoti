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
use Sonata\SeoBundle\Seo\SeoPageInterface as Seo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class RecommendationsController extends AbstractController
{
    /**
     * @Route("/", name="recommendations")
     */
    public function displayRecommendations(Security $security, SpotiRequest $spotiRequest, Request $request, Seo $seo, TranslatorInterface $translator): Response
    {
        $seo->addMeta('property', 'og:url',  $this->generateUrl('recommendations', [], UrlGeneratorInterface::ABSOLUTE_URL));
        if ($request->getLocale() !== 'fr') {
            $seo->addMeta('name', 'description',    $translator->trans('seo_description'));
            $seo->addMeta('property', 'og:description', $translator->trans('seo_description'));
        }
        $playlists = [];
        if ($security->isGranted('ROLE_SPOTIFY')) {
            $playlists = $spotiRequest->getUserPlaylistsForModaleSelection();
        }

        return $this->render('pages/recommendations.html.twig', [
            'playlists' => $playlists,
            'success' => $request->query->get('success'),
        ]);
    }

    /**
     * @Route("/makeRecommendations", name="makeRecommendations")
     * @throws Exception
     */
    public function makeRecommendations(Request $request, SessionInterface $session, RecommendationsService $recommendationsService): Response
    {
        $artists = [];
        $genres = [];
        $tracks = [];

        $seeds = array_filter($request->request->all(), fn($key) => str_starts_with($key, 'seeds'), ARRAY_FILTER_USE_KEY);

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
            (int) $request->get('popularity'),
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
            'genres' => $genres,
        ]);
    }

    /**
     * @Route("/saveTracksFromRecommendations", name="save_tracks_from_recommendtaions")
     */
    public function saveTracksFromRecommendations(LoggerInterface $logger, Request $request, SpotiRequest $spotiRequest, Security $security): RedirectResponse
    {
        $data = [
            'saveOption'       => $request->request->get('saveOption'),
            'tracks'           => json_decode($request->request->get('tracks'), true, 512, JSON_THROW_ON_ERROR),
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
