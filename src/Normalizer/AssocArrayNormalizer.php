<?php namespace Firebase\Normalizer;


use GuzzleHttp\Message\ResponseInterface;

class AssocArrayNormalizer implements NormalizerInterface {

    public function normalize(ResponseInterface $response)
    {
        return $response->json();
    }

} 