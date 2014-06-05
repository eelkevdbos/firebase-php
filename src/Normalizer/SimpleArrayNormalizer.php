<?php namespace Firebase\Normalizer;


use GuzzleHttp\Message\ResponseInterface;

class SimpleArrayNormalizer implements NormalizerInterface {

    public function normalize(ResponseInterface $response)
    {
        return array_values($response->json());
    }

} 