<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UtilsController extends AbstractController
{
    /**
     * @Route("/infoArtist", name="infoArtist")
     */
    public function getInfoOfArtist()
    {
        $api     = new \App\SpotifyWebAPI\SpotifyWebAPI();
        $api->setSession(\App\SpotiImplementation\Tools::getApiSession());
        $api->setOptions([
            'auto_refresh' => true,
        ]);
        $request       = new \App\SpotiImplementation\Request($api);
        var_dump($request->getArtist('1zjKozA82ritb32UljA7Yi'));exit();
    }
}
