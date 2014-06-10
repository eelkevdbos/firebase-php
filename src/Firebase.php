<?php namespace Firebase;

use Firebase\Normalizer\NormalizerInterface;

class Firebase implements FirebaseInterface {

    use Configurable;

    /**
     * HTTP Request Client
     *
     * @var mixed
     */
    protected $client;

    /**
     *
     * @var array
     */
    protected $normalizers;

    /**
     *
     * @var \Firebase\Normalizer\NormalizerInterface
     */
    protected $normalizer;

    public function __construct($options = array(), $client, $normalizers = array())
    {
        $this->setClient($client);
        $this->setOptions($options);
        $this->setNormalizers($normalizers);
    }

    /**
     * Read data from path
     * @param $path
     * @return mixed
     */
    public function get($path)
    {
        $response = $this->client->get($this->buildUrl($path), $this->buildOptions());
        return $this->normalizeResponse($response);
    }

    /**
     * Set data in path
     * @param $path
     * @param $value
     * @return mixed
     */
    public function set($path, $value)
    {
        $response = $this->client->put($this->buildUrl($path), $this->buildOptions($value));
        return $this->normalizeResponse($response);
    }

    /**
     * Update exising data in path
     * @param $path
     * @param $value
     * @return mixed
     */
    public function update($path, $value)
    {
        $response = $this->client->patch($this->buildUrl($path), $this->buildOptions($value));
        return $this->normalizeResponse($response);
    }

    /**
     * Delete item in path
     * @param $path
     * @return mixed
     */
    public function delete($path)
    {
        $response = $this->client->delete($this->buildUrl($path), $this->buildOptions());
        return $this->normalizeResponse($response);
    }

    /**
     * Push item to path
     * @param $path
     * @param $value
     * @return mixed
     */
    public function push($path, $value)
    {
        $response = $this->client->post($this->buildUrl($path), $this->buildOptions($value));
        return $this->normalizeResponse($response);
    }


    /**
     * Set a normalizer by string or a normalizer instance
     * @param string|\Firebase\Normalizer\NormalizerInterface $normalizer
     * @return $this
     */
    public function normalize($normalizer)
    {
        //ductyping normalizer
        if(method_exists($normalizer, 'normalize')) {
            $this->normalizer = $normalizer;
        } else if(isset($this->normalizers[$normalizer])) {
            $this->normalizer = $this->normalizers[$normalizer];
        }
        return $this;
    }

    /**
     * Normalizes the HTTP Request Client response
     * @param $response
     * @return mixed
     */
    public function normalizeResponse($response)
    {
        if(!is_null($this->normalizer)) {
            return $this->normalizer->normalize($response);
        }

        //default responsen is decoded json
        return $response->json();
    }

    /**
     * Set normalizers in an associative array
     * @param $normalizers
     * @return $this
     */
    public function setNormalizers($normalizers)
    {
        foreach($normalizers as $normalizer)
        {
            $this->normalizers[$normalizer->getName()] = $normalizer;
        }
        return $this;
    }

    /**
     * HTTP Request Client setter
     * @param $client
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * HTTP Request Client getter
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Prefix url with a base_url if present
     * @param $path
     * @return string
     */
    protected function buildUrl($path)
    {
        $url = $this->getOption('base_url','') . $path;

        //append .json if fix_url option is true and .json is missing
        if($this->getOption('fix_url', true) && strpos($url, '.json') === false) {
            $url .= '.json';
        }

        return $url;
    }

    /**
     * Build Query parameters for HTTP Request Client
     * @return array
     */
    protected function buildQuery()
    {
        $params = array();

        if($token = $this->getOption('token', false)) {
            $params['auth'] = $token;
        }

        return $params;
    }

    /**
     * Build options array for HTTP Request Client
     * @param null|array $data
     * @return array
     */
    protected function buildOptions($data = null)
    {
        $options = array(
            'query' => $this->buildQuery(),
            'debug' => $this->getOption('debug', false),
            'timeout' => $this->getOption('timeout', 0)
        );

        if(!is_null($data)) {
            $options['json'] = $data;
        }

        return $options;
    }

}