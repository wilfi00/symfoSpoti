<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\Type\PlaylistSelection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
        $form = $this->createForm(PlaylistSelection::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
dump($data);
            return $this->redirectToRoute('solenn');
        }

        return $this->render('spotiTemplates/_modale_playlists.html.twig', [
            'formPlaylistSelection' => $form->createView()
        ]);
    }
}
