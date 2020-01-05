<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\SpotiImplementation\Auth;

class AppController extends AbstractController
{
    /**
     * @Route("/spotiAuth")
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

        return $this->redirect('/testArea', 301);
    }

    /**
     * @Route("/testArea")
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

        // It's now possible to request data about the currently authenticated user
        // var_dump(
        //     $api->me()
        // );

        // Getting Spotify catalog data is of course also possible
        // var_dump(
        //     $api->getTrack('7EjyzZcbLxW7PaaLua9Ksb')
        // );

        //$api->changeVolume(['volume_percent' => 40]);
        // var_dump(Tools::getApiSession());
        // exit();
        $request = new \App\SpotiImplementation\Request($api);

        return new Response(
            '<html><body>' . print_r($request->getTenMetalArtists()) . '</body></html>'
        );
    }
}
