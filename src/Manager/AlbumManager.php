<?php

namespace App\Manager;

use App\Entity\Artist;
use App\Entity\User;
use App\Traits\SongManagerTrait;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Album;

class AlbumManager extends AbstractManager
{
    use SongManagerTrait;

    /**
     * FacturationDetailsManager constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityClassName = Album::class;
        parent::__construct($entityManager);
    }

    public function add(User $user, string $spotifyId, string $spotifyUri, string $name, int $popularity): void
    {
        $artist = new Album();
        $this->setCommonAttributes($artist, $user, $spotifyId,  $spotifyUri,  $name, $popularity);
        $this->save($artist);
    }
}
