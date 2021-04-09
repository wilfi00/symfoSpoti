<?php

namespace App\SpotiImplementation;

use \App\SpotiImplementation\Request as SpotiRequest;

class Save
{
    protected $saveMode;
    protected $tracks;
    protected $playlistName;
    protected $playlistId;
    
    protected $spotiRequest;
    
    // Les modes de sauvegardes
    const MODE_NEWPLAYLIST = 'createNewPlaylist';
    const MODE_EXISTINGPLAYLIST = 'existingPlaylist';
    const MODE_QUEUE = 'queue';
    
    public function __construct(SpotiRequest $spotiRequest, string $saveMode, array $tracks = [], $playlistName = '', $playlistId = null)
    {
        $this->saveMode = $saveMode;
        $this->tracks = $tracks;
        $this->playlistName = $playlistName;
        $this->playlistId = $playlistId;
        $this->spotiRequest  = $spotiRequest;
    }
    
    public function save()
    {
        $saveMode = $this->saveMode;
        
        if ($saveMode === static::MODE_NEWPLAYLIST) {
            return $this->saveUsingNewPlaylist();
        } elseif ($saveMode === static::MODE_EXISTINGPLAYLIST) {
            return $this->saveUsingExistingPlaylist();
        } elseif ($saveMode === static::MODE_QUEUE) {
            return $this->saveUsingQueue();
        } else {
            return false;
        }
    }
    
    protected function saveUsingNewPlaylist()
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
    
    protected function saveUsingExistingPlaylist() 
    {
        return $this->spotiRequest->addTracksToPlaylist($this->tracks, $this->playlistId);
    }
    
    protected function saveUsingQueue()
    {
        $successData = $this->spotiRequest->addTracksToQueue($this->tracks);
        
        return $successData['failure'] <= 0 && $successData['success'] > 0;
    }
}
