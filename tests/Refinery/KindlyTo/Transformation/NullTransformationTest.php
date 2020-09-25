<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Refinery\KindlyTo\Transformation\NullTransformation;
use ILIAS\Tests\Refinery\TestCase;
use ILIAS\Refinery\ConstraintViolationException;

/**
 * Test kind transformation to null
 */
class NullTransformationTest extends TestCase
{
    /**
     * NullTransformation
     */
    protected $transformation;

    public function setUp() : void
    {
        $this->transformation = new NullTransformation();
    }

    public function NullTestDataProvider()
    {
        return [
            'empty string' => ['', true],
            'space' => [' ', true],
            'spaces' => ['   ', true],
            'null' => [null, true],
            'string' => ['str', false],
            'int' => [1, false],
            'negative int' => [-1, false],
            'zero' => [0, false],
            'array' => [[], false],
            'bool (false)' => [false, false],
            'bool (true)' => [true, false]
        ];
    }

    /**
     * @dataProvider NullTestDataProvider
     */
    public function testNullTransformation($value, bool $valid)
    {
        if (!$valid) {
            $this->expectException(ConstraintViolationException::class);
        }
        $transformed = $this->transformation->transform($value);
        $this->assertNull($transformed);
    }
}
