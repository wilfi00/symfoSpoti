<?php

namespace App\SpotiImplementation;
set_time_limit(0);
use App\Repository\GenreRepository;

class Request
{
    protected $api;
    protected $genreRepository;

    function __construct($api)
    {
        $this->api = $api;
    }

    public static function factory()
    {
        $api = new \App\SpotifyWebAPI\SpotifyWebAPI();

        // Fetch the saved access token from somewhere. A database for example.
        $api->setSession(\App\SpotiImplementation\Tools::getApiSession());
        $api->setOptions([
            'auto_refresh' => true,
        ]);

        return new self($api);
    }

    public function searchForArtist($search)
    {
        $search = $this->api->search($search . '*', 'artist');
        return  $search->artists->items;
    }

    public function getSeveralArtists($limit = 5)
    {
        $search = $this->api->search(Tools::generateRandomCharacter() . '%', 'artist', ['limit' => $limit]);
        return  $search->artists->items;
    }

    public function getTenArtists($nbArtists = 10, $fromGenre = 'metal')
    {
        $artists = [];
        while (count($artists) < $nbArtists) {
            $tmpArtists = $this->getSeveralArtists(50);
            foreach ($tmpArtists as $tmpArtist) {
                $genres = $tmpArtist->genres;
                foreach ($genres as $genre) {
                    if (strpos($genre, $fromGenre) !== false) {
                        $artists[] = $tmpArtist;
                        break;
                    }
                }
            }
        }

        return $artists;
    }

    public function getRandomArtist2()
    {
        // $search = $this->api->search('caravan%', 'artist', ['limit' => 50]);
        $search = $this->api->search(Tools::generateRandomCharacter() . '% genre:electro+swing', 'artist', ['limit' => 50]);
        // $search = $this->api->search(Tools::generateRandomCharacter() . '% genre:metalcore', 'artist', ['limit' => 50]);

        return  $search->artists->items;
    }

    public function getRandomArtistsFromGenres($genresEntities, $nbArtists, $strict)
    {
        $artists = [];
        foreach ($genresEntities as $genre) {
            $artists = array_merge($artists, $this->getRandomArtistsFromGenre($genre, $nbArtists, $strict));
            // API rate limit spotify
            sleep(2);
        }
        return $artists;
    }

    public function getRandomArtistsFromGenre(\App\Entity\Genre $genre, $nbArtists = 10, $strict = true, $maxTry = 50)
    {
        $cpt         = 0;
        $artists     = [];
        $genre       = Tools::formatStringForSpotify($genre->getName());
        $tmpArtistId = []; // Permet de gérer le fait de récupérer des artistes uniques
        $nbArtists   = Tools::addErrorProbability($nbArtists);

        while ((count($artists) < $nbArtists) && ($cpt <= $maxTry)) {
            $cpt++;
            $search = $this->api->search(Tools::generateRandomCharacter() . '% genre:' . $genre, 'artist', ['limit' => 50]);
            $searchArtists = $search->artists->items;
            shuffle($searchArtists);
            foreach ($searchArtists as $artist) {
                if ($strict) {
                    foreach ($artist->genres as $g) {
                        if (count($artists) >= $nbArtists) {
                            break;
                        }

                        if ($g === Tools::formatInverseStringForSpotify($genre)) {
                            $id = $artist->id;
                            // On ne rajoute que des artistes uniques
                            if (!in_array($id, $tmpArtistId)) {
                                $tmpArtistId[] = $id;
                                $artists[]     = [
                                    'id'     => $id,
                                    'genres' => $artist->genres,
                                ];
                            }

                        }
                    };
                } else {
                    $artists[] = $artist->id;
                }

                if (count($artists) === $nbArtists) {
                    break;
                }
            }

            // Log
            $genreRepository = $this->getGenreRepository();
            if (!empty($genreRepository)) {
                $genreRepository->updateProgressOfPopularityGenres(Tools::formatInverseStringForSpotify($genre), $cpt);
            }
        }

        $genreRepository->updateTries(Tools::formatInverseStringForSpotify($genre), $cpt);
        return  $artists;
    }

