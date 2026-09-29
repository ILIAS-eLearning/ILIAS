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

declare(strict_types=1);

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Setup;
use ILIAS\Setup\Metrics;
use ILIAS\Setup\Metrics\Metric as M;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class StatusCommandTest extends TestCase
{
    public function testMetrics(): void
    {
        $agent_finder = $this->createStub(Setup\AgentFinder::class);
        $obj = new Setup\CLI\StatusCommand($agent_finder);
        $storage = new Metrics\ArrayStorage();
        $objective = $this->createStub(Setup\Objective::class);
        $agent = $this->createMock(Setup\AgentCollection::class);
        $expected = new M(M::STABILITY_MIXED, M::TYPE_COLLECTION, []);

        $agent
            ->expects($this->once())
            ->method("getStatusObjective")
            ->with($storage)
            ->willReturn(new Setup\ObjectiveCollection("text", false, $objective));

        $result = $obj->getMetrics($agent);

        $this->assertEquals($expected, $result);
    }

    public function testWithoutFilterAllAgentsReportAndConfigHasItsOwnSection(): void
    {
        $finder = $this->createStub(Setup\AgentFinder::class);
        $finder->method('getAgents')->willReturn($this->collection([
            'database' => $this->reporting(fn(Metrics\Storage $s) => $s->storeVolatileText('version', '8.4')),
            'common' => $this->reporting(fn(Metrics\Storage $s) => $s->storeConfigText('client', 'default')),
        ]));

        $tester = $this->runStatusCommand($finder, []);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame("database:\n    version: 8.4\nconfig:\n    common:\n        client: default\n", $tester->getDisplay());
    }

    public function testFilterForAnAgentOfTheCoreDoesNotLoadPluginsOrAskOtherAgents(): void
    {
        $finder = $this->createMock(Setup\AgentFinder::class);
        $finder->expects($this->never())->method('getAgents');
        $finder->method('getComponentAgents')->willReturn($this->collection([
            'database' => $this->reporting(fn(Metrics\Storage $s) => $s->storeVolatileText('version', '8.4')),
            'language' => $this->notAsked(),
        ]));

        $tester = $this->runStatusCommand($finder, ['--filter' => 'database.version']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame("8.4\n", $tester->getDisplay());
    }

    public function testFilterForAPluginOnlyAsksThatPlugin(): void
    {
        $finder = $this->createStub(Setup\AgentFinder::class);
        $finder->method('getComponentAgents')->willReturn($this->collection([
            'database' => $this->notAsked(),
        ]));
        $finder->method('getAgents')->willReturn($this->collection([
            'database' => $this->notAsked(),
            'MyPlugin' => $this->reporting(fn(Metrics\Storage $s) => $s->storeStableText('version', '1.2.3')),
        ]));

        $tester = $this->runStatusCommand($finder, ['--filter' => 'MyPlugin']);

        $this->assertSame("version: 1.2.3\n", $tester->getDisplay());
    }

    public function testFilterForTheConfigSectionOnlyShowsTheConfigOfThatAgent(): void
    {
        $finder = $this->createStub(Setup\AgentFinder::class);
        $finder->method('getComponentAgents')->willReturn($this->collection([
            'common' => $this->reporting(function (Metrics\Storage $s): void {
                $s->storeConfigText('client', 'default');
                $s->storeVolatileBool('is_installed', true);
            }),
            'database' => $this->notAsked(),
        ]));

        $tester = $this->runStatusCommand($finder, ['--filter' => 'config.common']);

        $this->assertSame("client: default\n", $tester->getDisplay());
    }

    public function testUnknownAgentFailsWithAHint(): void
    {
        $finder = $this->createStub(Setup\AgentFinder::class);
        $finder->method('getComponentAgents')->willReturn($this->collection([]));
        $finder->method('getAgents')->willReturn($this->collection([
            'database' => $this->notAsked(),
        ]));

        $tester = $this->runStatusCommand($finder, ['--filter' => 'nope']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString("There is no metric 'nope'.", $tester->getDisplay());
    }

    public function testUnknownMetricNamesWhatIsAvailable(): void
    {
        $finder = $this->createStub(Setup\AgentFinder::class);
        $finder->method('getComponentAgents')->willReturn($this->collection([
            'database' => $this->reporting(function (Metrics\Storage $s): void {
                $s->storeVolatileText('version', '8.4');
                $s->storeVolatileText('engine', 'innodb');
            }),
        ]));

        $tester = $this->runStatusCommand($finder, ['--filter' => 'database.name']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString(
            "There is no metric 'database.name'. Available below 'database': version, engine",
            $tester->getDisplay()
        );
    }

    public function testFilterCannotDescendIntoASingleValue(): void
    {
        $finder = $this->createStub(Setup\AgentFinder::class);
        $finder->method('getComponentAgents')->willReturn($this->collection([
            'database' => $this->reporting(fn(Metrics\Storage $s) => $s->storeVolatileText('version', '8.4')),
        ]));

        $tester = $this->runStatusCommand($finder, ['--filter' => 'database.version.major']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString(
            "Cannot filter by 'major': 'database.version' is a single value, not a collection.",
            $tester->getDisplay()
        );
    }

    private function runStatusCommand(Setup\AgentFinder $finder, array $input): CommandTester
    {
        $tester = new CommandTester(new Setup\CLI\StatusCommand($finder));
        $tester->execute($input);
        return $tester;
    }

    /**
     * @param array<string, Setup\Agent> $agents
     */
    private function collection(array $agents): Setup\AgentCollection
    {
        return new Setup\AgentCollection(
            new Refinery(new DataFactory(), $this->createStub(Language::class)),
            $agents
        );
    }

    /**
     * @param \Closure(Metrics\Storage): void $report
     */
    private function reporting(\Closure $report): Setup\Agent
    {
        $agent = $this->createStub(Setup\Agent::class);
        $agent->method('getStatusObjective')->willReturnCallback(
            function (Metrics\Storage $storage) use ($report): Setup\Objective {
                $report($storage);
                return new Setup\ObjectiveCollection('status', false);
            }
        );
        return $agent;
    }

    private function notAsked(): Setup\Agent
    {
        $agent = $this->createMock(Setup\Agent::class);
        $agent->expects($this->never())->method('getStatusObjective');
        return $agent;
    }
}
