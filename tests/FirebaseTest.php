<?php

class FirebaseTest extends PHPUnit_Framework_TestCase {

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
     * Helper to call protected methods
     * @param $object
     * @param $method
     * @param array $args
     * @return mixed
     */
    public static function callProtected($object, $method, $args = array())
    {
        $reflectionMethod = new ReflectionMethod(get_class($object), $method);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs($object, $args);
    }

    protected function setUp()
    {
        $this->firebaseConfig = array(
            'base_url' => 'http://baseurl',
            'token' => 'aabbcc',
            'timeout' => 30
        );

        $this->firebase = new Firebase\Firebase(
            Mockery::mock('GuzzleHttp\Client'),
            $this->firebaseConfig
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
        $response = Mockery::mock('GuzzleHttp\Message\Response');

        $guzzle->shouldReceive('get')->once()->andReturn($response);
        $response->shouldReceive('json')->once();

        $this->firebase->get('/test.json');
    }

    public function tearDown()
    {
        Mockery::close();
    }

} 