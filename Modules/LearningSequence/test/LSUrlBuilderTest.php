<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
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
