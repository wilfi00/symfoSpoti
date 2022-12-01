<?php

namespace App\Services;

use App\Entity\Recommendation;
use App\Entity\Track;
use App\Traits\SongEntityCreatorTrait;
use App\SpotiImplementation\Request as SpotiRequest;
use RuntimeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RecommendationsService
{
    use SongEntityCreatorTrait;

    protected const DEFAULT_FLOATVALUE = 0.5;
    protected const DEFAULT_INTVALUE = 50;

    public function __construct(protected ValidatorInterface $validator, protected SpotiRequest $spotiRequest)
    {
    }

    /**
     * @return array|object
     */
    public function getRecommendations(
        array $artists          = [],
        array $genres           = [],
        array $tracks           = [],
        float $acousticness     = 1.0,
        float $danceability     = 1.0,
//        int   $duration         = 120,
        float $energy           = 1.0,
        float $instrumentalness = 1.0,
        float $liveness         = 1.0,
//        float $loudness         = 1.0,
//        int   $mode             = 1,
        int   $popularity       = 42,
        float $speechiness      = 0.5,
//        float $tempo            = 42.0,
        float $valence          = 1.0
    ): array {
        $recommendationsTracks = [];
        $recommendation = (new Recommendation())
            ->setArtists($artists)
            ->setGenres($genres)
            ->setTracks($tracks)
            ->setAcousticness($acousticness)
            ->setDanceability($danceability)
            ->setEnergy($energy)
            ->setInstrumentalness($instrumentalness)
            ->setLiveness($liveness)
            ->setPopularity($popularity)
            ->setSpeechiness($speechiness)
            ->setValence($valence);

        $errors = $this->validator->validate($recommendation);
        if (count($errors) > 0) {
            throw new RuntimeException((string) $errors);
        }

        $filters = [
            'seed_artists' => $recommendation->getArtists(),
            'seed_genres' => $recommendation->getGenres(),
            'seed_tracks' => $recommendation->getTracks(),
        ];
        if ($recommendation->getAcousticness() !== static::DEFAULT_FLOATVALUE) {
            $filters['target_acousticness'] = $recommendation->getAcousticness();
        }
        if ($recommendation->getDanceability() !== static::DEFAULT_FLOATVALUE) {
            $filters['target_danceability'] = $recommendation->getDanceability();
        }
        if ($recommendation->getEnergy() !== static::DEFAULT_FLOATVALUE) {
            $filters['target_energy'] = $recommendation->getEnergy();
        }
        if ($recommendation->getInstrumentalness() !== static::DEFAULT_FLOATVALUE) {
            $filters['target_instrumentalness'] = $recommendation->getInstrumentalness();
        }
        if ($recommendation->getLiveness() !== static::DEFAULT_FLOATVALUE) {
            $filters['target_liveness'] = $recommendation->getLiveness();
        }
        if ($recommendation->getPopularity() !== static::DEFAULT_INTVALUE) {
            $filters['target_popularity'] = $recommendation->getPopularity();
        }
        if ($recommendation->getSpeechiness() !== static::DEFAULT_FLOATVALUE) {
            $filters['target_speechiness'] = $recommendation->getSpeechiness();
        }
        if ($recommendation->getValence() !== static::DEFAULT_FLOATVALUE) {
            $filters['target_valence'] = $recommendation->getValence();
        }

        $songs = $this->spotiRequest->getDirectApi()->getRecommendations($filters);

        foreach ($songs->tracks as $song) {
            $track = new Track();
            $this->setCommonDataToEntity($track, $song);
            $track->setPreviewUrl($song->preview_url);
            $track->setImage($this->getImageFromSpotiData($song->album->images));
            $track->setArtists($this->getArtistsFromSong($song));
            $track->setPopularity($song->popularity);
            $recommendationsTracks[] = $track;
        }

        return $recommendationsTracks;
    }

    protected function getArtistsFromSong($song): string
    {
        $artists = '';
        foreach ($song->artists as $artist) {
            $artists .= $artist->name . ', ';
        }

        return substr($artists, 0, -2);
    }
}