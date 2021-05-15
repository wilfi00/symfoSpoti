<?php

namespace App\Interfaces;

use Symfony\Component\Security\Core\User\UserInterface;

Interface SongInterface
{
    public function getId(): ?int;
    public function getSpotifyId(): ?string;
    public function setSpotifyId(string $spotify_id): self;
    public function getSpotifyUri(): ?string;
    public function setSpotifyUri(string $spotify_uri): self;
    public function getName(): ?string;
    public function setName(string $name): self;
    public function getUser(): ?UserInterface;
    public function setUser(?UserInterface $user): self;
    public function getType(): string;
    public function setImage(string $image);
}