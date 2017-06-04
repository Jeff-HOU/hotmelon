<?php

require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setApplicationName("Hot Melon");
$client->setDeveloperKey("AIzaSyDsJ9M5K0R7HudCsfQCHCOx4qevfwJJx3g");

$client->setAccessType('online');

$client->addScope(Google_Service_People::USERINFO_PROFILE);

$client->addScope(array(Google_Service_Drive::DRIVE, Google_Service_Slides::PRESENTATIONS));

$client->setAuthConfig("client_secret.json");

$client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);
    $refreshToken = $client->getRefreshToken();
    $newAccessToken = $client->getAccessToken();
    $newAccessToken['refresh_token'] = $refreshToken;
    setcookie("hot_melon_token", json_encode($token), time() + (86400 * 30), "/"); // 86400 = 1 day
    echo "<script>window.location = 'https://hotmelon.tech/chat.php'</script> Redirecting...";
}

if(!isset($_COOKIE["hot_melon_token"])) {
    //get a new one
    $authUrl = $client->createAuthUrl();
    if ($client->getRedirectUri() != "https://hotmelon.tech/gapi.php") {
        echo "<script>window.location = 'https://hotmelon.tech/gapi.php'</script> Redirecting...";
        die();
    }
    header("Location: ".$authUrl);
    die;
} else {
    //refresh an old one
    //var_dump(json_decode($_COOKIE["hot_melon_token"]));
    $client->setAccessToken(json_decode($_COOKIE["hot_melon_token"], true));
}

if ($client->isAccessTokenExpired()) {
    unset($_COOKIE["hot_melon_token"]);
    setcookie("hot_melon_token", json_encode($newAccessToken), -1, "/"); // 86400 = 1 day
}

/*if (!$client->getAccessToken()) { // auth call to google
    $authUrl = $client->createAuthUrl();
    header("Location: ".$authUrl);
    die;
}*/
/*
$people = new Google_Service_People($client);

//var_dump($people->people);

$names = $people->people->get("people/me", array(
    'requestMask.includeField'=>'person.names'
));

$photos = $people->people->get("people/me", array(
    'requestMask.includeField'=>'person.photos'
));

echo $names->names[0]->givenName;
echo "<img src='" . $photos->photos[0]->url . "'>";
*/