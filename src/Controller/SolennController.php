<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SolennController extends AbstractController
{
    /**
     * @Route("/testAreaSolenn", name="solenn")
     */
    public function testAreaSolenn(Request $request)
    {
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

            return $this->render('testArea/solenn.html.twig', [
               'form'       => $form->createView(),
               'artists'    => $answer,
           ]);
        }

        return $this->render('testArea/solenn.html.twig', [
           'form'    => $form->createView(),
           'artists' => null,
       ]);
    }

    /**
     * @Route("/addArtistToSelection", name="addArtist")
     */
    public function addArtistToSelection()
    {

    }
}
