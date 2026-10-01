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

namespace ILIAS\Tests\KeyValueStorage\Subject;

use ILIAS\KeyValueStorage\Subject\SubjectId;
use ILIAS\KeyValueStorage\Subject\SubjectProvider;
use ILIAS\Tests\KeyValueStorage\NamedSubjectProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SubjectIdTest extends TestCase
{
    public function testTheIdKnowsTheProviderThatNamedIt(): void
    {
        $provider = new NamedSubjectProvider('authentication');
        $id = new SubjectId($provider, '42');

        self::assertSame('authentication', $id->provider());
        self::assertSame('42', $id->id());
        self::assertSame(NamedSubjectProvider::class, $id->providerClass());
    }

    public function testAnEmptyIdIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subject id must not be empty.');

        new SubjectId(new NamedSubjectProvider(), '');
    }

    public function testAnIdLongerThanTheLimitIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Subject id must not exceed ' . SubjectId::MAX_LENGTH . ' characters, got '
            . (SubjectId::MAX_LENGTH + 1) . '.'
        );

        new SubjectId(new NamedSubjectProvider(), str_repeat('a', SubjectId::MAX_LENGTH + 1));
    }

    public function testAMultibyteIdLongerThanTheLimitIsRejectedByCharacterCount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Subject id must not exceed ' . SubjectId::MAX_LENGTH . ' characters, got '
            . (SubjectId::MAX_LENGTH + 1) . '.'
        );

        new SubjectId(new NamedSubjectProvider(), str_repeat('ä', SubjectId::MAX_LENGTH + 1));
    }

    public function testAnIdAtTheLimitIsAccepted(): void
    {
        $id = str_repeat('a', SubjectId::MAX_LENGTH);

        self::assertSame($id, (new SubjectId(new NamedSubjectProvider(), $id))->id());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidIds(): array
    {
        return [
            'uppercase' => ['U42'],
            'dot' => ['4.2'],
            'colon' => ['a:b'],
            'space' => ['a b'],
        ];
    }

    #[DataProvider('invalidIds')]
    public function testAnIdThatIsNotALowercaseIdentifierIsRejected(string $id): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subject id must be a lowercase identifier, got "' . $id . '".');

        new SubjectId(new NamedSubjectProvider(), $id);
    }

    public function testAnIdMayStartWithADigit(): void
    {
        self::assertSame('42', (new SubjectId(new NamedSubjectProvider(), '42'))->id());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidProviderNames(): array
    {
        return [
            'empty' => ['', 'Subject provider name must not be empty.'],
            'uppercase' => ['Auth', 'Subject provider name must be a lowercase identifier, got "Auth".'],
            'leading digit' => ['1auth', 'Subject provider name must be a lowercase identifier, got "1auth".'],
            'too long' => [
                str_repeat('a', SubjectProvider::MAX_NAME_LENGTH + 1),
                'Subject provider name must not exceed ' . SubjectProvider::MAX_NAME_LENGTH . ' characters, got '
                . (SubjectProvider::MAX_NAME_LENGTH + 1) . '.'
            ],
        ];
    }

    #[DataProvider('invalidProviderNames')]
    public function testAnInvalidProviderNameIsRejected(string $name, string $message): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new SubjectId(new NamedSubjectProvider($name), '42');
    }
}
