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
use ILIAS\Setup\Agent;
use ILIAS\Setup\AgentCollection;
use ILIAS\Setup\NamedAgent;
use ILIAS\Setup\ImplementationOfAgentFinder;
use ILIAS\Setup\ImplementationOfInterfaceFinder;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\Language;
use PHPUnit\Framework\TestCase;

class ImplementationOfAgentFinderTest extends TestCase
{
    private string $plugin_directory;

    protected function setUp(): void
    {
        $this->plugin_directory = sys_get_temp_dir() . "/ilias_agent_finder_" . bin2hex(random_bytes(4))
            . "/Customizing/global/plugins";
    }

    protected function tearDown(): void
    {
        $root = dirname($this->plugin_directory, 3);
        if (!is_dir($root)) {
            return;
        }
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($root);
    }

    private function finder(
        array $component_agents,
        ?ImplementationOfInterfaceFinder $interface_finder = null
    ): ImplementationOfAgentFinder {
        $refinery = new Refinery(new DataFactory(), $this->createStub(Language::class));

        return new ImplementationOfAgentFinder(
            $refinery,
            new DataFactory(),
            $this->createStub(Language::class),
            $interface_finder ?? $this->createStub(ImplementationOfInterfaceFinder::class),
            $component_agents,
            $this->plugin_directory
        );
    }

    private function createPlugin(string $path, string ...$agent_classes): void
    {
        mkdir($this->plugin_directory . "/" . $path . "/classes", 0777, true);
        foreach ($agent_classes as $class) {
            $file = $this->plugin_directory . "/" . $path . "/classes/class.$class.php";
            file_put_contents($file, sprintf(<<<'PHP'
                <?php
                class %1$s extends \ilPluginDefaultAgent
                {
                    public function __construct(
                        \ILIAS\Refinery\Factory $refinery,
                        \ILIAS\Data\Factory $data_factory,
                        \ILIAS\Language\Language $lng
                    ) {
                        parent::__construct('%1$s');
                    }
                }
                PHP, $class));
            require_once $file;
        }
    }

    /**
     * A marker objective, declared in the global namespace exactly once, so the
     * generated plugin agents (which live in the global namespace, under the plugin
     * path) can return it and the assertions can name it.
     */
    private function ensureTestObjectiveClass(): void
    {
        if (class_exists(\TestPluginObjective::class, false)) {
            return;
        }
        $file = sys_get_temp_dir() . "/ilias_agent_finder_test_objective.php";
        file_put_contents(
            $file,
            "<?php\n\nclass TestPluginObjective extends \\ILIAS\\Setup\\Objective\\NullObjective\n{\n}\n"
        );
        require_once $file;
    }

    /**
     * Writes a plugin agent class with a given body into the plugin tree and loads
     * it, so its class file really sits under the plugin path the finder matches on.
     * Returns the generated (suffixed, so unique across tests) class name.
     */
    private function writePluginAgent(string $plugin, string $template): string
    {
        $class = "il" . $plugin . "Agent" . bin2hex(random_bytes(4));
        $dir = $this->plugin_directory . "/Services/Repository/RepositoryObject/" . $plugin . "/classes";
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $file = $dir . "/class.$class.php";
        file_put_contents($file, "<?php\n\n" . sprintf($template, $class));
        require_once $file;
        return $class;
    }

    /** A class extending the plugin default agent, named %1$s. */
    private function defaultAgentTemplate(): string
    {
        return <<<'PHP'
            class %1$s extends \ilPluginDefaultAgent
            {
                public function __construct(
                    \ILIAS\Refinery\Factory $refinery,
                    \ILIAS\Data\Factory $data_factory,
                    \ILIAS\Language\Language $lng
                ) {
                    parent::__construct('%1$s');
                }
            }
            PHP;
    }

