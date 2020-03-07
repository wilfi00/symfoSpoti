<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use \App\SpotiImplementation\Request as SpotiRequest;
use \App\SpotiImplementation\Auth as SpotiAuth;
use App\Repository\GenreRepository;
use \Sonata\SeoBundle\Seo\SeoPageInterface as Seo;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DiscoverController extends AbstractController
{
    /**
     * @Route("/", name="discover")
     */
    public function displayDiscover(Request $request, GenreRepository $genreRepository, Seo $seo, TranslatorInterface $translator)
    {
        $seo->addMeta('property', 'og:url',  $this->generateUrl('discover', [], UrlGeneratorInterface::ABSOLUTE_URL));
        if ($request->getLocale() !== 'fr') {
            $seo->addMeta('name', 'description',    $translator->trans('seo_description'));
            $seo->addMeta('property', 'og:description', $translator->trans('seo_description'));
        }

        return $this->render('pages/discover.html.twig', [
            'jsConfig' => [
                'generatePlaylistUrl' => $this->generateUrl('generatePlaylist'),
                'genres'              => $genreRepository->findAllGetArray(),
                'success'             => $request->query->get('success'),
                'text'                => [
                    'playlistSaveSucessFeedback' => $translator->trans('discover_playlistSaveSucessFeedback'),
                    'feedbackError'              => $translator->trans('feedbackError'),
                ]
            ],
            'tracks' => [],
            'saveIntoPlaylistUrl' => $this->generateUrl('saveTracksIntoPlaylist'),
            'text' => [
                'h1'                  => $translator->trans('discover_h1'),
                'p1'                  => $translator->trans('discover_p1'),
                'p1Warning'           => $translator->trans('discover_p1Warning'),
                'searchPlaceholder'   => $translator->trans('discover_searchPlaceholder'),
                'songs'               => $translator->trans('discover_songs'),
                'generate'            => $translator->trans('discover_generate'),
                'generateToolTip'     => $translator->trans('discover_generateToolTip'),
                'playlistName'        => $translator->trans('discover_playlistName'),
                'playlistSave'        => $translator->trans('discover_playlistSave'),
                'playlistSaveToolTip' => $translator->trans('discover_playlistSaveToolTip'),
            ]
        ]);
    }

    /**
     * Vérification de la validité des données postées par le formulaire discovery
     *
     * @return bool True si valid
     */
    protected function isValidDatasForDiscover($request, GenreRepository $genreRepository)
    {
        $data = json_decode($request->getContent(), true);
        // Vérification de la data sur le nombre de chanson
        if (!isset($data['nbSongs'])) {
            return false;
        } else {
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
        }

        // Vérification de la data sur les genres
        if (!isset($data['genres'])) {
            return false;
        } elseif (!is_array($data['genres'])) {
            return false;
        } else {
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
        }

        return ['nbSongs' => $nbSongs, 'genres' => $genreEntities];
    }

     /**
     * @Route("/generatePlaylist", name="generatePlaylist")
     */
    public function generatePlaylist(Request $request, GenreRepository $genreRepository)
    {
        $data = $this->isValidDatasForDiscover($request, $genreRepository);
        if ($data === false) {
            throw new \Exception('Something went wrong!');
        }

        // On détermine le nombre d'artistes à récupérer en fonction du nombre de chansons
        $nbSongs           = $data['nbSongs'];
        $genreEntities     = $data['genres'];
        $nbSongsPerArtists = 2;
        $nbArtists         = ceil($nbSongs / count($genreEntities) / $nbSongsPerArtists);
        $request       = SpotiRequest::factory();
        $request->setGenreRespository($genreRepository);
        $artists       = $request->getRandomArtistsFromGenres($genreEntities, $nbArtists, true);
        $tracksRequest = $request->getTopsTracksFromArtists($artists, $nbSongsPerArtists);

        $tracksId = array_keys($tracksRequest);
        shuffle($tracksId);
        $tracksId = array_slice($tracksId, 0, $nbSongs);

        $requestSpoti = SpotiRequest::factory();
        $spotiTracks  = $requestSpoti->getTracks($tracksId);

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
        return $this->render('spotiTemplates/_tracks.html.twig', ['tracks' => $tracks]);
    }

    /**
     * @Route("/setPopularityGenres", name="setPopularityGenres")
     */
    public function setPopularityGenres(GenreRepository $genreRepository)
    {
        $api = new \App\SpotifyWebAPI\SpotifyWebAPI();
        $api->setSession(SpotiAuth::getApiSession());
        $api->setOptions([
            'auto_refresh' => true,
        ]);
        $request = new SpotiRequest($api);
        $request->setGenreRespository($genreRepository);
        $genres  = $genreRepository->findAll();

$genres = array_slice($genres, 696, 1000);

        foreach ($genres as $genre) {
            $request->getRandomArtistsFromGenre($genre, 50);
            sleep(30);
        }
    }

    /**
     * @Route("/saveTracksIntoPlaylist", name="saveTracksIntoPlaylist")
     */
    public function saveTracksIntoPlaylist(Request $request)
    {
        // On part du principe que ça va échouer ;(
        $success = false;

        // Si l'utilisateur n'est pas logé sur spotify, on le fait
        $session = $request->getSession();
        if (!SpotiAuth::isUserAuthenticated($session)) {
            // On sauvegarde les datas post avant la redirection pour se connecter
            $session->set(SpotiAuth::CALLBACK_DATA, [
                'playlistName' => $request->request->get('playlistName'),
                'tracks'       => json_decode($request->request->get('tracks'))
            ]);
            return $this->redirect($this->generateUrl('init'), 301);
        }

        $playlistName = $request->request->get('playlistName');
        $tracks       = json_decode($request->request->get('tracks'));

        if ($playlistName === null && $tracks === null) {
            $data         = $session->get(SpotiAuth::CALLBACK_DATA);
            $playlistName = $data['playlistName'];
            $tracks       = $data['tracks'];
        }

        if (!empty($playlistName) && !empty($tracks)) {
            $request  = SpotiRequest::factory();
            $playlist = $request->createNewPlaylist($playlistName);
            $request->addTracksToPlaylist($tracks, $playlist->id);
            // Succès de l'opération, feedback vert \o/
            $success = true;
        }


        return $this->redirect($this->generateUrl('discover', ['success' => $success]));
    }
}
