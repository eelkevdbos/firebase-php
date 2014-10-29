<?php

require_once 'traits/ProtectedCaller.php';
require_once 'stubs/DummyEncoder.php';

class TokenGeneratorTest extends PHPUnit_Framework_TestCase {

    use ProtectedCaller;

    /**
     *
     * @var \Firebase\Auth\TokenGenerator
     */
    protected $generator;

    /**
     *
     * @var string
     */
    protected $secret = 'AABBCCDD';

    public function setUp()
    {
        $this->generator = new \Firebase\Auth\TokenGenerator($this->secret, new DummyEncoder());
    }

    public function testGenerateToken()
    {
        $encoderArguments = $this->generator->generateToken(array(), array('issuedAt' => 1));
        $this->assertEquals(array(array('d' => array(), 'iat' => time(), 'v' => 0), $this->secret, 'HS256'), $encoderArguments);
    }

    /**
     * Expect claims to be build by their respective builders
     */
    public function testBuildClaim()
    {
        $version = self::callProtected($this->generator, 'buildClaim', array('version', 1));
        $this->assertEquals($version, array('v', 1));

        $issuedAt = self::callProtected($this->generator, 'buildClaim', array('issuedAt', 1));
        $this->assertEquals($issuedAt, array('iat', 1));

        $data = self::callProtected($this->generator, 'buildClaim', array('data', 1));
        $this->assertEquals($data, array('d', 1));

        $notBefore = self::callProtected($this->generator, 'buildClaim', array('notBefore', 1));
        $this->assertEquals($notBefore, array('nbf', 1));

        $expires = self::callProtected($this->generator, 'buildClaim', array('expires', 1));
        $this->assertEquals($expires, array('exp', 1));

        $admin = self::callProtected($this->generator, 'buildClaim', array('admin', 1));
        $this->assertEquals($admin, array('admin', true));

        $debug = self::callProtected($this->generator, 'buildClaim', array('debug', 0));
        $this->assertEquals($debug, array('debug', false));
    }

    public function testBuildDataClaim()
    {
        $data = self::callProtected($this->generator, 'buildClaim', array('data', array('data' => true)));
        $this->assertEquals($data, array('d', array('data' => true)));

        $this->setExpectedException('UnexpectedValueException');
        self::callProtected($this->generator, 'buildClaim', array('data', "\xB1\x31"));
    }

    public function testGetValidTimestamp()
    {
        $dateTime = new DateTime();

        $validDateTime = self::callProtected($this->generator, 'getValidTimestamp', array($dateTime));
        $this->assertEquals($validDateTime, $dateTime->getTimestamp());

        $validUnix = self::callProtected($this->generator, 'getValidTimestamp', array(127000));
        $this->assertEquals(127000, $validUnix);

        $this->setExpectedException('UnexpectedValueException');
        $invalidTime = self::callProtected($this->generator, 'getValidTimestamp', array(new stdClass()));
    }

    /**
     * Expect invalid options not to be built into a claim
     */
    public function testInvalidClaim()
    {
        $result = self::callProtected($this->generator, 'buildClaims', array(array(), array('unknown')));
        $this->assertEquals(3, count($result));
    }

} 