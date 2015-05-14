<?php

include dirname(__DIR__) . '/vendor/autoload.php';

use Firebase\Firebase;
use Firebase\Criteria;

$fb = Firebase::initialize($argv[2], $argv[1]);

print_r($fb->get($argv[3], new Criteria('$key', ['equalTo' => $argv[4]])));