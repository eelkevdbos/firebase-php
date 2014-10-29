<?php

include dirname(__DIR__) . '/vendor/autoload.php';

$client = new GuzzleHttp\Client();

$fb = new Firebase\Firebase(array(
    'token' => $argv[1],
    'base_url' => $argv[2],
    'timeout' => 30
), $client);

$requests = $fb->batch(function ($client) {

    for($i = 0; $i < 100; $i++) {
        $client->push('list', $i);
    }

});

//pooling the requests and executing async
$pool = new \GuzzleHttp\Pool($client, $requests);
$pool->wait();

//the pool accepts an optional array as third argument
//for more info have a look at: http://docs.guzzlephp.org/en/latest/clients.html?highlight=pool