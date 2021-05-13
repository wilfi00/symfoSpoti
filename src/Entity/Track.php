<?php

namespace App\Entity;

use App\Interfaces\SongInterface;
use App\Traits\SongTrait;
use App\Repository\TrackRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TrackRepository::class)
 */
class Track implements SongInterface
{
    use SongTrait;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $preview_url;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tracks")
     */
    private $user;

    public function getPreviewUrl(): ?string
    {
        return $this->preview_url;
    }

    public function setPreviewUrl(?string $preview_url): self
    {
        $this->preview_url = $preview_url;

        return $this;
    }
}
