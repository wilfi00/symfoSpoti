<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Recommendation
{
    public const GENRE_TYPE = 'genre';

    private const MIN_SEEDS = 1;
    private const MAX_SEEDS = 5;
    private ?int $id;
    private array $artists = [];
    private array $genres = [];
    private array $tracks = [];

    /**
     * @Assert\Range(
     *      min = 0,
     *      max = 1,
     *      notInRangeMessage = "You must be between {{ min }} and {{ max }}",
     * )
     */
    private ?float $acousticness;

    /**
     * @Assert\Range(
     *      min = 0,
     *      max = 1,
     *      notInRangeMessage = "You must be between {{ min }} and {{ max }}",
     * )
     */
    private ?float $danceability;

    /**
     * @Assert\Range(
     *      min = 0,
     *      max = 1,
     *      notInRangeMessage = "You must be between {{ min }} and {{ max }}",
     * )
     */
    private ?float $energy;

    /**
     * @Assert\Range(
     *      min = 0,
     *      max = 1,
     *      notInRangeMessage = "You must be between {{ min }} and {{ max }}",
     * )
     */
    private ?float $instrumentalness;

    /**
     * @Assert\Range(
     *      min = 0,
     *      max = 1,
     *      notInRangeMessage = "You must be between {{ min }} and {{ max }}",
     * )
     */
    private ?float $liveness;

    /**
     * @Assert\Range(
     *      min = 0,
     *      max = 1,
     *      notInRangeMessage = "You must be between {{ min }} and {{ max }}",
     * )
     */
    private ?float $speechiness;

    /**
     * @Assert\Range(
     *      min = 0,
     *      max = 1,
     *      notInRangeMessage = "You must be between {{ min }} and {{ max }}",
     * )
     */
    private ?float $valence;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArtists(): ?array
    {
        return $this->artists;
    }

    public function setArtists(?array $artists): self
    {
        if (is_array($artists)) {
            $this->artists = $artists;
        }

        return $this;
    }

    public function getGenres(): ?array
    {
        return $this->genres;
    }

    public function setGenres(?array $genres): self
    {
        if (is_array($genres)) {
            $this->genres = $genres;
        }

        return $this;
    }

    public function getTracks(): ?array
    {
        return $this->tracks;
    }

    public function setTracks(?array $tracks): self
    {
        if (is_array($tracks)) {
            $this->tracks = $tracks;
        }

        return $this;
    }

    public function getAcousticness(): ?float
    {
        return $this->acousticness;
    }

    public function setAcousticness(?float $acousticness): self
    {
        $this->acousticness = $acousticness;

        return $this;
    }

    public function getDanceability(): ?float
    {
        return $this->danceability;
    }

    public function setDanceability(?float $danceability): self
    {
        $this->danceability = $danceability;

        return $this;
    }

    public function getEnergy(): ?float
    {
        return $this->energy;
    }

    public function setEnergy(?float $energy): self
    {
        $this->energy = $energy;

        return $this;
    }

    public function getInstrumentalness(): ?float
    {
        return $this->instrumentalness;
    }

    public function setInstrumentalness(?float $instrumentalness): self
    {
        $this->instrumentalness = $instrumentalness;

        return $this;
    }

    public function getLiveness(): ?float
    {
        return $this->liveness;
    }

    public function setLiveness(?float $liveness): self
    {
        $this->liveness = $liveness;

        return $this;
    }

    public function getSpeechiness(): ?float
    {
        return $this->speechiness;
    }

    public function setSpeechiness(?float $speechiness): self
    {
        $this->speechiness = $speechiness;

        return $this;
    }

    public function getValence(): ?float
    {
        return $this->valence;
    }

    public function setValence(?float $valence): self
    {
        $this->valence = $valence;

        return $this;
    }

    /**
     * @Assert\Callback
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context): void
    {
        $seeds = count($this->getTracks()) + count($this->getGenres()) + count($this->getArtists());
        if ($seeds < static::MIN_SEEDS || $seeds > static::MAX_SEEDS) {
            $context->buildViolation(sprintf('Le nombre de seeds doit être entre %s et %s', static::MIN_SEEDS, static::MAX_SEEDS))
                ->atPath('artists')
                ->addViolation();
        }
    }
}
