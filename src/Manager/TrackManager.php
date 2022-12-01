<?php

namespace App\Manager;

use App\Traits\SongManagerTrait;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Track;
use Symfony\Component\Security\Core\User\UserInterface;

class TrackManager extends AbstractManager
{
    use SongManagerTrait;

    /**
     * FacturationDetailsManager constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityClassName = Track::class;
        parent::__construct($entityManager);
    }

    public function add(UserInterface $user, string $spotifyId, string $spotifyUri, string $name, string $image): void
    {
        $track = new Track();
        $this->setCommonAttributes($track, $user, $spotifyId,  $spotifyUri,  $name, $image);
        $this->save($track);
    }
}
