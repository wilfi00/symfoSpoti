<?php

namespace App\Traits;

use App\Interfaces\SongInterface;

trait SongEntityCreatorTrait
{
    protected function setCommonDataToEntity(SongInterface $song, $item): void
    {
        $song->setSpotifyId($item->id);
        $song->setName($item->name);
        $song->setSpotifyUri($item->uri);
    }

    protected function getImageFromSpotiData($data): string
    {
        if (is_array($data) && isset($data[0])) {
            return $data[0]->url;
        }

        return '';
    }
}
