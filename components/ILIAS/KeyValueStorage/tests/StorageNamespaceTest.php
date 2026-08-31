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

use ILIAS\KeyValueStorage\StorageNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StorageNamespaceTest extends TestCase
{
    #[DataProvider('validNamespaces')]
    public function testValidNamespacesAreAccepted(string $value): void
    {
        $namespace = new StorageNamespace($value);

        $this->assertSame($value, $namespace->value());
        $this->assertSame($value, (string) $namespace);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validNamespaces(): array
    {
        return [
            'single segment' => ['ui'],
            'two segments' => ['ui.storage'],
            'many segments' => ['my_component.view_state.table'],
            'digits after letter' => ['h5p.content2'],
            'underscores' => ['my_component.view_state'],
            'maximum length' => [str_repeat('a', StorageNamespace::MAX_LENGTH)],
        ];
    }

    #[DataProvider('invalidNamespaces')]
    public function testInvalidNamespacesAreRejected(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new StorageNamespace($value);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidNamespaces(): array
    {
        return [
            'empty' => [''],
            'uppercase' => ['Ui.Storage'],
            'leading digit' => ['1ui'],
            'hyphen' => ['ui-storage'],
            'leading dot' => ['.ui'],
            'trailing dot' => ['ui.'],
            'empty segment' => ['ui..storage'],
            'colon' => ['ui:storage'],
            'backslash' => ['ILIAS\\UI'],
            'too long' => [str_repeat('a', StorageNamespace::MAX_LENGTH + 1)],
        ];
    }
}
