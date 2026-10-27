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

namespace ILIAS\Tests\Setup;

use ILIAS\Setup;
use ILIAS\Setup\AgentCollection;
use ILIAS\Setup\ImplementationOfAgentFinder;
use ILIAS\Setup\ImplementationOfInterfaceFinder;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Refinery\Transformation;
use PHPUnit\Framework\TestCase;

class ImplementationOfAgentFinderTest extends TestCase
{
    public function testPluginWithoutAgentGetsTheDefaultAgent(): void
    {
        $agent = $this->getFinder([])->getPluginAgent('TestPlugin');

        $this->assertInstanceOf(\ilPluginDefaultAgent::class, $agent);
        $this->assertNotInstanceOf(\ilPluginAgentWithDefaultObjectives::class, $agent);
    }

    public function testAgentExtendingTheDefaultAgentIsUsedDirectly(): void
    {
        $agent = $this->getFinder([TestPluginDefaultAgent::class])->getPluginAgent('TestPlugin');

        $this->assertInstanceOf(TestPluginDefaultAgent::class, $agent);
    }

    public function testAgentImplementingAgentGetsTheDefaultObjectives(): void
    {
        $agent = $this->getFinder([TestPluginAgent::class])->getPluginAgent('TestPlugin');

        $this->assertInstanceOf(\ilPluginAgentWithDefaultObjectives::class, $agent);

        $install_objectives = $this->flatten($agent->getInstallObjective());
        $this->assertContains(\ilComponentInstallPluginObjective::class, $install_objectives);
        $this->assertContains(\ilComponentActivatePluginsObjective::class, $install_objectives);
        $this->assertContains(TestPluginObjective::class, $install_objectives);

        $update_objectives = $this->flatten($agent->getUpdateObjective());
        $this->assertContains(\ilComponentUpdatePluginObjective::class, $update_objectives);
        $this->assertNotContains(\ilComponentActivatePluginsObjective::class, $update_objectives);
        $this->assertContains(TestPluginObjective::class, $update_objectives);
    }

    public function testAgentImplementingAgentKeepsItsPaths(): void
    {
        $agent = $this->getFinder([TestPluginAgent::class])->getPluginAgent('TestPlugin');

        $this->assertSame(['test_objective'], array_keys($agent->getNamedObjectives()));
        $this->assertSame(['test_migration'], array_keys($agent->getMigrations()));
        $this->assertTrue($agent->hasConfig());
    }

    public function testSeveralAgentsAreCollectedUnderTheirShortClassNames(): void
    {
        $agent = $this->getFinder(
            [TestPluginDefaultAgent::class, TestPluginAgent::class]
        )->getPluginAgent('TestPlugin');

        $this->assertInstanceOf(AgentCollection::class, $agent);
        $this->assertInstanceOf(TestPluginDefaultAgent::class, $agent->getAgent('TestPluginDefaultAgent'));
        $this->assertInstanceOf(TestPluginAgent::class, $agent->getAgent('TestPluginAgent'));
    }

    protected function getFinder(array $agent_classes): ImplementationOfAgentFinder
    {
        $interface_finder = $this->createStub(ImplementationOfInterfaceFinder::class);
        $interface_finder
            ->method('getMatchingClassNames')
            ->willReturn(new \ArrayIterator($agent_classes));

        return new ImplementationOfAgentFinder(
            $this->createStub(\ILIAS\Refinery\Factory::class),
            new \ILIAS\Data\Factory(),
            $this->createStub(\ILIAS\Language\Language::class),
            $interface_finder,
            []
        );
    }

    /**
     * @return string[] class names of all objectives, collections resolved
     */
    protected function flatten(Setup\Objective $objective): array
    {
        if (!$objective instanceof Setup\ObjectiveCollection) {
            return [get_class($objective)];
        }

        return array_merge(...array_map(
            fn(Setup\Objective $o): array => $this->flatten($o),
            $objective->getObjectives()
        ));
    }
}

class TestPluginObjective extends NullObjective
{
}

class TestPluginAgent implements Setup\Agent
{
    public function __construct(
        \ILIAS\Refinery\Factory $refinery,
        \ILIAS\Data\Factory $data_factory,
        \ILIAS\Language\Language $lng
    ) {
    }

    public function hasConfig(): bool
    {
        return true;
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        throw new \LogicException('not needed');
    }

    public function getInstallObjective(Setup\Config $config = null): Setup\Objective
    {
        return new TestPluginObjective();
    }

    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        return new TestPluginObjective();
    }

    public function getBuildObjective(): Setup\Objective
    {
        return new NullObjective();
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new NullObjective();
    }

    public function getMigrations(): array
    {
        return ['test_migration' => $this->createStub()];
    }

    public function getNamedObjectives(?Setup\Config $config = null): array
    {
        return [
            'test_objective' => new Setup\ObjectiveConstructor(
                'test objective',
                static fn(): Setup\Objective => new NullObjective()
            )
        ];
    }

    private function createStub(): Setup\Migration
    {
        return new class () implements Setup\Migration {
            public function getLabel(): string
            {
                return 'test migration';
            }

            public function getDefaultAmountOfStepsPerRun(): int
            {
                return 1;
            }

            public function getPreconditions(Setup\Environment $environment): array
            {
                return [];
            }

            public function prepare(Setup\Environment $environment): void
            {
            }

            public function step(Setup\Environment $environment): void
            {
            }

            public function getRemainingAmountOfSteps(): int
            {
                return 0;
            }
        };
    }
}

class TestPluginDefaultAgent extends \ilPluginDefaultAgent
{
    public function __construct(
        \ILIAS\Refinery\Factory $refinery,
        \ILIAS\Data\Factory $data_factory,
        \ILIAS\Language\Language $lng
    ) {
        parent::__construct('TestPlugin');
    }
}
