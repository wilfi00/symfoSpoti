<?php

namespace App\Entity;

use App\Interfaces\SongInterface;
use App\Traits\SongTrait;
use App\Repository\AlbumRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=AlbumRepository::class)
 * @Gedmo\SoftDeleteable()
 */
class Album implements SongInterface
{
    use SongTrait;
    use TimestampableEntity;
    use SoftDeleteableEntity;

    public const TYPE = 'album';

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="albums")
     */
    private UserInterface $user;

    public function getType(): string
    {
        return static::TYPE;
    }
}
