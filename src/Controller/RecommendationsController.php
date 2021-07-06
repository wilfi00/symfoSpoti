<?php

namespace App\Controller;

use App\Services\RecommendationsService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecommendationsController extends AbstractController
{
    /**
     * @Route("/recommendations", name="recommendations")
     * @param Request $request
     * @return Response
     */
    public function displayRecommendations(Request $request): Response
    {
        return $this->render('pages/recommendations.html.twig', ['songs' => []]);
    }

    /**
     * @Route("/makeRecommendation", name="makeRecommendation")
     * @param Request $request
     * @param RecommendationsService $recommendationsService
     * @return Response
     * @throws Exception
     */
    public function makeRecommendation(Request $request, RecommendationsService $recommendationsService): Response
    {
        $artists = ['7EQ0qTo7fWT7DPxmxtSYEc'];
        $genres = [];
        $tracks = [];

        $tracks = $recommendationsService->getRecommendations(
            $artists,
            $genres,
            $tracks,
            (float) $request->get('acousticness'),
            (float) $request->get('danceability'),
            (int) $request->get('duration'),
            (float) $request->get('energy'),
            (float) $request->get('instrumentalness'),
            (float) $request->get('liveness'),
            (float) $request->get('loudness'),
            (int) $request->get('mode'),
            (int) $request->get('popularity'),
            (float) $request->get('speechiness'),
            (float) $request->get('tempo'),
            (float) $request->get('valence')
        );

        return $this->render('pages/recommendations.html.twig', ['songs' => $tracks]);
    }
}
