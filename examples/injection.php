<?php

include dirname(__DIR__) . '/vendor/autoload.php';

//being able to use the immutable base_url configuration for guzzle with new dependency injection
\Firebase\Firebase::setClientResolver(function ($options) {
   return new \GuzzleHttp\Client(array(
       'base_url' => $options['base_url']
   ));
});

$fb = new Firebase\Firebase(array(
    'token' => $argv[1],
    'base_url' => $argv[2],
    'timeout' => 30
));

print_r($fb->get($argv[3]));
