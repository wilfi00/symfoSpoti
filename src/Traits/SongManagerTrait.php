<?php

namespace App\Traits;

use App\Entity\User;
use App\Interfaces\SongInterface;

trait SongManagerTrait
{
    public function setCommonAttributes(SongInterface $song, User $user, string $spotifyId, string $spotifyUri, string $name, int $popularity): SongInterface
    {
        $song->setUser($user);
        $song->setSpotifyId($spotifyId);
        $song->setSpotifyUri($spotifyUri);
        $song->setName($name);
        $song->setPopularity($popularity);
        return $song;
    }
}