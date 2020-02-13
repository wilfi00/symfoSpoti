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
        $genre   = json_decode($request->getContent(), true)[0];
        $genre   = $genreRepository->findByGenre($genre);
        // var_dump($genre);exit();
        $api     = new \App\SpotifyWebAPI\SpotifyWebAPI();
        $api->setSession(\App\SpotiImplementation\Tools::getApiSession());
        $api->setOptions([
            'auto_refresh' => true,
        ]);
        $request       = new \App\SpotiImplementation\Request($api);
        $request->setGenreRespository($genreRepository);
        $artistsId     = $request->getRandomArtistsFromGenre($genre, 50, true);
        $tracksRequest = $request->getTopsTracksFromArtists($artistsId, 2);
        shuffle($tracksRequest);

        $tracksId = [
            '5fx0MPLoGImFYsnqK3jBbO',
            '0MB7xIp2KzXsN84zcd0CCG',
            '6U5dJB1GszvHA8dLvO7n50',
            '0KkcPbenGqMINYgcKYXZyJ',
            '3Iowon86yo3Gm1Lj1fouIG',
        ];
        $tracksId = $tracksRequest;

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

$genres = array_slice($genres, 0, 200);

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
    }
}
