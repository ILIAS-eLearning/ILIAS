<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup\AgentFinder;
use ILIAS\Setup\AgentCollection;
use ILIAS\Setup\CLI\HasAgent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

class HasAgentTest extends TestCase
{
    public function setUp() : void
    {
        $this->agent_finder = $this->createMock(AgentFinder::class);
        $this->has_agent = new class($this->agent_finder) {
            use HasAgent;
            public function __construct($af)
            {
                $this->agent_finder = $af;
            }

            public function _getRelevantAgent($i)
            {
                return $this->getRelevantAgent($i);
            }
        };
    }

    public function testGetRelevantAgentWithoutOption()
    {
        $ii = $this->createMock(InputInterface::class);
        $ac = $this->createMock(AgentCollection::class);

        $ii
            ->method("getOption")
            ->willReturn(null);

        $this->agent_finder
            ->expects($this->once())
            ->method("getAgents")
            ->with()
            ->willReturn($ac);

        $agent = $this->has_agent->_getRelevantAgent($ii);

        $this->assertEquals($ac, $agent);
    }

    public function testGetRelevantAgentWithNoPluginOption()
    {
        $ii = $this->createMock(InputInterface::class);
        $ac = $this->createMock(AgentCollection::class);

        $ii
            ->method("getOption")
            ->will($this->returnValueMap([
                ["no-plugins", true],
                ["skip", null]
            ]));

        $this->agent_finder
            ->expects($this->once())
            ->method("getCoreAgents")
            ->with()
            ->willReturn($ac);

        $agent = $this->has_agent->_getRelevantAgent($ii);

        $this->assertEquals($ac, $agent);
    }

    public function testGetRelevantAgentWithPluginNameOptions()
    {
        $ii = $this->createMock(InputInterface::class);
        $ac = $this->createMock(AgentCollection::class);

        $ii
            ->method("getOption")
            ->will($this->returnValueMap([
                ["no-plugins", null],
                ["skip", null]
            ]));

        $ii
            ->method("getArgument")
            ->with("plugin-name")
            ->willReturn("foobar");

        $this->agent_finder
            ->expects($this->once())
            ->method("getPluginAgent")
            ->with("foobar")
            ->willReturn($ac);

        $agent = $this->has_agent->_getRelevantAgent($ii);

        $this->assertEquals($ac, $agent);
    }
}
