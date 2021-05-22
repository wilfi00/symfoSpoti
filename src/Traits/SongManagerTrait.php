<?php

namespace App\Traits;

use App\Interfaces\SongInterface;
use Symfony\Component\Security\Core\User\UserInterface;

trait SongManagerTrait
{
    public function setCommonAttributes(
        SongInterface $song,
        UserInterface $user,
        string $spotifyId,
        string $spotifyUri,
        string $name,
        string $image
    ): SongInterface {
        $song->setUser($user);
        $song->setSpotifyId($spotifyId);
        $song->setSpotifyUri($spotifyUri);
        $song->setName($name);
        $song->setImage($image);
        return $song;
    }
}