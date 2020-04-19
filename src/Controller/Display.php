<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\Type\PlaylistSelection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use \App\SpotiImplementation\Request as SpotiRequest;
use \App\SpotiImplementation\Auth as SpotiAuth;
use \App\SpotiImplementation\Tools as SpotiTools;

class Display extends AbstractController
{
    public function displayArtists($artists)
    {
        return $this->render('spotiTemplates/_artists.html.twig', [
           'artists' => $artists,
       ]);
    }

    /**
     * @Route("/displayPlaylists", name="displayPlaylists")
     */
    public function displayPlaylists(Request $request)
    {
        $session = $request->getSession();

        if (!SpotiAuth::isUserAuthenticated($session)) {
            return $this->redirectToRoute('init');
        }

        $form = $this->createForm(PlaylistSelection::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $requestSpoti = SpotiRequest::factory();
            $requestSpoti->addTopTracksToPlaylist($data);

            SpotiTools::emptyArtistSelectionInSession();

            return $this->redirectToRoute('solenn');
        }

        return $this->render('spotiTemplates/_modale_playlists.html.twig', [
            'formPlaylistSelection' => $form->createView()
        ]);
    }

    /**
     * @Route("/displayTracks", name="displayTracks")
     *
     * @deprecated
     */
    public function displayTracks()
    {
        $tracks   = [];
        $tracksId = [
            '5fx0MPLoGImFYsnqK3jBbO',
            '0MB7xIp2KzXsN84zcd0CCG',
            '6U5dJB1GszvHA8dLvO7n50',
            '0KkcPbenGqMINYgcKYXZyJ',
            '3Iowon86yo3Gm1Lj1fouIG',
        ];
        $requestSpoti = \App\SpotiImplementation\Request::factory();
        $spotiTracks  = $requestSpoti->getTracks($tracksId);

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
