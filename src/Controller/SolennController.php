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

        foreach ($artistsSelection as $artist) {
            $artistsTmp['name'] = $artist['name'];
            if (!empty($artist['images'])) {
                $artistsTmp['image'] = $artist['images'][0]['url'];
            }

            $artists[] = $artistsTmp;
        }
// var_dump($artists);exit();
        return $artists;
    }

    /**
     * @Route("/testAreaSolenn", name="solenn")
     */
    public function testAreaSolenn(Request $request)
    {
        $artists = $this->initArtists($request->getSession());

        $form = $this->createFormBuilder()
            ->add('artist', TextType::class,   ['label' => 'Entrez le nom de l\'artiste : '])
            ->add('save',   SubmitType::class, ['label' => 'Chercher cet artiste'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $artistName = $data['artist'];

            $request    = \App\SpotiImplementation\Request::factory();
            $answer     = $request->searchForArtist($artistName);

            foreach ($answer as $artist) {
                $tmpImg = '';
                $tmpImgArray = $artist->images;

                if (!empty($tmpImgArray)) {
                    $tmpImg = $tmpImgArray[0]->url;
                }
                $artists[] = [
                    'image' => $tmpImg,
                    'name'  => $artist->name
                ];
            }
        }

        return $this->render('testArea/solenn.html.twig', [
           'form'    => $form->createView(),
           'artists' => $artists,
       ]);
    }

    /**
     * @Route("/addArtistToSelection", name="addArtist")
     */
    public function addArtistToSelection(Request $request)
    {
        // \App\SpotiImplementation\Tools::emptyArtistSelectionInSession();
        $artist = json_decode($request->getContent(), true);
        \App\SpotiImplementation\Tools::saveArtistSelectionInSession($artist['body']);

        return new Response();
    }

    /**
     * @Route("/getArtistsSelection", name="getArtists")
     */
    public function getArtistsSelection()
    {
        return $this->json(\App\SpotiImplementation\Tools::getArtistsSelectionInSession());
    }

    /**
     * @Route("/getArtistsSelection", name="getArtistsHtml")
     */
    public function getArtistsSelectionHtml()
    {
        return $this->json(\App\SpotiImplementation\Tools::getArtistsSelectionInSession());
    }
}
