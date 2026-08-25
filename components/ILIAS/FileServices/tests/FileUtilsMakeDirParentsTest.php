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

use PHPUnit\Framework\TestCase;

/**
 * @see https://mantis.ilias.de/view.php?id=41902
 */
final class FileUtilsMakeDirParentsTest extends TestCase
{
    private string $base;
    private int $previous_umask;
    private ?ilLoggerFactory $previous_logger_factory = null;

    protected function setUp(): void
    {
        // a umask of 0 would hide the leak this test is about
        $this->previous_umask = umask(0022);

        $this->base = sys_get_temp_dir() . '/il_41902_' . bin2hex(random_bytes(8));
        mkdir($this->base, 0777, true);

        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($this->createStub(ilLogger::class));

        $reflection = new ReflectionProperty(ilLoggerFactory::class, 'instance');
        $this->previous_logger_factory = $reflection->getValue();
        $reflection->setValue(null, $logger_factory);
    }

    protected function tearDown(): void
    {
        $reflection = new ReflectionProperty(ilLoggerFactory::class, 'instance');
        $reflection->setValue(null, $this->previous_logger_factory);

        $this->removeRecursively($this->base);

        umask($this->previous_umask);
    }

    public function testDirectoriesAreCreated(): void
    {
        $target = $this->base . '/one/two/three';

        $this->assertTrue(ilFileUtils::makeDirParents($target));
        $this->assertDirectoryExists($target);
    }

    public function testUmaskIsRestoredOnSuccess(): void
    {
        $before = umask();

        ilFileUtils::makeDirParents($this->base . '/one/two/three');

        $this->assertSame($before, umask());
    }

    /**
     * A path component which exists but is a file makes the method bail out. Before the
     * fix for 41902 that early return skipped the umask() restore, so the process kept
     * running with umask(0000) and created world writable files afterwards.
     */
    public function testUmaskIsRestoredWhenAPathComponentIsAFile(): void
    {
        $file = $this->base . '/blocking_file';
        file_put_contents($file, 'not a directory');

        $before = umask();

        $this->assertFalse(ilFileUtils::makeDirParents($file . '/below'));
        $this->assertSame($before, umask());
    }

    private function removeRecursively(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }
        if (!is_dir($path)) {
            unlink($path);
            return;
        }
        foreach (scandir($path) as $entry) {
            if ($entry === '.') {
                continue;
            }
            if ($entry === '..') {
                continue;
            }
            $this->removeRecursively($path . '/' . $entry);
        }
        rmdir($path);
    }
}
