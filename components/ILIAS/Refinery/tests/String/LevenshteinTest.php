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

namespace ILIAS\src\Refinery\String;

use ILIAS\Data\Factory;
use ILIAS\Refinery\String\Group;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\Stub;

class LevenshteinTest extends TestCase
{
    private Group $group;
    private Factory $factory;

    /**
     * @var string multibyte string with three bytes per character
     */
    private string $test_multibyte_string = '你好';

    /**
     * @var string containing emoji
     */
    private string $test_emoji = '😮‍💨';

    protected function setUp(): void
    {
        $this->factory = new Factory();
        /** @var \ILIAS\Language\Language&Stub $language */
        $language = $this->createStub(\ILIAS\Language\Language::class);
        $this->group = new Group($this->factory, $language);
    }

    // Code paths
    public function testSizeReturn(): void
    {
        $transformation = $this->group->levenshtein()->standard('book', 3);

        $this->assertEquals(-1.0, $transformation->transform('bookshelf'));
    }

    public function testExceedMaximumDistance(): void
    {
        $transformation = $this->group->levenshtein()->standard('book', 1);

        $this->assertEquals(-1.0, $transformation->transform('back'));
    }

    public function testSuccessfulReturn(): void
    {
        $transformation = $this->group->levenshtein()->standard('book', 1);

        $this->assertEquals(0.0, $transformation->transform('book'));
    }

    public function testNoMaximumDistance(): void
    {
        $transformation = $this->group->levenshtein()->standard('book', 0);

        $this->assertEquals(2.0, $transformation->transform('back'));
    }

    public function testException(): void
    {
        $transformation = $this->group->levenshtein()->standard('book', 0);
        $this->expectException(InvalidArgumentException::class);

        $this->assertEquals(2.0, $transformation->transform(496));
    }

    public function testNoMaxEscapeButOverTheLimit(): void
    {
        $transformation = $this->group->levenshtein()->standard('Juni', 2);

        $this->assertEquals(-1, $transformation->transform('Januar'));
    }

    // Numerical
    public function testCustomCostsMixed(): void
    {
        $transformation = $this->group->levenshtein()->custom('back', 20, 2.0, 1.0, 1.5);

        $this->assertEquals(12, $transformation->transform('bookshelf'));
    }

    public function testCustomCostsInsert(): void
    {
        $transformation = $this->group->levenshtein()->custom('book', 5, 2.0, 1.0, 1.5);

        $this->assertEquals(2, $transformation->transform('books'));
    }

    public function testCustomCostsDeletion(): void
    {
        $transformation = $this->group->levenshtein()->custom('bookshelf', 10, 2.0, 1.0, 1.5);

        $this->assertEquals(7.5, $transformation->transform('book'));
    }

    public function testCustomCostsReplacement(): void
    {
        $transformation = $this->group->levenshtein()->custom('bookshelf', 10, 2.0, 1.0, 1.5);

        $this->assertEquals(4.0, $transformation->transform('bookstore'));
    }

    // test apply to
    public function testApplyToSuccessfulDefault(): void
    {
        $transformation = $this->group->levenshtein()->standard('bookshelf', 10);
        $value_object = $this->factory->ok('book');
        $result_object = $transformation->applyTo($value_object);

        $this->assertEquals(5, $result_object->value());
    }

    public function testApplyToSuccessfulCustomCost(): void
    {
        $transformation = $this->group->levenshtein()->custom('bookshelf', 10, 2.0, 1.0, 1.5);
        $value_object = $this->factory->ok('book');
        $result_object = $transformation->applyTo($value_object);

        $this->assertEquals(7.5, $result_object->value());
    }

    public function testApplyToWrongType(): void
    {
        $transformation = $this->group->levenshtein()->custom('bookshelf', 10, 2.0, 1.0, 1.5);
        $value_object = $this->factory->ok(496);
        $result_object = $transformation->applyTo($value_object);

        $this->assertTrue($result_object->isError());
    }

    // test Multibyte strings:
    public function testMultibyteStringThreeByte(): void
    {
        $transformation = $this->group->levenshtein()->custom($this->test_multibyte_string, 10, 2.0, 1.0, 1.5);

        $this->assertEquals(0, $transformation->transform($this->test_multibyte_string));
    }

    public function testEmoji(): void
    {
        $transformation = $this->group->levenshtein()->custom('book', 20, 2.0, 1.0, 1.5);

        $this->assertEquals(4.5, $transformation->transform($this->test_emoji));
    }

    public function testApplyToMultibyteString(): void
    {
        $transformation = $this->group->levenshtein()->custom('bookshelf', 20, 2.0, 1.0, 1.5);
        $value_object = $this->factory->ok($this->test_multibyte_string);
        $result_object = $transformation->applyTo($value_object);

        $this->assertEquals(12.5, $result_object->value());
    }
}
