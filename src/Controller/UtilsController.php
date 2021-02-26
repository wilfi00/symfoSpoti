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
        $request       = \App\SpotiImplementation\Request::factory();
        var_dump($request->getArtist('1zjKozA82ritb32UljA7Yi'));
        var_dump($request->getArtist('1I9Hqy4QnMyVhZwRM2r41B'));
        exit();
    }
}
