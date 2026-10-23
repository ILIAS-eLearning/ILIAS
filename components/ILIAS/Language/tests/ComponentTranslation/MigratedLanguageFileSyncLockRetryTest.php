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
 * WithOverlayLock() retries when the lock file is replaced while waiting, and checks for symbolic links on every attempt.
 */
class MigratedLanguageFileSyncLockRetryTest extends MigratedLanguageFileSyncLockTestCase
{
    /**
     * Polls until the child $pid has an open file descriptor on exactly $path (its acquireLock() reached
     * fopen()/flock()), so tests can replace the lock file at the right moment without sleeping. Needs
     * /proc (see requireProcFdSupport()); $ignored_fds are descriptor numbers inherited from this process.
     *
     * @param list<string> $ignored_fds
     */
    private function waitForOpenFd(int $pid, string $path, float $timeout_seconds, array $ignored_fds = []): bool
    {
        $deadline = microtime(true) + $timeout_seconds;
        do {
            foreach ($this->openFdsFor($pid, $path) as $entry) {
                if (!in_array($entry, $ignored_fds, true)) {
                    return true;
                }
            }
            usleep(1_000);
        } while (microtime(true) < $deadline);

        return false;
    }

    /**
     * Skips the test unless /proc/self/fd exists, which waitForOpenFd() needs; call before taking any lock
     * or starting a child.
     */
    private function requireProcFdSupport(): void
    {
        if (!is_dir('/proc/self/fd')) {
            $this->markTestSkipped('This test requires /proc (Linux) to observe the child process\'s open file descriptors.');
        }
    }

    /**
     * The descriptor numbers of $pid pointing at realpath($path) (resolved once, because /proc readlink()
     * returns resolved targets).
     *
     * @return list<string> the file descriptor numbers of $pid whose target is exactly the realpath()
     *   of $path - resolved once so a TMPDIR that is itself a symlink (readlink() on /proc/<pid>/fd/*
     *   always returns the fully resolved target) does not make a genuine match go unrecognized.
     */
    private function openFdsFor(int $pid, string $path): array
    {
        $matches = [];
        $entries = @scandir('/proc/' . $pid . '/fd');
        if ($entries === false) {
            return $matches;
        }
        $resolved_path = realpath($path) ?: $path;
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (@readlink('/proc/' . $pid . '/fd/' . $entry) === $resolved_path) {
                $matches[] = $entry;
            }
        }

