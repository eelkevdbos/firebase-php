<?php namespace Firebase;

use Firebase\Event\RequestsBatchedEvent;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\RequestInterface;

class Firebase implements FirebaseMethods {

    use Configurable;

    /**
     * HTTP Request Client
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     *
     * @var array
     */
    protected $normalizers;

    /**
     * Request array for batching
     * @var array
     */
    protected $requests = array();

    /**
     *
     * @var \Firebase\Normalizer\NormalizerInterface
     */
    protected $normalizer;

    public function __construct($options = array(), ClientInterface $client, $normalizers = array())
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
        $request = $this->createRequest('GET', $path);
        return $this->handleRequest($request);
    }

    /**
     * Set data in path
     * @param $path
     * @param $value
     * @return mixed
     */
    public function set($path, $value)
    {
        $request = $this->createRequest('PUT', $path, $value);
        return $this->handleRequest($request);
    }

    /**
     * Update exising data in path
     * @param $path
     * @param $value
     * @return mixed
     */
    public function update($path, $value)
    {
        $request = $this->createRequest('PATCH', $path, $value);
        return $this->handleRequest($request);
    }

    /**
     * Delete item in path
     * @param $path
     * @return mixed
     */
    public function delete($path)
    {
        $request = $this->createRequest('DELETE', $path);
        return $this->handleRequest($request);
    }

    /**
     * Push item to path
     * @param $path
     * @param $value
     * @return mixed
     */
    public function push($path, $value)
    {
        $request = $this->createRequest('POST', $path, $value);
        return $this->handleRequest($request);
    }

    /**
     * Create a Request object
     * @param $method
     * @param $path
     * @param $value
     * @return \GuzzleHttp\Message\RequestInterface
     */
    protected function createRequest($method, $path, $value = null)
    {
        return $this->client->createRequest($method, $this->buildUrl($path), $this->buildOptions($value));
    }

    /**
     * Stores requests when batching, sends request
     * @param RequestInterface $request
     * @return mixed
     */
    protected function handleRequest(RequestInterface $request)
    {
        if (!$this->getOption('batch', false)) {
            $response = $this->client->send($request);
            return $this->normalizeResponse($response);
        }
        $this->requests[] = $request;
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
    protected function normalizeResponse($response)
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
     * @param ClientInterface $client
     * @return $this
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return ClientInterface
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


    public function batch($callable)
    {
        //enable batching in the config
        $this->setOption('batch', true);

        //gather requests
        call_user_func_array($callable, array($this));

        $request = $this->requests;

        $emitter = $this->client->getEmitter();
        $emitter->emit('requests.batched', new RequestsBatchedEvent($this->requests));

        //reset the requests for the next batch
        $this->requests = [];

        return $request;
    }

}