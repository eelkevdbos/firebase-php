<?php

require_once 'traits/ProtectedCaller.php';
require_once 'stubs/DummyNormalizer.php';

class FirebaseTest extends PHPUnit_Framework_TestCase
{

    use ProtectedCaller;

    /**
     * @var \Firebase\Firebase
     */
    protected $firebase;

    /**
     *
     * @var array
     */
    protected $firebaseConfig;

    /**
     * @var GuzzleHttp\Message\RequestInterface
     */
    protected $request;

    /**
     * @var GuzzleHttp\Event\EmitterInterface
     */
    protected $emitter;

    /**
     * @var GuzzleHttp\Message\ResponseInterface
     */
    protected $response;

    /**
     * @var GuzzleHttp\Client;
     */
    protected $client;

    protected $token;

    protected function setUp()
    {
        $this->token = 'aabbcc';
        $this->request = Mockery::mock('GuzzleHttp\Message\RequestInterface');
        $this->response = Mockery::mock('GuzzleHttp\Message\ResponseInterface')->shouldIgnoreMissing();
        $this->emitter = Mockery::mock('GuzzleHttp\Event\EmitterInterface')->shouldIgnoreMissing();
        $this->client = Mockery::mock('GuzzleHttp\ClientInterface');

        $this->firebaseConfig = array(
            'base_url' => 'http://baseurl',
            'token'    => $this->token,
            'timeout'  => 30
        );

        $this->firebase = new Firebase\Firebase(
            $this->firebaseConfig,
            $this->client
        );
    }

    public function testOptionBuilder()
    {
        //test with token and data options
        $result = self::callProtected($this->firebase, 'buildOptions', array(array('test' => 'success')));
        $this->assertTrue(isset($result['query']) && isset($result['json']));

        //test without data
        $result = self::callProtected($this->firebase, 'buildOptions');
        $this->assertTrue(!isset($result['json']));

    }

    public function testUrlBuilder()
    {
        //test fully specified
        $pathWithJson = '/test.json';
        $result = self::callProtected($this->firebase, 'buildUrl', array($pathWithJson));
        $this->assertEquals($this->firebaseConfig['base_url'] . $pathWithJson, $result);

        //test appending .json by url builder
        $pathWithoutJson = '/test';

        $result = self::callProtected($this->firebase, 'buildUrl', array($pathWithoutJson));
        $this->assertEquals($this->firebaseConfig['base_url'] . $pathWithJson, $result);
    }

    public function testQueryBuilder()
    {
        //testing query building with tokenk supplied
        $result = self::callProtected($this->firebase, 'buildQuery');
        $this->assertEquals(array('auth' => $this->token), $result);

        //testing query building without token
        $this->firebase->setOption('token', null);
        $result = self::callProtected($this->firebase, 'buildQuery');
        $this->assertEquals(array(), $result);
    }

    public function testRequestMethods()
    {
        $guzzle = $this->firebase->getClient();

        $guzzle->shouldReceive(array(
            'createRequest' => $this->request,
            'send'          => $this->response
        ))->times(5);

        $this->firebase->get('/test.json');
        $this->firebase->push('/test.json', 'a');
        $this->firebase->update('/test.json', 'c');
        $this->firebase->set('/test.json', 'b');
        $this->firebase->delete('/test.json');
    }

    public function testEmptyGetter()
    {
        $guzzle = $this->firebase->getClient();

        $guzzle->shouldReceive(array(
            'createRequest' => $this->request,
            'send'          => $this->response
        ))->once();

        $this->firebase->get();
    }

    public function testNamedNormalizer()
    {
        //test multiple normalizers
        $this->firebase->setNormalizers(array(new DummyNormalizer()));
        $this->firebase->normalize('dummy');
    }

    public function testDuckNormalizer()
    {
        $this->firebase->normalize(new \DummyNormalizer());
        $this->assertEquals($this->callProtected($this->firebase, 'normalizeResponse', array($this->response)), $this->response);
    }

    public function testBatchRequests()
    {
        $this->firebase->getClient()
            ->shouldReceive('createRequest')
            ->twice()
            ->andReturn($this->request);

        $this->firebase->getClient()
            ->shouldReceive('getEmitter')
            ->once()
            ->andReturn($this->emitter);

        $requests = $this->firebase->batch(function ($fb) {
            $fb->get('/test/1');
            $fb->get('/test/2');
        });

        $this->assertCount(2, $requests);
    }

    public function testBatchEvents()
    {
        $this->firebase->getClient()
            ->shouldReceive('createRequest')
            ->once()
            ->andReturn($this->request);

        $this->firebase->getClient()
            ->shouldReceive('getEmitter')
            ->once()
            ->andReturn($emitter = new \GuzzleHttp\Event\Emitter());

        $emitter->on('requests.batched', function ($event) {
            $this->assertCount(1, $event->getRequests());
        });

        $this->firebase->batch(function ($fb) {
            $fb->get('/test/1');
        });
    }

    public function testAlternativeInjection()
    {
        $optionsRef = array();

        \Firebase\Firebase::setClientResolver(function ($options) use (&$optionsRef) {
            $optionsRef = $options;

            return Mockery::mock('GuzzleHttp\ClientInterface');
        });

        $firebase = new \Firebase\Firebase(array('injected_option' => true));

        $this->assertInstanceOf('GuzzleHttp\ClientInterface', $firebase->getClient());
        $this->assertArrayHasKey('injected_option', $optionsRef);
    }

    public function testEvaluatePathValueArguments()
    {

        $firebase = new \Firebase\Firebase(array('injected_option' => true));
        $pathAndValue = $this->callProtected($firebase, 'evaluatePathValueArguments', [['a', 'b']]);

        $this->assertEquals($pathAndValue, ['a', 'b']);

        $pathNullValue = $this->callProtected($firebase, 'evaluatePathValueArguments', [['a', \Firebase\Firebase::NULL_ARGUMENT]]);
        $this->assertEquals($pathNullValue, ['', 'a']);
    }

    public function testDefaultStaticConstructor()
    {
        $firebase = \Firebase\Firebase::initialize($this->firebaseConfig['base_url'], $this->token);
        $this->assertEquals($firebase->getOption('base_url'), $this->firebaseConfig['base_url']);
        $this->assertEquals($firebase->getOption('token'), $this->token);

        //unset client resolver and check for default guzzle client implementation
        \Firebase\Firebase::$clientResolver = null;
        $firebase = \Firebase\Firebase::initialize($this->firebaseConfig['base_url'], $this->token);
        $this->assertInstanceOf('GuzzleHttp\Client', $firebase->getClient());
    }

    public function testCriteriaParsing()
    {
        $query = $this->callProtected($this->firebase, 'buildQuery', [new \Firebase\Criteria('$key', ['equalTo' => 'A'])]);

        $this->assertArrayHasKey('orderBy', $query);
        $this->assertArrayHasKey('equalTo', $query);
    }

} 