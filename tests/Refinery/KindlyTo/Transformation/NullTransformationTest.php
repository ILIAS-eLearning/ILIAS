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

use ILIAS\Refinery\KindlyTo\Transformation\NullTransformation;
use ILIAS\Tests\Refinery\TestCase;
use ILIAS\Refinery\ConstraintViolationException;

class NullTransformationTest extends TestCase
{
    private NullTransformation $transformation;

    protected function setUp(): void
    {
        $this->transformation = new NullTransformation();
    }

    public function NullTestDataProvider(): array
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
     * @param mixed $value
     * @param bool $valid
     * @throws Exception
     */
    public function testNullTransformation($value, bool $valid): void
    {
        if (!$valid) {
            $this->expectException(ConstraintViolationException::class);
        }
        $transformed = $this->transformation->transform($value);
        $this->assertNull($transformed);
    }
}
