<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\GenreRepository;

class DiscoverController extends AbstractController
{
    /**
     * @Route("/", name="discover")
     */
    public function displayDiscover()
    {
        return $this->render('testArea/discover.html.twig', [
            'jsConfig' => [
                'searchGenreUrl'      => $this->generateUrl('searchGenre'),
                'generatePlaylistUrl' => $this->generateUrl('generatePlaylist'),
                'saveIntoPlaylistUrl' => $this->generateUrl('saveTracksIntoPlaylist'),
            ],
            'tracks' => [],
        ]);
    }

    /**
     * @Route("/searchGenre", name="searchGenre")
     */
    public function searchGenre(Request $request, GenreRepository $genreRepository)
    {
        return $this->json($genreRepository->findByGenres(json_decode($request->getContent(), true)));
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

            $genreEntities = [];
            // Pour chaque genre on vérifie que se sont effectivement des genres enregistrés en proposés en DB
            foreach ($genres as $genre) {
                $genreEntity = $genreRepository->findByGenre($genre);

                if ($genreEntity === false) {
                    var_dump('3');
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
        $nbSongsPerArtists = 2;
        $nbArtists         = round($nbSongs / $nbSongsPerArtists);
        $genreEntities     = $data['genres'];

        $api = new \App\SpotifyWebAPI\SpotifyWebAPI();
        $api->setSession(\App\SpotiImplementation\Tools::getApiSession());
        $api->setOptions([
            'auto_refresh' => true,
        ]);
        $request       = new \App\SpotiImplementation\Request($api);
        $request->setGenreRespository($genreRepository);
        $artists       = $request->getRandomArtistsFromGenres($genreEntities, $nbArtists, true);
        $tracksRequest = $request->getTopsTracksFromArtists($artists, $nbSongsPerArtists);

        $tracksId = array_keys($tracksRequest);
        shuffle($tracksId);
        $tracksId = array_slice($tracksId, 0, $nbSongs);

        $requestSpoti = \App\SpotiImplementation\Request::factory();
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

    public function test()
    {
        $api     = new \App\SpotifyWebAPI\SpotifyWebAPI();
        $api->setSession(\App\SpotiImplementation\Tools::getApiSession());
        $api->setOptions([
            'auto_refresh' => true,
        ]);

        $request = new \App\SpotiImplementation\Request($api);
        $artistsId = $request->getRandomArtistsFromGenre(50, 'metalcore');
        $tracks = $request->getTopsTracksFromArtists($artistsId, 2);
        shuffle($tracks);

        // spotify:playlist:74GkpvpZYcQ0fgpX9SQsWV
        // $playlist = '74GkpvpZYcQ0fgpX9SQsWV';
        // $request->addTracksToPlaylist($tracks, $playlist);
        // var_dump('done !');exit();
    }

    /**
     * @Route("/setPopularityGenres", name="setPopularityGenres")
     */
    public function setPopularityGenres(GenreRepository $genreRepository)
    {
        $api = new \App\SpotifyWebAPI\SpotifyWebAPI();
        $api->setSession(\App\SpotiImplementation\Tools::getApiSession());
        $api->setOptions([
            'auto_refresh' => true,
        ]);
        $request = new \App\SpotiImplementation\Request($api);
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
        $playlistName = $request->get('name');
        $tracks       = $request->get('tracks');

        $api = new \App\SpotifyWebAPI\SpotifyWebAPI();
        $api->setSession(\App\SpotiImplementation\Tools::getApiSession());
        $api->setOptions([
            'auto_refresh' => true,
        ]);
        $request = new \App\SpotiImplementation\Request($api);
        $playlist = $request->createNewPlaylist($playlistName);
        $request->addTracksToPlaylist($tracks, $playlist->id);

        return new Response();
    }
}
