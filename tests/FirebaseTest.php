<?php

require_once 'traits/ProtectedCaller.php';
require_once 'stubs/DummyNormalizer.php';

class FirebaseTest extends PHPUnit_Framework_TestCase {

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

    protected function setUp()
    {
        $this->request = Mockery::mock('GuzzleHttp\Message\RequestInterface');
        $this->response = Mockery::mock('GuzzleHttp\Message\ResponseInterface')->shouldIgnoreMissing();

        $this->firebaseConfig = array(
            'base_url' => 'http://baseurl',
            'token' => 'aabbcc',
            'timeout' => 30
        );

        $this->firebase = new Firebase\Firebase(
            $this->firebaseConfig,
            Mockery::mock('GuzzleHttp\ClientInterface')
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

    public function testGetRequest()
    {
        $guzzle = $this->firebase->getClient();
        $guzzle->shouldReceive(array(
            'createRequest' => $this->request,
            'send' => $this->response
        ))->once();
        $this->firebase->get('/test.json');
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
        $this->assertEquals($this->callProtected($this->firebase, 'normalizeResponse', array('A')), 'A');
    }

    public function testBatchRequests()
    {
        
    }

} 