    public function getSeveralTracks($metal = false)
    {
        $tracks    = [] ;
        if ($metal) {
            $artists = $this->getTenMetalArtists();
        } else {
            $artists = $this->getSeveralArtists();
        }
        $artistsId = ApiTools::convertApiObjectsToIds($artists);

        foreach ($artistsId as $id) {
            $topTracks = $this->api->getArtistTopTracks($id, 'country=FR');
            foreach ($topTracks->tracks as $topTrack) {
                $tracks[] = $topTrack;
            }
        }

        return $tracks;
    }

    public function addTracksToPlaylist($tracks, $playlistId)
    {
        // Spotify ne peut traiter que 50 tracks max
        $multipleArraysTracks = array_chunk($tracks, 50);

        foreach($multipleArraysTracks as $tracks) {
            $this->api->addPlaylistTracks($playlistId, $tracks);
        }
    }

    public function getUserPlaylistsForModaleSelection()
    {
        $currentUserId = $this->api->me()->id;

        $playlists          = [];
        $playlistsRequest   = [];
        $tmpPlaylistRequest = [];
        $maxLimit           = 50;
        $offset             = 0;

        do {
            $tmpPlaylistsRequest = $this->api->getMyPlaylists([
                'limit'  => $maxLimit,
                'offset' => $offset,
            ])->items;

            foreach($tmpPlaylistsRequest as $tmpPlaylistRequest) {
                if (!\App\SpotiImplementation\Tools::isCurrentUserOwnerOfPlaylist($currentUserId, $tmpPlaylistRequest)) {
                    continue;
                }

                $name = $tmpPlaylistRequest->name;
                if (empty($name)) {
                    $name = 'Aucun nom';
                }
                $playlists[$name] = $tmpPlaylistRequest->id;
            }

            $offset += $maxLimit;
        } while(sizeof($tmpPlaylistsRequest) >= $maxLimit);

        return $playlists;
    }

    public function getTopsTracksFromArtists($artists, $nbTracks)
    {
        $tracks = [];
        foreach ($artists as $artist) {
            $topTracks = $this->getTopTracksFromArtist($artist['id'], $nbTracks);
            foreach ($topTracks as $track) {
                $tracks[$track]  = $artist['genres'];
            }
        }

        return $tracks;
    }

    protected function getTopTracksFromArtist($id, $nbTracks = 10)
    {
        if ($nbTracks < 1 || $nbTracks > 10) {
            $nbTracks = 10;
        }

        $tracks   = [];
        $topTracks = $this->api->getArtistTopTracks($id, 'country=FR')->tracks;
        foreach($topTracks as $track) {
            $tracks[] = $track->id;
        }

        return array_slice($tracks, 0, $nbTracks);
    }

    public function addTopTracksToPlaylist($data)
    {
        $nbSongs     = $data['nbSongs'];
        $playlistId  = $data['playlist'];
        $artists     = \App\SpotiImplementation\Tools::getArtistsSelectionInSession();
        $tracksToAdd = [];

        foreach($artists as $artist) {
            $tracksToAdd = array_merge($tracksToAdd, $this->getTopTracksFromArtist($artist['id'], $nbSongs));
        }

        $this->addTracksToPlaylist($tracksToAdd, $playlistId);
    }

    public function getTracks($tracks)
    {
        $tracksToReturn = [];

        // Spotify ne peut traiter que 50 tracks max
        $multipleArraysTracks = array_chunk($tracks, 50);

        foreach($multipleArraysTracks as $tracks) {
            $tracksToReturn = array_merge($tracksToReturn, $this->api->getTracks($tracks)->tracks);
        }

        return $tracksToReturn;
    }

    public function setGenreRespository(GenreRepository $genreRepository)
    {
        $this->genreRepository = $genreRepository;
    }

    protected function getGenreRepository()
    {
        return $this->genreRepository;
    }

    public function createNewPlaylist($name, $isPublic = true)
    {
        return $this->api->createPlaylist(['name' => $name]);
    }
}
