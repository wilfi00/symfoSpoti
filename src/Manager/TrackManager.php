<?php

namespace App\Manager;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Track;

class TrackManager extends AbstractManager
{
    /**
     * FacturationDetailsManager constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityClassName = Track::class;
        parent::__construct($entityManager);
    }
}
