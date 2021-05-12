<?php

namespace App\Entity;

use App\Traits\SongTrait;
use App\Repository\ArtistRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArtistRepository::class)
 */
class Artist
{
    use SongTrait;
}
