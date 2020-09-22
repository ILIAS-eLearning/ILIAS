<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Tests\Refinery\TestCase;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery;
use ILIAS\Data;

/**
 * Test ByTrying transformation
 */
class ByTryingTransformationTest extends TestCase
{
    const ERROR = 'error_expected';

    public function setUp() : void
    {
        $df = new Data\Factory();
        $lang = $this->getLanguage();
        $this->refine = new \ILIAS\Refinery\Factory($df, $lang);
    }

    public function NullOrNumericDataProvider()
    {
        return [
            'empty string' => ['', null],
            'empty string - one space' => [' ', null],
            'empty string - more spaces' => ['  ', null],
            'null' => [null, null],
            'string' => ['str', self::ERROR],
            'int' => [1, 1],
            'negative int' => [-1, -1],
            'zero' => [0, 0],
            'array' => [[], self::ERROR],
            'bool (false)' => [false, self::ERROR],
            'bool (true)' => [true, self::ERROR]
        ];
    }

    /**
     * @dataProvider NullOrNumericDataProvider
     */
    public function testNullOrNumeric($value, $expected)
    {
        $transformation = $this->refine->byTrying([
            $this->refine->numeric()->isNumeric(),
            $this->refine->kindlyTo()->null()
        ]);

        if ($expected === self::ERROR) {
            $this->expectException(ConstraintViolationException::class);
        }
        $transformed = $transformation->transform($value);
        $this->assertEquals($expected, $transformed);
    }


    public function NullOrNumericOrStringDataProvider()
    {
        return [
            'string' => ['str', 'str'],
            'null' => [null, null],
            'empty string' => ['', null],
            'int' => [1, 1],
            'bool (true)' => [true, self::ERROR],
            'array' => [[], self::ERROR]
        ];
    }

    /**
     * @dataProvider NullOrNumericOrStringDataProvider
     */
    public function testNullOrNumericOrString($value, $expected)
    {
        $transformation = $this->refine->byTrying([
            $this->refine->kindlyTo()->null(),
            $this->refine->numeric()->isNumeric(),
            $this->refine->to()->string()
        ]);

        if ($expected === self::ERROR) {
            $this->expectException(ConstraintViolationException::class);
        }
        $transformed = $transformation->transform($value);
        $this->assertEquals($expected, $transformed);
    }

    public function StringOrNullDataProvider()
    {
        return [
            'string' => ['str', 'str'],
            'null' => [null, null],
            'empty string' => ['', ''],
            'int' => [1, self::ERROR],
            'array' => [[], self::ERROR]
        ];
    }

    /**
     * @dataProvider StringOrNullDataProvider
     */
    public function testStringOrNull($value, $expected)
    {
        $transformation = $this->refine->byTrying([
            $this->refine->to()->string(),
            $this->refine->kindlyTo()->null()
        ]);

        if ($expected === self::ERROR) {
            $this->expectException(ConstraintViolationException::class);
        }
        $transformed = $transformation->transform($value);
        $this->assertEquals($expected, $transformed);
    }
}
