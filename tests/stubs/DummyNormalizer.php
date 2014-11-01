<?php

class DummyNormalizer {

    public function getName()
    {
        return 'dummy';
    }

    public function normalize($input)
    {
        return $input;
    }

} 