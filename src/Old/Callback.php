<?php

require_once 'Src/Tools.php';

Tools::getRequiredFiles();

$session = new SpotifyWebAPI\Session(
    Tools::CLIENT_ID,
    Tools::CLIENT_SECRET,
    Tools::REDIRECT_URI
);

// Request a access token using the code from Spotify
$session->requestAccessToken($_GET['code']);

// Store the access and refresh tokens somewhere. In a database for example.
/*Tools::setCurrentToken($session->getAccessToken());
Tools::setRefreshToken($session->getRefreshToken());*/
Tools::saveApiSession($session);

// Send the user along and fetch some data!
header('Location: App.php');
die();
