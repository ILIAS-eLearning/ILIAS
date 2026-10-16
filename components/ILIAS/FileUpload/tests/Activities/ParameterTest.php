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

namespace ILIAS\FileUpload\Activities;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
    private Parameter $parameter;

    protected function setUp(): void
    {
        $this->parameter = new Parameter(new Refinery(new DataFactory(), $this->createStub(Language::class)));
    }

    /**
     * @return array<string, array{mixed, int}>
     */
    public static function ids(): array
    {
        return [
            'an int' => [23, 23],
            'digits' => ['23', 23],
            'blanks around it' => [' 23 ', 23],
        ];
    }

    #[DataProvider('ids')]
    public function testAnIdIsTaken(mixed $value, int $expected): void
    {
        $this->assertSame($expected, $this->parameter->id(['ref_id' => $value], 'ref_id', 'missing'));
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function noIds(): array
    {
        return [
            'a list, which a cast makes the root' => [['5']],
            'digits followed by text' => ['102abc'],
            'a fraction' => ['110.9'],
            'a float' => [110.9],
            'zero' => [0],
            'a negative number' => ['-5'],
            'a yes' => [true],
            'leading zeros' => ['0023'],
            'a numeric float' => [23.0],
        ];
    }

    #[DataProvider('noIds')]
    public function testAnythingElseIsNoId(mixed $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage("'ref_id' has to be a positive whole number.");

        $this->parameter->id(['ref_id' => $value], 'ref_id', 'missing');
    }

    public function testAMissingIdIsAnsweredAsSuch(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('A file object is required.');

        $this->parameter->id(['ref_id' => ''], 'ref_id', 'A file object is required.');
    }

    public function testTextIsTrimmedAndLosesItsTags(): void
    {
        $this->assertSame(
            'A title',
            $this->parameter->text(['title' => ' <b>A</b> <img src=x onerror=alert(1)>title '], 'title')
        );
        $this->assertSame('42', $this->parameter->text(['title' => 42], 'title'));
    }

    public function testTextThatWasNotSentIsNull(): void
    {
        $this->assertNull($this->parameter->text([], 'title'));
        $this->assertNull($this->parameter->text(['title' => null], 'title'));
    }

    public function testAStringKeepsWhatLooksLikeTags(): void
    {
        $this->assertSame('a<b>c', $this->parameter->string(['rid' => ' a<b>c '], 'rid'));
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function noTexts(): array
    {
        return [
            'a list' => [['x']],
            'a yes' => [true],
        ];
    }

    #[DataProvider('noTexts')]
    public function testAnythingElseIsNoText(mixed $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(400);

        $this->parameter->text(['title' => $value], 'title');
    }

    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function clearAnswers(): array
    {
        return [
            'true' => [true, true],
            'false' => [false, false],
            '"1"' => ['1', true],
            '"0"' => ['0', false],
            '"true"' => ['true', true],
            '"false"' => ['false', false],
            '1' => [1, true],
            '0' => [0, false],
        ];
    }

    #[DataProvider('clearAnswers')]
    public function testAClearAnswerIsTaken(mixed $value, bool $expected): void
    {
        $this->assertSame($expected, $this->parameter->flag(['flag' => $value], 'flag', !$expected));
    }

    public function testWithoutAnAnswerTheDefaultApplies(): void
    {
        $this->assertTrue($this->parameter->flag([], 'flag', true));
        $this->assertFalse($this->parameter->flag(['flag' => null], 'flag', false));
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function unclearAnswers(): array
    {
        return [
            'a word' => ['ja'],
            'a checkbox' => ['on'],
            'a yes' => ['yes'],
            'a number' => ['2'],
            'a list' => [['1']],
        ];
    }

    #[DataProvider('unclearAnswers')]
    public function testAnUnclearAnswerIsRefused(mixed $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(400);

        $this->parameter->flag(['flag' => $value], 'flag', true);
    }

    public function testAListIsTakenAsListOrCommaSeparated(): void
    {
        $this->assertSame(['file', 'cat'], $this->parameter->list(['types' => 'file, ,cat'], 'types'));
        $this->assertSame(['file', 'cat'], $this->parameter->list(['types' => ['file', ' cat ']], 'types'));
        $this->assertSame([], $this->parameter->list([], 'types'));
    }

    public function testAListOfAnythingElseIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(400);

        $this->parameter->list(['types' => ['file', ['cat']]], 'types');
    }
}
