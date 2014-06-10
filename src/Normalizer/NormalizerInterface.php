<?php namespace Firebase\Normalizer;


use GuzzleHttp\Message\ResponseInterface;

interface NormalizerInterface {

    public function normalize(ResponseInterface $response);

    public function getName();

}