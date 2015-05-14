<?php

include dirname(__DIR__) . '/vendor/autoload.php';

use Firebase\Firebase;

//being able to use the immutable base_url configuration for guzzle with new dependency injection
Firebase::setClientResolver(function ($options) {
    return new \GuzzleHttp\Client([
        'base_url' => $options['base_url']
    ]);
});

$fb = Firebase::initialize($argv[2], $argv[1]);

print_r($fb->get($argv[3]));
