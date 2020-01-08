<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Display extends AbstractController
{
    public function displayArtists($artists)
    {
//         if ($artists === null) {
//             $artists = [];
//         }
// dump( \App\SpotiImplementation\Tools::getArtistsSelectionInSession());
//         array_push($artists, \App\SpotiImplementation\Tools::getArtistsSelectionInSession());
//         // dump($artists);
        return $this->render('spotiTemplates/_artists.html.twig', [
           'artists' => $artists,
       ]);
    }
}
