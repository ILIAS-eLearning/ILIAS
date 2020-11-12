<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class BuildArtifactsCommandTest extends TestCase
{
    public function testBasicFunctionality() : void
    {
        $agent_finder = $this->createMock(Setup\AgentFinder::class);

        $agent = $this->createMock(Setup\AgentCollection::class);
        $agent_finder
            ->expects($this->once())
            ->method("getAgents")
            ->with()
            ->willReturn($agent);

        $objective = $this->createMock(Setup\Objective::class);
        $agent
            ->expects($this->once())
            ->method("getBuildArtifactObjective")
            ->with()
            ->willReturn($objective);

        $objective
            ->expects($this->once())
            ->method("getPreconditions")
            ->willReturn([]);

        $objective
            ->expects($this->once())
            ->method("achieve")
            ->will($this->returnCallback(function (Setup\Environment $e) {
                return $e;
            }));

        $objective
            ->expects($this->once())
            ->method("isApplicable")
            ->willReturn(true);

        $command = new Setup\CLI\BuildArtifactsCommand($agent_finder);
        $tester = new CommandTester($command);
        $tester->execute([]);
    }
}
