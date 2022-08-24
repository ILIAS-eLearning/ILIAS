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

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\To\Transformation\DictionaryTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;
use UnexpectedValueException;

class DictionaryTransformationTest extends TestCase
{
    /**
     * @throws \ilException
     */
    public function testDictionaryTransformationValid(): void
    {
        $transformation = new DictionaryTransformation(new StringTransformation());

        $result = $transformation->transform(['hello' => 'world']);

        $this->assertEquals(['hello' => 'world'], $result);
    }

    public function testDictionaryTransformationInvalidBecauseKeyIsNotAString(): void
    {
        $this->expectNotToPerformAssertions();

        $transformation = new DictionaryTransformation(new StringTransformation());

        try {
            $result = $transformation->transform(['world']);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testDictionaryTransformationInvalidBecauseValueIsNotAString(): void
    {
        $this->expectNotToPerformAssertions();

        $transformation = new DictionaryTransformation(new StringTransformation());

        try {
            $result = $transformation->transform(['hello' => 1]);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testDictionaryTransformationNonArrayCanNotBeTransformedAndThrowsException(): void
    {
        $this->expectNotToPerformAssertions();

        $transformation = new DictionaryTransformation(new StringTransformation());

        try {
            $result = $transformation->transform(1);
        } catch (UnexpectedValueException $exception) {
            return;
        }

        $this->fail();
    }

    public function testDictionaryApplyValid(): void
    {
        $transformation = new DictionaryTransformation(new StringTransformation());

        $result = $transformation->applyTo(new Ok(['hello' => 'world']));

        $this->assertEquals(['hello' => 'world'], $result->value());
    }

    public function testDictionaryApplyInvalidBecauseKeyIsNotAString(): void
    {
        $transformation = new DictionaryTransformation(new StringTransformation());

        $result = $transformation->applyTo(new Ok(['world']));

        $this->assertTrue($result->isError());
    }

    public function testDictionaryApplyInvalidBecauseValueIsNotAString(): void
    {
        $transformation = new DictionaryTransformation(new StringTransformation());

        $result = $transformation->applyTo(new Ok(['hello' => 1]));

        $this->assertTrue($result->isError());
    }

    public function testDictonaryNonArrayToTransformThrowsException(): void
    {
        $transformation = new DictionaryTransformation(new StringTransformation());

        $result = $transformation->applyTo(new Ok(1));

        $this->assertTrue($result->isError());
    }
}
