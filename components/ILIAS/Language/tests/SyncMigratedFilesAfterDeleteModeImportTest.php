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

/**
 * ilObjLanguageExt::syncMigratedFilesAfterDeleteModeImport(): the "delete" mode wipes lng_data/lng_modules of the whole
 * language up front, so _saveValues() finds no lng_modules row for any module and never reaches replaceLangModule().
 * This method independently syncs the overlay of every module the language had lng_data rows for, unioned with the modules
 * in $to_save, each with exactly its entries from $to_save (an empty map for an unmentioned module).
 *
 * Tests the private method via reflection: through importLanguageFile() it would need the global language file and many
 * unrelated raw queries. Syncs target the overlay under CLIENT_DATA_DIR, never the shipped `.po`.
 *
 * Separate processes, because CLIENT_DATA_DIR cannot be redefined and an earlier test class of a full-suite run
 * may already have defined it as /var/iliasdata, which would make every test skip.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class SyncMigratedFilesAfterDeleteModeImportTest extends ilLanguageBaseTestCase
{
    private ?string $fixture_directory = null;
    /** True if setUp() defined CLIENT_DATA_DIR itself; tearDown() removes only such a root. */
    private bool $created_client_data_dir_root = false;

    /**
     * The method evaluates ILIAS_ABSOLUTE_PATH and CLIENT_DATA_DIR before sync() could no-op, so every test needs both
     * defined, also without seedFixtureModule() (executionOrder="random" would otherwise make it order-dependent).
     * An already defined CLIENT_DATA_DIR is reused: tearDown() removes only this class' own subdirectories.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        // these tests simulate a foreign, pre-existing CLIENT_DATA_DIR and manage the constant themselves
        if (in_array($this->name(), [
            'testTearDownNeverDeletesAClientDataDirThisClassDidNotCreateItself',
            'testRefusesToWriteIntoAClientDataDirOutsideSysTempDir',
        ], true)) {
            return;
        }
        $this->guardClientDataDirIsTestOwned();
        if (!defined('CLIENT_DATA_DIR')) {
            define('CLIENT_DATA_DIR', sys_get_temp_dir() . '/ilias_sync_delete_import_test_' . bin2hex(random_bytes(4)));
            $this->created_client_data_dir_root = true;
        }
    }

    /** Writing fixture files into a foreign CLIENT_DATA_DIR (e.g. the real /var/iliasdata) must be refused, not only its deletion. */
    private function guardClientDataDirIsTestOwned(): void
    {
        if (defined('CLIENT_DATA_DIR') && !str_starts_with(CLIENT_DATA_DIR, sys_get_temp_dir() . '/')) {
            $this->markTestSkipped(
                'CLIENT_DATA_DIR ("' . CLIENT_DATA_DIR . '") is not a test-owned temp directory - '
                . 'refusing to write fixture files there.'
            );
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->fixture_directory) && is_dir($this->fixture_directory)) {
            array_map('unlink', glob($this->fixture_directory . '/*') ?: []);
            rmdir($this->fixture_directory);
        }

        // delete only a CLIENT_DATA_DIR this test created: it once wiped the real /var/iliasdata in a full-suite run
        foreach ($this->overlayFixtureDirectories() as $overlay_fixture_dir) {
            if (is_dir($overlay_fixture_dir)) {
                $this->removeDirectoryRecursively($overlay_fixture_dir);
            }
        }
        if ($this->created_client_data_dir_root && defined('CLIENT_DATA_DIR') && is_dir(CLIENT_DATA_DIR)) {
            $this->removeDirectoryRecursively(CLIENT_DATA_DIR);
        }

        parent::tearDown();
    }

    /**
     * The overlay directories this test's fixtures wrote to; empty if none were seeded.
     *
     * @return list<string>
     */
    private function overlayFixtureDirectories(): array
    {
        if (!isset($this->fixture_directory) || !defined('CLIENT_DATA_DIR')) {
            return [];
        }

        return array_map(
            static fn(string $module): string => rtrim(CLIENT_DATA_DIR, '/') . '/lang/' . $module,
            array_values($this->seeded_modules)
        );
    }

    /**
     * The modules seedFixtureModule() seeded; their overlays (`lang/<module>/`) are this test's own.
     *
     * @var array<string, string>
     */
    private array $seeded_modules = [];

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
     * Seeds only the shipped `.po` of a throwaway module (no shipped `.mo` exists).
     *
     * @param array<string, string> $entries identifier => value
     */
    private function seedFixtureModule(string $module, string $lang_key, array $entries): LanguageFileDirectory
    {
        $this->seeded_modules[$module] = $module;
        $this->fixture_directory ??= __DIR__ . '/tmp-clearmigrated-fixtures-' . bin2hex(random_bytes(4));
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

    /** Bootstraps the overlay `.po`+`.mo` from the shipped `.po`, simulating an installed language (sync() would create a missing one too). */
    private function bootstrapOverlayFromShipped(string $module, string $lang_key): void
    {
        $overlay_dir = $this->overlayDirectory($module, $lang_key);
        if (!is_dir($overlay_dir)) {
            mkdir($overlay_dir, 0775, true);
        }
        copy(
            $this->fixture_directory . '/' . $module . '_' . $lang_key . '.po',
            $overlay_dir . $module . '_' . $lang_key . '.po'
        );
        MigratedPoFixture::writeOverlayRevision($overlay_dir . $module . '_' . $lang_key, MigratedPoFixture::readPo($this->fixture_directory . '/' . $module . '_' . $lang_key . '.po'));
    }

    private function overlayDirectory(string $module = 'dtest', string $lang_key = 'de'): string
    {
        return rtrim(CLIENT_DATA_DIR, '/') . '/lang/' . $module . '/' . $lang_key . '/';
    }

    private function registerDirectoryManager(LanguageFileDirectory ...$contributed): void
    {
        $this->setGlobalVariable(
            LanguageFileDirectoryManager::class,
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), ...$contributed)
        );
    }

    private function loadFixturePo(string $module, string $lang_key): \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog
    {
        return MigratedPoFixture::readPo($this->fixture_directory . '/' . $module . '_' . $lang_key . '.po');
    }

    private function loadOverlayPo(string $module, string $lang_key): \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog
    {
        return MigratedPoFixture::readPo($this->overlayDirectory($module, $lang_key) . $module . '_' . $lang_key . '.po');
    }

    private function overlayMoExists(string $module, string $lang_key): bool
    {
        return is_file(MigratedPoFixture::overlayMo($this->overlayDirectory($module, $lang_key) . $module . '_' . $lang_key));
    }

    /**
     * @param array<string, string> $to_save module.separator.topic => value, the shape importLanguageFile() passes to _saveValues()
     */
    private function invoke(array $modules_before_delete, array $to_save): void
    {
        $instance = (new ReflectionClass(ilObjLanguageExt::class))->newInstanceWithoutConstructor();
        $instance->key = 'de';
        $instance->separator = '#:#';

        $method = new ReflectionMethod(ilObjLanguageExt::class, 'syncMigratedFilesAfterDeleteModeImport');
        $method->invoke($instance, $modules_before_delete, $to_save);
    }

    /** A module that existed before the wipe but is absent from $to_save is synced with an empty map; the shipped `.po` stays untouched. */
    public function testSyncsAModuleMissingFromTheImportWithAnEmptyMap(): void
    {
        $directory = $this->seedFixtureModule('dtest', 'de', ['greeting' => 'Hallo']);
        $this->bootstrapOverlayFromShipped('dtest', 'de');
        $this->registerDirectoryManager($directory);

        $this->invoke(['dtest'], []);

        $this->assertFileDoesNotExist($this->overlayDirectory() . 'dtest_de.po');
        $this->assertNotNull(
            $this->loadFixturePo('dtest', 'de')->find('dtest', 'greeting'),
            'the shipped .po must never be touched by this sync path'
        );
    }

    /** A module both existing before and present in $to_save is synced with exactly its subset, into the overlay. */
    public function testSyncsAModulePresentInTheImportWithItsToSaveSubset(): void
    {
        $directory = $this->seedFixtureModule('dtest', 'de', ['greeting' => 'Hallo']);
        $this->bootstrapOverlayFromShipped('dtest', 'de');
        $this->registerDirectoryManager($directory);

        $this->invoke(['dtest'], ['dtest#:#greeting' => 'Hallo, neu']);

        $translation = $this->loadOverlayPo('dtest', 'de')->find(null, 'greeting');
        $this->assertNotNull($translation);
        $this->assertSame('Hallo, neu', $translation->getTranslation());

        $shipped_translation = $this->loadFixturePo('dtest', 'de')->find('dtest', 'greeting');
        $this->assertNotNull($shipped_translation);
        $this->assertSame('Hallo', $shipped_translation->getTranslation(), 'the shipped .po must never be touched');
    }

    public function testDoesNotThrowForANonMigratedModuleMissingFromTheImport(): void
    {
        $this->registerDirectoryManager();

        $this->invoke(['plain_module'], []);

        $this->addToAssertionCount(1);
    }

    public function testIsANoOpWhenNoDirectoryManagerIsRegisteredAtAll(): void
    {
        $this->invoke(['dtest'], []);

        $this->addToAssertionCount(1);
    }

    /** A key without separator must not be attributed to a module: pins explode() yielding exactly two parts. */
    public function testAToSaveKeyWithoutASeparatorDoesNotAccidentallyAttachToAnyModule(): void
    {
        $directory = $this->seedFixtureModule('dtest', 'de', ['greeting' => 'Hallo']);
        $this->bootstrapOverlayFromShipped('dtest', 'de');
        $this->registerDirectoryManager($directory);

        // "dtest" is only reached via $modules_before_delete, so it is synced with an empty map
        $this->invoke(['dtest'], ['not_a_valid_key' => 'value']);

        $this->assertFileDoesNotExist($this->overlayDirectory() . 'dtest_de.po');
    }

    /** Of several modules existing before only one is in $to_save: the missing one is emptied, the present one updated. */
    public function testSyncsEachOfSeveralModulesWithItsOwnCorrectEntries(): void
    {
        $missing = $this->seedFixtureModule('dtest', 'de', ['greeting' => 'Hallo']);
        $this->bootstrapOverlayFromShipped('dtest', 'de');
        $kept = $this->seedFixtureModule('ktest', 'de', ['greeting' => 'Hallo']);
        $this->bootstrapOverlayFromShipped('ktest', 'de');
        $this->registerDirectoryManager($missing, $kept);

        $this->invoke(['dtest', 'ktest'], ['ktest#:#greeting' => 'Hallo, neu']);

        $this->assertFileDoesNotExist($this->overlayDirectory() . 'dtest_de.po');
        $updated = $this->loadOverlayPo('ktest', 'de')->find(null, 'greeting');
        $this->assertNotNull($updated);
        $this->assertSame('Hallo, neu', $updated->getTranslation());
    }

    /** An import into a language without overlay creates it, seeded with the shipped originals. */
    public function testCreatesAMissingOverlayWithTheShippedOriginal(): void
    {
        $directory = $this->seedFixtureModule('dtest', 'de', ['greeting' => 'Hallo']);
        $this->registerDirectoryManager($directory);

        $this->invoke(['dtest'], ['dtest#:#greeting' => 'Hallo, neu']);

        $this->assertTrue($this->overlayMoExists('dtest', 'de'));
        $greeting = $this->loadOverlayPo('dtest', 'de')->find(null, 'greeting');
        $this->assertSame('Hallo, neu', $greeting->getTranslation());
        $this->assertSame('Hallo', \ILIAS\Language\ComponentTranslation\LocalChangeComments::getOriginal($greeting));
    }

    /**
     * tearDown() once deleted the whole CLIENT_DATA_DIR if it was is_dir(), wiping the real /var/iliasdata in a full-suite
     * run: a foreign, pre-existing definition must stay untouched. Own process so no other test defines it first;
     * setUp() skips auto-defining it for this test by name.
     */
    #[RunInSeparateProcess]
    public function testTearDownNeverDeletesAClientDataDirThisClassDidNotCreateItself(): void
    {
        $this->assertFalse(defined('CLIENT_DATA_DIR'));

        $foreign_dir = sys_get_temp_dir() . '/ilias_foreign_client_data_dir_' . bin2hex(random_bytes(4));
        mkdir($foreign_dir, 0775, true);
        file_put_contents($foreign_dir . '/marker.txt', 'do-not-delete');
        // a foreign CLIENT_DATA_DIR this class did not create
        define('CLIENT_DATA_DIR', $foreign_dir);

        try {
            $directory = $this->seedFixtureModule('dforeign', 'de', ['greeting' => 'Hallo']);
            $this->bootstrapOverlayFromShipped('dforeign', 'de');
            $this->registerDirectoryManager($directory);
            $this->assertTrue($this->overlayMoExists('dforeign', 'de'));

            // tearDown() runs again after the test; harmless, nothing is left to clean up
            $this->tearDown();

            $this->assertFileExists(
                $foreign_dir . '/marker.txt',
                'tearDown() deleted (part of) a CLIENT_DATA_DIR this test class did not create itself.'
            );
            $this->assertFalse($this->overlayMoExists('dforeign', 'de'));
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

    /** A CLIENT_DATA_DIR outside sys_get_temp_dir() must not be written into: guardClientDataDirIsTestOwned() skips. */
    #[RunInSeparateProcess]
    public function testRefusesToWriteIntoAClientDataDirOutsideSysTempDir(): void
    {
        $foreign_dir = __DIR__ . '/tmp-not-a-temp-dir-' . bin2hex(random_bytes(4));
        $this->assertFalse(str_starts_with($foreign_dir, sys_get_temp_dir() . '/'));
        mkdir($foreign_dir, 0775, true);
        define('CLIENT_DATA_DIR', $foreign_dir);

        try {
            try {
                $this->guardClientDataDirIsTestOwned();
                $this->fail('guardClientDataDirIsTestOwned() must refuse a CLIENT_DATA_DIR outside sys_get_temp_dir().');
            } catch (\PHPUnit\Framework\SkippedWithMessageException $e) {
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
