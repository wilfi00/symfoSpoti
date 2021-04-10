<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use \App\SpotiImplementation\Request as SpotiRequest;

class Display extends AbstractController
{
    public function displayArtists($artists)
    {
        return $this->render('spotiTemplates/_artists.html.twig', [
           'artists' => $artists,
       ]);
    }
    
    /**
     * @Route("/displayTracks", name="displayTracks")
     *
     * @deprecated
     */
    public function displayTracks(SpotiRequest $spotiRequest)
    {
        $tracks   = [];
        $tracksId = [
            '5fx0MPLoGImFYsnqK3jBbO',
            '0MB7xIp2KzXsN84zcd0CCG',
            '6U5dJB1GszvHA8dLvO7n50',
            '0KkcPbenGqMINYgcKYXZyJ',
            '3Iowon86yo3Gm1Lj1fouIG',
        ];
        $spotiTracks  = $spotiRequest->getTracks($tracksId);

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
}
