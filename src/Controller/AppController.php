<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\SpotiImplementation\Auth;
use App\SolennArea;

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
    public function testArea()
    {
        $api = new \App\SpotifyWebAPI\SpotifyWebAPI();

        // Fetch the saved access token from somewhere. A database for example.
        //$api->setAccessToken(Tools::getCurrentToken());
        $api->setSession(\App\SpotiImplementation\Tools::getApiSession());
        $api->setOptions([
            'auto_refresh' => true,
        ]);

//         $request    = new \App\SpotiImplementation\Request($api);
//         $test       = var_export($request->getRandomArtist(), true);
// var_dump($test);

        return $this->render('testArea/base.html.twig', [
            'solennUrl' => $this->generateUrl('solenn'),
        ]);
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
