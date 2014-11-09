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

    protected function setUp()
    {
        $this->request = Mockery::mock('GuzzleHttp\Message\RequestInterface');
        $this->response = Mockery::mock('GuzzleHttp\Message\ResponseInterface')->shouldIgnoreMissing();
        $this->emitter = Mockery::mock('GuzzleHttp\Event\EmitterInterface')->shouldIgnoreMissing();
        $this->client = Mockery::mock('GuzzleHttp\ClientInterface');

        $this->firebaseConfig = array(
            'base_url' => 'http://baseurl',
            'token' => 'aabbcc',
            'timeout' => 30
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

        //test turning off appending .json
        $this->firebase->setOption('fix_url', false);

        $result = self::callProtected($this->firebase, 'buildUrl', array($pathWithoutJson));
        $this->assertEquals($this->firebaseConfig['base_url'] . $pathWithoutJson, $result);
    }

    public function testQueryBuilder()
    {
        //testing query building with tokenk supplied
        $result = self::callProtected($this->firebase, 'buildQuery');
        $this->assertEquals(array('auth' => 'aabbcc'), $result);

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
            'send' => $this->response
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
            'send' => $this->response
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

} 