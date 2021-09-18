<?php

namespace App\Entity;

use App\Interfaces\SongInterface;
use App\Traits\SongTrait;
use App\Repository\TrackRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=TrackRepository::class)
 * @Gedmo\SoftDeleteable()
 */
class Track implements SongInterface
{
    use SongTrait;
    use TimestampableEntity;
    use SoftDeleteableEntity;

    public const TYPE = 'track';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $preview_url;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tracks")
     */
    protected UserInterface $user;

    protected ?string $artists;

    public function getPreviewUrl(): ?string
    {
        return $this->preview_url;
    }

    public function setPreviewUrl(?string $preview_url): self
    {
        $this->preview_url = $preview_url;

        return $this;
    }

    public function getType(): string
    {
        return static::TYPE;
    }

    public function setArtists(string $artists): self
    {
        $this->artists = $artists;

        return $this;
    }

    public function getArtists(): ?string
    {
        return $this->artists;
    }
}
