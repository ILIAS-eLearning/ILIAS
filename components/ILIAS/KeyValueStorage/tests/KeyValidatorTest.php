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

namespace ILIAS\Tests\KeyValueStorage;

use ILIAS\KeyValueStorage\KeyValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class KeyValidatorTest extends TestCase
{
    private KeyValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new KeyValidator();
    }

    #[DataProvider('validKeyProvider')]
    public function testAcceptsValidKey(string $key): void
    {
        $this->validator->validate($key);

        self::assertNotSame('', $key);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function validKeyProvider(): array
    {
        return [
            'simple' => ['sort_column'],
            'dotted' => ['ui.table.sort'],
            'with_digits' => ['column2'],
        ];
    }

    public function testRejectsEmptyKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Storage key must be a non-empty string.');

        $this->validator->validate('');
    }

    public function testAcceptsKeyAtMaxLength(): void
    {
        $key = \str_repeat('k', KeyValidator::MAX_LENGTH);

        $this->validator->validate($key);

        self::assertSame(KeyValidator::MAX_LENGTH, \mb_strlen($key, 'UTF-8'));
    }

    public function testRejectsKeyExceedingMaxLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Storage key must not exceed ' . KeyValidator::MAX_LENGTH . ' characters, got '
            . (KeyValidator::MAX_LENGTH + 1) . '.'
        );

        $this->validator->validate(\str_repeat('k', KeyValidator::MAX_LENGTH + 1));
    }

    public function testRejectsKeyExceedingMaxLengthWithMultibyteCharacters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Storage key must not exceed ' . KeyValidator::MAX_LENGTH . ' characters, got '
            . (KeyValidator::MAX_LENGTH + 1) . '.'
        );

        $this->validator->validate(\str_repeat('é', KeyValidator::MAX_LENGTH + 1));
    }

    #[DataProvider('reservedCharacterProvider')]
    public function testRejectsReservedCharacters(string $key, string $character): void
    {
        try {
            $this->validator->validate($key);
            self::fail('Expected InvalidArgumentException for reserved character ' . $character . '.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame(
                'Storage key must not contain reserved characters "{}()/\@:".',
                $exception->getMessage()
            );
        }
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function reservedCharacterProvider(): array
    {
        return [
            'brace open' => ['key{part', '{'],
            'brace close' => ['key}part', '}'],
            'parenthesis open' => ['key(part', '('],
            'parenthesis close' => ['key)part', ')'],
            'slash' => ['key/part', '/'],
            'backslash' => ['key\\part', '\\'],
            'at sign' => ['key@part', '@'],
            'colon' => ['key:part', ':'],
        ];
    }
}
