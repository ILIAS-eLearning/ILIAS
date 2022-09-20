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

namespace ILIAS\Tests\Refinery\In\Transformation;

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\In\Parallel;
use ILIAS\Refinery\To\Transformation\FloatTransformation;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Tests\Refinery\TestCase;
use UnexpectedValueException;

class ParallelTest extends TestCase
{
    public function testParallelTransformation(): void
    {
        $parallel = new Parallel(
            [
                new StringTransformation(),
                new StringTransformation()
            ]
        );

        $result = $parallel->transform('hello');

        $this->assertEquals(['hello', 'hello'], $result);
    }


    public function testParallelTransformationForApplyTo(): void
    {
        $parallel = new Parallel(
            [
                new StringTransformation(),
                new StringTransformation()
            ]
        );

        $result = $parallel->applyTo(new Ok('hello'));

        $this->assertEquals(['hello', 'hello'], $result->value());
    }

    public function testParallelTransformationFailsBecauseOfInvalidType(): void
    {
        $this->expectNotToPerformAssertions();
        $parallel = new Parallel([new StringTransformation()]);

        try {
            $result = $parallel->transform(42.0);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testParallelApply(): void
    {
        $parallel = new Parallel(
            [
                new StringTransformation(),
                new IntegerTransformation(),
                new FloatTransformation()
            ]
        );

        $result = $parallel->applyTo(new Ok(42));

        $this->assertTrue($result->isError());
    }

    public function testInvalidTransformationThrowsException(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $parallel = new Parallel(
                [
                    new StringTransformation(),
                    'this is invalid'
                ]
            );
        } catch (ConstraintViolationException $exception) {
            return;
        }

        $this->fail();
    }
}
