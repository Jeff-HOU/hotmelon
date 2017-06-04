<?php

require_once 'gapi.php';

$client->setIncludeGrantedScopes(true);

$slides = new Google_Service_Slides($client);

$presentation = $slides->presentations->create(new Google_Service_Slides_Presentation(array(
    "title" => "testing"
)));
echo $presentation->presentationId;
