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
use \App\SpotiImplementation\Save as SpotiSave;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;
use App\Services\InfoFormatter;

class DiscoverFromArtistsController extends AbstractController
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
    public function artistSelection(Request $request, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $logger->info(InfoFormatter::KEYWORD . 'petit message de log', ['test' => 'value']);
        $session = $request->getSession();

        if (!SpotiAuth::isUserAuthenticated($session)) {
            return $this->redirectToRoute('init');
        }

        $artists = [];

        $form = $this->createFormBuilder(null, ['attr' => ['id' => 'search-form']])
            ->add('artist', TextType::class,   ['label' => false, 'attr' => ['placeholder' => 'discover_fa_fill_artist']])
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
                    'image'  => $tmpImg,
                    'name'   => $artist->name,
                    'id'     => $artist->id,
                    'genres' => $artist->genres,
                ];
            }

            return $this->render('spotiTemplates/_artists.html.twig', [
                'artists' => $artists,
           ]);
        }

        $playlists = [];
        if (SpotiAuth::isUserAuthenticated($request->getSession())) {
            $requestSpoti  = SpotiRequest::factory();
            $playlists     = $requestSpoti->getUserPlaylistsForModaleSelection();
        }

        return $this->render('pages/discover_from_artists.html.twig', [
            'playlists'     => $playlists,
            'saveUrl'       => $this->generateUrl('save_tracks_from_artists'),
            'form'          => $form->createView(),
            'artistsSearch' => $artists,
            'artistsInit'   => $this->initArtists($session),
            'jsConfig'      => [
               'addArtistToSelectionUrl'    => $this->generateUrl('addArtist'),
               'removeArtistToSelectionUrl' => $this->generateUrl('removeArtist'),
               'removeAllSelectionUrl'      => $this->generateUrl('emptyArtistsSelection'),
               'success'       => $request->query->get('success'),
               'text'          => [
                    'playlistSaveSucessFeedback' => $translator->trans('discover_playlistSaveSucessFeedback'),
                    'feedbackError'              => $translator->trans('feedbackError'),
                ],
           ],
       ]);
    }

    /**
     * @Route("/addArtistToSelection", name="addArtist")
     */
    public function addArtistToSelection(Request $request, LoggerInterface $logger)
    {
        $logger->info(InfoFormatter::KEYWORD . 'petit message de log', ['test' => 'value']);
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
    
    /**
     * @Route("/saveTracksFromArtists", name="save_tracks_from_artists")
     */
    public function saveTracksFromArtists(Request $request, Session $session)
    {
        // On part du principe que ça va échouer ;(
        $success = false;
        
        $artists = SpotiTools::getArtistsSelectionInSession($session);
        if (empty($artists)) {
            return $this->redirect($this->generateUrl('artist_selection', ['success' => 0]));
        }
        
        $data = [
            'saveOption'       => $request->request->get('saveOption'),
            'playlistName'     => $request->request->get('playlistName'),
            'existingPlaylist' => $request->request->get('existingPlaylist'),
            'artists'          => array_column($artists, 'id'),
            'nbTracks'         => $request->request->get('nbTracks'),
        ];

        // Si l'utilisateur n'est pas logé sur spotify, on le fait
        $session = $request->getSession();
        if (!SpotiAuth::isUserAuthenticated($session)) {
            // On sauvegarde les datas post avant la redirection pour se connecter
            $session->set(SpotiAuth::CALLBACK_DATA, $data);
            return $this->redirect($this->generateUrl('init'), 301);
        }
        // Récupération des données si on vient de se logger
        if ($data['saveOption'] === null) {
            $data = $session->get(SpotiAuth::CALLBACK_DATA);
        }
        
        foreach ($data['artists'] as &$artist) {
            $artist = [
              'id'     => $artist
            ];
        }
        
        $request       = SpotiRequest::factory();
        $tracksRequest = $request->getTopsTracksFromArtists(
            $data['artists'], 
            $data['nbTracks']
        );
        
        $spotiSave = new SpotiSave(
            $data['saveOption'],
            array_keys($tracksRequest),
            $data['playlistName'],
            $data['existingPlaylist'],
        );
        $success = $spotiSave->save();
        
        if ($success) {
            SpotiTools::emptyArtistSelectionInSession();
        }

        return $this->redirect($this->generateUrl('artist_selection', ['success' => $success]));
    }
}
