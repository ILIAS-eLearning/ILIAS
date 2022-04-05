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
