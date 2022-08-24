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

namespace ILIAS\Tests\Refinery\To\Transformation;

use ILIAS\Data\Result;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Refinery\To\Transformation\TupleTransformation;
use ILIAS\Refinery\IsArrayOfSameType;
use ILIAS\Tests\Refinery\TestCase;
use UnexpectedValueException;

class TupleTransformationTest extends TestCase
{
    /**
     * @throws \ilException
     */
    public function testTupleTransformationsAreCorrect(): void
    {
        $transformation = new TupleTransformation(
            [new IntegerTransformation(), new IntegerTransformation()]
        );

        $result = $transformation->transform([1, 2]);

        $this->assertEquals([1, 2], $result);
    }

    public function testTupleIsIncorrectAndWillThrowException(): void
    {
        $this->expectNotToPerformAssertions();

        $transformation = new TupleTransformation(
            [new IntegerTransformation(), new StringTransformation()]
        );

        try {
            $result = $transformation->transform([1, 2]);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testTupleIsIncorrectAndWillThrowException2(): void
    {
        $this->expectNotToPerformAssertions();

        $transformation = new TupleTransformation(
            [new IntegerTransformation(), 'hello' => new IntegerTransformation()]
        );

        try {
            $result = $transformation->transform([1, 2]);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }


    public function testToManyValuesForTransformation(): void
    {
        $this->expectNotToPerformAssertions();

        $transformation = new TupleTransformation(
            [new IntegerTransformation(), new IntegerTransformation()]
        );

        try {
            $result = $transformation->transform([1, 2, 3]);
        } catch (UnexpectedValueException $exception) {
            return;
        }
        $this->fail();
    }

    public function testTupleAppliesAreCorrect(): void
    {
        $transformation = new TupleTransformation(
            [new IntegerTransformation(), new IntegerTransformation()]
        );

        $result = $transformation->applyTo(new Result\Ok([1, 2]));

        $this->assertEquals([1, 2], $result->value());
    }

    public function testTupleAppliesAreIncorrectAndWillReturnErrorResult(): void
    {
        $transformation = new TupleTransformation(
            [new IntegerTransformation(), new StringTransformation()]
        );

        $result = $transformation->applyTo(new Result\Ok([1, 2]));

        $this->assertTrue($result->isError());
    }

    public function testToManyValuesForApply(): void
    {
        $transformation = new TupleTransformation(
            [new IntegerTransformation(), new StringTransformation()]
        );

        $result = $transformation->applyTo(new Result\Ok([1, 2, 3]));

        $this->assertTrue($result->isError());
    }

    public function testInvalidTransformationWillThrowException(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $transformation = new TupleTransformation(
                [new IntegerTransformation(), 'hello']
            );
        } catch (UnexpectedValueException $exception) {
            return;
        }


        $this->fail();
    }
}
