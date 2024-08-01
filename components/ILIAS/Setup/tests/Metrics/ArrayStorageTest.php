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

namespace ILIAS\Tests\Setup\Metrics;

use ILIAS\Setup\Metrics;
use ILIAS\Setup\Metrics\Metric as M;
use ILIAS\Setup\Metrics\MetricType as MT;
use ILIAS\Setup\Metrics\MetricStability as MS;
use PHPUnit\Framework\TestCase;

class ArrayStorageTest extends TestCase
{
    protected Metrics\ArrayStorage $storage;

    public function setUp(): void
    {
        $this->storage = new Metrics\ArrayStorage();
    }

    public function testBasicStorage(): void
    {
        $m1 = new M(MS::CONFIG, MT::BOOL, fn() => true, "desc1");
        $m2 = new M(MS::CONFIG, MT::BOOL, fn() => true, "desc2");

        $this->storage->store("m1", $m1);
        $this->storage->store("m2", $m2);

        $expected = [
            "m1" => $m1,
            "m2" => $m2
        ];

        $this->assertEquals($expected, $this->storage->get());
    }

    public function testOverwrites(): void
    {
        $m1 = new M(MS::CONFIG, MT::BOOL, fn() => true, "desc1");
        $m2 = new M(MS::CONFIG, MT::BOOL, fn() => true, "desc2");

        $this->storage->store("m1", $m1);
        $this->storage->store("m1", $m2);

        $expected = [
            "m1" => $m2
        ];

        $this->assertEquals($expected, $this->storage->get());
    }

    public function testNesting(): void
    {
        $m1 = new M(MS::CONFIG, MT::BOOL, fn() => true, "desc1");

        $this->storage->store("a.b.c", $m1);

        $expected = [
            "a" => [
                "b" => [
                    "c" => $m1
                ]
            ]
        ];

        $this->assertEquals($expected, $this->storage->get());
    }

    public function testAsMetric(): void
    {
        $this->storage->store("a", new M(MS::STABLE, MT::COUNTER, fn() => 0));
        $this->storage->store("b.c", new M(MS::VOLATILE, MT::BOOL, fn() => true));

        $expected = new M(
            MS::MIXED,
            MT::COLLECTION,
            fn() => [
                "a" => new M(MS::STABLE, MT::COUNTER, fn() => 0),
                "b" => new M(MS::MIXED, MT::COLLECTION, fn() => [
                    "c" => new M(MS::VOLATILE, MT::BOOL, fn() => true)
                ])
            ]
        );

        $this->assertEquals($expected, $this->storage->asMetric());
    }
}
