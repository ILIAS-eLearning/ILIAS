<?php
/* Copyright (c) 2020 Luka K. A. Stocker, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\KindlyTo\Transformation\ListTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;

/**
 * Test transformations in this Group
 */
class ListTransformationTest extends TestCase
{
    /**
     * @dataProvider ArrayToListTransformationDataProvider
     * @param $originValue
     * @param $expectedValue
     */
    public function testListTransformation($originValue, $expectedValue)
    {
        $transformList = new ListTransformation(new StringTransformation());
        $transformedValue = $transformList->transform($originValue);
        $this->assertIsArray($transformedValue);
        $this->assertEquals($expectedValue, $transformedValue);
    }

    /**
     * @dataProvider ArrayFailureDataProvider
     * @param $origValue
     */
    public function testFailingTransformations($origValue)
    {
        $this->expectException(ConstraintViolationException::class);
        $transformList = new ListTransformation(new StringTransformation());
        $transformList->transform($origValue);
    }

    public function ArrayToListTransformationDataProvider()
    {
        return [
            'first_arr' => [['hello', 'world'], ['hello', 'world']],
            'second_arr' => [['hello2','world2'], ['hello2', 'world2']],
            'string_val' => ['hello world',['hello world']],
            'empty_array' => [[], []]
        ];
    }

    public function ArrayFailureDataProvider()
    {
        return [
            'null_array' => [[null]],
            'value_is_no_string' => [['hello', 2]]
        ];
    }
}
