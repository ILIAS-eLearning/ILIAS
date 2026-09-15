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

namespace ILIAS\Tests\KeyValueStorage\Internal;

use ILIAS\KeyValueStorage\Internal\KeyRules;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class KeyRulesTest extends TestCase
{
    private KeyRules $rules;

    protected function setUp(): void
    {
        $this->rules = new KeyRules();
    }

    #[DataProvider('validKeys')]
    public function testValidKeysPass(string $key): void
    {
        $this->rules->check($key);

        $this->addToAssertionCount(1);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validKeys(): array
    {
        return [
            'plain' => ['sort_column'],
            'dotted' => ['view.sort.column'],
            'php namespace' => ['ILIAS\\UI\\Implementation\\Component\\Table\\Data_my_table'],
            'braces' => ['{tpl}'],
            'slash' => ['a/b'],
            'at sign' => ['user@example'],
            'unicode' => ['Ümläut'],
            'maximum length' => [str_repeat('a', KeyRules::MAX_LENGTH)],
        ];
    }

    #[DataProvider('invalidKeys')]
    public function testInvalidKeysAreRejected(string $key): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->rules->check($key);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidKeys(): array
    {
        return [
            'empty' => [''],
            'colon' => ['a:b'],
            'null byte' => ["a\0b"],
            'newline' => ["a\nb"],
            'delete' => ["a\x7Fb"],
            'too long' => [str_repeat('a', KeyRules::MAX_LENGTH + 1)],
        ];
    }

    public function testLengthIsCountedInCharactersNotBytes(): void
    {
        $this->rules->check(str_repeat('ü', KeyRules::MAX_LENGTH));

        $this->addToAssertionCount(1);
    }
}
