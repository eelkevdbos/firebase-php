<?php

include dirname(__DIR__) . '/vendor/autoload.php';

$client = new GuzzleHttp\Client();

$fb = new Firebase\Firebase(array(
    'token' => $argv[1],
    'base_url' => $argv[2],
    'timeout' => 30,
    'debug' => true
), $client);

//we can use the batched requests returned by the batch method
//but we will use the RequestsBatchedEvent to pool them up
$client->getEmitter()->on(
    'requests.batched',
    function ($event) use ($client) {
        $pool = new \GuzzleHttp\Pool($client, $event->getRequests());
        $pool->wait();
    }
);

$requests = $fb->batch(function ($client) {

    for($i = 0; $i < 100; $i++) {
        $client->push('list', $i);
    }

});

