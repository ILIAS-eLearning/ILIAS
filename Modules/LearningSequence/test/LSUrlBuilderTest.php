<?php

use PHPUnit\Framework\TestCase;
use ILIAS\Data\Factory;

class LSUrlBuilderTest extends TestCase
{
    public function setUp()
    {
        $data_factory = new Factory();
        $uri = $data_factory->uri('http://ilias.de/somepath');
        $this->ub = new LSUrlBuilder($uri);
    }

    public function testDataType()
    {
        $this->assertInstanceOf(ILIAS\Data\URI::class, $this->ub->getURL('x'));
    }

    public function testUrlConcatenation()
    {
        $uri = $this->ub->getURL('command', 123);
        $expected = LSUrlBuilder::PARAM_LSO_COMMAND . '=command&'
            . LSUrlBuilder::PARAM_LSO_PARAMETER . '=123';
        $this->assertEquals($expected, $uri->query());
    }
}
