<?php

declare(strict_types=1);

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

namespace ILIAS\Tests\Setup\Metrics;

use ILIAS\Setup\Metrics;
use ILIAS\Setup\Metrics\Metric as M;
use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Component\Panel\Listing\Factory as LF;
use ILIAS\UI\Implementation\Component\Panel\Factory as PF;
use ILIAS\UI\Factory;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Panel\Report;

class MetricTest extends TestCase
{
    /**
     * @dataProvider metricProvider
     */
    public function testConstructMetric(string $stability, string $type, $value, string $description, bool $success): void
    {
        if (!$success) {
            $this->expectException(\InvalidArgumentException::class);
        }
        $metric = new Metrics\Metric($stability, $type, $value, $description);
        $this->assertEquals($stability, $metric->getStability());
        $this->assertEquals($type, $metric->getType());
        $this->assertEquals($value, $metric->getValue());
        $this->assertEquals($description, $metric->getDescription());
    }

    public function metricProvider(): array
    {
        $config = Metrics\Metric::STABILITY_CONFIG;
        $stable = Metrics\Metric::STABILITY_STABLE;
        $volatile = Metrics\Metric::STABILITY_VOLATILE;
        $mixed = Metrics\Metric::STABILITY_MIXED;

        $bool = Metrics\Metric::TYPE_BOOL;
        $counter = Metrics\Metric::TYPE_COUNTER;
        $gauge = Metrics\Metric::TYPE_GAUGE;
        $timestamp = Metrics\Metric::TYPE_TIMESTAMP;
        $text = Metrics\Metric::TYPE_TEXT;
        $collection = Metrics\Metric::TYPE_COLLECTION;

        $other_metric = new Metrics\Metric($volatile, $bool, true);

        return [
            "invalid_stability" => ["no_stability", $bool, true, "", false],
            "invalid_type" => [$config, "no_type", true, "", false],

            "bool" => [$config, $bool, true, "A boolean", true],
            "counter" => [$stable, $counter, 23, "A counter", true],
            "gauge1" => [$volatile, $gauge, 42, "A gauge", true],
            "gauge2" => [$volatile, $gauge, 13.37, "A gauge", true],
            "timestamp" => [$config, $timestamp, new \DateTimeImmutable(), "A timestamp", true],
            "text" => [$stable, $text, "some text", "A text", true],
            "collection" => [$volatile, $collection, ["other" => $other_metric], "A collection", true],

            "no_bool1" => [$config, $bool, 1, "", false],
            "no_bool2" => [$config, $bool, "foo", "", false],
            "no_bool3" => [$config, $bool, new \DateTimeImmutable(), "", false],
            "no_bool4" => [$config, $bool, [], "", false],

            "no_counter1" => [$stable, $counter, false, "", false],
            "no_counter2" => [$stable, $counter, 3.1, "", false],
            "no_counter3" => [$stable, $counter, "foo", "", false],
            "no_counter4" => [$stable, $counter, new \DateTimeImmutable(), "", false],
            "no_counter5" => [$stable, $counter, [], "", false],

            "no_gauge1" => [$volatile, $gauge, true, "", false],
            "no_gauge2" => [$volatile, $gauge, "foo", "", false],
            "no_gauge3" => [$volatile, $gauge, new \DateTimeImmutable(), "", false],
            "no_gauge4" => [$volatile, $gauge, [], "", false],

            "no_timestamp1" => [$config, $timestamp, false, "", false],
            "no_timestamp2" => [$config, $timestamp, 1, "", false],
            "no_timestamp3" => [$config, $timestamp, "foo", "", false],
            "no_timestamp4" => [$config, $timestamp, [], "", false],

            "no_text1" => [$stable, $text, true, "", false],
            "no_text2" => [$stable, $text, 1, "", false],
            "no_text3" => [$stable, $text, new \DateTimeImmutable(), "", false],
            "no_text4" => [$stable, $text, [], "", false],

            "no_collection1" => [$volatile, $collection, false, "", false],
            "no_collection2" => [$volatile, $collection, 1, "", false],
            "no_collection3" => [$volatile, $collection, new \DateTimeImmutable(), "", false],
            "no_collection4" => [$volatile, $collection, "foo", "", false],
            "no_collection5" => [$volatile, $collection, ["a"], "", false],

            "mixed_collection" => [$mixed, $collection, [], "", true],
            "no_mixed_bool" => [$mixed, $bool, true, "", false],
            "no_mixed_counter" => [$mixed, $counter, 1, "", false],
            "no_mixed_gauge" => [$mixed, $gauge, 1.0, "", false],
            "no_mixed_timestamp" => [$mixed, $timestamp, new \DateTimeImmutable(), "", false],
            "no_mixed_text" => [$mixed, $text, "", "", false],
        ];
    }

