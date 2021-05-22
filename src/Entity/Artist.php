<?php

namespace App\Entity;

use App\Interfaces\SongInterface;
use App\Traits\SongTrait;
use App\Repository\ArtistRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=ArtistRepository::class)
 */
class Artist implements SongInterface
{
    use SongTrait;
    use TimestampableEntity;
    use SoftDeleteableEntity;

    public const TYPE = 'artist';

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="artists")
     */
    private UserInterface $user;

    public function getType(): string
    {
        return static::TYPE;
    }
}
