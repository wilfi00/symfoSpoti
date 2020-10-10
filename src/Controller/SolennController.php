<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use \App\SpotiImplementation\Request as SpotiRequest;
use \App\SpotiImplementation\Auth as SpotiAuth;
use \App\SpotiImplementation\Tools as SpotiTools;
use Symfony\Component\HttpFoundation\Session\Session;

class SolennController extends AbstractController
{
    public function initArtists($session)
    {
        $artists          = [];
        $artistsSelection = SpotiTools::getArtistsSelectionInSession($session);

        if (empty($artistsSelection )) {
            return $artists;
        }

        foreach ($artistsSelection as $artist) {
            $artistsTmp['name'] = $artist['name'];

            if (isset($artist['image'])) {
                $artistsTmp['image'] = $artist['image'];
            } elseif (!empty($artist['images'])) {
                $artistsTmp['image'] = $artist['images'][0]['url'];
            }

            $artistsTmp['id'] = $artist['id'];

            $artists[] = $artistsTmp;
        }

        return $artists;
    }

    /**
     * @Route("/artistsSelection", name="artist_selection")
     */
    public function testAreaSolenn(Request $request)
    {
        $session = $request->getSession();

        if (!SpotiAuth::isUserAuthenticated($session)) {
            return $this->redirectToRoute('init');
        }

        $artists = [];

        $form = $this->createFormBuilder(null, ['attr' => ['id' => 'search-form']])
            ->add('artist', TextType::class,   ['label' => 'Entrez le nom d\'un groupe : '])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data       = $form->getData();
            $artistName = $data['artist'];

            $requestSpoti = SpotiRequest::factory();
            $answer       = $requestSpoti->searchForArtist($artistName);

            foreach ($answer as $artist) {
                $tmpImg = '';
                $tmpImgArray = $artist->images;

                if (!empty($tmpImgArray)) {
                    $tmpImg = $tmpImgArray[0]->url;
                }
                $artists[] = [
                    'image' => $tmpImg,
                    'name'  => $artist->name,
                    'id'    => $artist->id
                ];
            }

            return $this->render('spotiTemplates/_artists.html.twig', [
                'artists' => $artists,
           ]);
        }

        return $this->render('pages/solenn.html.twig', [
           'form'          => $form->createView(),
           'artistsSearch' => $artists,
           'artistsInit'   => $this->initArtists($session),
           'jsConfig'      => [
               'addArtistToSelectionUrl'    => $this->generateUrl('addArtist'),
               'removeArtistToSelectionUrl' => $this->generateUrl('removeArtist'),
               'removeAllSelectionUrl'      => $this->generateUrl('emptyArtistsSelection'),
           ]
       ]);
    }

    /**
     * @Route("/addArtistToSelection", name="addArtist")
     */
    public function addArtistToSelection(Request $request)
    {
        SpotiTools::saveArtistSelectionInSession(json_decode($request->getContent(), true));

        return new Response();
    }

    /**
     * @Route("/removeArtistToSelectionUrl", name="removeArtist")
     */
    public function removeArtistToSelectionUrl(Request $request)
    {
        SpotiTools::deleteArtistSelectionInSession($request->getContent());

        return new Response();
    }

    /**
     * @Route("/emptyArtistsSelection", name="emptyArtistsSelection")
     */
    public function emptyArtistsSelection()
    {
        SpotiTools::emptyArtistSelectionInSession();
        return new Response();
    }
}
