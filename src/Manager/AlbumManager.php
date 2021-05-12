<?php

namespace App\Manager;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Album;

class AlbumManager extends AbstractManager
{
    /**
     * FacturationDetailsManager constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityClassName = Album::class;
        parent::__construct($entityManager);
    }
}
