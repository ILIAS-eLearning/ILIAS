<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;

class MapValuesTest extends TestCase
{
    protected array $test_array = [
        "A" => 260,
        "B" => 22,
        "C" => 4010
    ];
    protected array $result_array = [
        "A" => 520,
        "B" => 44,
        "C" => 8020
    ];
    protected ILIAS\Refinery\Factory $f;

    protected function setUp() : void
    {
        $dataFactory = new ILIAS\Data\Factory();
        $language = $this->createMock('\ilLanguage');

        $this->f = new ILIAS\Refinery\Factory($dataFactory, $language);
        $this->map_values = $this->f->container()->mapValues($this->f->custom()->transformation(fn($v) => $v*2));
    }

    public function testTransform() : void
    {
        $result = $this->map_values->transform($this->test_array);
        $this->assertEquals($this->result_array, $result);
        $this->assertEquals(["A", "B", "C"], array_keys($result));
    }

    public function testTransformFails() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->map_values->transform(null);
    }
}
