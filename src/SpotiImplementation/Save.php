<?php

namespace App\SpotiImplementation;

use \App\SpotiImplementation\Request as SpotiRequest;
use Exception;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\SpotifyWebAPIException;

class Save
{
    // Les modes de sauvegardes
    protected const MODE_NEWPLAYLIST = 'createNewPlaylist';
    protected const MODE_EXISTINGPLAYLIST = 'existingPlaylist';
    protected const MODE_QUEUE = 'queue';

    public function __construct(protected LoggerInterface $logger, protected SpotiRequest $spotiRequest, protected string $saveMode, protected array $tracks = [], protected string $playlistName = '', protected $playlistId = null)
    {
    }
    
    public function save(): bool
    {
        $saveMode = $this->saveMode;

        if ($saveMode === static::MODE_NEWPLAYLIST) {
            return $this->saveUsingNewPlaylist();
        }

        if ($saveMode === static::MODE_EXISTINGPLAYLIST) {
            return $this->saveUsingExistingPlaylist();
        }

        if ($saveMode === static::MODE_QUEUE) {
            return $this->saveUsingQueue();
        }

        return false;
    }
    
    protected function saveUsingNewPlaylist(): bool
    {
        $success = false;

        try {
            $playlistName = $this->playlistName;
            $tracks       = $this->tracks;
            if ($playlistName !== '' && !empty($tracks)) {
                $playlist = $this->spotiRequest->createNewPlaylist($playlistName);
                $this->spotiRequest->addTracksToPlaylist($tracks, $playlist->id);
                // Succès de l'opération, feedback vert \o/
                $success = true;
            }
        } catch(Exception $exception) {
            $this->logger->critical($exception->getMessage() . "\n" . $exception->getTraceAsString());
        }
        
       return $success;
    }
    
    protected function saveUsingExistingPlaylist(): bool
    {
        $success = false;
        try {
            $success = $this->spotiRequest->addTracksToPlaylist($this->tracks, $this->playlistId);
        } catch(Exception $exception) {
            $this->logger->critical($exception->getMessage() . "\n" . $exception->getTraceAsString());
        }

        return $success;
    }
    
    protected function saveUsingQueue(): bool
    {
        $successData = [
            'failure' => 1,
            'success' => 0,
        ];

        try {
            $successData = $this->spotiRequest->addTracksToQueue($this->tracks);
        } catch(Exception $exception) {
            $this->logger->critical($exception->getMessage() . "\n" . $exception->getTraceAsString());
        }
        
        return $successData['failure'] <= 0 && $successData['success'] > 0;
    }
}
