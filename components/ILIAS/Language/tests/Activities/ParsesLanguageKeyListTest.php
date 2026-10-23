<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Activities\InvalidInputException;
use ILIAS\Language\Activities\ParsesLanguageKeyList;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * ParsesLanguageKeyList is a trait, exercised through a test double exposing its private toLanguageKeyList();
 * see InstallLanguageTest for the "real caller" coverage.
 */
class ParsesLanguageKeyListTest extends TestCase
{
    /**
     * @return list<string>
     */
    private function parse(mixed $value): array
    {
        $host = new class () {
            use ParsesLanguageKeyList;

            /**
             * @return list<string>
             */
            public function parse(mixed $value): array
            {
                return $this->toLanguageKeyList($value);
            }
        };

        return $host->parse($value);
    }

    public function testAcceptsAPlainTwoLetterLanguageKey(): void
    {
        $this->assertSame(['de'], $this->parse('de'));
    }

    /**
     * Items are trimmed before validation, including a trailing newline (comma-separated request values carry
     * incidental whitespace) - unlike MigratedLanguageFilePaths/InstalledLanguageDatabaseRepository, which reject it.
     */
    #[DataProvider('whitespaceWrappedDeProvider')]
    public function testTrimsSurroundingWhitespaceIncludingNewlinesBeforeValidating(string $value): void
    {
        $this->assertSame(['de'], $this->parse($value));
    }

    /**
     * @return array<string, list<string>>
     */
    public static function whitespaceWrappedDeProvider(): array
    {
        return [
            'plain spaces' => [' de '],
            'trailing newline' => ["de\n"],
            'trailing CRLF' => ["de\r\n"],
        ];
    }

    /**
     * LANGUAGE_KEY_FORMAT after trimming: exactly two lowercase ASCII letters.
     */
    #[DataProvider('invalidLanguageKeysProvider')]
    public function testRejectsAMalformedLanguageKey(string $value): void
    {
        $this->expectException(InvalidInputException::class);

        $this->parse($value);
    }

    /**
     * @return array<string, list<string>>
     */
    public static function invalidLanguageKeysProvider(): array
    {
        return [
            'uppercase' => ['DE'],
            'one letter' => ['d'],
            'three letters' => ['deu'],
            'contains a digit' => ['de1'],
            'contains a hyphen' => ['de-at'],
        ];
    }

    public function testDeduplicatesAcrossACommaSeparatedStringAndAnArray(): void
    {
        $this->assertSame(['de', 'fr'], $this->parse([' de, fr ', 'de']));
    }

    public function testRejectsAnEmptyValueAsAtLeastOneLanguageKeyIsRequired(): void
    {
        $this->expectException(InvalidInputException::class);

        $this->parse(' , ');
    }

    public function testRejectsANonStringNonArrayValue(): void
    {
        $this->expectException(InvalidInputException::class);

        $this->parse(42);
    }

    public function testRejectsANestedArrayValue(): void
    {
        $this->expectException(InvalidInputException::class);

        $this->parse(['de', ['fr']]);
    }
}
