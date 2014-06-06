<?php namespace Firebase\Normalizer;


use GuzzleHttp\Message\ResponseInterface;

class StringNormalizer implements NormalizerInterface {

    public function normalize(ResponseInterface $response)
    {
        return (string)$response->getBody();
    }

} 