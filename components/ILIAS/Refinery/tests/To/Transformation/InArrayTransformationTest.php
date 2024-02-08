<?php

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

declare(strict_types=1);

namespace ILIAS\Tests\Refinery\To\Transformation;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\To\Transformation\InArrayTransformation;
use PHPUnit\Framework\TestCase;
use ILIAS\Language\Language;
use UnexpectedValueException;

class InArrayTransformationTest extends TestCase
{
    public function testConstruct(): void
    {
        $language = $this->getMockBuilder(Language::class)->disableOriginalConstructor()->getMock();
        $this->assertInstanceOf(InArrayTransformation::class, new InArrayTransformation([], $language));
    }

    /**
     * @dataProvider memberProvider
     */
    public function testAccept(string $value, bool $successful): void
    {
        $language = $this->getMockBuilder(Language::class)->disableOriginalConstructor()->getMock();
        $transformation = new InArrayTransformation(['foo', 'bar'], $language);

        $this->assertSame($successful, $transformation->accepts($value));
    }

    /**
     * @dataProvider memberProvider
     */
    public function testTransform(string $value, bool $successful): void
    {
        if (!$successful) {
            $this->expectException(UnexpectedValueException::class);
        }

        $language = $this->getMockBuilder(Language::class)->disableOriginalConstructor()->getMock();
        $transformation = new InArrayTransformation(['foo', 'bar'], $language);

        $this->assertSame($value, $transformation->transform($value));
    }

    /**
     * @dataProvider memberProvider
     */
    public function testApplyTo(string $value, bool $successful): void
    {
        $language = $this->getMockBuilder(Language::class)->disableOriginalConstructor()->getMock();
        $transformation = new InArrayTransformation(['foo', 'bar'], $language);

        $result = $transformation->applyTo(new Ok($value));
        $this->assertSame($successful, $result->isOk());
        if ($successful) {
            $this->assertSame($value, $result->value());
        } else {
            $this->assertInstanceOf(UnexpectedValueException::class, $result->error());
        }
    }

    public function memberProvider(): array
    {
        return [
            'Invalid member.' => ['hej', false],
            'Valid member.' => ['foo', true],
        ];
    }
}