    /** A class implementing Setup\Agent (not extending the default agent), named %1$s. */
    private function implementingAgentTemplate(): string
    {
        return <<<'PHP'
            class %1$s implements \ILIAS\Setup\Agent
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

                public function getArrayToConfigTransformation(): \ILIAS\Refinery\Transformation
                {
                    throw new \LogicException('not needed');
                }

                public function getInstallObjective(?\ILIAS\Setup\Config $config = null): \ILIAS\Setup\Objective
                {
                    return new \TestPluginObjective();
                }

                public function getUpdateObjective(?\ILIAS\Setup\Config $config = null): \ILIAS\Setup\Objective
                {
                    return new \TestPluginObjective();
                }

                public function getBuildObjective(): \ILIAS\Setup\Objective
                {
                    return new \ILIAS\Setup\Objective\NullObjective();
                }

                public function getStatusObjective(\ILIAS\Setup\Metrics\Storage $storage): \ILIAS\Setup\Objective
                {
                    return new \ILIAS\Setup\Objective\NullObjective();
                }

                public function getMigrations(): array
                {
                    return ['test_migration' => new class () implements \ILIAS\Setup\Migration {
                        public function getLabel(): string
                        {
                            return 'test migration';
                        }

                        public function getDefaultAmountOfStepsPerRun(): int
                        {
                            return 1;
                        }

                        public function getPreconditions(\ILIAS\Setup\Environment $environment): array
                        {
                            return [];
                        }

                        public function prepare(\ILIAS\Setup\Environment $environment): void
                        {
                        }

                        public function step(\ILIAS\Setup\Environment $environment): void
                        {
                        }

                        public function getRemainingAmountOfSteps(): int
                        {
                            return 0;
                        }
                    }];
                }

                public function getNamedObjectives(?\ILIAS\Setup\Config $config = null): array
                {
                    return [
                        'test_objective' => new \ILIAS\Setup\ObjectiveConstructor(
                            'test objective',
                            static fn(): \ILIAS\Setup\Objective => new \ILIAS\Setup\Objective\NullObjective()
                        )
                    ];
                }
            }
            PHP;
    }

    private function pluginFinder(array $agent_classes): ImplementationOfAgentFinder
    {
        $interface_finder = $this->createStub(ImplementationOfInterfaceFinder::class);
        $interface_finder
            ->method('getMatchingClassNames')
            ->willReturn(new \ArrayIterator($agent_classes));

        return $this->finder([], $interface_finder);
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

    public function testNamedAgentIsKeyedByItsDeclaredName(): void
    {
        $named = $this->createStub(NamedAgent::class);
        $named->method('getAgentName')->willReturn('content_isolation');

        $collection = $this->finder([$named])->getComponentAgents();

        $this->assertSame($named, $collection->getAgent('content_isolation'));
    }

    public function testPlainAgentFallsBackToClassNameKeying(): void
    {
        $plain = $this->createStub(Agent::class);

        $collection = $this->finder([$plain])->getComponentAgents();

        // no semantic name -> not reachable under one, but reachable by class name
        $this->assertNull($collection->getAgent('content_isolation'));
        $this->assertSame($plain, $collection->getAgent($plain::class));
    }

    public function testPluginsAreFoundFourLevelsBelowThePluginDirectory(): void
    {
        $this->createPlugin("Services/Repository/RepositoryObject/Alpha");
        $this->createPlugin("Modules/Course/CourseHook/Beta");
        mkdir($this->plugin_directory . "/Services/Repository/RepositoryObject/.hidden", 0777, true);
        touch($this->plugin_directory . "/Services/Repository/RepositoryObject/README.md");

        $agents = $this->finder([])->getAgents();

        $this->assertEqualsCanonicalizing(['Alpha', 'Beta'], array_keys($agents->getAgents()));
        $this->assertInstanceOf(\ilPluginDefaultAgent::class, $agents->getAgent('Alpha'));
    }

    public function testAgentsOfAllPluginsAreSearchedInOnePass(): void
    {
        $suffix = bin2hex(random_bytes(4));
        $gamma = "ilGammaSetupAgent$suffix";
        $delta_1 = "ilDeltaOneSetupAgent$suffix";
        $delta_2 = "ilDeltaTwoSetupAgent$suffix";
        $this->createPlugin("Services/Repository/RepositoryObject/Gamma", $gamma);
        $this->createPlugin("Services/Repository/RepositoryObject/Delta", $delta_1, $delta_2);
        $this->createPlugin("Services/Repository/RepositoryObject/Epsilon");

        $interface_finder = $this->createMock(ImplementationOfInterfaceFinder::class);
        $interface_finder
            ->expects($this->once())
            ->method('getMatchingClassNames')
            ->willReturn(new \ArrayIterator([$gamma, $delta_1, $delta_2]));

        $agents = $this->finder([], $interface_finder)->getAgents();

        $this->assertInstanceOf($gamma, $agents->getAgent('Gamma'));
        $this->assertInstanceOf(AgentCollection::class, $agents->getAgent('Delta'));
        $this->assertCount(2, $agents->getAgent('Delta')->getAgents());
        $this->assertInstanceOf(\ilPluginDefaultAgent::class, $agents->getAgent('Epsilon'));
    }

    public function testPluginAgentIgnoresClassesOfOtherPlugins(): void
    {
        $suffix = bin2hex(random_bytes(4));
        $zeta = "ilZetaSetupAgent$suffix";
        // a plugin that happens to contain a directory named like another plugin
        $this->createPlugin("Services/Repository/RepositoryObject/Eta/lib/Zeta", $zeta);

        $interface_finder = $this->createStub(ImplementationOfInterfaceFinder::class);
        $interface_finder->method('getMatchingClassNames')->willReturn(new \ArrayIterator([$zeta]));

        $agent = $this->finder([], $interface_finder)->getPluginAgent('Zeta');

        $this->assertNotInstanceOf($zeta, $agent);
        $this->assertInstanceOf(\ilPluginDefaultAgent::class, $agent);
    }

    public function testPluginWithoutAgentGetsTheDefaultAgent(): void
    {
        $agent = $this->pluginFinder([])->getPluginAgent('TestPlugin');

        $this->assertInstanceOf(\ilPluginDefaultAgent::class, $agent);
        $this->assertNotInstanceOf(\ilPluginAgentWithDefaultObjectives::class, $agent);
    }

    public function testAgentExtendingTheDefaultAgentIsUsedDirectly(): void
    {
        $class = $this->writePluginAgent('TestPlugin', $this->defaultAgentTemplate());

        $agent = $this->pluginFinder([$class])->getPluginAgent('TestPlugin');

        $this->assertInstanceOf($class, $agent);
    }

    public function testAgentImplementingAgentGetsTheDefaultObjectives(): void
    {
        $this->ensureTestObjectiveClass();
        $class = $this->writePluginAgent('TestPlugin', $this->implementingAgentTemplate());

        $agent = $this->pluginFinder([$class])->getPluginAgent('TestPlugin');

        $this->assertInstanceOf(\ilPluginAgentWithDefaultObjectives::class, $agent);

        $install_objectives = $this->flatten($agent->getInstallObjective());
        $this->assertContains(\ilComponentInstallPluginObjective::class, $install_objectives);
        $this->assertContains(\ilComponentActivatePluginsObjective::class, $install_objectives);
        $this->assertContains(\TestPluginObjective::class, $install_objectives);

        $update_objectives = $this->flatten($agent->getUpdateObjective());
        $this->assertContains(\ilComponentUpdatePluginObjective::class, $update_objectives);
        $this->assertNotContains(\ilComponentActivatePluginsObjective::class, $update_objectives);
        $this->assertContains(\TestPluginObjective::class, $update_objectives);
    }

    public function testAgentImplementingAgentKeepsItsPaths(): void
    {
        $this->ensureTestObjectiveClass();
        $class = $this->writePluginAgent('TestPlugin', $this->implementingAgentTemplate());

        $agent = $this->pluginFinder([$class])->getPluginAgent('TestPlugin');

        $this->assertSame(['test_objective'], array_keys($agent->getNamedObjectives()));
        $this->assertSame(['test_migration'], array_keys($agent->getMigrations()));
        $this->assertTrue($agent->hasConfig());
    }

    public function testSeveralAgentsAreCollectedUnderTheirShortClassNames(): void
    {
        $default_agent = $this->writePluginAgent('TestPlugin', $this->defaultAgentTemplate());
        $implementing_agent = $this->writePluginAgent('TestPlugin', $this->implementingAgentTemplate());

        $agent = $this->pluginFinder([$default_agent, $implementing_agent])->getPluginAgent('TestPlugin');

        $this->assertInstanceOf(AgentCollection::class, $agent);
        $this->assertInstanceOf($default_agent, $agent->getAgent($default_agent));
        $this->assertInstanceOf($implementing_agent, $agent->getAgent($implementing_agent));
    }
}
