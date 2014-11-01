<?php namespace Firebase\Normalizer;


use GuzzleHttp\Message\ResponseInterface;

abstract class AbstractNormalizer implements NormalizerInterface {

    protected $name;

    abstract function normalize(ResponseInterface $response);

    public function getName()
    {
        return $this->name;
    }

} 