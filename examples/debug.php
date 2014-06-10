<?php

include dirname(__DIR__) . '/vendor/autoload.php';

$fb = new Firebase\Firebase(array(
    'token' => $argv[1],
    'base_url' => $argv[2],
    'timeout' => 30,
    'debug' => true
), new GuzzleHttp\Client());

print_r($fb->get($argv[3]));