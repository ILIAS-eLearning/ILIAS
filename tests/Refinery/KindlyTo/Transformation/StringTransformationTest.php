<?php
/* Copyright (c) 2020 Luka K. A. Stocker, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;

/**
 * Test transformations in this Group
 */
class StringTransformationTest extends TestCase
{
    private $transformation;

    public function setUp() : void
    {
        $this->transformation = new StringTransformation();
    }

    /**
     * @dataProvider StringTestDataProvider
     * @param $originVal
     * @param string $expectedVal
     */
    public function testStringTransformation($originVal, $expectedVal)
    {
        $transformedValue = $this->transformation->transform($originVal);
        $this->assertIsString($transformedValue);
        $this->assertEquals($expectedVal, $transformedValue);
    }

    public function StringTestDataProvider()
    {
        $obj = new class extends \StdClass {
            public function __toString()
            {
                return 'an object';
            }
        };
        return [
            'string_val' => ['hello', 'hello'],
            'int_val' => [300, '300'],
            'neg_int_val' => [-300, '-300'],
            'zero_int_val' => [0, '0'],
            'pos_bool' => [true, 'true'],
            'neg_bool' => [false, 'false'],
            'float_val' => [20.5, '20.5'],
            'object_val' => [$obj, 'an object']
        ];
    }
}
