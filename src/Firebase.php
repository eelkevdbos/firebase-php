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
     * @var \Firebase\Normalizer\NormalizerInterface
     */
    protected $normalizer;

    public function __construct($client, $options = array())
    {
        $this->setClient($client);
        $this->setOptions($options);
    }

    /**
     * Read data from path
     * @param $path
     * @param null $defaultValue
     * @return mixed
     */
    public function get($path, $defaultValue = null)
    {
        $response = $this->client->get($this->buildUrl($path), $this->buildOptions());
        return $this->normalize($response) ?: $defaultValue;
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
        return $this->normalize($response);
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
        return $this->normalize($response);
    }

    /**
     * Delete item in path
     * @param $path
     * @return mixed
     */
    public function delete($path)
    {
        $response = $this->client->delete($this->buildUrl($path), $this->buildOptions());
        return $this->normalize($response);
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
        return $this->normalize($response);
    }

    /**
     * Normalizes the HTTP Request Client response
     * @param $response
     * @return mixed
     */
    public function normalize($response)
    {
        if(!is_null($this->normalizer)) {
            return $this->normalizer->normalize($response);
        }
        return $response->json();
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
     * Setter for response normalizer
     * @param $normalizer
     * @return $this
     */
    public function setNormalizer(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
        return $this;
    }

    /**
     * Getter for response normalizer
     * @return \Firebase\Normalizer\NormalizerInterface
     */
    public function getNormalizer()
    {
        return $this->normalizer;
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
        $options = array('query' => $this->buildQuery());

        if(!is_null($data)) {
            $options['json'] = $data;
        }

        return $options;
    }

}