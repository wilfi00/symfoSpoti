<?php

namespace App\SpotiImplementation;
use App\Repository\GenreRepository;
use \App\SpotiImplementation\Auth as SpotiAuth;
use SpotifyWebAPI\SpotifyWebAPI;

class Request
{
    protected $basicSession; // Permet de jouer la plupart des actions de l'API spotify
    protected $userSession; // Permet de jouer les actions liées à un utilisateur (exemple : enregistrer une playlist)
    protected $api;
    protected $genreRepository;

    function __construct($api)
    {
        $this->api = $api;
    }

    public static function factory()
    {
        $api = new SpotifyWebAPI();

        return new self($api);
    }

    protected function setBasicSession()
    {
        $this->api->setSession(SpotiAuth::getBasicApiSession());
    }

    protected function setUserSession()
    {
        $this->api->setSession(SpotiAuth::getApiSession());
        $this->api->setOptions([
            'auto_refresh' => true,
        ]);
    }

    public function searchForArtist($search)
    {
        $this->setBasicSession();
        $search = $this->api->search($search . '*', 'artist');
        return  $search->artists->items;
    }

    public function getSeveralArtists($limit = 5)
    {
        $this->setBasicSession();
        $search = $this->api->search(Tools::generateRandomCharacter() . '%', 'artist', ['limit' => $limit]);
        return  $search->artists->items;
    }

    public function getTenArtists($nbArtists = 10, $fromGenre = 'metal')
    {
        $this->setBasicSession();
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
        $this->setBasicSession();
        // $search = $this->api->search('caravan%', 'artist', ['limit' => 50]);
        $search = $this->api->search(Tools::generateRandomCharacter() . '% genre:electro+swing', 'artist', ['limit' => 50]);
        // $search = $this->api->search(Tools::generateRandomCharacter() . '% genre:metalcore', 'artist', ['limit' => 50]);

        return  $search->artists->items;
    }

    public function getRandomArtistsFromGenres($genresEntities, $nbArtists, $strict)
    {
        $this->setBasicSession();
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
        $this->setBasicSession();
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
        $this->setBasicSession();
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
        $this->setUserSession();
        if (empty($tracks) || empty($playlistId)) {
            return;
        }
        // Spotify ne peut traiter que 50 tracks max
        $multipleArraysTracks = array_chunk($tracks, 50);

        foreach($multipleArraysTracks as $tracks) {
            $this->api->addPlaylistTracks($playlistId, $tracks);
        }
        
        return true;
    }

