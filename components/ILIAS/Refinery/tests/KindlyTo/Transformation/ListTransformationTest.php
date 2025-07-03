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

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\KindlyTo\Transformation\ListTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;
use PHPUnit\Framework\Attributes\DataProvider;

class ListTransformationTest extends TestCase
{
    #[DataProvider('ArrayToListTransformationDataProvider')]
    public function testListTransformation(mixed $originValue, mixed $expectedValue): void
    {
        $transformList = new ListTransformation(new StringTransformation());
        $transformedValue = $transformList->transform($originValue);
        $this->assertIsArray($transformedValue);
        $this->assertEquals($expectedValue, $transformedValue);
    }

    #[DataProvider('ArrayFailureDataProvider')]
    public function testFailingTransformations(mixed $origValue): void
    {
        $this->expectException(UnexpectedValueException::class);
        $transformList = new ListTransformation(new StringTransformation());
        $transformList->transform($origValue);
    }

    public static function ArrayToListTransformationDataProvider(): array
    {
        return [
            'first_arr' => [['hello', 'world'], ['hello', 'world']],
            'second_arr' => [['hello2', 'world2'], ['hello2', 'world2']],
            'string_val' => ['hello world', ['hello world']],
            'empty_array' => [[], []]
        ];
    }

    public static function ArrayFailureDataProvider(): array
    {
        return [
            'null_array' => [[null]],
            'value_is_no_string' => [['hello', 2]]
        ];
    }
}
