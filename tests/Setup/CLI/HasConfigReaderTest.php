<?php

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
 
namespace ILIAS\Tests\Setup\CLI;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use ILIAS\Setup\CLI\ConfigReader;
use ILIAS\Setup\CLI\HasConfigReader;
use ILIAS\Setup\Config;
use ILIAS\Setup\Agent;

class HasConfigReaderTest extends TestCase
{
    protected $has_config_reader;

    public function setUp() : void
    {
        $this->config_reader = $this->createMock(ConfigReader::class);
        $this->has_config_reader = new class($this->config_reader) {
            use HasConfigReader;
            public function __construct($cr)
            {
                $this->config_reader = $cr;
            }

            public function _readAgentConfig(Agent $agent, InputInterface $input) : ?Config
            {
                return $this->readAgentConfig($agent, $input);
            }
        };
    }

    public function testReadAgentConfigWithoutConfig()
    {
        $agent = $this->createMock(Agent::class);
        $ii = $this->createMock(InputInterface::class);

        $agent
            ->method("hasConfig")
            ->willReturn(false)
        ;

        $this->assertNull($this->has_config_reader->_readAgentConfig($agent, $ii));
    }
}
