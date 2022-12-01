<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use \App\SpotiImplementation\Request as SpotiRequest;

class UtilsController extends AbstractController
{
    /**
     * @Route("/infoArtist", name="infoArtist")
     */
    public function getInfoOfArtist(SpotiRequest $spotiRequest): never
    {
        var_dump($spotiRequest->getArtist('1zjKozA82ritb32UljA7Yi'));
        var_dump($spotiRequest->getArtist('1I9Hqy4QnMyVhZwRM2r41B'));
        exit();
    }
}
