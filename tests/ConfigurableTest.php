<?php

require_once 'stubs/DummyConfigurable.php';

class ConfigurableTest extends PHPUnit_Framework_TestCase {

    protected $configurable;

    public function setUp()
    {
        $this->configurable = new DummyConfigurable();
    }

    public function testGetOption()
    {
        $this->assertEquals('b', $this->configurable->getOption('a'));
    }

    public function testSetOption()
    {
        $this->configurable->setOption('a', 'c');
        $this->assertEquals('c', $this->configurable->getOption('a'));
    }

    public function testSetOptions()
    {
        $this->configurable->setOptions(['c' => 'd']);
        $this->assertEquals('d', $this->configurable->getOption('c'));
    }

    public function testGetOptions()
    {
        $input = array('c' => 'd', 'e' => 'f');
        $this->configurable->setOptions($input);
        $this->assertEquals($input, $this->configurable->getOptions());
    }

    public function testMergeOptions()
    {
        $input = array('c' => 'd', 'e' => 'f');
        $this->configurable->mergeOptions($input);
        $output = $input + array('a' => 'b');
        $this->assertEquals($output, $this->configurable->getOptions());
    }

} 