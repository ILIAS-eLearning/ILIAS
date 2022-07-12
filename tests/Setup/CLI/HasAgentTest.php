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

    public function testGetRelevantAgentWithoutOption() : void
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

    public function testGetRelevantAgentWithNoPluginOption() : void
    {
        $ii = $this->createMock(InputInterface::class);
        $ac = $this->createMock(AgentCollection::class);

        $ii
            ->method("hasOption")
            ->willReturn(true);

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

    public function testGetRelevantAgentWithPluginNameOptions() : void
    {
        $ii = $this->createMock(InputInterface::class);
        $ac = $this->createMock(AgentCollection::class);


        $ii
            ->method("hasOption")
            ->willReturn(true);

        $ii
            ->method("getOption")
            ->will($this->returnValueMap([
                ["no-plugins", null],
                ["plugin", "foobar"],
                ["skip", null]
            ]));

        $this->agent_finder
            ->expects($this->once())
            ->method("getPluginAgent")
            ->with("foobar")
            ->willReturn($ac);

        $agent = $this->has_agent->_getRelevantAgent($ii);

        $this->assertEquals($ac, $agent);
    }
}
