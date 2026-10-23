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
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;

/**
 * WithOverlayLock(): mutual exclusion, re-entrancy guard, release after an exception, the read-only fallback and the cases without a lock.
 */
class MigratedLanguageFileSyncLockTest extends MigratedLanguageFileSyncLockTestCase
{
    /**
     * Probes $lock_file with a non-blocking exclusive flock() from another process: 0 = free, 1 = held by
     * someone else, 2 = not openable.
     */
    private function probeLock(string $lock_file): int
    {
        // Mirrors acquireLock()'s own 'c' ?: 'r' fallback: a lock file that is only readable to this
        // process (see the read-only-fallback test) must not make the probe report "could not even
        // open it" (2) when the real question is only whether the lock itself is held (1) or free (0).
        $code = sprintf(
            '$h = @fopen(%1$s, "c") ?: @fopen(%1$s, "r"); if ($h === false) { exit(2); } exit(flock($h, LOCK_EX | LOCK_NB) ? 0 : 1);',
            var_export($lock_file, true)
        );
        $result = $this->runPhpWithTimeout($code, 3.0);
        $this->assertTrue($result['finished'], 'the non-blocking lock probe itself must never hang: ' . $result['output']);

        return (int) $result['exit_code'];
    }

    private function nestedLockChildCode(): string
    {
        $template = <<<'PHP'
            use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
            use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
            use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
            use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;

            $root = __ROOT__;
            require_once $root . '/vendor/composer/vendor/autoload.php';

            $directory = new class implements LanguageFileDirectory {
                public function getPrefix(): string
                {
                    return 'stest';
                }
                public function getPath(): string
                {
                    return 'x/';
                }
                public function getSuffix(): string
                {
                    return '';
                }
                public function isLocal(): bool
                {
                    return false;
                }
            };
            $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $directory);
            $client_data_dir = __CLIENT_DATA_DIR__;

            $log = [];
            $result = MigratedLanguageFileSync::withOverlayLock(
                $manager,
                'de',
                'stest',
                $client_data_dir,
                function () use ($manager, $client_data_dir, &$log) {
                    $log['outer'] = 1;

                    return MigratedLanguageFileSync::withOverlayLock(
                        $manager,
                        'de',
                        'stest',
                        $client_data_dir,
                        function () use (&$log) {
                            $log['inner'] = 1;

                            return 'nested-return';
                        }
                    );
                }
            );
            $log['result'] = $result;
            foreach ($log as $key => $value) {
                echo $key . ':' . $value . "\n";
            }
            PHP;

