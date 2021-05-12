<?php

namespace App\Entity;

use App\Traits\SongTrait;
use App\Repository\TrackRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TrackRepository::class)
 */
class Track
{
    use SongTrait;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $preview_url;

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