    /**
     * @dataProvider typedMetricsProvider
     */
    public function testToYAML(M $metric, string $expected): void
    {
        $this->assertEquals($expected, $metric->toYAML());
    }

    public function typedMetricsProvider(): array
    {
        return [
            "bool_true" => [new M(M::STABILITY_STABLE, M::TYPE_BOOL, true), "true"],
            "bool_false" => [new M(M::STABILITY_STABLE, M::TYPE_BOOL, false), "false"],
            "counter_0" => [new M(M::STABILITY_STABLE, M::TYPE_COUNTER, 0), "0"],
            "counter_1337" => [new M(M::STABILITY_STABLE, M::TYPE_COUNTER, 1337), "1337"],
            "gauge_23" => [new M(M::STABILITY_STABLE, M::TYPE_GAUGE, 23), "23"],
            "gauge_42_0" => [new M(M::STABILITY_STABLE, M::TYPE_GAUGE, 42.0), "42.000"],
            "gauge_42_001" => [new M(M::STABILITY_STABLE, M::TYPE_GAUGE, 42.001), "42.001"],
            "timestamp" => [new M(M::STABILITY_STABLE, M::TYPE_TIMESTAMP, new \DateTimeImmutable("1985-05-04T13:37:00+01:00")), "1985-05-04T13:37:00+0100"],
            "text" => [new M(M::STABILITY_STABLE, M::TYPE_TEXT, "some text"), "some text"],
            "text_with_nl" => [new M(M::STABILITY_STABLE, M::TYPE_TEXT, "some\ntext"), ">\nsome\ntext"],
        ];
    }

    public function testIndentation(): void
    {
        $metrics = new M(M::STABILITY_STABLE, M::TYPE_COLLECTION, [
            "a" => new M(M::STABILITY_STABLE, M::TYPE_COLLECTION, [
                "h" => new M(M::STABILITY_STABLE, M::TYPE_TEXT, "a_h"),
                "c" => new M(M::STABILITY_STABLE, M::TYPE_COLLECTION, [
                    "d" => new M(M::STABILITY_STABLE, M::TYPE_COLLECTION, [
                        "e" => new M(M::STABILITY_STABLE, M::TYPE_TEXT, "a_c_d_e"),
                        "f" => new M(M::STABILITY_STABLE, M::TYPE_TEXT, "a_c_d_f")
                    ]),
                    "g" => new M(M::STABILITY_STABLE, M::TYPE_TEXT, "a_c_g")
                ]),
                "i" => new M(M::STABILITY_STABLE, M::TYPE_TEXT, "a_i\na_i")
            ]),
            "b" => new M(M::STABILITY_STABLE, M::TYPE_COLLECTION, [
                "j" => new M(M::STABILITY_STABLE, M::TYPE_TEXT, "b_j")
            ]),
            "k" => new M(M::STABILITY_STABLE, M::TYPE_TEXT, "k")
        ]);

        $expected = <<<METRIC
a:
    h: a_h
    c:
        d:
            e: a_c_d_e
            f: a_c_d_f
        g: a_c_g
    i: >
        a_i
        a_i
b:
    j: b_j
k: k
METRIC;

        $this->assertEquals($expected, $metrics->toYAML());
    }

