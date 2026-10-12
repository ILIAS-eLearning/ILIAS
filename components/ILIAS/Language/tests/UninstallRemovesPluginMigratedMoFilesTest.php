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
 * ilPluginLanguage::uninstall() removes the overlay .po/.mo of a migrated plugin (counterpart of
 * ilObjLanguage::removeMigratedMoFiles(), see UninstallRemovesMigratedMoFilesTest): ilLanguage reads an overlay without
 * checking that the owning plugin is still installed, so a stale one would keep serving its content. Both overlay files
 * go via MigratedLanguageFileSync::removeOverlay(), the shipped `.po` stays untouched.
 *
 * Separate processes, because CLIENT_DATA_DIR cannot be redefined and an earlier test class of a full-suite run
 * may already have defined it as /var/iliasdata, which would make every test skip.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class UninstallRemovesPluginMigratedMoFilesTest extends ilLanguageBaseTestCase
{
    private ?string $fixture_directory = null;
    private ?string $plugin_root_directory = null;
    /** True if this test defined CLIENT_DATA_DIR itself; tearDown() removes only such a root. */
    private bool $created_client_data_dir_root = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->fixture_directory) && is_dir($this->fixture_directory)) {
            $this->removeDirectoryRecursively($this->fixture_directory);
        }
        if (isset($this->plugin_root_directory) && is_dir($this->plugin_root_directory)) {
            $this->removeDirectoryRecursively($this->plugin_root_directory);
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
            define('CLIENT_DATA_DIR', sys_get_temp_dir() . '/ilias_plugin_uninstall_mo_test_' . bin2hex(random_bytes(4)));
            $this->created_client_data_dir_root = true;
        }
    }

    /** Seeds only the shipped `.po` of a throwaway module/language (no shipped `.mo` exists). */
    private function seedShippedModule(string $module, string $lang_key, array $entries): LanguageFileDirectory
    {
        $this->fixture_directory ??= __DIR__ . '/tmp-plugin-uninstall-fixtures-' . bin2hex(random_bytes(4));
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

    /** Seeds the overlay `.po`+`.mo` pair under CLIENT_DATA_DIR, mirroring the shipped relative path. Requires seedShippedModule() first. */
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

    /**
     * getAvailableLangFiles() scans "<plugin path>/lang" for "ilias_<key>.lang"; only the file names matter.
     *
     * @param list<string> $lang_keys
     */
    private function createPluginInfo(string $module_prefix, array $lang_keys): ilPluginInfo
    {
        $this->plugin_root_directory = __DIR__ . '/tmp-plugin-root-' . bin2hex(random_bytes(4));
        mkdir($this->plugin_root_directory . '/lang', 0775, true);
        foreach ($lang_keys as $lang_key) {
            touch($this->plugin_root_directory . '/lang/ilias_' . $lang_key . '.lang');
        }

        $component = $this->createStub(ilComponentInfo::class);
        $component->method('getId')->willReturn('comp');

        $slot = $this->createStub(ilPluginSlotInfo::class);
        $slot->method('getId')->willReturn('slot');

        $plugin_info = $this->createStub(ilPluginInfo::class);
        $plugin_info->method('getComponent')->willReturn($component);
        $plugin_info->method('getPluginSlot')->willReturn($slot);
        $plugin_info->method('getId')->willReturn($module_prefix);
        $plugin_info->method('getPath')->willReturn($this->plugin_root_directory);

        return $plugin_info;
    }

    private function createDatabaseStub(): ilDBInterface
    {
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn(mixed $v): string => "'" . (string) $v . "'");

        return $db;
    }

    public function testUninstallRemovesBothOverlayFilesForEveryLanguageThePluginShipsButLeavesTheShippedPoUntouched(): void
    {
        $module = 'comp_slot_ptest';
        $directory = $this->seedShippedModule($module, 'de', ['greeting' => 'Hallo']);
        $this->seedOverlay($module, 'de', ['greeting' => 'Hallo']);
        $this->seedShippedModule($module, 'fr', ['greeting' => 'Bonjour']);
        $this->seedOverlay($module, 'fr', ['greeting' => 'Bonjour']);
        $this->registerDirectoryManager($directory);
        $this->setGlobalVariable('ilDB', $this->createDatabaseStub());

        $plugin_language = new ilPluginLanguage($this->createPluginInfo('ptest', ['de', 'fr']));
        $plugin_language->uninstall();

        $this->assertFileDoesNotExist($this->overlayPath($module, 'de', 'mo'));
        $this->assertFileDoesNotExist($this->overlayPath($module, 'de', 'po'));
        $this->assertFileDoesNotExist($this->overlayPath($module, 'fr', 'mo'));
        $this->assertFileDoesNotExist($this->overlayPath($module, 'fr', 'po'));
        $this->assertFileExists($this->shippedPath($module, 'de', 'po'));
        $this->assertFileExists($this->shippedPath($module, 'fr', 'po'));
    }

    public function testUninstallDoesNotTouchAnotherModulesOverlay(): void
    {
        $module = 'comp_slot_ptest';
        $other_module = 'comp_slot_other';
        $directory = $this->seedShippedModule($module, 'de', ['greeting' => 'Hallo']);
        $this->seedOverlay($module, 'de', ['greeting' => 'Hallo']);
        $other_directory = $this->seedShippedModule($other_module, 'de', ['greeting' => 'Hallo']);
        $this->seedOverlay($other_module, 'de', ['greeting' => 'Hallo']);
        $this->registerDirectoryManager($directory, $other_directory);
        $this->setGlobalVariable('ilDB', $this->createDatabaseStub());

        $plugin_language = new ilPluginLanguage($this->createPluginInfo('ptest', ['de']));
        $plugin_language->uninstall();

        $this->assertFileDoesNotExist($this->overlayPath($module, 'de', 'mo'));
        $this->assertFileExists($this->overlayPath($other_module, 'de', 'mo'));
        $this->assertFileExists($this->overlayPath($other_module, 'de', 'po'));
    }

    public function testIsANoOpWhenNoDirectoryManagerIsRegisteredAtAll(): void
    {
        $this->setGlobalVariable('ilDB', $this->createDatabaseStub());

        $plugin_language = new ilPluginLanguage($this->createPluginInfo('ptest', ['de']));
        $plugin_language->uninstall();

        $this->addToAssertionCount(1);
    }

    public function testIsANoOpWhenTheModuleHasNoOverlayForALanguageItShips(): void
    {
        $module = 'comp_slot_ptest';
        $directory = $this->seedShippedModule($module, 'de', ['greeting' => 'Hallo']);
        $this->seedOverlay($module, 'de', ['greeting' => 'Hallo']);
        $this->registerDirectoryManager($directory);
        $this->setGlobalVariable('ilDB', $this->createDatabaseStub());

        $plugin_language = new ilPluginLanguage($this->createPluginInfo('ptest', ['de', 'es']));
        $plugin_language->uninstall();

        $this->assertFileDoesNotExist($this->overlayPath($module, 'de', 'mo'));
    }

    /** A prefix that cannot be a module name (plugin id with a hyphen) is never looked up: a warning is logged and a validly prefixed module's overlay survives. */
    public function testUninstallLogsAndSkipsWhenThePrefixIsNotAValidModuleName(): void
    {
        $other_module = 'comp_slot_other';
        $other_directory = $this->seedShippedModule($other_module, 'de', ['greeting' => 'Hallo']);
        $this->seedOverlay($other_module, 'de', ['greeting' => 'Hallo']);
        $this->registerDirectoryManager($other_directory);
        $this->setGlobalVariable('ilDB', $this->createDatabaseStub());
        $logger = $this->createMock(ilLogger::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('comp_slot_p-test'));
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);

        $plugin_language = new ilPluginLanguage($this->createPluginInfo('p-test', ['de']));
        $plugin_language->uninstall();

        $this->assertFileExists($this->overlayPath($other_module, 'de', 'mo'));
        $this->assertFileExists($this->overlayPath($other_module, 'de', 'po'));
    }

    /** Without a resolvable CLIENT_DATA_DIR the overlay cleanup must no-op, never touch the shipped `.po`; own process, since another test defines it. */
    #[RunInSeparateProcess]
    public function testIsANoOpWhenClientDataDirIsNotResolvable(): void
    {
        $this->assertFalse(defined('CLIENT_DATA_DIR'));

        $module = 'comp_slot_ptest';
        $directory = $this->seedShippedModule($module, 'de', ['greeting' => 'Hallo']);
        $shipped_po_before = file_get_contents($this->shippedPath($module, 'de', 'po'));
        $this->registerDirectoryManager($directory);
        $this->setGlobalVariable('ilDB', $this->createDatabaseStub());

        $plugin_language = new ilPluginLanguage($this->createPluginInfo('ptest', ['de']));
        $plugin_language->uninstall();

        $this->assertSame($shipped_po_before, file_get_contents($this->shippedPath($module, 'de', 'po')));
    }

    /** A failure removing one language's overlay is logged via $DIC->logger()->forComponent('lang')->warning() and swallowed; the other languages are still processed. */
    public function testLogsAndSwallowsARemovalFailureForOneLanguageWithoutAbortingOthers(): void
    {
        if (posix_getuid() === 0) {
            $this->markTestSkipped('Cannot force an unremovable file while running as root; skipping.');
        }

        // both languages share one directory, so both fail: the loop continuing is proven by both being attempted and logged
        $module = 'comp_slot_ptest';
        $this->ensureClientDataDirDefined();
        $this->fixture_directory ??= __DIR__ . '/tmp-plugin-uninstall-fixtures-' . bin2hex(random_bytes(4));
        $shipped_readonly_dir = $this->fixture_directory . '/readonly';
        mkdir($shipped_readonly_dir, 0775, true);

        $relative_readonly_path = 'components/ILIAS/Language/tests/' . basename($this->fixture_directory) . '/readonly/';
        $overlay_readonly_dir = rtrim(CLIENT_DATA_DIR, '/') . '/lang/' . $module;

        foreach (['de' => 'Hallo', 'fr' => 'Bonjour'] as $lang_key => $value) {
            $translations = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog();
            $translations->add(MigratedPoFixture::entry($module, 'greeting', $value));
            MigratedPoFixture::writePo("$shipped_readonly_dir/{$module}_{$lang_key}.po", $translations);
            MigratedPoFixture::writePair("$overlay_readonly_dir/$lang_key/{$module}_{$lang_key}", $translations);
        }

        $directory = new class ($module, $relative_readonly_path) implements LanguageFileDirectory {
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
        $this->registerDirectoryManager($directory);
        $this->setGlobalVariable('ilDB', $this->createDatabaseStub());

        chmod("$overlay_readonly_dir/de", 0555);
        chmod("$overlay_readonly_dir/fr", 0555);

        $warnings = [];
        $logger = $this->createStub(ilLogger::class);
        $logger->method('warning')->willReturnCallback(function (string $message) use (&$warnings): void {
            $warnings[] = $message;
        });
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturnMap([
            ['lang', $logger],
        ]);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);

        set_error_handler(static fn(): bool => true, E_WARNING);
        try {
            $plugin_language = new ilPluginLanguage($this->createPluginInfo('ptest', ['de', 'fr']));
            $plugin_language->uninstall();
        } finally {
            restore_error_handler();
            chmod("$overlay_readonly_dir/de", 0775);
            chmod("$overlay_readonly_dir/fr", 0775);
        }

        // each failed language is logged individually, preceded by the warning that its lock file could not be created
        $lock_warnings = array_values(array_filter($warnings, static fn(string $w): bool => str_contains($w, 'lock file')));
        $warnings = array_values(array_filter($warnings, static fn(string $w): bool => !str_contains($w, 'lock file')));
        $this->assertCount(2, $lock_warnings);
        $this->assertCount(2, $warnings);
        $logged_languages = array_map(
            static fn(string $w): string => preg_match('/language "([a-z]{2})"/', $w, $m) === 1 ? $m[1] : '',
            $warnings
        );
        sort($logged_languages);
        $this->assertSame(['de', 'fr'], $logged_languages);
        $this->assertFileExists(MigratedPoFixture::overlayMo("$overlay_readonly_dir/de/{$module}_de"));
        $this->assertFileExists(MigratedPoFixture::overlayMo("$overlay_readonly_dir/fr/{$module}_fr"));
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

    /** A CLIENT_DATA_DIR outside sys_get_temp_dir() must not be written into: ensureClientDataDirDefined() skips. */
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
