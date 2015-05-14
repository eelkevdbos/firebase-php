<?php

include dirname(__DIR__) . '/vendor/autoload.php';

use Firebase\Firebase;
use GuzzleHttp\Pool;

$fb = Firebase::initialize($argv[2], $argv[1]);

$requests = $fb->batch(function ($fb) {

    /** @var Firebase $fb */
    for($i = 0; $i < 100; $i++) {
        $fb->push('list', $i);
    }

});

//pooling the requests and executing async
$pool = new Pool($fb->getClient(), $requests);
$pool->wait();

//the pool accepts an optional array as third argument
//for more info have a look at: http://docs.guzzlephp.org/en/latest/clients.html?highlight=pool