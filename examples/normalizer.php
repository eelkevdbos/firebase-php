<?php

include dirname(__DIR__) . '/vendor/autoload.php';

use Firebase\Firebase;
use Firebase\Normalizer\StringNormalizer;

$fb = Firebase::initialize($argv[2], $argv[1]);

print_r($fb->normalize(new StringNormalizer())->get($argv[3]));