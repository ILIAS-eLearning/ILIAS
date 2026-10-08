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

namespace ILIAS\Forum\Import\Test;

use ilException;
use ILIAS\Forum\Import\ConfinedImportFile;
use PHPUnit\Framework\TestCase;

class ConfinedImportFileTest extends TestCase
{
    private string $root = '';

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/frm-import-' . bin2hex(random_bytes(8));
        mkdir($this->root . '/package/objects', 0777, true);
        file_put_contents($this->root . '/package/objects/picture.png', 'png');
        file_put_contents($this->root . '/outside.txt', 'secret');
    }

    protected function tearDown(): void
    {
        if ($this->root === '' || !is_dir($this->root)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $entry) {
            $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }
        rmdir($this->root);
    }

    public function testFileInsideThePackageIsResolvedToItsRealPath(): void
    {
        $resolved = (new ConfinedImportFile($this->root . '/package'))->resolve('objects/picture.png');

        $this->assertSame(realpath($this->root . '/package/objects/picture.png'), $resolved);
    }

    public function testMissingFileIsAbsent(): void
    {
        $resolved = (new ConfinedImportFile($this->root . '/package'))->resolve('objects/missing.png');

        $this->assertNull($resolved);
    }

    public function testEmptyPathIsAbsent(): void
    {
        $resolved = (new ConfinedImportFile($this->root . '/package'))->resolve('');

        $this->assertNull($resolved);
    }

    public function testParentSegmentsDoNotReachAFileOutsideThePackage(): void
    {
        $resolved = (new ConfinedImportFile($this->root . '/package'))->resolve('../outside.txt');

        $this->assertNull($resolved);
    }

    public function testControlCharactersAndDotSegmentsStillResolveAFileInsideThePackage(): void
    {
        $resolved = (new ConfinedImportFile($this->root . '/package'))->resolve("./objects/\x00picture.png");

        $this->assertSame(realpath($this->root . '/package/objects/picture.png'), $resolved);
    }

    public function testSymlinkLeavingThePackageIsRejected(): void
    {
        $linked = symlink($this->root . '/outside.txt', $this->root . '/package/leak.txt');
        $this->assertTrue($linked);

        $this->expectException(ilException::class);
        $this->expectExceptionMessage('escapes the import directory');

        (new ConfinedImportFile($this->root . '/package'))->resolve('leak.txt');
    }

    public function testEmptyImportDirectoryIsRejected(): void
    {
        $this->expectException(ilException::class);
        $this->expectExceptionMessage('sandboxed import directory');

        (new ConfinedImportFile(''))->resolve('objects/picture.png');
    }

    public function testMissingImportDirectoryIsRejected(): void
    {
        $this->expectException(ilException::class);
        $this->expectExceptionMessage('does not exist');

        (new ConfinedImportFile($this->root . '/missing'))->resolve('objects/picture.png');
    }
}
