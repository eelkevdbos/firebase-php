<?php

include 'vendor/autoload.php';

$fb = new Firebase\Firebase(new GuzzleHttp\Client(), array(
    'token' => $argv[0],
    'base_url' => $argv[1],
    'timeout' => 30
));

print_r($fb->get($argv[2]));
