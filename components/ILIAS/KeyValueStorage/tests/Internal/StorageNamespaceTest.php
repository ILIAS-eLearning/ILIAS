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

use ILIAS\KeyValueStorage\Internal\StorageNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StorageNamespaceTest extends TestCase
{
    /**
     * @param list<string> $segments
     */
    #[DataProvider('validNamespaces')]
    public function testValidNamespacesAreAccepted(array $segments, string $value): void
    {
        $namespace = new StorageNamespace($segments);

        $this->assertSame($value, $namespace->value());
        $this->assertSame($value, (string) $namespace);
    }

    /**
     * @return array<string, array{list<string>, string}>
     */
    public static function validNamespaces(): array
    {
        return [
            'single segment' => [['ui'], 'ui'],
            'two segments' => [['ui', 'storage'], 'ui.storage'],
            'many segments' => [['my_component', 'view_state', 'table'], 'my_component.view_state.table'],
            'uppercase' => [['Ui', 'Storage'], 'Ui.Storage'],
            'leading digit' => [['1ui'], '1ui'],
            'hyphen' => [['ui-storage'], 'ui-storage'],
            'backslash' => [['ILIAS\\UI'], 'ILIAS\\UI'],
            'maximum length' => [[str_repeat('a', StorageNamespace::MAX_LENGTH)], str_repeat('a', StorageNamespace::MAX_LENGTH)],
        ];
    }

    /**
     * @param list<mixed> $segments
     */
    #[DataProvider('invalidNamespaces')]
    public function testInvalidNamespacesAreRejected(array $segments): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new StorageNamespace($segments);
    }

    /**
     * @return array<string, array{list<mixed>}>
     */
    public static function invalidNamespaces(): array
    {
        return [
            'empty list' => [[]],
            'empty segment' => [['ui', '']],
            'dot in segment' => [['ui.storage']],
            'colon in segment' => [['ui:storage']],
            'control character' => [["ui\nstorage"]],
            'non-string segment' => [['ui', 1]],
            'too long' => [[str_repeat('a', StorageNamespace::MAX_LENGTH + 1)]],
        ];
    }
}
