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

use ILIAS\Setup;
use ILIAS\Setup\Metrics;
use PHPUnit\Framework\TestCase;
use ILIAS\Setup\Metrics\Metric as M;
use ILIAS\Setup\Metrics\MetricType as MT;
use ILIAS\Setup\Metrics\MetricStability as MS;

class StatusCommandDummy extends Setup\CLI\StatusCommand
{
    public function dummy_filter(string $filter, Metrics\Metric $metric): array
    {
        return $this->filter($filter, $metric);
    }
}

class StatusCommandTest extends TestCase
{
    public function testMetrics(): void
    {
        $agent_finder = $this->createMock(Setup\AgentFinder::class);
        $obj = new Setup\CLI\StatusCommand($agent_finder);
        $storage = new Metrics\ArrayStorage();
        $objective = $this->createMock(Setup\Objective::class);
        $agent = $this->createMock(Setup\AgentCollection::class);
        $expected = new M(MS::VOLATILE, MT::COLLECTION, fn() => []);

        $agent
            ->expects($this->once())
            ->method("getStatusObjective")
            ->with($storage)
            ->willReturn(new Setup\ObjectiveCollection("text", false, $objective));

        $result = $obj->getMetrics($agent);

        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider metricProvider
     */
    public function testMetricsFilterWithEmptyFilter(
        Metrics\Metric $metric,
        Metrics\Metric $expected_metric,
        string $filter,
        bool $show_config_section,
        bool $success
    ): void {
        $agent_finder = $this->createMock(Setup\AgentFinder::class);
        $obj = new StatusCommandDummy($agent_finder);

        if (!$success) {
            $this->expectException(\RuntimeException::class);
        }

        $result = $obj->dummy_filter($filter, $metric);

        $this->assertEquals($show_config_section, $result[0]);
        $this->assertEquals($expected_metric, $result[1]);
    }

    public static function metricProvider(): array
    {
        return [
            "non_filter" => [
                new M(MS::VOLATILE, MT::COLLECTION, fn() => []),
                new M(MS::VOLATILE, MT::COLLECTION, fn() => []),
                "",
                false,
                true
            ],
            "non_collection_1" => [
                new M(MS::VOLATILE, MT::TEXT, fn() => "test"),
                new M(MS::VOLATILE, MT::COLLECTION, fn() => []),
                "test",
                false,
                false
            ],
            "non_collection_2" => [
                new M(MS::VOLATILE, MT::COUNTER, fn() => 2),
                new M(MS::VOLATILE, MT::COLLECTION, fn() => []),
                "test",
                false,
                false
            ],
            "non_collection_3" => [
                new M(MS::VOLATILE, MT::BOOL, fn() => false),
                new M(MS::VOLATILE, MT::COLLECTION, fn() => []),
                "test",
                false,
                false
            ],
            "non_collection_4" => [
                new M(MS::VOLATILE, MT::GAUGE, fn() => 3.14),
                new M(MS::VOLATILE, MT::COLLECTION, fn() => []),
                "test",
                false,
                false
            ],
            "non_collection_5" => [
                new M(MS::VOLATILE, MT::TIMESTAMP, fn() => new \DateTimeImmutable()),
                new M(MS::VOLATILE, MT::COLLECTION, fn() => []),
                "test",
                false,
                false
            ],
            "deep_1" => [
                new M(MS::VOLATILE, MT::COLLECTION, fn() => [
                    "foo" => new M(MS::VOLATILE, MT::TEXT, fn() => "foo"),
                    "bar" => new M(MS::VOLATILE, MT::TEXT, fn() => "bar")
                ]),
                new M(MS::VOLATILE, MT::TEXT, fn() => "bar"),
                "bar",
                false,
                true
            ],
            "deep_2" => [
                new M(MS::VOLATILE, MT::COLLECTION, fn() => [
                    "foo" => new M(MS::VOLATILE, MT::COLLECTION, fn() => [
                        "foobar" => new M(MS::VOLATILE, MT::TEXT, fn() => "foobar")
                    ]),
                    "bar" => new M(MS::VOLATILE, MT::TEXT, fn() => "bar")
                ]),
                new M(MS::VOLATILE, MT::TEXT, fn() => "bar"),
                "foo.foobar",
                false,
                true
            ],
            "non_existing_key" => [
                new M(MS::VOLATILE, MT::COLLECTION, fn() => [
                    "foo" => new M(MS::VOLATILE, MT::TEXT, fn() => "foo"),
                    "bar" => new M(MS::VOLATILE, MT::TEXT, fn() => "bar")
                ]),
                new M(MS::VOLATILE, MT::TEXT, fn() => "bar"),
                "no_key",
                false,
                false
            ],
            "deep_config_1" => [
                new M(MS::VOLATILE, MT::COLLECTION, fn() => [
                    "foo" => new M(MS::VOLATILE, MT::TEXT, fn() => "foo"),
                    "bar" => new M(MS::VOLATILE, MT::TEXT, fn() => "bar")
                ]),
                new M(MS::VOLATILE, MT::TEXT, fn() => "bar"),
                "config.bar",
                true,
                true
            ],
            "deep_config_2" => [
                new M(MS::VOLATILE, MT::COLLECTION, fn() => [
                    "foo" => new M(MS::VOLATILE, MT::COLLECTION, fn() => [
                        "foobar" => new M(MS::VOLATILE, MT::TEXT, fn() => "foobar")
                    ]),
                    "bar" => new M(MS::VOLATILE, MT::TEXT, fn() => "bar")
                ]),
                new M(MS::VOLATILE, MT::TEXT, fn() => "bar"),
                "config.foo.foobar",
                true,
                true
            ],
        ];
    }
}