    public function testExtractBySeverity(): void
    {
        $metrics = new M(M::STABILITY_MIXED, M::TYPE_COLLECTION, [
            "a" => new M(M::STABILITY_MIXED, M::TYPE_COLLECTION, [
                "h" => new M(M::STABILITY_CONFIG, M::TYPE_TEXT, "a_h"),
                "c" => new M(M::STABILITY_MIXED, M::TYPE_COLLECTION, [
                    "d" => new M(M::STABILITY_MIXED, M::TYPE_COLLECTION, [
                        "e" => new M(M::STABILITY_STABLE, M::TYPE_TEXT, "a_c_d_e"),
                        "f" => new M(M::STABILITY_VOLATILE, M::TYPE_TEXT, "a_c_d_f")
                    ]),
                    "g" => new M(M::STABILITY_CONFIG, M::TYPE_TEXT, "a_c_g")
                ]),
                "i" => new M(M::STABILITY_STABLE, M::TYPE_TEXT, "a_i\na_i")
            ]),
            "b" => new M(M::STABILITY_MIXED, M::TYPE_COLLECTION, [
                "j" => new M(M::STABILITY_VOLATILE, M::TYPE_TEXT, "b_j")
            ]),
            "k" => new M(M::STABILITY_CONFIG, M::TYPE_TEXT, "k")
        ]);

        $expected_extracted = new M(M::STABILITY_CONFIG, M::TYPE_COLLECTION, [
            "a" => new M(M::STABILITY_CONFIG, M::TYPE_COLLECTION, [
                "h" => new M(M::STABILITY_CONFIG, M::TYPE_TEXT, "a_h"),
                "c" => new M(M::STABILITY_CONFIG, M::TYPE_COLLECTION, [
                    "g" => new M(M::STABILITY_CONFIG, M::TYPE_TEXT, "a_c_g")
                ]),
            ]),
            "k" => new M(M::STABILITY_CONFIG, M::TYPE_TEXT, "k")
        ]);
        $expected_rest = new M(M::STABILITY_MIXED, M::TYPE_COLLECTION, [
            "a" => new M(M::STABILITY_MIXED, M::TYPE_COLLECTION, [
                "c" => new M(M::STABILITY_MIXED, M::TYPE_COLLECTION, [
                    "d" => new M(M::STABILITY_MIXED, M::TYPE_COLLECTION, [
                        "e" => new M(M::STABILITY_STABLE, M::TYPE_TEXT, "a_c_d_e"),
                        "f" => new M(M::STABILITY_VOLATILE, M::TYPE_TEXT, "a_c_d_f")
                    ])
                ]),
                "i" => new M(M::STABILITY_STABLE, M::TYPE_TEXT, "a_i\na_i")
            ]),
            "b" => new M(M::STABILITY_MIXED, M::TYPE_COLLECTION, [
                "j" => new M(M::STABILITY_VOLATILE, M::TYPE_TEXT, "b_j")
            ])
        ]);

        list($extracted, $rest) = $metrics->extractByStability(M::STABILITY_CONFIG);

        $this->assertEquals($expected_extracted, $extracted);
        $this->assertEquals($expected_rest, $rest);
    }

    /**
     * @dataProvider typedMetricsProvider
     */
    public function testToArrayWithFlatValues(M $metric, string $expected): void
    {
        $this->assertEquals($expected, $metric->toArray());
    }

    public function testToArrayWithDeepOne(): void
    {
        $metric = new M(M::STABILITY_STABLE, M::TYPE_COLLECTION, [
           "bool_true" => new M(M::STABILITY_STABLE, M::TYPE_BOOL, true)
        ]);

        $this->assertEquals(["bool_true" => "true"], $metric->toArray());
    }

    public function testToArrayWithDeepTwo(): void
    {
        $metric = new M(M::STABILITY_STABLE, M::TYPE_COLLECTION, [
            "db" => new M(M::STABILITY_STABLE, M::TYPE_COLLECTION, [
                "bool_true" => new M(M::STABILITY_STABLE, M::TYPE_BOOL, true)
            ])
        ]);

        $this->assertEquals(["db" => ["bool_true" => "true"]], $metric->toArray());
    }

    public function testToUIReport(): void
    {
        $factory = $this->createMock(Factory::class);
        $listing_f = new LF();
        $panel_f = new PF($listing_f);
        $signal = new SignalGenerator();
        $legacy_f = new \ILIAS\UI\Implementation\Component\Legacy\Factory($signal);
        $legacy = $legacy_f->legacy("<pre>string</pre>");

        $factory
            ->expects($this->once())
            ->method("legacy")
            ->with("<pre>" . "string" . "</pre>")
            ->willReturn($legacy)
        ;

        $factory
            ->expects($this->exactly(2))
            ->method("panel")
            ->willReturn($panel_f)
        ;

        $metric = new M(M::STABILITY_STABLE, M::TYPE_TEXT, "string", "");

        $result = $metric->toUIReport($factory, "Status");

        $this->assertInstanceOf(Report::class, $result);
    }
}
