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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Container\MapValues;
use ILIAS\Refinery\Transformation;
use PHPUnit\Framework\TestCase;

class MapValuesTest extends TestCase
{
    /** @var array<string, int> */
    private array $test_array = [
        "A" => 260,
        "B" => 22,
        "C" => 4010
    ];
    /** @var array<string, int> */
    private array $result_array = [
        "A" => 520,
        "B" => 44,
        "C" => 8020
    ];
    private Refinery $f;
    private Transformation $map_values;

    protected function setUp(): void
    {
        $dataFactory = new DataFactory();
        $language = $this->createMock(ilLanguage::class);

        $this->f = new Refinery($dataFactory, $language);
        $this->map_values = $this->f->container()->mapValues($this->f->custom()->transformation(fn ($v) => $v * 2));
    }

    public function testTransform(): void
    {
        $result = $this->map_values->transform($this->test_array);
        $this->assertEquals($this->result_array, $result);
        $this->assertEquals(["A", "B", "C"], array_keys($result));
    }

    public function testTransformFails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->map_values->transform(null);
    }
}