        return str_replace(
            ['__ROOT__', '__CLIENT_DATA_DIR__'],
            [var_export(ILIAS_ABSOLUTE_PATH, true), var_export($this->client_data_dir, true)],
            $template
        );
    }

    /**
     * The re-entrancy guard lets a nested call return instead of blocking on its own lock; runs in a child
     * with a hard timeout.
     */
    public function testANestedWithOverlayLockForTheSameModuleReturnsInsteadOfDeadlocking(): void
    {
        $this->seedLockDirectory();

        $result = $this->runPhpWithTimeout($this->nestedLockChildCode(), 5.0);

        $this->assertTrue(
            $result['finished'],
            'a nested withOverlayLock() call for the same module must return instead of hanging: ' . $result['output']
        );
        $this->assertSame(0, $result['exit_code'], $result['output']);
        $this->assertStringContainsString('outer:1', $result['output']);
        $this->assertStringContainsString('inner:1', $result['output']);
        $this->assertStringContainsString('result:nested-return', $result['output']);
    }

    /**
     * A concurrent process is refused the lock while the callback runs and gets it as soon as it returns.
     */
    public function testAConcurrentProcessCannotAcquireTheLockWhileTheCallbackRunsButCanAfterwards(): void
    {
        $this->seedLockDirectory();
        $lock_file = $this->lockFile();

        $probe_during = null;
        MigratedLanguageFileSync::withOverlayLock(
            $this->manager,
            'de',
            self::MODULE,
            $this->client_data_dir,
            function () use (&$probe_during, $lock_file): void {
                $probe_during = $this->probeLock($lock_file);
            }
        );
        $probe_after = $this->probeLock($lock_file);

        $this->assertSame(1, $probe_during, 'a concurrent process must not be able to lock the file while the callback runs');
        $this->assertSame(0, $probe_after, 'the lock must be released once the callback returns');
    }

    /**
     * Mutation: the `finally` release being removed or reordered - an exception in the callback must
     * not leave the lock held forever.
     */
    public function testTheLockIsReleasedAfterAnExceptionInTheCallback(): void
    {
        $this->seedLockDirectory();
        $lock_file = $this->lockFile();

        try {
            MigratedLanguageFileSync::withOverlayLock(
                $this->manager,
                'de',
                self::MODULE,
                $this->client_data_dir,
                static function (): void {
                    throw new RuntimeException('boom');
                }
            );
            $this->fail('Expected a RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertSame('boom', $e->getMessage());
        }

        $this->assertSame(0, $this->probeLock($lock_file), 'the lock must be released even after an exception in the callback');
    }

    /**
     * A lock file that cannot be opened for writing is still locked via a read-only handle (a concurrent
     * probe is refused).
     */
    public function testAcquiresARealLockViaTheReadOnlyFallbackWhenTheLockFileCannotBeOpenedForWriting(): void
    {
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            $this->markTestSkipped('Root can always open the lock file for writing; the read-only fallback cannot be exercised.');
        }
        $lock_file = $this->lockFile();
        mkdir(dirname($lock_file), 0775, true);
        touch($lock_file);
        chmod($lock_file, 0444);

        try {
            $probe_during = null;
            $ran = false;
            MigratedLanguageFileSync::withOverlayLock(
                $this->manager,
                'de',
                self::MODULE,
                $this->client_data_dir,
                function () use (&$probe_during, &$ran, $lock_file): void {
                    $ran = true;
                    $probe_during = $this->probeLock($lock_file);
                }
            );

            $this->assertTrue($ran, 'the callback must still run under the read-only-fallback lock');
            $this->assertSame(1, $probe_during, 'the read-only fallback must still take a real, exclusive lock');
        } finally {
            chmod($lock_file, 0664);
        }
    }

    /**
     * Mutation: withOverlayLock() attempting to acquire a lock even without a manager - it must run
     * $callback directly and touch nothing on disk.
     */
    public function testRunsWithoutALockOrDirectoryWhenTheManagerIsNull(): void
    {
        $ran = false;
        $result = MigratedLanguageFileSync::withOverlayLock(
            null,
            'de',
            self::MODULE,
            $this->client_data_dir,
            static function () use (&$ran): string {
                $ran = true;

                return 'ok';
            }
        );

        $this->assertTrue($ran);
        $this->assertSame('ok', $result);
        $this->assertDirectoryDoesNotExist($this->client_data_dir . '/lang');
    }

    /**
     * Mutation: findDirectory() being bypassed - a module without a contributed directory must run
     * unlocked, too.
     */
    public function testRunsWithoutALockWhenTheModuleHasNoContributedDirectory(): void
    {
        $ran = false;
        MigratedLanguageFileSync::withOverlayLock(
            $this->manager,
            'de',
            'unknown_module',
            $this->client_data_dir,
            static function () use (&$ran): void {
                $ran = true;
            }
        );

        $this->assertTrue($ran);
        $this->assertDirectoryDoesNotExist($this->client_data_dir . '/lang');
    }

    /**
     * A module with a directory but neither an overlay directory nor a shipped .po runs unlocked and
     * creates nothing.
     */
    public function testRunsWithoutALockWhenTheModuleIsNotShippedAndHasNoExistingOverlayDirectory(): void
    {
        $ran = false;
        MigratedLanguageFileSync::withOverlayLock(
            $this->manager,
            'de',
            self::MODULE,
            $this->client_data_dir,
            static function () use (&$ran): void {
                $ran = true;
            },
            ILIAS_ABSOLUTE_PATH
        );

        $this->assertTrue($ran);
        $this->assertDirectoryDoesNotExist($this->client_data_dir . '/lang');
    }
}
