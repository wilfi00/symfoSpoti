<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\SpotiImplementation\Request as SpotiRequest;
use App\SpotiImplementation\Auth as SpotiAuth;
use App\SpotiImplementation\Save as SpotiSave;
use App\SpotiImplementation\Tools as SpotiTools;
use App\Repository\GenreRepository;
use Sonata\SeoBundle\Seo\SeoPageInterface as Seo;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Manager\GenreManager as GenreManager;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Session\Session;

class DiscoverController extends AbstractController
{
    /**
     * @Route("/", name="discover")
     * @param Request $request
     * @param GenreRepository $genreRepository
     * @param Seo $seo
     * @param TranslatorInterface $translator
     * @param SpotiRequest $spotiRequest
     * @param Security $security
     * @param Session $session
     * @return Response
     */
    public function displayDiscover(
        Request $request, 
        GenreRepository $genreRepository,
        Seo $seo, 
        TranslatorInterface $translator,
        SpotiRequest $spotiRequest,
        Security $security,
        Session $session
    ) {
        $seo->addMeta('property', 'og:url',  $this->generateUrl('discover', [], UrlGeneratorInterface::ABSOLUTE_URL));
        if ($request->getLocale() !== 'fr') {
            $seo->addMeta('name', 'description',    $translator->trans('seo_description'));
            $seo->addMeta('property', 'og:description', $translator->trans('seo_description'));
        }
        
        $genres = array_slice($genreRepository->findAllGetArray(), 0, 60);
        foreach ($genres as &$genre) {
            $genre['active'] = true;
        }
        
        $playlists = [];
        if ($security->isGranted('ROLE_SPOTIFY')) {
            $playlists = $spotiRequest->getUserPlaylistsForModaleSelection();
        }

        return $this->render('pages/discover.html.twig', [
            'urlSearchGenre'      => $this->generateUrl('searchGenres'),
            'jsConfig'            => [
                'generatePlaylistUrl' => $this->generateUrl('generatePlaylist'),
                'genres'              => $genres,
                'success'             => $request->query->get('success'),
                'text'                => [
                    'playlistSaveSucessFeedback' => $translator->trans('discover_playlistSaveSucessFeedback'),
                    'feedbackError'              => $translator->trans('feedbackError'),
                ]
            ],
            'tracks'              => SpotiTools::getTracksInSession($session),
            'saveUrl' => $this->generateUrl('save_tracks_from_genres'),
            'text'                => [
                'feedbackError'              => $translator->trans('feedbackError'),
            ],
            'playlists'           => $playlists,
        ]);
    }


