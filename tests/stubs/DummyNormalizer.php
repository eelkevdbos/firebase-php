<?php

class DummyNormalizer implements \Firebase\Normalizer\NormalizerInterface {

    public function getName()
    {
        return 'dummy';
    }

    public function normalize(\GuzzleHttp\Message\ResponseInterface $response)
    {
        return $response;
    }

} 