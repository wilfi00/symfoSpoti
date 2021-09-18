<?php

namespace App\Services;

use App\Entity\Album;
use App\Entity\Artist;
use App\Entity\Recommendation;
use App\Entity\Track;
use App\SpotiImplementation\Request as SpotiRequest;
use App\Traits\SongEntityCreatorTrait;
use InvalidArgumentException;

class SearchSongService
{
    use SongEntityCreatorTrait;

    protected SpotiRequest $spotiRequest;

    public function __construct(SpotiRequest $spotiRequest)
    {
        $this->spotiRequest = $spotiRequest;
    }

    public function search(string $type, string $query): array
    {
        if (!in_array($type, $this->getAvailableTypes(), true)) {
            throw new InvalidArgumentException('Le type ' . $type . " n'est pas implémenté");
        }

        if ($type === Recommendation::GENRE_TYPE) {
            $resultSearch = $this->spotiRequest->getDirectApi()->getGenreSeeds();
        } else {
            $resultSearch = $this->spotiRequest->getDirectApi()->search($query, $type, ['limit' => 10]);
        }


        switch($type) {
            case Track::TYPE:
                return $this->getNewTracks($resultSearch->tracks->items);
            case Artist::TYPE:
                return $this->getNewArtists($resultSearch->artists->items);
            case Album::TYPE:
                return $this->getNewAlbums($resultSearch->albums->items);
            case Recommendation::GENRE_TYPE:
                return $this->searchGenre($query, $resultSearch->genres);
        }
    }

    protected function getNewTracks(array $items): array
    {
        $tracks = [];
        foreach ($items as $item) {
            $track = new Track();
            $this->setCommonDataToEntity($track, $item);
            $track->setPreviewUrl($item->preview_url);
            $track->setImage($this->getImageFromSpotiData($item->album->images));
            $tracks[] = $track;
        }

        return $tracks;
    }

    protected function getNewAlbums(array $items): array
    {
        $albums = [];
        foreach ($items as $item) {
            $album = new Album();
            $this->setCommonDataToEntity($album, $item);
            $album->setImage($this->getImageFromSpotiData($item->images));
            $albums[] = $album;
        }

        return $albums;
    }

    protected function getNewArtists(array $items): array
    {
        $artists = [];
        foreach ($items as $item) {
            $artist = new Artist();
            $this->setCommonDataToEntity($artist, $item);
            $artist->setImage($this->getImageFromSpotiData($item->images));
            $artists[] = $artist;
        }

        return $artists;
    }

    protected function getAvailableTypes(): array
    {
        return [
            Track::TYPE,
            Artist::TYPE,
            Album::TYPE,
            Recommendation::GENRE_TYPE,
        ];
    }

    protected function searchGenre(string $query, array $genres): array
    {
        $input = preg_quote($query, '~');
        return preg_grep('~' . $input . '~i', $genres);
    }
}