    /**
     * @Route("/searchGenres", name="searchGenres")
     * @param Request $request
     * @param GenreManager $genreManager
     * @param Seo $seo
     * @return Response
     */
    public function searchGenres(Request $request, GenreManager $genreManager, Seo $seo)
    {
        $seo->addMeta('name', 'robots',  'noindex');
        $search   = json_decode($request->getContent(), true)['search'];
        $genres   = array_slice($genreManager->findAllBySearch($search), 0, 60);
        foreach ($genres as &$genre) {
            $genre['active'] = true;
        }
        
        $response = new Response();
        $response->setContent(json_encode($genres));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Vérification de la validité des données postées par le formulaire discovery
     *
     * @param $request
     * @param GenreRepository $genreRepository
     * @return bool True si valid
     */
    protected function isValidDatasForDiscover($request, GenreRepository $genreRepository)
    {
        $data = json_decode($request->getContent(), true);
        // Vérification de la data sur le nombre de chanson
        if (!isset($data['nbSongs'])) {
            return false;
        }

        $nbSongs = $data['nbSongs'];
        if (!empty($nbSongs)) {
            $validValues = [25, 50, 100, 150, 200];
            $nbSongs     = intval($nbSongs);
            if (!in_array($nbSongs, $validValues)) {
                return false;
            }
        } else {
            return false;
        }

        // Vérification de la data sur les genres
        if (!isset($data['genres'])) {
            return false;
        }

        if (!is_array($data['genres'])) {
            return false;
        }

        $genres = $data['genres'];
        if (empty($genres)) {
            return false;
        }

        // On s'assure de ne pas avoir de doublons
        $genres = array_unique($genres);

        $genreEntities = [];
        // Pour chaque genre on vérifie que se sont effectivement des genres enregistrés en proposés en DB
        foreach ($genres as $genreId) {
            $genreEntity = $genreRepository->find($genreId);

            if ($genreEntity === false) {
                return false;
            } else {
                $genreEntities[] = $genreEntity;
            }
        }

        return ['nbSongs' => $nbSongs, 'genres' => $genreEntities];
    }

    /**
     * @Route("/generatePlaylist", name="generatePlaylist")
     *
     * @param Request $request
     * @param GenreRepository $genreRepository
     * @param SpotiRequest $spotiRequest
     * @param Session $session
     * @return Response
     * @throws \Exception
     */
    public function generatePlaylist(Request $request, GenreRepository $genreRepository, SpotiRequest $spotiRequest, Session $session)
    {
        $data = $this->isValidDatasForDiscover($request, $genreRepository);
        if ($data === false) {
            throw new \RuntimeException('Something went wrong!');
        }

        // On détermine le nombre d'artistes à récupérer en fonction du nombre de chansons
        $nbSongs           = $data['nbSongs'];
        $genreEntities     = $data['genres'];
        $nbSongsPerArtists = 2;
        $nbArtists         = ceil($nbSongs / count($genreEntities) / $nbSongsPerArtists);
        $spotiRequest->setGenreRespository($genreRepository);
        $artists       = $spotiRequest->getRandomArtistsFromGenres($genreEntities, $nbArtists, true);
        $tracksRequest = $spotiRequest->getTopsTracksFromArtists($artists, $nbSongsPerArtists);

        $tracksId = array_keys($tracksRequest);
        shuffle($tracksId);
        $tracksId = array_slice($tracksId, 0, $nbSongs);

        $spotiTracks  = $spotiRequest->getTracks($tracksId);

        foreach ($spotiTracks as $spotiTrack) {
            $tmpImg      = '';
            $tmpImgArray = $spotiTrack->album->images;

            if (!empty($tmpImgArray)) {
                $tmpImg = $tmpImgArray[0]->url;
            }

            $tracks[] = [
                'id'         => $spotiTrack->id,
                'name'       => $spotiTrack->name,
                'artistName' => $spotiTrack->artists[0]->name,
                'image'      => $tmpImg,
                'genres'     => $tracksRequest[$spotiTrack->id],
            ];
        }
        SpotiTools::saveTracksInSession($session, $tracks);
        return $this->render('spotiTemplates/_tracks.html.twig', ['tracks' => $tracks]);
    }

    /**
     * @Route("/generateBetterPlaylist", name="generateBetterPlaylist")
     *
     * @param Request $request
     * @param GenreRepository $genreRepository
     * @param SpotiRequest $spotiRequest
     * @return Response
     * @throws \Exception
     */
    public function generateBetterPlaylist(Request $request, GenreRepository $genreRepository, SpotiRequest $spotiRequest)
    {
        $data = $this->isValidDatasForDiscover($request, $genreRepository);
        if ($data === false) {
            throw new \RuntimeException('Something went wrong!');
        }
        
        $nbSongs       = $data['nbSongs'];
        $genreEntities = $data['genres'];
        
        $response = $spotiRequest->getBestRecommendations($genreEntities, $nbSongs);
        $spotiTracks   = $response;
        
        foreach ($spotiTracks as $spotiTrack) {
            $tmpImg      = '';
            $tmpImgArray = $spotiTrack->album->images;

            if (!empty($tmpImgArray)) {
                $tmpImg = $tmpImgArray[0]->url;
            }

            $tracks[] = [
                'id'         => $spotiTrack->id,
                'name'       => $spotiTrack->name,
                'artistName' => $spotiTrack->artists[0]->name,
                'image'      => $tmpImg,
                'genres'     => [],
            ];
        }
        return $this->render('spotiTemplates/_tracks.html.twig', ['tracks' => $tracks]);
    }

    /**
     * @Route("/saveTracksFromGenres", name="save_tracks_from_genres")
     * @param Request $request
     * @param SpotiRequest $spotiRequest
     * @param Security $security
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveTracksFromGenres(Request $request, SpotiRequest $spotiRequest, Security $security)
    {
        // On part du principe que ça va échouer ;(
        $success = false;
        
        $data = [
            'saveOption'       => $request->request->get('saveOption'),
            'tracks'           => json_decode($request->request->get('tracks'), true),
            'playlistName'     => $request->request->get('playlistName'),
            'existingPlaylist' => $request->request->get('existingPlaylist'),
        ];

        // Si l'utilisateur n'est pas logé sur spotify, on le fait
        // Sert plus à grand chose car maintenant si on est là on est normalement connnecté :)
        $session = $request->getSession();
        if (!$security->isGranted('ROLE_SPOTIFY')) {
            // On sauvegarde les datas post avant la redirection pour se connecter
            $session->set(SpotiAuth::CALLBACK_DATA, $data);
            return $this->redirect($this->generateUrl('spoti_auth'), 301);
        }
        // Récupération des données si on vient de se logger
        if ($data['tracks'] === null) {
            $data = $session->get(SpotiAuth::CALLBACK_DATA);
        }
        
        $spotiSave = new SpotiSave(
            $spotiRequest,
            $data['saveOption'],
            $data['tracks'],
            $data['playlistName'],
            $data['existingPlaylist'],
        );
        $success = $spotiSave->save();

        return $this->redirect($this->generateUrl('discover', ['success' => $success]));
    }

    /**
     * @Route("/setPopularityGenres", name="setPopularityGenres")
     * @param GenreRepository $genreRepository
     * @param SpotiRequest $spotiRequest
     */
    public function setPopularityGenres(GenreRepository $genreRepository, SpotiRequest $spotiRequest)
    {
        $spotiRequest->setGenreRespository($genreRepository);
        $genres  = $genreRepository->findAll();

$genres = array_slice($genres, 696, 1000);

        foreach ($genres as $genre) {
            $spotiRequest->getRandomArtistsFromGenre($genre, 50);
            sleep(30);
        }
    }
}
