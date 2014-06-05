<?php namespace Firebase\Normalizer;

use GuzzleHttp\Message\ResponseInterface;

class ObjectNormalizer implements NormalizerInterface {

    public function normalize(ResponseInterface $response)
    {
        return $response->json(array('object' => true));
    }

} 