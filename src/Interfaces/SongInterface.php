<?php

namespace App\Interfaces;

use App\Entity\User;

Interface SongInterface
{
    public function getId(): ?int;
    public function getSpotifyId(): ?string;
    public function setSpotifyId(string $spotify_id): self;
    public function getSpotifyUri(): ?string;
    public function setSpotifyUri(string $spotify_uri): self;
    public function getName(): ?string;
    public function setName(string $name): self;
    public function getPopularity(): ?int;
    public function setPopularity(int $popularity): self;
    public function getUser(): ?User;
    public function setUser(?User $user): self;
}