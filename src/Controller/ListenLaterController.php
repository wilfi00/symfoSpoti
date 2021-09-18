<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Artist;
use App\Entity\Track;
use App\Manager\AlbumManager;
use App\Manager\ArtistManager;
use App\Manager\TrackManager;
use App\Services\SearchSongService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ListenLaterController extends AbstractController
{
    /**
     * @Route("/listenLaterNotConnected", name="listen_later_not_connected")
     * @param Security $security
     * @return RedirectResponse|Response
     */
    public function isNotConnected(Security $security)
    {
        if ($security->isGranted('ROLE_SPOTIFY')) {
            return $this->redirectToRoute('listen_later');
        }
        return $this->render('pages/listen_later_not_connected.html.twig');
    }

    /**
     * @Route("/listenLater", name="listen_later")
     * @param Security $security
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function listenLater(Security $security, TranslatorInterface $translator): Response
    {
        if (!$security->isGranted('ROLE_SPOTIFY')) {
            return $this->redirectToRoute('listen_later_not_connected');
        }

        return $this->render('pages/listen_later.html.twig', [
            'songs'    => [],
            'jsConfig' => [
                'urlAddSong' => $this->generateUrl('addListenLater'),
                'text'       => [
                    'addSuccess'    => $translator->trans('listenlater_addSucess'),
                    'feedbackError' => $translator->trans('feedbackError'),
                ],
            ],
        ]);
    }

    /**
     * @Route("/listenLaterConsult", name="listen_later_consult")
     * @param Security $security
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function listenLaterConsult(Security $security, Request $request, TranslatorInterface $translator): Response
    {
        if (!$security->isGranted('ROLE_SPOTIFY')) {
            return $this->redirectToRoute('listen_later_not_connected');
        }

        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return $this->redirectToRoute('listen_later_not_connected');
        }

        return $this->render('pages/listen_later_consult.html.twig', [
            'tracks'  => $user->getTracks(),
            'artists' => $user->getArtists(),
            'albums'  => $user->getAlbums(),
            'jsConfig' => [
                'isFrench' => $request->getLocale() === 'fr',
                'urlDeleteSong' => $this->generateUrl('delete_song'),
                'text'       => [
                    'deleteSuccess' => $translator->trans('listenlater_deleteSuccess'),
                    'feedbackError' => $translator->trans('feedbackError'),
                ],
            ]
        ]);
    }

    /**
     * @Route("/searchSongType", name="searchSongType")
     * @param Request $request
     * @param SearchSongService $searchSongService
     * @param Security $security
     * @return Response|void
     */
    public function searchSongType(Request $request, SearchSongService $searchSongService, Security $security)
    {
        if (!$security->isGranted('ROLE_SPOTIFY')) {
            return;
        }

        $type  = $request->request->get('typeSearch');
        $query = $request->request->get('searchText');

        $result = $searchSongService->search($type, $query);

        return $this->render('spotiTemplates/_songs.html.twig', ['songs' => $result]);
    }

    /**
     * @Route("/addListenLater", name="addListenLater")
     * @param Request $request
     * @param TrackManager $trackManager
     * @param ArtistManager $artistManager
     * @param AlbumManager $albumManager
     * @return Response
     */
    public function addListenLater(Request $request, TrackManager $trackManager, ArtistManager $artistManager, AlbumManager $albumManager): Response
    {
        $currentUser = $this->getUser();

        switch ($request->request->get('type')) {
            case Track::TYPE:
                $trackManager->add(
                    $currentUser,
                    $request->request->get('spotifyid'),
                    $request->request->get('spotifyuri'),
                    $request->request->get('name'),
                    $request->request->get('image')
                );
                break;
            case Artist::TYPE:
                $artistManager->add(
                    $currentUser,
                    $request->request->get('spotifyid'),
                    $request->request->get('spotifyuri'),
                    $request->request->get('name'),
                    $request->request->get('image')
                );
                break;
            case Album::TYPE:
                $albumManager->add(
                    $currentUser,
                    $request->request->get('spotifyid'),
                    $request->request->get('spotifyuri'),
                    $request->request->get('name'),
                    $request->request->get('image')
                );
                break;
        }

        $response = new Response();
        $response->setContent(json_encode([
            'success' => true,
        ]));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/deleteSong", name="delete_song")
     *
     * @param Request $request
     * @param TrackManager $trackManager
     * @param ArtistManager $artistManager
     * @param AlbumManager $albumManager
     * @return Response
     */
    public function deleteSongLater(Request $request, TrackManager $trackManager, ArtistManager $artistManager, AlbumManager $albumManager): Response
    {
        $songId = $request->request->get('id');

        switch ($request->request->get('type')) {
            case Track::TYPE:
                $track = $trackManager->find($songId);
                $trackManager->softDelete($track);
                break;
            case Artist::TYPE:
                $artist = $artistManager->find($songId);
                $artistManager->softDelete($artist);
                break;
            case Album::TYPE:
                $album = $albumManager->find($songId);
                $albumManager->softDelete($album);
                break;
        }

        $response = new Response();
        $response->setContent(json_encode([
            'success' => true,
        ]));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
