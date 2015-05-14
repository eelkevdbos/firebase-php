<?php

include dirname(__DIR__) . '/vendor/autoload.php';

use Firebase\Firebase;
use Firebase\Auth\TokenGenerator;

//initialize token generator with secret
$fbTokenGen = new TokenGenerator($argv[1]);

//initialize with custom token
$fb = Firebase::initialize($argv[2], $fbTokenGen->generateToken(array(), array('admin' => true)));

print_r($fb->get($argv[3]));