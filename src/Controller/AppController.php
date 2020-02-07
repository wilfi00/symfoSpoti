<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\SpotiImplementation\Auth;
use App\SolennArea;
use App\Repository\GenreRepository;

class AppController extends AbstractController
{
    /**
     * @Route("/spotiAuth", name="init")
     */
    public function spotiAuthentificate()
    {
        $url = \App\SpotiImplementation\Auth::spotiInit();

        return $this->redirect($url, 301);
    }

    /**
     * @Route("/spotiCallback")
     */
    public function spotiCallback()
    {
        \App\SpotiImplementation\Auth::spotiCallback();

        $defaultUrl = $this->generateUrl('testArea');

        return $this->redirect(\App\SpotiImplementation\Tools::getUrlAfterAuthentification($defaultUrl), 301);
    }

    /**
     * @Route("/testArea", name="testArea")
     */
    public function testArea(GenreRepository $genreRepository)
    {
        $genres = $genreRepository->findByGenres(['metal']);
        // var_dump($genres);exit();

        return $this->render('testArea/discover.html.twig');
    }

    /**
     * @Route("/generateMetalcore", name="generateMetalcore")
     */
    public function generateHundredMetalCoreSongs()
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
        $playlist = '74GkpvpZYcQ0fgpX9SQsWV';
        $request->addTracksToPlaylist($tracks, $playlist);
        var_dump('done !');exit();
    }
}
