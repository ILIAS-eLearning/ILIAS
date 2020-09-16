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

    protected function single($trafo, $value, $expected)
    {
        if ($expected === self::ERROR) {
            $this->expectException(ConstraintViolationException::class);
        }
        $transformed = $trafo->transform($value);
        $this->assertEquals($expected, $value);
    }

    protected function runTests($constraints, $data)
    {
        $trafo = $this->refine->logical()->byTrying($constraints);
        foreach ($data as $key => $value) {
            list($v, $expected) = $value;
            $this->single($trafo, $v, $expected);
        }
    }

    public function testNullOrNumericWithSpaces()
    {
        $trafo = $this->refine->logical()->byTrying([
            $this->refine->numeric()->isNumeric(),
            $this->refine->kindlyTo()->null()
        ]);
        $this->assertNull($trafo->transform(''));
        $this->assertNull($trafo->transform(' '));
        $this->assertNull($trafo->transform('    '));
    }

    public function testNullOrNumeric()
    {
        $data = [
            'empty string' => ['', null],
            'null' => [null, null],
            'string' => ['str', self::ERROR],
            'int' => [1, 1],
            'negative int' => [-1, -1],
            'zero' => [0, 0],
            'array' => [[], self::ERROR],
            'bool (false)' => [false, self::ERROR],
            'bool (true)' => [true, self::ERROR]
        ];

        $constraints = [
            $this->refine->numeric()->isNumeric(),
            $this->refine->kindlyTo()->null()
        ];
        $this->runTests($constraints, $data);
    }

    public function testNullOrNumericOrString()
    {
        $data = [
            'string' => ['str', 'str'],
            'null' => [null, null],
            'empty string' => ['', null],
            'int' => [1, 1],
            'bool (true)' => [true, self::ERROR]
        ];

        $constraints = [
            $this->refine->kindlyTo()->null(),
            $this->refine->numeric()->isNumeric(),
            $this->refine->to()->string()
        ];
        $this->runTests($constraints, $data);
    }

    public function testStringOrNull()
    {
        $data = [
            'string' => ['str', 'str'],
            'null' => [null, null],
            'empty string' => ['', ''],
            'int' => [1, self::ERROR]
        ];

        $constraints = [
            $this->refine->to()->string(),
            $this->refine->kindlyTo()->null()
        ];
        $this->runTests($constraints, $data);
    }
}
