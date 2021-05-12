<?php

namespace App\SpotiImplementation;

use App\Repository\GenreRepository;
use \App\SpotiImplementation\Auth as SpotiAuth;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Component\Security\Core\Security;

class Request
{
    protected $api;
    protected $genreRepository;
    protected $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
        $this->api = new SpotifyWebAPI(['auto_retry' => true, 'auto_refresh' => true]);
        
        $user = $this->security->getUser();
        if (null === $user) {
            $spotiSession = SpotiAuth::makeBasicAuth();
        } else {
            $spotiSession = SpotiAuth::makeUserAuth($user);
        }
        $this->api->setSession($spotiSession);
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

    public function getRandomArtistsFromGenres($genresEntities, $nbArtists, $strict): array
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
        $genreRepository = $this->getGenreRepository();
        $cpt             = 0;
        $artists         = [];
        $genreString     = Tools::formatStringForSpotify($genre->getName());
        $tmpArtistId     = []; // Permet de gérer le fait de récupérer des artistes uniques
        $nbArtistsProb   = Tools::addErrorProbability($nbArtists);

        while ((count($artists) < $nbArtistsProb) && ($cpt <= $maxTry)) {
            $cpt++;
            $search = $this->api->search(Tools::generateRandomCharacter() . '% genre:' . $genreString, 'artist', ['limit' => 50]);
            $searchArtists = $search->artists->items;
            shuffle($searchArtists);
            foreach ($searchArtists as $artist) {
                if ($strict) {
                    foreach ($artist->genres as $g) {
                        if (count($artists) >= $nbArtistsProb) {
                            break;
                        }

                        if ($g === Tools::formatInverseStringForSpotify($genreString)) {
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

                if (count($artists) === $nbArtistsProb) {
                    break;
                }
            }

            // Log
            if (!empty($genreRepository)) {
                $genreRepository->updateProgressOfPopularityGenres(Tools::formatInverseStringForSpotify($genreString), $cpt);
            }
        }

        $genreRepository->updateTries(Tools::formatInverseStringForSpotify($genreString), $cpt);
        return  $artists;
    }

    public function addTracksToPlaylist($tracks, $playlistId)
    {
        if (empty($tracks) || empty($playlistId)) {
            return;
        }
        // Spotify ne peut traiter que 50 tracks max
        $multipleArraysTracks = array_chunk($tracks, 50);

        foreach ($multipleArraysTracks as $track) {
            $this->api->addPlaylistTracks($playlistId, $track);
        }
        
        return true;
    }

    public function getUserPlaylistsForModaleSelection(): array
    {
        $currentUserId = $this->api->me()->id;

        $playlists          = [];
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
        } while(count($tmpPlaylistsRequest) >= $maxLimit);

        return $playlists;
    }

    public function getTopsTracksFromArtists($artists, $nbTracks): array
    {
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

    protected function getTopTracksFromArtist($id, $nbTracks = 10): array
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

    public function getTracks($tracks): array
    {
        $tracksToReturn = [];

        // Spotify ne peut traiter que 50 tracks max
        $multipleArraysTracks = array_chunk($tracks, 50);

        foreach($multipleArraysTracks as $tracks) {
            $tracksToReturn = array_merge($tracksToReturn, $this->api->getTracks($tracks)->tracks);
        }

        return $tracksToReturn;
    }

    public function setGenreRespository(GenreRepository $genreRepository): void
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

    public function getArtist($id)
    {
        return $this->api->getArtist($id);
    }
    
    public function getAllFollowedArtists(): array
    {
        $artists           = [];
        $lastArtistId      = null;
        $maxLimit          = 50;
        
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

        } while($security < 100 && $tmpArtistsRequest->total > count($artists));

        return $artists;
    }
    
    public function getBestRecommendations(array $genresEntities, int $nbTracks = 50): array
    {
        $uniqArtists = [];
        $cpt    = 0;
        $maxTry = 50;
        $tracks = [];
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
                $tracks[$reco->id] = $reco;
            }
            $cpt++;    
        }
        
        shuffle($tracks);
        return array_slice($tracks, 0, $nbTracks);
    }
    
    protected function translateGenresToSeeds(array $genresEntities): array
    {
        $perfects     = [];
        $genresSeeds  = [];
        $seeds        = $this->api->getGenreSeeds()->genres;
        
        foreach ($seeds as $seed) {
            foreach ($genresEntities as $genre) {
                $name = strtolower($genre->getName());
                if ((stripos($name, $seed) !== false)
                    || (stripos($seed, $name) !== false)) {
                        
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
    
    protected function getFollowedArtistsByGenres($genresEntities): array
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
    
    protected function getAllLikedTracks(): array
    {
        $tracks           = [];
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
    
    public function addTracksToQueue(array $tracks): array
    {
        $success  = 0;
        $failure  = 0;
        
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
    
    public function isThereOneAvailableDevice(): bool
    {
        return $this->getActiveDevice() !== null;
    }
    
    public function getActiveDevice()
    {
        $deviceId = null;
        foreach ($this->api->getMyDevices()->devices as $device) {
            if ($device->is_active) {
                return $device->id;
            }

            $deviceId = $device->id;
        }
        
        return $deviceId;
    }
}
