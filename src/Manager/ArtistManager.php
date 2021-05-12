<?php

namespace App\Manager;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Artist;

class ArtistManager extends AbstractManager
{
    /**
     * FacturationDetailsManager constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityClassName = Artist::class;
        parent::__construct($entityManager);
    }
    
}
