<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Session;
use App\SpotiImplementation\Auth as SpotiAuth;
use App\SpotiImplementation\Tools as SpotiTools;

class AuthController extends AbstractController
{
    /**
     * @Route("/spotiAuth", name="init")
     */
    public function spotiAuthentificate()
    {
        $session = new Session();
        $url = SpotiAuth::spotiInit();

        return $this->redirect($url, 301);
    }

    /**
     * @Route("/spotiCallback")
     */
    public function spotiCallback()
    {
        SpotiAuth::spotiCallback();

        $defaultUrl = $this->generateUrl('discover');

        return $this->redirect(SpotiAuth::getUrlAfterAuthentification($defaultUrl), 301);
    }
}