        return $matches;
    }

    /**
     * The $ignored_fds baseline for waitForOpenFd(): descriptors of $pid already pointing at $path, to be
     * taken right after starting the child.
     *
     * @return list<string>
     */
    private function seedIgnoredFds(int $pid, string $path): array
    {
        return $this->openFdsFor($pid, $path);
    }

    private function lockAttempts(): int
    {
        return (new \ReflectionClassConstant(MigratedLanguageFileSync::class, 'LOCK_ATTEMPTS'))->getValue();
    }

    /**
     * One withOverlayLock() call in a child process, printing RESULT:<value> or
     * EXCEPTION:<class>:<message> plus CALLS:<n>; $error_log_file receives the child's error_log()
     * warnings so tests can assert whether one was logged.
     */
    private function acquireOnceChildCode(?string $error_log_file = null): string
    {
        $template = <<<'PHP'
            use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
            use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
            use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
            use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;

            $root = __ROOT__;
            require_once $root . '/vendor/composer/vendor/autoload.php';

            $error_log_file = __ERROR_LOG_FILE__;
            if ($error_log_file !== null) {
                ini_set('error_log', $error_log_file);
            }

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

            try {
                $calls = 0;
                $result = MigratedLanguageFileSync::withOverlayLock(
                    $manager,
                    'de',
                    'stest',
                    $client_data_dir,
                    static function () use (&$calls, $client_data_dir): string {
                        $calls++;
                        // the inode of the lock file held right now - the lock file itself is
                        // removed afterwards (no overlay exists here)
                        clearstatcache();
                        $held = @lstat($client_data_dir . '/lang/stest/de/lock');
                        echo 'HELD:' . ($held === false ? '' : $held['ino']) . "\n";

                        return 'ok';
                    }
                );
                echo 'RESULT:' . $result . "\n";
                echo 'CALLS:' . $calls . "\n";
            } catch (\Throwable $e) {
                echo 'EXCEPTION:' . get_class($e) . ':' . $e->getMessage() . "\n";
            }
            PHP;

        return str_replace(
            ['__ROOT__', '__CLIENT_DATA_DIR__', '__ERROR_LOG_FILE__'],
            [
                var_export(ILIAS_ABSOLUTE_PATH, true),
                var_export($this->client_data_dir, true),
                var_export($error_log_file, true),
            ],
            $template
        );
    }

    /**
     * @return array{pid: int, process: resource, pipes: array<int, resource>}
     */
    private function startAcquireOnceChild(?string $error_log_file = null): array
    {
        $started = ChildPhp::open(['-r', $this->acquireOnceChildCode($error_log_file)]);
        if ($started === null) {
            $this->fail('Could not start the child PHP process.');
        }
        [$process, $pipes] = $started;
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        $status = proc_get_status($process);

        return ['pid' => $status['pid'], 'process' => $process, 'pipes' => $pipes];
    }

    /**
     * @param resource $process
     * @param array<int, resource> $pipes
     * @return array{finished: bool, output: string}
     */
    private function drainChild($process, array $pipes, float $timeout_seconds): array
    {
        $deadline = microtime(true) + $timeout_seconds;
        $output = '';
        do {
            $output .= (string) stream_get_contents($pipes[1]);
            $output .= (string) stream_get_contents($pipes[2]);
            $status = proc_get_status($process);
            if (!$status['running']) {
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);

                return ['finished' => true, 'output' => $output];
            }
            usleep(20_000);
        } while (microtime(true) < $deadline);

        proc_terminate($process, 9);
        usleep(100_000);
        $output .= (string) stream_get_contents($pipes[1]);
        $output .= (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        return ['finished' => false, 'output' => $output];
    }

    /**
     * Kills a child left running by a failed assertion and closes its handles; safe to call after
     * drainChild().
     *
     * @param array{pid: int, process: resource, pipes: array<int, resource>} $child
     */
    private function terminateChildIfRunning(array $child): void
    {
        if (is_resource($child['process'])) {
            $status = proc_get_status($child['process']);
            if ($status['running']) {
                proc_terminate($child['process'], 9);
                usleep(100_000);
            }
        }
        foreach ($child['pipes'] as $pipe) {
            if (is_resource($pipe)) {
                @fclose($pipe);
            }
        }
        if (is_resource($child['process'])) {
            @proc_close($child['process']);
        }
    }

    /**
     * Replaces the file at $lock_file by a new inode locked via $next_handle before releasing
     * $current_handle, so a waiter is only unblocked once the replacement is in place.
     *
     * @param resource $current_handle
     * @return resource the new handle, still holding its lock
     */
    private function replaceLockedFile(string $lock_file, $current_handle)
    {
        $this->assertTrue((bool) @unlink($lock_file), 'could not unlink the lock file to replace it');
        $next_handle = fopen($lock_file, 'c');
        $this->assertNotFalse($next_handle, 'could not recreate the lock file');
        $this->assertTrue(flock($next_handle, LOCK_EX));
        flock($current_handle, LOCK_UN);
        fclose($current_handle);

        return $next_handle;
    }

    /**
     * The lock file is replaced twice while a real process waits on it (synchronized via flock() and
     * /proc, not timing); it must retry twice and end up holding the third, stable file.
     */
    public function testAWaiterThatLocksAnInodeDeletedInTheMeantimeRetriesAndEndsUpHoldingTheNewFile(): void
    {
        $this->requireProcFdSupport();
        $this->seedLockDirectory();
        $lock_file = $this->lockFile();

        $current_handle = fopen($lock_file, 'c');
        $this->assertNotFalse($current_handle);
        $this->assertTrue(flock($current_handle, LOCK_EX));

        $child = $this->startAcquireOnceChild();
        $ignored_fds = $this->seedIgnoredFds($child['pid'], $lock_file);
        try {
            for ($round = 1; $round <= 2; $round++) {
                $this->assertTrue(
                    $this->waitForOpenFd($child['pid'], $lock_file, 5.0, $ignored_fds),
                    'the child never opened the lock file for attempt ' . $round
                );
                $current_handle = $this->replaceLockedFile($lock_file, $current_handle);
            }

            $this->assertTrue(
                $this->waitForOpenFd($child['pid'], $lock_file, 5.0, $ignored_fds),
                'the child never opened the lock file for the final, stable attempt'
            );
            clearstatcache(true, $lock_file);
            $final_inode = lstat($lock_file)['ino'];
            flock($current_handle, LOCK_UN);
            fclose($current_handle);
            $current_handle = null;

            $result = $this->drainChild($child['process'], $child['pipes'], 5.0);
            $this->assertTrue($result['finished'], 'the child must not hang: ' . $result['output']);
            $this->assertStringContainsString('RESULT:ok', $result['output']);

            $this->assertStringContainsString('HELD:' . $final_inode . "\n", $result['output']);
            clearstatcache(true, $lock_file);
            $this->assertSame(
                $final_inode,
                lstat($lock_file)['ino'],
                'the waiter must end up holding the file that was actually left in place, not trigger a further replacement'
            );
        } finally {
            if (is_resource($current_handle)) {
                flock($current_handle, LOCK_UN);
                fclose($current_handle);
            }
            $this->terminateChildIfRunning($child);
        }
    }

    /**
     * When every attempt finds a replaced lock file, acquireLock() gives up with null and the callback
     * runs exactly once, unlocked, with a warning logged.
     */
    public function testTheLockFileReplacedOnEveryAttemptExhaustsTheRetriesAndRunsCallbackOnceUnlockedWithAWarningLogged(): void
    {
        $this->requireProcFdSupport();
        $this->seedLockDirectory();
        $lock_file = $this->lockFile();
        $attempts = $this->lockAttempts();
        $error_log_file = $this->client_data_dir . '-error-log-exhausted.txt';

        $current_handle = fopen($lock_file, 'c');
        $this->assertNotFalse($current_handle);
        $this->assertTrue(flock($current_handle, LOCK_EX));

        $child = $this->startAcquireOnceChild($error_log_file);
        $ignored_fds = $this->seedIgnoredFds($child['pid'], $lock_file);
        try {
            for ($round = 1; $round <= $attempts; $round++) {
                $this->assertTrue(
                    $this->waitForOpenFd($child['pid'], $lock_file, 5.0, $ignored_fds),
                    'the child never opened the lock file for attempt ' . $round
                );
                $current_handle = $this->replaceLockedFile($lock_file, $current_handle);
            }
            flock($current_handle, LOCK_UN);
            fclose($current_handle);
            $current_handle = null;

            $result = $this->drainChild($child['process'], $child['pipes'], 5.0);

            $this->assertTrue($result['finished'], 'the child must not hang: ' . $result['output']);
            $this->assertStringContainsString(
                'RESULT:ok',
                $result['output'],
                'exhausting LOCK_ATTEMPTS must fall back to running the callback unlocked, not throw'
            );
            $this->assertStringContainsString('CALLS:1', $result['output'], 'the callback must run exactly once');
            $this->assertStringNotContainsString('EXCEPTION', $result['output']);

            $this->assertFileExists($error_log_file, 'exhausting LOCK_ATTEMPTS must log a warning');
            $warning = (string) file_get_contents($error_log_file);
            $this->assertStringContainsString('removed or replaced', $warning);
            $this->assertStringContainsString((string) $attempts, $warning);
        } finally {
            if (is_resource($current_handle)) {
                flock($current_handle, LOCK_UN);
                fclose($current_handle);
            }
            $this->terminateChildIfRunning($child);
            @unlink($error_log_file);
        }
    }

    /**
     * Replaced only on the first attempt: the second attempt acquires a real lock and no warning is
     * logged.
     */
    public function testTheLockFileReplacedOnlyOnTheFirstAttemptStillAcquiresTheLockNormallyWithoutAWarning(): void
    {
        $this->requireProcFdSupport();
        $this->seedLockDirectory();
        $lock_file = $this->lockFile();
        $error_log_file = $this->client_data_dir . '-error-log-recovered.txt';

        $current_handle = fopen($lock_file, 'c');
        $this->assertNotFalse($current_handle);
        $this->assertTrue(flock($current_handle, LOCK_EX));

        $child = $this->startAcquireOnceChild($error_log_file);
        $ignored_fds = $this->seedIgnoredFds($child['pid'], $lock_file);
        try {
            $this->assertTrue(
                $this->waitForOpenFd($child['pid'], $lock_file, 5.0, $ignored_fds),
                'the child never opened the lock file for the first attempt'
            );
            $current_handle = $this->replaceLockedFile($lock_file, $current_handle);

            $this->assertTrue(
                $this->waitForOpenFd($child['pid'], $lock_file, 5.0, $ignored_fds),
                'the child never opened the lock file for the second, now-stable attempt'
            );
            flock($current_handle, LOCK_UN);
            fclose($current_handle);
            $current_handle = null;

            $result = $this->drainChild($child['process'], $child['pipes'], 5.0);

            $this->assertTrue($result['finished'], 'the child must not hang: ' . $result['output']);
            $this->assertStringContainsString('RESULT:ok', $result['output']);
            $this->assertStringContainsString('CALLS:1', $result['output'], 'the callback must run exactly once');
            $this->assertStringNotContainsString('EXCEPTION', $result['output']);

            $this->assertSame(
                '',
                (string) @file_get_contents($error_log_file),
                'recovering on a later attempt must not log the "removed or replaced" warning'
            );
        } finally {
            if (is_resource($current_handle)) {
                flock($current_handle, LOCK_UN);
                fclose($current_handle);
            }
            $this->terminateChildIfRunning($child);
            @unlink($error_log_file);
        }
    }

    /**
     * A symlink swapped in while the child waits is detected on a later attempt, not only before the first
     * one.
     */
    public function testTheSymbolicLinkCheckAppliesOnEveryAttemptNotJustTheFirst(): void
    {
        $this->requireProcFdSupport();
        $this->seedLockDirectory();
        $lock_file = $this->lockFile();
        $decoy = $this->client_data_dir . '-decoy-lock-target';
        file_put_contents($decoy, 'DECOY');

        $current_handle = fopen($lock_file, 'c');
        $this->assertNotFalse($current_handle);
        $this->assertTrue(flock($current_handle, LOCK_EX));

        $child = $this->startAcquireOnceChild();
        $ignored_fds = $this->seedIgnoredFds($child['pid'], $lock_file);
        try {
            $this->assertTrue(
                $this->waitForOpenFd($child['pid'], $lock_file, 5.0, $ignored_fds),
                'the child never opened the lock file for the first attempt'
            );
            $this->assertTrue((bool) @unlink($lock_file));
            symlink($decoy, $lock_file);
            flock($current_handle, LOCK_UN);
            fclose($current_handle);
            $current_handle = null;

            $result = $this->drainChild($child['process'], $child['pipes'], 5.0);

            $this->assertTrue($result['finished'], 'the child must not hang: ' . $result['output']);
            $this->assertStringContainsString(
                'RESULT:ok',
                $result['output'],
                'a symlink discovered on a LATER attempt must fall back to running unlocked, not bubble up an exception'
            );
            $this->assertSame('DECOY', file_get_contents($decoy), 'the symlink target must never be touched');
            $this->assertSame($decoy, readlink($lock_file), 'the symlink itself must be left in place');
        } finally {
            @unlink($decoy);
            if (is_resource($current_handle)) {
                flock($current_handle, LOCK_UN);
                fclose($current_handle);
            }
            $this->terminateChildIfRunning($child);
        }
    }
}
