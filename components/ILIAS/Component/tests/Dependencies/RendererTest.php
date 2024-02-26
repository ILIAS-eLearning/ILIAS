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

namespace ILIAS\Component\Tests\Dependencies;

use PHPUnit\Framework\TestCase;
use ILIAS\Component\Dependencies\Reader;
use ILIAS\Component\Dependencies\Resolver;
use ILIAS\Component\Dependencies\Renderer;
use ILIAS\Component\Component;

class RendererTest extends TestCase
{
    protected Reader $reader;
    protected Resolver $resolver;
    protected Renderer $renderer;

    public function setUp(): void
    {
        $this->reader = new Reader();
        $this->resolver = new Resolver();
        $this->renderer = new Renderer();
    }

    /**
     * @dataProvider scenarios
     */
    public function testScenario($scenario_file, $result_file, $components)
    {
        require_once(__DIR__ . "/scenarios/$scenario_file");

        $components = array_map(fn($c) => $this->reader->read(new $c()), $components);
        $resolved = $this->resolver->resolveDependencies([], ...$components);

        $result = $this->renderer->render(...$resolved);

        $expected = file_get_contents(__DIR__ . "/scenarios/$result_file");
        $this->assertEquals($expected, $result);
    }

    public function scenarios()
    {
        return [
            "no dependencies" => ["scenario1.php", "result1.php",
                [
                    \ILIAS\Component\Tests\Dependencies\Scenario1\ComponentA::class
                ]
            ],
            "pull dependency" => ["scenario2.php", "result2.php",
                [
                    \ILIAS\Component\Tests\Dependencies\Scenario2\ComponentA::class,
                    \ILIAS\Component\Tests\Dependencies\Scenario2\ComponentB::class
                ]
            ],
            "use dependency" => ["scenario3.php", "result3.php",
                [
                    \ILIAS\Component\Tests\Dependencies\Scenario3\ComponentA::class,
                    \ILIAS\Component\Tests\Dependencies\Scenario3\ComponentB::class
                ]
            ],
            "seek dependency" => ["scenario4.php", "result4.php",
                [
                    \ILIAS\Component\Tests\Dependencies\Scenario4\ComponentA::class,
                    \ILIAS\Component\Tests\Dependencies\Scenario4\ComponentB::class,
                    \ILIAS\Component\Tests\Dependencies\Scenario4\ComponentC::class
                ]
            ],
            "render entry points" => ["scenario5.php", "result5.php",
                [
                    \ILIAS\Component\Tests\Dependencies\Scenario5\ComponentA::class
                ]
            ]
        ];
    }
}
