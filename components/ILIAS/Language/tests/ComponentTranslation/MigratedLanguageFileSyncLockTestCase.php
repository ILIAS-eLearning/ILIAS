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

use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use PHPUnit\Framework\TestCase;

/**
 * Fixture of the MigratedLanguageFileSyncLock*Test classes: a throwaway client data directory and the
 * helpers to run lock scenarios in child PHP processes.
 */
abstract class MigratedLanguageFileSyncLockTestCase extends TestCase
{
    use RestoresProcessState;

    protected const string MODULE = 'stest';

    protected string $client_data_dir;

    protected LanguageFileDirectory $directory;

    protected LanguageFileDirectoryManager $manager;

    protected function setUp(): void
    {
        // never the build of the installation, see MigratedPoFixture::resetRuntime()
        MigratedPoFixture::resetRuntime();
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../../'));
        }

        $this->client_data_dir = sys_get_temp_dir() . '/ilias_mlfs_lock_test_' . bin2hex(random_bytes(4));
        mkdir($this->client_data_dir, 0775, true);
        $this->directory = MigratedPoFixture::directory(self::MODULE, 'x/');
        $this->manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $this->directory);
    }

    protected function tearDown(): void
    {
        MigratedPoFixture::removeDirectory($this->client_data_dir);

        parent::tearDown();
    }

    protected function lockFile(): string
    {
        return MigratedPoFixture::overlayLock(MigratedPoFixture::overlayBase($this->client_data_dir, $this->directory, 'de'));
    }

    /**
     * Forces withOverlayLock() to take a real lock without needing a shipped `.po`: the overlay
     * directory already existing is enough (see withOverlayLock()'s early-return condition).
     */
    protected function seedLockDirectory(): void
    {
        mkdir(dirname($this->lockFile()), 0775, true);
    }

    /**
     * Runs $code in a child PHP process and kills it after $timeout_seconds, so a regression that
     * deadlocks fails the test instead of hanging the suite.
     *
     * @return array{finished: bool, exit_code: ?int, output: string}
     */
    protected function runPhpWithTimeout(string $code, float $timeout_seconds): array
    {
        $started = ChildPhp::open(['-r', $code]);
        if ($started === null) {
            $this->fail('Could not start the child PHP process.');
        }
        [$process, $pipes] = $started;
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

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

                return ['finished' => true, 'exit_code' => $status['exitcode'], 'output' => $output];
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

        return ['finished' => false, 'exit_code' => null, 'output' => $output];
    }
}
