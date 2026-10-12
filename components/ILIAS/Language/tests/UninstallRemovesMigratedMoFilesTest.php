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
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\SkippedWithMessageException;

/**
 * ilObjLanguage::removeMigratedMoFiles(), the overlay-file counterpart to uninstall()'s DB flush: without it a migrated module's
 * overlay .po/.mo stayed after uninstalling and ilLanguage::txtlng() (which does not check the language is installed) kept
 * serving stale content. It removes both overlay files via MigratedLanguageFileSync::removeOverlay() and leaves the shipped
 * `.po` untouched.
 *
 * Separate processes, because CLIENT_DATA_DIR cannot be redefined and an earlier test class of a full-suite run
 * may already have defined it as /var/iliasdata, which would make every test skip.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class UninstallRemovesMigratedMoFilesTest extends ilLanguageBaseTestCase
{
    private ?string $fixture_directory = null;
    /** True if this test defined CLIENT_DATA_DIR itself; tearDown() removes only such a root. */
    private bool $created_client_data_dir_root = false;

    private function removeDirectoryRecursively(string $dir): void
    {
        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->removeDirectoryRecursively($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }

        MigratedPoFixture::resetRuntime();
    }

    protected function tearDown(): void
    {
        if (isset($this->fixture_directory) && is_dir($this->fixture_directory)) {
            $this->removeDirectoryRecursively($this->fixture_directory);
        }

        // delete only a CLIENT_DATA_DIR this test created: it once wiped the real /var/iliasdata in a full-suite run
        $overlay_fixture_dir = $this->overlayFixtureDirectory();
        if ($overlay_fixture_dir !== null && is_dir($overlay_fixture_dir)) {
            $this->removeDirectoryRecursively($overlay_fixture_dir);
        }
        if ($this->created_client_data_dir_root && defined('CLIENT_DATA_DIR') && is_dir(CLIENT_DATA_DIR)) {
            $this->removeDirectoryRecursively(CLIENT_DATA_DIR);
        }

        parent::tearDown();
    }

    /** The one subdirectory under CLIENT_DATA_DIR this test's fixture wrote to (see seedOverlay()); null if none. */
    private function overlayFixtureDirectory(): ?string
    {
        if (!isset($this->fixture_directory) || !defined('CLIENT_DATA_DIR')) {
            return null;
        }

        return rtrim(CLIENT_DATA_DIR, '/') . '/lang';
    }

    /**
     * Deliberately not called from setUp(): the "CLIENT_DATA_DIR cannot be resolved" test must observe it undefined.
     * An already defined CLIENT_DATA_DIR is reused: tearDown() removes only this class' own subdirectory.
     */
    private function ensureClientDataDirDefined(): void
    {
        // seeding an overlay would write fixture files into a foreign CLIENT_DATA_DIR (e.g. the real /var/iliasdata): refuse
        if (defined('CLIENT_DATA_DIR') && !str_starts_with(CLIENT_DATA_DIR, sys_get_temp_dir() . '/')) {
            $this->markTestSkipped(
                'CLIENT_DATA_DIR ("' . CLIENT_DATA_DIR . '") is not a test-owned temp directory - '
                . 'refusing to write fixture files there.'
            );
        }
        if (!defined('CLIENT_DATA_DIR')) {
            define('CLIENT_DATA_DIR', sys_get_temp_dir() . '/ilias_uninstall_mo_test_' . bin2hex(random_bytes(4)));
            $this->created_client_data_dir_root = true;
        }
    }

    /**
     * Seeds only the shipped `.po` of a throwaway module (no shipped `.mo` exists).
     *
     * @param array<string, string> $entries identifier => value
     */
    private function seedShippedModule(string $module, string $lang_key, array $entries): LanguageFileDirectory
    {
        $this->fixture_directory ??= __DIR__ . '/tmp-uninstall-fixtures-' . bin2hex(random_bytes(4));
        if (!is_dir($this->fixture_directory)) {
            mkdir($this->fixture_directory, 0775, true);
        }

        $translations = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog();
        foreach ($entries as $identifier => $value) {
            $translations->add(MigratedPoFixture::entry($module, $identifier, $value));
        }

        $base_path = $this->fixture_directory . '/' . $module . '_' . $lang_key;
        MigratedPoFixture::writePo($base_path . '.po', $translations);

        $relative_path = 'components/ILIAS/Language/tests/' . basename($this->fixture_directory) . '/';

        return new class ($module, $relative_path) implements LanguageFileDirectory {
            public function __construct(private string $prefix, private string $path)
            {
            }

            public function getPrefix(): string
            {
                return $this->prefix;
            }

            public function getPath(): string
            {
                return $this->path;
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
    }

    /**
     * Seeds the overlay `.po`+`.mo` pair under CLIENT_DATA_DIR, mirroring the shipped relative path, simulating an installed
     * language. Requires seedShippedModule() first.
     *
     * @param array<string, string> $entries identifier => value
     */
    private function seedOverlay(string $module, string $lang_key, array $entries): void
    {
        $this->ensureClientDataDirDefined();

        $translations = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog();
        foreach ($entries as $identifier => $value) {
            $translations->add(MigratedPoFixture::entry($module, $identifier, $value));
        }

        $overlay_dir = rtrim(CLIENT_DATA_DIR, '/') . '/lang/' . $module . '/' . $lang_key . '/';
        if (!is_dir($overlay_dir)) {
            mkdir($overlay_dir, 0775, true);
        }

        $base_path = $overlay_dir . $module . '_' . $lang_key;
        MigratedPoFixture::writePo($base_path . '.po', $translations);
        MigratedPoFixture::writeOverlayRevision($base_path, $translations);
    }

    private function registerDirectoryManager(LanguageFileDirectory ...$contributed): void
    {
        $this->setGlobalVariable(
            LanguageFileDirectoryManager::class,
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), ...$contributed)
        );
    }

    private function shippedPath(string $module, string $lang_key, string $extension): string
    {
        return $this->fixture_directory . '/' . $module . '_' . $lang_key . '.' . $extension;
    }

    private function overlayPath(string $module, string $lang_key, string $extension): string
    {
        $base = rtrim(CLIENT_DATA_DIR, '/') . '/lang/' . $module . '/' . $lang_key . '/' . $module . '_' . $lang_key;

        return match ($extension) {
            'mo' => MigratedPoFixture::overlayMo($base),
            'lock' => MigratedPoFixture::overlayLock($base),
            default => $base . '.' . $extension,
        };
    }

    private function loadShippedPo(string $module, string $lang_key): \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog
    {
        return MigratedPoFixture::readPo($this->shippedPath($module, $lang_key, 'po'));
    }

    private function callRemoveMigratedMoFiles(string $lang_key): void
    {
        (new ReflectionClass(ilObjLanguage::class))
            ->getMethod('removeMigratedMoFiles')
            ->invoke(null, $lang_key);
    }

    /**
     * What the runtime serves for $module/$lang_key, see MigratedPoFixture::servedTexts().
     *
     * @return array<string, string>|null
     */
    private function callLoadFromMigratedLanguageFile(string $module, string $lang_key, bool $build = true): ?array
    {
        return MigratedPoFixture::servedTexts($module, $lang_key, $build);
    }

    public function testRemovesBothOverlayFilesButLeavesTheShippedPoUntouched(): void
    {
        $directory = $this->seedShippedModule('utest', 'de', ['greeting' => 'Hallo']);
        $this->seedOverlay('utest', 'de', ['greeting' => 'Hallo']);
        $this->registerDirectoryManager($directory);
        $po_before = file_get_contents($this->shippedPath('utest', 'de', 'po'));

        $this->callRemoveMigratedMoFiles('de');

        $this->assertFileDoesNotExist($this->overlayPath('utest', 'de', 'mo'));
        $this->assertFileDoesNotExist($this->overlayPath('utest', 'de', 'po'));
        $this->assertFileExists($this->shippedPath('utest', 'de', 'po'));
        $this->assertSame($po_before, file_get_contents($this->shippedPath('utest', 'de', 'po')));
    }

    public function testRemovesEachContributedModuleIndependently(): void
    {
        $first = $this->seedShippedModule('uone', 'de', ['greeting' => 'Hallo']);
        $this->seedOverlay('uone', 'de', ['greeting' => 'Hallo']);
        $second = $this->seedShippedModule('utwo', 'de', ['greeting' => 'Servus']);
        $this->seedOverlay('utwo', 'de', ['greeting' => 'Servus']);
        $this->registerDirectoryManager($first, $second);

        $this->callRemoveMigratedMoFiles('de');

        $this->assertFileDoesNotExist($this->overlayPath('uone', 'de', 'mo'));
        $this->assertFileDoesNotExist($this->overlayPath('uone', 'de', 'po'));
        $this->assertFileDoesNotExist($this->overlayPath('utwo', 'de', 'mo'));
        $this->assertFileDoesNotExist($this->overlayPath('utwo', 'de', 'po'));
        $this->assertFileExists($this->shippedPath('uone', 'de', 'po'));
        $this->assertFileExists($this->shippedPath('utwo', 'de', 'po'));
    }

    public function testOnlyRemovesTheOverlayForTheRequestedLanguageNotOtherLanguages(): void
    {
        $directory = $this->seedShippedModule('utest', 'de', ['greeting' => 'Hallo']);
        $this->seedOverlay('utest', 'de', ['greeting' => 'Hallo']);
        $this->seedOverlay('utest', 'fr', ['greeting' => 'Bonjour']);
        $this->registerDirectoryManager($directory);

        $this->callRemoveMigratedMoFiles('de');

        $this->assertFileDoesNotExist($this->overlayPath('utest', 'de', 'mo'));
        $this->assertFileExists($this->overlayPath('utest', 'fr', 'mo'));
        $this->assertFileExists($this->overlayPath('utest', 'fr', 'po'));
    }

    public function testIsANoOpWhenNoOverlayExistsForThatLanguage(): void
    {
        $directory = $this->seedShippedModule('utest', 'de', ['greeting' => 'Hallo']);
        $this->registerDirectoryManager($directory);

        $this->callRemoveMigratedMoFiles('de');

        $this->addToAssertionCount(1);
        $this->assertFileExists($this->shippedPath('utest', 'de', 'po'));
    }

    public function testIsANoOpForALanguageThatHasNoOverlayAtAll(): void
    {
        $directory = $this->seedShippedModule('utest', 'de', ['greeting' => 'Hallo']);
        $this->seedOverlay('utest', 'de', ['greeting' => 'Hallo']);
        $this->registerDirectoryManager($directory);

        $this->callRemoveMigratedMoFiles('fr');

        $this->assertFileExists($this->overlayPath('utest', 'de', 'mo'));
        $this->assertFileExists($this->overlayPath('utest', 'de', 'po'));
    }

    public function testReturnsImmediatelyWithoutErrorWhenNoDirectoryManagerIsRegisteredAtAll(): void
    {
        $this->callRemoveMigratedMoFiles('de');

        $this->addToAssertionCount(1);
    }

    /** Without a resolvable CLIENT_DATA_DIR removeMigratedMoFiles() must no-op, never touch the shipped `.po`; own process, since another test defines it. */
    #[RunInSeparateProcess]
    public function testIsANoOpWhenClientDataDirIsNotResolvable(): void
    {
        $this->assertFalse(defined('CLIENT_DATA_DIR'));

        $directory = $this->seedShippedModule('utest', 'de', ['greeting' => 'Hallo']);
        $shipped_po_before = file_get_contents($this->shippedPath('utest', 'de', 'po'));
        $this->registerDirectoryManager($directory);

        $this->callRemoveMigratedMoFiles('de');

        $this->assertSame($shipped_po_before, file_get_contents($this->shippedPath('utest', 'de', 'po')));
    }

    public function testInvalidatesIlLanguagesCacheSoTheRemovalIsVisibleImmediately(): void
    {
        $directory = $this->seedShippedModule('utest', 'de', ['greeting' => 'Hallo']);
        $this->seedOverlay('utest', 'de', ['greeting' => 'Hallo']);
        $this->registerDirectoryManager($directory);
        // "de" is installed (the migrated state is only served for installed languages)
        (new ReflectionProperty(ilLanguage::class, 'installed_languages'))->setValue(null, ['de']);

        $before = $this->callLoadFromMigratedLanguageFile('utest', 'de');
        $this->assertSame(['greeting' => 'Hallo'], $before);

        $this->callRemoveMigratedMoFiles('de');

        // a stale hit from before the removal must not leak through
        $this->assertNull($this->callLoadFromMigratedLanguageFile('utest', 'de', false));
    }

    /**
     * A failure removing one module's overlay is logged via $DIC->logger()->forComponent('lang')->warning() and swallowed,
     * and does not abort other modules. The logger is stubbed via the 'ilLoggerFactory' service; the failing module's overlay
     * directory is made unwritable (unlink() needs directory permission) and the PHP warning silenced.
     */
    public function testLogsAndSwallowsARemovalFailureForOneModuleWithoutAbortingOthers(): void
    {
        if (posix_getuid() === 0) {
            $this->markTestSkipped('Cannot force an unremovable file while running as root; skipping.');
        }

        $this->ensureClientDataDirDefined();
        $this->fixture_directory ??= __DIR__ . '/tmp-uninstall-fixtures-' . bin2hex(random_bytes(4));
        if (!is_dir($this->fixture_directory)) {
            mkdir($this->fixture_directory, 0775, true);
        }

        // the failing module has its own sub-path, so only its overlay directory is locked
        $shipped_readonly_dir = $this->fixture_directory . '/readonly';
        mkdir($shipped_readonly_dir, 0775, true);
        $shipped_translations = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog();
        $shipped_translations->add(MigratedPoFixture::entry('uone', 'greeting', 'Hallo'));
        MigratedPoFixture::writePo($shipped_readonly_dir . '/uone_de.po', $shipped_translations);

        $relative_readonly_path = 'components/ILIAS/Language/tests/' . basename($this->fixture_directory) . '/readonly/';
        $overlay_readonly_dir = rtrim(CLIENT_DATA_DIR, '/') . '/lang/uone/de/';
        mkdir($overlay_readonly_dir, 0775, true);
        MigratedPoFixture::writePair($overlay_readonly_dir . 'uone_de', $shipped_translations);

        $failing = new class ('uone', $relative_readonly_path) implements LanguageFileDirectory {
            public function __construct(private string $prefix, private string $path)
            {
            }

            public function getPrefix(): string
            {
                return $this->prefix;
            }

            public function getPath(): string
            {
                return $this->path;
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
        $working = $this->seedShippedModule('utwo', 'de', ['greeting' => 'Servus']);
        $this->seedOverlay('utwo', 'de', ['greeting' => 'Servus']);
        $this->registerDirectoryManager($failing, $working);

        chmod($overlay_readonly_dir, 0555);

        // two warnings: the lock file cannot be created, then the removal itself fails
        $warnings = [];
        $logger = $this->createStub(ilLogger::class);
        $logger->method('warning')->willReturnCallback(function (string $message) use (&$warnings): void {
            $warnings[] = $message;
        });
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);

        set_error_handler(static fn(): bool => true, E_WARNING);
        try {
            $this->callRemoveMigratedMoFiles('de');
        } finally {
            restore_error_handler();
            chmod($overlay_readonly_dir, 0775);
        }

        $this->assertCount(2, $warnings);
        $this->assertStringContainsString('uone/de/lock', $warnings[0]);
        $this->assertStringContainsString('uone', $warnings[1]);

        $this->assertFileExists(MigratedPoFixture::overlayMo($overlay_readonly_dir . 'uone_de'));
        $this->assertFileExists($overlay_readonly_dir . 'uone_de.po');

        $this->assertFileDoesNotExist($this->overlayPath('utwo', 'de', 'mo'));
        $this->assertFileDoesNotExist($this->overlayPath('utwo', 'de', 'po'));
    }

    /**
     * tearDown() once deleted the whole CLIENT_DATA_DIR if it was merely defined(), wiping the real /var/iliasdata in a
     * full-suite run: a foreign, pre-existing definition must stay untouched. Own process so no other test defines it first.
     */
    #[RunInSeparateProcess]
    public function testTearDownNeverDeletesAClientDataDirThisClassDidNotCreateItself(): void
    {
        $foreign_dir = sys_get_temp_dir() . '/ilias_foreign_client_data_dir_' . bin2hex(random_bytes(4));
        mkdir($foreign_dir, 0775, true);
        file_put_contents($foreign_dir . '/marker.txt', 'do-not-delete');
        // a foreign CLIENT_DATA_DIR this class did not create
        define('CLIENT_DATA_DIR', $foreign_dir);

        try {
            // the fixture is still written into the foreign directory; only deleting CLIENT_DATA_DIR itself must never happen
            $directory = $this->seedShippedModule('uforeign', 'de', ['greeting' => 'Hallo']);
            $this->seedOverlay('uforeign', 'de', ['greeting' => 'Hallo']);
            $this->registerDirectoryManager($directory);
            $this->assertFileExists($this->overlayPath('uforeign', 'de', 'po'));

            // tearDown() runs again after the test; harmless, nothing is left to clean up
            $this->tearDown();

            $this->assertFileExists(
                $foreign_dir . '/marker.txt',
                'tearDown() deleted (part of) a CLIENT_DATA_DIR this test class did not create itself.'
            );
            $this->assertFileDoesNotExist($this->overlayPath('uforeign', 'de', 'po'));
        } finally {
            // runs even if an assertion failed, so $foreign_dir is never left behind
            if (is_file($foreign_dir . '/marker.txt')) {
                unlink($foreign_dir . '/marker.txt');
            }
            if (is_dir($foreign_dir)) {
                $this->removeDirectoryRecursively($foreign_dir);
            }
        }
    }

    /** A CLIENT_DATA_DIR outside sys_get_temp_dir() must not be written into: ensureClientDataDirDefined() skips. Own process. */
    #[RunInSeparateProcess]
    public function testRefusesToWriteIntoAClientDataDirOutsideSysTempDir(): void
    {
        $foreign_dir = __DIR__ . '/tmp-not-a-temp-dir-' . bin2hex(random_bytes(4));
        $this->assertFalse(str_starts_with($foreign_dir, sys_get_temp_dir() . '/'));
        mkdir($foreign_dir, 0775, true);
        define('CLIENT_DATA_DIR', $foreign_dir);

        try {
            try {
                $directory = $this->seedShippedModule('uoutside', 'de', ['greeting' => 'Hallo']);
                $this->seedOverlay('uoutside', 'de', ['greeting' => 'Hallo']);
                $this->fail('seedOverlay() must refuse to write into a CLIENT_DATA_DIR outside sys_get_temp_dir().');
            } catch (SkippedWithMessageException $e) {
                $this->assertStringContainsString($foreign_dir, $e->getMessage());
            }

            $this->assertSame([], array_diff(scandir($foreign_dir) ?: [], ['.', '..']));
        } finally {
            if (is_dir($foreign_dir)) {
                $this->removeDirectoryRecursively($foreign_dir);
            }
        }
    }
}
