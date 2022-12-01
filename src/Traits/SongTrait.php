<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

trait SongTrait
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected int $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected string $spotify_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected string $spotify_uri;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $image = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpotifyId(): ?string
    {
        return $this->spotify_id;
    }

    public function setSpotifyId(string $spotify_id): self
    {
        $this->spotify_id = $spotify_id;

        return $this;
    }

    public function getSpotifyUri(): ?string
    {
        return $this->spotify_uri;
    }

    public function setSpotifyUri(string $spotify_uri): self
    {
        $this->spotify_uri = $spotify_uri;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(?string $imageUrl): self
    {
        $this->image = $imageUrl;

        return $this;
    }
}
