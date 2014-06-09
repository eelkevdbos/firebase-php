<?php

class DummyEncoder {

    /**
     * Dummy encoder, returns function arguments
     * @param $claims
     * @param $secret
     * @param $hashMethod
     * @return array
     */
    public function encode($claims, $secret, $hashMethod)
    {
        return array($claims, $secret, $hashMethod);
    }

} 