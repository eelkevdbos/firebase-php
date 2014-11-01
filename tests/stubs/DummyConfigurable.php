<?php

class DummyConfigurable {

    use Firebase\Configurable;

    /**
     * Set initial item in config
     * @return void
     */
    public function __construct()
    {
        $this->config['a'] = 'b';
    }

} 