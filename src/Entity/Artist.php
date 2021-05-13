<?php

namespace App\Entity;

use App\Interfaces\SongInterface;
use App\Traits\SongTrait;
use App\Repository\ArtistRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArtistRepository::class)
 */
class Artist implements SongInterface
{
    use SongTrait;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="artists")
     */
    private $user;
}
