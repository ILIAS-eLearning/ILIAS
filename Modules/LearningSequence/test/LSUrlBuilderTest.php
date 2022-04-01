<?php declare(strict_types=1);

/* Copyright (c) 2021 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\Data\Factory;

class LSUrlBuilderTest extends TestCase
{
    protected LSUrlBuilder $ub;

    protected function setUp() : void
    {
        $data_factory = new Factory();
        $uri = $data_factory->uri('https://ilias.de/somepath');
        $this->ub = new LSUrlBuilder($uri);
    }

    public function testDataType() : void
    {
        $this->assertInstanceOf(ILIAS\Data\URI::class, $this->ub->getURL('x'));
    }

    public function testUrlConcatenation() : void
    {
        $uri = $this->ub->getURL('command', 123);
        $expected = LSUrlBuilder::PARAM_LSO_COMMAND . '=command&'
            . LSUrlBuilder::PARAM_LSO_PARAMETER . '=123';
        $this->assertEquals($expected, $uri->getQuery());
    }
}
