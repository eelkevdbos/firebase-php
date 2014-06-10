<?php namespace Firebase\Normalizer;


use GuzzleHttp\Message\ResponseInterface;

class AbstractNormalizer implements NormalizerInterface {

    protected $name;

    public function normalize(ResponseInterface $response) {}

    public function getName()
    {
        return $this->name;
    }

} 