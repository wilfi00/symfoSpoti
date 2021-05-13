<?php

namespace App\Entity;

use App\Interfaces\SongInterface;
use App\Traits\SongTrait;
use App\Repository\AlbumRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AlbumRepository::class)
 */
class Album implements SongInterface
{
    use SongTrait;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="albums")
     */
    private $user;
}
