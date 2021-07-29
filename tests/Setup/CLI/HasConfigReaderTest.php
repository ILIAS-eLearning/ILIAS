<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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
