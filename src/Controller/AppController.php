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

        return $this->redirect($this->generateUrl('testArea'), 301);
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

        $request    = new \App\SpotiImplementation\Request($api);
        $test       = var_export($request->getSeveralArtists(1), true);

        return $this->render('testArea/base.html.twig', [
            'solennUrl' => $this->generateUrl('solenn'),
        ]);
    }
}
