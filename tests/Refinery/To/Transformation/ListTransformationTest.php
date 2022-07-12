<?php declare(strict_types=1);

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

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\To\Transformation\ListTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Tests\Refinery\TestCase;
use UnexpectedValueException;

class ListTransformationTest extends TestCase
{
    /**
     * @throws \ilException
     */
    public function testListTransformationIsValid() : void
    {
        $listTransformation = new ListTransformation(new StringTransformation());

        $result = $listTransformation->transform(['hello', 'world']);

        $this->assertEquals(['hello', 'world'], $result);
    }

    public function testTransformOnEmptyArrayReturnsEmptyList() : void
    {
        $listTransformation = new ListTransformation(new StringTransformation());
        $this->assertSame([], $listTransformation->transform([]));
    }

    public function testApplyToOnEmptyArrayDoesNotFail() : void
    {
        $listTransformation = new ListTransformation(new StringTransformation());
        $result = $listTransformation->applyTo(new Ok([]));
        $this->assertFalse($result->isError());
    }

    public function testTransformOnNullFails() : void
    {
        $this->expectNotToPerformAssertions();

        $listTransformation = new ListTransformation(new StringTransformation());
        try {
            $result = $listTransformation->transform(null);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testApplyToOnNullFails() : void
    {
        $listTransformation = new ListTransformation(new StringTransformation());
        $result = $listTransformation->applyTo(new Ok(null));
        $this->assertTrue($result->isError());
    }


    public function testListTransformationIsInvalid() : void
    {
        $this->expectNotToPerformAssertions();

        $listTransformation = new ListTransformation(new StringTransformation());

        try {
            $result = $listTransformation->transform(['hello', 2]);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testListApplyIsValid() : void
    {
        $listTransformation = new ListTransformation(new StringTransformation());

        $result = $listTransformation->applyTo(new Ok(['hello', 'world']));

        $this->assertEquals(['hello', 'world'], $result->value());
        $this->assertTrue($result->isOK());
    }

    public function testListApplyIsInvalid() : void
    {
        $listTransformation = new ListTransformation(new StringTransformation());

        $result = $listTransformation->applyTo(new Ok(['hello', 2]));

        $this->assertTrue($result->isError());
    }
}
