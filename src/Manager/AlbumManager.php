<?php

namespace App\Manager;

use App\Traits\SongManagerTrait;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Album;
use Symfony\Component\Security\Core\User\UserInterface;

class AlbumManager extends AbstractManager
{
    use SongManagerTrait;

    /**
     * FacturationDetailsManager constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityClassName = Album::class;
        parent::__construct($entityManager);
    }

    public function add(UserInterface $user, string $spotifyId, string $spotifyUri, string $name, string $image): void
    {
        $album = new Album();
        $this->setCommonAttributes($album, $user, $spotifyId,  $spotifyUri,  $name, $image);
        $this->save($album);
    }
}
