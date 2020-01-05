<?php

namespace App\SpotiImplementation;

class Request
{
    protected $api;

    function __construct($api)
    {
        $this->api = $api;
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
}
