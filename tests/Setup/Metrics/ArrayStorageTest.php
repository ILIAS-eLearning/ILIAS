<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\Metrics;

use ILIAS\Setup\Metrics;
use ILIAS\Setup\Metrics\Metric as M;
use PHPUnit\Framework\TestCase;

class ArrayStorageTest extends TestCase
{
    public function setUp() : void
    {
        $this->storage = new Metrics\ArrayStorage;
    }

    public function testBasicStorage()
    {
        $m1 = new M(M::STABILITY_CONFIG, M::TYPE_BOOL, true, "desc1");
        $m2 = new M(M::STABILITY_CONFIG, M::TYPE_BOOL, true, "desc2");

        $this->storage->store("m1", $m1);
        $this->storage->store("m2", $m2);

        $expected = [
            "m1" => $m1,
            "m2" => $m2
        ];

        $this->assertEquals($expected, $this->storage->get());
    }

    public function testOverwrites()
    {
        $m1 = new M(M::STABILITY_CONFIG, M::TYPE_BOOL, true, "desc1");
        $m2 = new M(M::STABILITY_CONFIG, M::TYPE_BOOL, true, "desc2");

        $this->storage->store("m1", $m1);
        $this->storage->store("m1", $m2);

        $expected = [
            "m1" => $m2
        ];

        $this->assertEquals($expected, $this->storage->get());
    }

    public function testNesting()
    {
        $m1 = new M(M::STABILITY_CONFIG, M::TYPE_BOOL, true, "desc1");

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

    public function testAsMetric()
    {
        $this->storage->store("a", new M(M::STABILITY_STABLE, M::TYPE_COUNTER, 0));
        $this->storage->store("b.c", new M(M::STABILITY_VOLATILE, M::TYPE_BOOL, true));

        $expected = new M(
            M::STABILITY_MIXED,
            M::TYPE_COLLECTION,
            [
                "a" => new M(M::STABILITY_STABLE, M::TYPE_COUNTER, 0),
                "b" => new M(M::STABILITY_MIXED, M::TYPE_COLLECTION, [
                    "c" => new M(M::STABILITY_VOLATILE, M::TYPE_BOOL, true)
                ])
            ]
        );

        $this->assertEquals($expected, $this->storage->asMetric());
    }
}
