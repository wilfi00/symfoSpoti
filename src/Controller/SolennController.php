<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\HttpFoundation\Session\Session;

class SolennController extends AbstractController
{
    public function initArtists($session)
    {
        $artists          = [];
        $artistsSelection = \App\SpotiImplementation\Tools::getArtistsSelectionInSession($session);

        if (empty($artistsSelection )) {
            return $artists;
        }

        foreach ($artistsSelection as $artist) {
            // var_dump($artist);exit();
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
     * @Route("/testAreaSolenn", name="solenn")
     */
    public function testAreaSolenn(Request $request)
    {
        $session = $request->getSession();

        if (!\App\SpotiImplementation\Tools::isAuthenticated($session)) {
            return $this->redirectToRoute('init');
        }

        $artists = [];

        $form = $this->createFormBuilder(null, ['attr' => ['id' => 'search-form']])
            ->add('artist', TextType::class,   ['label' => 'Entrez le nom de l\'artiste : '])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $artistName = $data['artist'];

            $requestSpoti = \App\SpotiImplementation\Request::factory();
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

        return $this->render('testArea/solenn.html.twig', [
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
        \App\SpotiImplementation\Tools::saveArtistSelectionInSession(json_decode($request->getContent(), true));

        return new Response();
    }

    /**
     * @Route("/removeArtistToSelectionUrl", name="removeArtist")
     */
    public function removeArtistToSelectionUrl(Request $request)
    {
        \App\SpotiImplementation\Tools::deleteArtistSelectionInSession($request->getContent());

        return new Response();
    }

    /**
     * @Route("/emptyArtistsSelection", name="emptyArtistsSelection")
     */
    public function emptyArtistsSelection()
    {
        \App\SpotiImplementation\Tools::emptyArtistSelectionInSession();
        return new Response();
    }
}
