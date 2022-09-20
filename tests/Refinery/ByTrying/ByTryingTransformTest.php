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
use ILIAS\Tests\Refinery\TestCase;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Data\Factory as DataFactory;

class ByTryingTransformationTest extends TestCase
{
    private const ERROR = 'error_expected';

    private Refinery $refinery;

    protected function setUp(): void
    {
        $df = new DataFactory();
        $lang = $this->getLanguage();
        $this->refinery = new Refinery($df, $lang);
    }

    public function NullOrNumericDataProvider(): array
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
     * @param mixed $value
     * @param mixed $expected
     */
    public function testNullOrNumeric($value, $expected): void
    {
        $transformation = $this->refinery->byTrying([
            $this->refinery->numeric()->isNumeric(),
            $this->refinery->kindlyTo()->null()
        ]);

        if ($expected === self::ERROR) {
            $this->expectException(ConstraintViolationException::class);
        }
        $transformed = $transformation->transform($value);
        $this->assertEquals($expected, $transformed);
    }


    public function NullOrNumericOrStringDataProvider(): array
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
     * @param mixed $value
     * @param mixed $expected
     */
    public function testNullOrNumericOrString($value, $expected): void
    {
        $transformation = $this->refinery->byTrying([
            $this->refinery->kindlyTo()->null(),
            $this->refinery->numeric()->isNumeric(),
            $this->refinery->to()->string()
        ]);

        if ($expected === self::ERROR) {
            $this->expectException(ConstraintViolationException::class);
        }
        $transformed = $transformation->transform($value);
        $this->assertEquals($expected, $transformed);
    }

    public function StringOrNullDataProvider(): array
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
     * @param mixed $value
     * @param mixed $expected
     */
    public function testStringOrNull($value, $expected): void
    {
        $transformation = $this->refinery->byTrying([
            $this->refinery->to()->string(),
            $this->refinery->kindlyTo()->null()
        ]);

        if ($expected === self::ERROR) {
            $this->expectException(ConstraintViolationException::class);
        }
        $transformed = $transformation->transform($value);
        $this->assertEquals($expected, $transformed);
    }
}
