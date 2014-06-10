<?php namespace Firebase\Normalizer;


use GuzzleHttp\Message\ResponseInterface;

class AssocArrayNormalizer extends AbstractNormalizer implements NormalizerInterface {

    protected $name = 'assoc';

    public function normalize(ResponseInterface $response)
    {
        return $response->json();
    }

} 