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

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\KindlyTo\Transformation\ListTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;
use UnexpectedValueException;

class ListTransformationTest extends TestCase
{
    /**
     * @dataProvider ArrayToListTransformationDataProvider
     * @param mixed $originValue
     * @param mixed $expectedValue
     */
    public function testListTransformation($originValue, $expectedValue): void
    {
        $transformList = new ListTransformation(new StringTransformation());
        $transformedValue = $transformList->transform($originValue);
        $this->assertIsArray($transformedValue);
        $this->assertEquals($expectedValue, $transformedValue);
    }

    /**
     * @dataProvider ArrayFailureDataProvider
     * @param mixed $origValue
     */
    public function testFailingTransformations($origValue): void
    {
        $this->expectException(UnexpectedValueException::class);
        $transformList = new ListTransformation(new StringTransformation());
        $transformList->transform($origValue);
    }

    public function ArrayToListTransformationDataProvider(): array
    {
        return [
            'first_arr' => [['hello', 'world'], ['hello', 'world']],
            'second_arr' => [['hello2', 'world2'], ['hello2', 'world2']],
            'string_val' => ['hello world', ['hello world']],
            'empty_array' => [[], []]
        ];
    }

    public function ArrayFailureDataProvider(): array
    {
        return [
            'null_array' => [[null]],
            'value_is_no_string' => [['hello', 2]]
        ];
    }
}
