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
     * @Route("/discover", name="discover")
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
     * @Route("/generatePlaylist", name="generatePlaylist")
     */
    public function generatePlaylist(Request $request, GenreRepository $genreRepository)
    {
        $nbSongs           = 100;
        $nbSongsPerArtists = 2;
        $nbArtists         = round($nbSongs / $nbSongsPerArtists);
        $genreEntities     = [];
        $genres            = json_decode($request->getContent(), true);
        foreach ($genres as $genre) {
            $genreEntities[] = $genreRepository->findByGenre($genre);
        }

        $api     = new \App\SpotifyWebAPI\SpotifyWebAPI();
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
