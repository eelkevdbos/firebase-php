<?php

include dirname(__DIR__) . '/vendor/autoload.php';

//initialize token generator with secret
$fbTokenGen = new Firebase\Auth\TokenGenerator($argv[1]);

//setup firebase defaults
$fb = new Firebase\Firebase(new GuzzleHttp\Client(), array(
    'base_url' => $argv[2],
    'timeout' => 30
));

//set the token via the setOption method or supply the token via constructor options
$fb->setOption('token',$fbTokenGen->generateToken(array(), array('admin' => true)));

print_r($fb->get($argv[3]));