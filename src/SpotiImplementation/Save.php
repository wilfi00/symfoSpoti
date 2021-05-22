<?php

namespace App\SpotiImplementation;

use \App\SpotiImplementation\Request as SpotiRequest;

class Save
{
    protected string $saveMode;
    protected array $tracks;
    protected string $playlistName;
    protected $playlistId;

    protected Request $spotiRequest;
    
    // Les modes de sauvegardes
    protected const MODE_NEWPLAYLIST = 'createNewPlaylist';
    protected const MODE_EXISTINGPLAYLIST = 'existingPlaylist';
    protected const MODE_QUEUE = 'queue';
    
    public function __construct(SpotiRequest $spotiRequest, string $saveMode, array $tracks = [], string $playlistName = '', $playlistId = null)
    {
        $this->saveMode = $saveMode;
        $this->tracks = $tracks;
        $this->playlistName = $playlistName;
        $this->playlistId = $playlistId;
        $this->spotiRequest  = $spotiRequest;
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
        
        $playlistName = $this->playlistName;
        $tracks       = $this->tracks;
        if ($playlistName !== '' && !empty($tracks)) {
            $playlist = $this->spotiRequest->createNewPlaylist($playlistName);
            $this->spotiRequest->addTracksToPlaylist($tracks, $playlist->id);
            // Succès de l'opération, feedback vert \o/
            $success = true;
        }
        
       return $success;
    }
    
    protected function saveUsingExistingPlaylist(): bool
    {
        return $this->spotiRequest->addTracksToPlaylist($this->tracks, $this->playlistId);
    }
    
    protected function saveUsingQueue(): bool
    {
        $successData = $this->spotiRequest->addTracksToQueue($this->tracks);
        
        return $successData['failure'] <= 0 && $successData['success'] > 0;
    }
}
