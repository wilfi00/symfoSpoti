<?php

namespace App\Entity;

use App\Traits\SongTrait;
use App\Repository\AlbumRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AlbumRepository::class)
 */
class Album
{
    use SongTrait;
}