    public function getUserPlaylistsForModaleSelection()
    {
        $this->setUserSession();
        $currentUserId = $this->api->me()->id;

        $playlists          = [];
        $tmpPlaylistRequest = [];
        $maxLimit           = 50;
        $offset             = 0;

        do {
            $tmpPlaylistsRequest = $this->api->getMyPlaylists([
                'limit'  => $maxLimit,
                'offset' => $offset,
            ])->items;

            foreach($tmpPlaylistsRequest as $tmpPlaylistRequest) {
                if (!Tools::isCurrentUserOwnerOfPlaylist($currentUserId, $tmpPlaylistRequest)) {
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
        $this->setBasicSession();
        $tracks = [];
        foreach ($artists as $artist) {
            $topTracks = $this->getTopTracksFromArtist($artist['id'], $nbTracks);
            foreach ($topTracks as $track) {
                if (array_key_exists('genres', $artist)) {
                    $tracks[$track]  = $artist['genres'];    
                } else {
                    $tracks[$track] = '';
                }
            }
        }

        return $tracks;
    }

    protected function getTopTracksFromArtist($id, $nbTracks = 10)
    {
        $this->setBasicSession();
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
        $artists     = Tools::getArtistsSelectionInSession();
        $tracksToAdd = [];

        foreach($artists as $artist) {
            $tracksToAdd = array_merge($tracksToAdd, $this->getTopTracksFromArtist($artist['id'], $nbSongs));
        }

        $this->addTracksToPlaylist($tracksToAdd, $playlistId);
    }

    public function getTracks($tracks)
    {
        $this->setBasicSession();
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
        $this->setUserSession();
        return $this->api->createPlaylist(['name' => $name]);
    }

    public function getArtist($id)
    {
        $this->setBasicSession();
        return $this->api->getArtist($id);
    }
    
    public function getAllFollowedArtists()
    {
        $this->setUserSession();
        
        $artists           = [];
        $lastArtistId      = null;
        $tmpArtistsRequest = [];
        $maxLimit          = 50;
        $offset            = 0;
        
        $security = 0;
        
        do {
            $security++;
            
            // Requête à l'API
            $tmpArtistsRequest = $this->api->getUserFollowedArtists([
                'limit'  => $maxLimit,
                'after'  => $lastArtistId,
            ])->artists;
            $currentArtists = $tmpArtistsRequest->items;

            // Stocke des infos
            $artists = array_merge($artists, $currentArtists);
            $lastArtistId = $tmpArtistsRequest->cursors->after;
    
            $offset += $maxLimit;
        } while($security < 100 && (sizeof($currentArtists) > $maxLimit));

        return $artists;
    }
    
    public function getUserInformations()
    {
        $this->setUserSession();
        return $this->api->me();
    }
    
    public function getGenreSeeds()
    {
        $this->setBasicSession();
        return $this->api->getGenreSeeds();
    }
    
    public function getBestRecommendations(array $genresEntities, int $nbTracks = 50, $includeFollowedArtits = false, $includeLikedSongs = false)
    {
        $uniqArtists = [];
        $cpt    = 0;
        $maxTry = 50;
        $tracks = [];
        $this->setBasicSession();
        $genres = $this->translateGenresToSeeds($genresEntities);
        $artists = $this->getFollowedArtistsByGenres($genresEntities);
        $likedTracks  = [];
        while ((count($tracks) < $nbTracks) && ($cpt <= $maxTry)) {
            $recos = $this->api->getRecommendations([
                'seed_artists' => $artists,
                'seed_genres'  => $genres,
                'seed_tracks'  => $likedTracks,
            ])->tracks;
            
            foreach ($recos as $reco) {
                foreach ($reco->artists as $recoArtist) {
                    $uniqArtists[] = $recoArtist->id;
                }
                $tracks[$reco->id] = $reco;
                
            }
            $cpt++;    
        }
        
        shuffle($tracks);
        return array_slice($tracks, 0, $nbTracks);
    }
    
    protected function translateGenresToSeeds(array $genresEntities)
    {
        $perfects     = [];
        $genresSeeds  = [];
        $seeds        = $this->api->getGenreSeeds()->genres;
        
        foreach ($seeds as $seed) {
            foreach ($genresEntities as $genre) {
                $name = strtolower($genre->getName());
                if ((strpos($name, strtolower($seed)) !== false)
                    || (strpos($seed, strtolower($name)) !== false)) {
                        
                    if ($seed == $name) {
                        $perfects[] = $seed;
                    } else {
                        $genresSeeds[] = $seed;
                    }
                }
            }
        }
        
        foreach ($perfects as $perfect) {
            array_unshift($genresSeeds, $perfect);
        }
        $genresSeeds = array_unique($genresSeeds);
        
        return array_slice($genresSeeds, 0, 2);
    }
    
    protected function getFollowedArtistsByGenres($genresEntities)
    {
        $artists = [];
        $artistsFollowed = $this->getAllFollowedArtists();
        foreach ($artistsFollowed as $artist) {
            foreach ($genresEntities as $genre) {
                if (in_array($genre->getName(), $artist->genres)) {
                    $artists[] = $artist->id;
                }
            }
        }
        shuffle($artists);
        return array_slice($artists, 0, 3);
    }
    
    protected function getAllLikedTracks()
    {
       // getMySavedTracks
        
        $this->setUserSession();
        
        $tracks           = [];
        $tmpTracksRequest = [];
        $maxLimit          = 50;
        $offset            = 0;
        
        $security = 0;
        
        do {
            $security++;
            
            // Requête à l'API
            $currentTracks = $this->api->getMySavedTracks([
                'limit'   => $maxLimit,
                'offset'  => $offset,
            ])->items;
            
            foreach ($currentTracks as $currentTrack) {
                $tracks[] = $currentTrack->track;
            }

    
            $offset += $maxLimit;
        } while($security < 10 && (sizeof($currentTracks) >= $maxLimit));

        return $tracks;
    }
    
    protected function getLikedTracks($genresEntities)
    {
        $this->getAllLikedTracks();
    }
    
    public function addTracksToQueue(array $tracks)
    {
        $success  = 0;
        $failure  = 0;
        
        $this->setUserSession();
        if ($this->isThereOneAvailableDevice()) {
            foreach ($tracks as $trackUri) {
                if ($this->api->queue($trackUri, $this->getActiveDevice())) {
                    $success++;
                } else {
                    $failure++;
                }
            }    
        }
        
        return [
            'success'   => $success,
            'failure'   => $failure,
            'error_msg' => 'no_device',
        ];
    }
    
    public function isThereOneAvailableDevice()
    {
        return $this->getActiveDevice() !== null;
    }
    
    public function getActiveDevice()
    {
        $this->setUserSession();
        $deviceId = null;
        foreach ($this->api->getMyDevices()->devices as $device) {
            if ($device->is_active) {
                return $device->id;
            } else {
                $deviceId = $device->id;
            }
        }
        
        return $deviceId;
    }
}
