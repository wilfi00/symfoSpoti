<?php

require_once 'Src/Tools.php';

Tools::getRequiredFiles();

$api = new SpotifyWebAPI\SpotifyWebAPI();

// Fetch the saved access token from somewhere. A database for example.
//$api->setAccessToken(Tools::getCurrentToken());
$api->setSession(Tools::getApiSession());
$api->setOptions([
    'auto_refresh' => true,
]);

// It's now possible to request data about the currently authenticated user
// var_dump(
//     $api->me()
// );

// Getting Spotify catalog data is of course also possible
// var_dump(
//     $api->getTrack('7EjyzZcbLxW7PaaLua9Ksb')
// );

//$api->changeVolume(['volume_percent' => 40]);
// var_dump(Tools::getApiSession());
// exit();
$request = new Request($api);
var_dump($request->getTenMetalArtists());
// $request->addSeveralTracksToPlaylist();

// $search = $api->search('the hardest part is forgetting those you swore', 'track', ['limit' => '20']);
// $tracks = $search->tracks->items;
//
// $search = $api->search('being as an ocean', 'artist', ['limit' => '20']);
// $artists = $search->artists->items;
// var_dump($artists);
