<?php namespace Firebase\Normalizer;


use GuzzleHttp\Message\ResponseInterface;

class SimpleArrayNormalizer extends AbstractNormalizer implements NormalizerInterface {

    protected $name = 'simple';

    public function normalize(ResponseInterface $response)
    {
        return array_values($response->json());
    }

} 