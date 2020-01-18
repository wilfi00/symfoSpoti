<?php

namespace App\SpotiImplementation;

class Request
{
    protected $api;

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

    public function getTenMetalArtists()
    {
        $artists = [];
        while (count($artists) < 1) {
            $tmpArtists = $this->getSeveralArtists(50);
            foreach ($tmpArtists as $tmpArtist) {
                $genres = $tmpArtist->genres;
                foreach ($genres as $genre) {
                    if (strpos($genre, 'metal') !== false) {
                        $artists[] = $tmpArtist;
                        break;
                    }
                }
            }
        }

        return $artists;
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

    public function addSeveralTracksToPlaylist()
    {
        $playlistRequest = $this->api->getMyPlaylists('limit=1');
        $playlistId      = $playlistRequest->items[0]->id;
        $tracks          = ApiTools::convertApiObjectsToIds($this->getSeveralTracks(true));

        $this->api->addPlaylistTracks($playlistId, $tracks);
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

        return array_slice($tracks, $nbTracks - 10);
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

        $this->api->addPlaylistTracks($playlistId, $tracksToAdd);
    }
}
