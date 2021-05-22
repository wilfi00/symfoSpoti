<?php

namespace App\Manager;

use App\Traits\SongManagerTrait;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Artist;
use Symfony\Component\Security\Core\User\UserInterface;

class ArtistManager extends AbstractManager
{
    use SongManagerTrait;

    /**
     * FacturationDetailsManager constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityClassName = Artist::class;
        parent::__construct($entityManager);
    }

    public function add(UserInterface $user, string $spotifyId, string $spotifyUri, string $name, string $image): void
    {
        $artist = new Artist();
        $this->setCommonAttributes($artist, $user, $spotifyId,  $spotifyUri,  $name, $image);
        $this->save($artist);
    }
}
