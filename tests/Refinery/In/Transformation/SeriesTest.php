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
use ILIAS\Refinery\In\Series;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Tests\Refinery\TestCase;
use UnexpectedValueException;

class SeriesTest extends TestCase
{
    public function testSeriesTransformation(): void
    {
        $series = new Series([new StringTransformation()]);

        $result = $series->transform('hello');

        $this->assertEquals('hello', $result);
    }

    public function testSeriesApplyTo(): void
    {
        $series = new Series([
            new StringTransformation(),
            new StringTransformation()
        ]);

        $result = $series->applyTo(new Ok('hello'));

        $this->assertEquals('hello', $result->value());
    }

    public function testSeriesTransformationFails(): void
    {
        $this->expectNotToPerformAssertions();

        $series = new Series([
            new IntegerTransformation(),
            new StringTransformation()
        ]);

        try {
            $result = $series->transform(42.0);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }


    /**
     * @throws \ilException
     */
    public function testSeriesApply(): void
    {
        $series = new Series([
            new IntegerTransformation(),
            new StringTransformation()
        ]);

        $result = $series->applyTo(new Ok(42.0));

        $this->assertTrue($result->isError());
    }

    public function testInvalidTransformationThrowsException(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $parallel = new Series(
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
