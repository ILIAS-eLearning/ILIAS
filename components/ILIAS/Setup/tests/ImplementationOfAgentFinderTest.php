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
}
