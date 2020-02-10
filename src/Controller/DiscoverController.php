<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\GenreRepository;

class DiscoverController extends AbstractController
{
    /**
     * @Route("/discover", name="discover")
     */
    public function displayDiscover()
    {
        return $this->render('testArea/discover.html.twig', [
            'jsConfig' =>[
                'searchGenreUrl' => $this->generateUrl('searchGenre'),
            ],
            'tracks' => [],
        ]);
    }

    /**
     * @Route("/searchGenre", name="searchGenre")
     */
    public function searchGenre(Request $request, GenreRepository $genreRepository)
    {
        return $this->json($genreRepository->findByGenres(json_decode($request->getContent(), true)));
    }
}
