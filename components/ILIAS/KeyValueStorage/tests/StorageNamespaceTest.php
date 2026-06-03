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
use PHPUnit\Framework\TestCase;

class StorageNamespaceTest extends TestCase
{
    public function testAcceptsValidNamespace(): void
    {
        $namespace = new StorageNamespace('ui.table.sort');

        self::assertSame('ui.table.sort', $namespace->value());
        self::assertSame('ui.table.sort', (string) $namespace);
    }

    public function testAcceptsSingleSegmentNamespace(): void
    {
        $namespace = new StorageNamespace('ui');

        self::assertSame('ui', $namespace->value());
    }

    public function testRejectsEmptyNamespace(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Storage namespace must not be empty.');

        new StorageNamespace('');
    }

    public function testRejectsUppercaseNamespace(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Storage namespace must be a dot-separated lowercase identifier');

        new StorageNamespace('UI.Table');
    }

    public function testRejectsLeadingDigit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Storage namespace must be a dot-separated lowercase identifier');

        new StorageNamespace('1invalid');
    }

    public function testRejectsHyphenatedNamespace(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Storage namespace must be a dot-separated lowercase identifier');

        new StorageNamespace('ui-table');
    }

    public function testAcceptsNamespaceAtMaxLength(): void
    {
        $namespace_value = 'a' . \str_repeat('b', StorageNamespace::MAX_LENGTH - 1);

        $namespace = new StorageNamespace($namespace_value);

        self::assertSame(StorageNamespace::MAX_LENGTH, \strlen($namespace->value()));
    }

    public function testRejectsNamespaceExceedingMaxLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Storage namespace must not exceed ' . StorageNamespace::MAX_LENGTH . ' characters, got '
            . (StorageNamespace::MAX_LENGTH + 1) . '.'
        );

        new StorageNamespace('a' . \str_repeat('b', StorageNamespace::MAX_LENGTH));
    }
}
