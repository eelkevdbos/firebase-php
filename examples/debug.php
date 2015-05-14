<?php

include dirname(__DIR__) . '/vendor/autoload.php';

use Firebase\Firebase;

$fb = $fb = Firebase::initialize($argv[2], $argv[1], ['debug' => true]);

print_r($fb->get($argv[3]));