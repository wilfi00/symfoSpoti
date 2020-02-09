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
        return $this->render('testArea/discover.html.twig', [ 'jsConfig' =>
            [
                'searchGenreUrl' => $this->generateUrl('searchGenre'),
            ]
        ]);
    }

    /**
     * @Route("/searchGenre", name="searchGenre")
     */
    public function searchGenre(Request $request, GenreRepository $genreRepository)
    {
        return $this->json($genreRepository->findByGenres(json_decode($request->getContent(), true)));
    }

    /**
     * @Route("/displaySongs", name="displaySongs")
     */
    public function displaySongs()
    {
        $songsId = [
            '5fx0MPLoGImFYsnqK3jBbO',
            '0MB7xIp2KzXsN84zcd0CCG',
            '6U5dJB1GszvHA8dLvO7n50',
            '0KkcPbenGqMINYgcKYXZyJ',
            '3Iowon86yo3Gm1Lj1fouIG',
        ];
        $requestSpoti = \App\SpotiImplementation\Request::factory();
        $answer       = $requestSpoti->searchForArtist($artistName);
        // $songs =

        return $this->render('spotiTemplates/songs.html.twig', [ 'songs' => $songs]);
    }
}
