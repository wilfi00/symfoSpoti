<?php

namespace App\Services;

use App\Entity\Recommendation;
use App\Entity\Track;
use App\Traits\SongEntityCreatorTrait;
use App\SpotiImplementation\Request as SpotiRequest;
use Exception;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RecommendationsService
{
    use SongEntityCreatorTrait;

    protected SpotiRequest $spotiRequest;
    protected ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator, SpotiRequest $spotiRequest)
    {
        $this->spotiRequest = $spotiRequest;
        $this->validator = $validator;
    }

    /**
     * @param array $artists
     * @param array $genres
     * @param array $tracks
     * @param float $acousticness
     * @param float $danceability
     * @param int $duration
     * @param float $energy
     * @param float $instrumentalness
     * @param float $liveness
     * @param float $loudness
     * @param int $mode
     * @param int $popularity
     * @param float $speechiness
     * @param float $tempo
     * @param float $valence
     * @return array|object
     * @throws Exception
     */
    public function getRecommendations(
        array $artists          = [],
        array $genres           = [],
        array $tracks           = [],
        float $acousticness     = 1.0,
        float $danceability     = 1.0,
        int   $duration         = 120,
        float $energy           = 1.0,
        float $instrumentalness = 1.0,
        float $liveness         = 1.0,
        float $loudness         = 1.0,
        int   $mode             = 1,
        int   $popularity       = 42,
        float $speechiness      = 0.5,
        float $tempo            = 42.0,
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
            ->setSpeechiness($speechiness)
            ->setValence($valence);

        $errors = $this->validator->validate($recommendation);
        if (count($errors) > 0) {
            throw new Exception((string) $errors);
        }

        $songs = $this->spotiRequest->getDirectApi()->getRecommendations([
            'seed_artists' => $recommendation->getArtists(),
            'seed_genres' => $recommendation->getGenres(),
            'seed_tracks' => $recommendation->getTracks(),
            'target_acousticness' => $recommendation->getAcousticness(), // 1 => track is acoustic
            'target_danceability' => $recommendation->getDanceability(), // 1 => dansable :D
//            'target_duration_ms' => $duration,
            'target_energy' => $recommendation->getEnergy(), // intensity and activity => fast, loud and noisy) genre death meta
            'target_instrumentalness' => $recommendation->getInstrumentalness(), // 1 => no vocal (ooh and aah comptent pas)
            'target_liveness' => $recommendation->getLiveness(), // 1 => c'est du live
//            'target_loudness' => $loudness, // -60 à 0 si le son est fort sur toute la track  (Loudness is the quality of a sound that is the primary psychological correlate of physical strength (amplitude))
//            'target_mode' => $mode, // major 1 minor 0 (Mode indicates the modality (major or minor) of a track, the type of scale from which its melodic content is derived.)
            'target_popularity' => $popularity, // 0 à 100 mais pas sur du tout
            // 0.66 describe tracks that are probably made entirely of spoken words.
            // Values between 0.33 and 0.66 describe tracks that may contain both music and speech, either in sections or layered, including such cases as rap music
            // Values below 0.33 most likely represent music and other non-speech-like tracks.
            'target_speechiness' => $recommendation->getSpeechiness(),
//            'target_tempo' => $tempo, // The overall estimated tempo of a track in beats per minute (BPM).
            'target_valence' => $recommendation->getValence(), // 1 => positif happy euphoric... 0 => sad depressed angry
        ]);

        foreach ($songs->tracks as $song) {
            $track = new Track();
            $this->setCommonDataToEntity($track, $song);
            $track->setPreviewUrl($song->preview_url);
            $track->setImage($this->getImageFromSpotiData($song->album->images));
            $track->setArtists($this->getArtistsFromSong($song));
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