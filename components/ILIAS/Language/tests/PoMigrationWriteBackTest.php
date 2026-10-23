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
use ILIAS\Language\ComponentTranslation\LocalChangeComments;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\SkippedWithMessageException;

/**
 * ilObjLanguage::replaceLangModule(), through which every language-admin write goes, mirrors a migrated module's
 * .po/.mo files without skipping the lng_modules write (dual-write, so the DB stays a rollback target).
 *
 * Writes go only to the overlay pair under CLIENT_DATA_DIR, never to the shipped pair. Tests seed the shipped pair,
 * bootstrap the overlay from it, assert on the overlay and check the shipped pair stayed byte-identical.
 *
 * Separate processes, because CLIENT_DATA_DIR cannot be redefined and an earlier test class of a full-suite run
 * may already have defined it as /var/iliasdata, which would make every test skip.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class PoMigrationWriteBackTest extends ilLanguageBaseTestCase
{
    private ?string $fixture_directory = null;
    /** True if this test defined CLIENT_DATA_DIR itself; tearDown() removes only such a root. */
    private bool $created_client_data_dir_root = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        // CLIENT_DATA_DIR is deliberately not defined here: bootstrapOverlayFromShipped() defines it, and
        // testRefusesToWriteIntoAClientDataDirOutsideSysTempDir() needs it still undefined.

        MigratedPoFixture::resetRuntime();
    }

    protected function tearDown(): void
    {
        if (isset($this->fixture_directory) && is_dir($this->fixture_directory)) {
            array_map('unlink', glob($this->fixture_directory . '/*') ?: []);
            rmdir($this->fixture_directory);
        }

        $overlay_dir = rtrim(CLIENT_DATA_DIR, '/') . '/lang';
        if (isset($this->fixture_directory) && is_dir($overlay_dir)) {
            MigratedPoFixture::removeDirectory($overlay_dir);
        }
        // never remove a foreign CLIENT_DATA_DIR
        if ($this->created_client_data_dir_root && is_dir(CLIENT_DATA_DIR)) {
            MigratedPoFixture::removeDirectory(CLIENT_DATA_DIR);
        }

        parent::tearDown();
    }

    /**
     * Seeds a .po/.mo pair for a throwaway module at the shipped location; never creates an overlay.
     *
     * @param array<string, array{value: string, fuzzy?: bool, original?: string}> $entries 'original' seeds the
     *   baseline an overlay bootstrapped by sync() would carry (the shipped `.po` never has it); omit it to simulate
     *   a key without baseline, e.g. one added after migration.
     */
    private function seedFixtureModule(string $module, string $lang_key, array $entries): LanguageFileDirectory
    {
        $this->fixture_directory ??= __DIR__ . '/tmp-writeback-fixtures-' . bin2hex(random_bytes(4));
        if (!is_dir($this->fixture_directory)) {
            mkdir($this->fixture_directory, 0775, true);
        }

        $translations = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog();
        foreach ($entries as $identifier => $entry) {
            $translation = MigratedPoFixture::entry($module, $identifier, $entry['value']);
            if ($entry['fuzzy'] ?? false) {
                $translation->addFlag('fuzzy');
            }
            if (isset($entry['original'])) {
                LocalChangeComments::setOriginal($translation, $entry['original']);
            }
            $translations->add($translation);
        }

        MigratedPoFixture::writePo($this->fixture_directory . '/' . $module . '_' . $lang_key . '.po', $translations);

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

    /** Copies the shipped pair into the overlay, simulating an already installed language (an admin edit keeps an existing "original" baseline). */
    private function ensureClientDataDir(): void
    {
        $created = MigratedPoFixture::ensureClientDataDirDefinedOrSkip($this);
        $this->created_client_data_dir_root = $this->created_client_data_dir_root || $created;
    }

    private function bootstrapOverlayFromShipped(string $module, string $lang_key): void
    {
        $this->ensureClientDataDir();
        $overlay_base = $this->overlayBase($module, $lang_key);
        if (!is_dir(dirname($overlay_base))) {
            mkdir(dirname($overlay_base), 0775, true);
        }
        copy($this->fixture_directory . '/' . $module . '_' . $lang_key . '.po', $overlay_base . '.po');
        MigratedPoFixture::writeOverlayRevision($overlay_base, MigratedPoFixture::readPo($overlay_base . '.po'));
    }

    private function overlayBase(string $module, string $lang_key): string
    {
        return rtrim(CLIENT_DATA_DIR, '/') . '/lang/' . $module . '/' . $lang_key . '/' . $module . '_' . $lang_key;
    }

    private function registerDirectoryManager(LanguageFileDirectory ...$contributed): void
    {
        $this->setGlobalVariable(
            LanguageFileDirectoryManager::class,
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), ...$contributed)
        );
    }

    /** replaceLangModule() unserializes what it wrote as a self-check (mantis #20046/#19140): the stub only has to decode to an array. */
    private function stubDatabaseForReplaceLangModule(): void
    {
        $this->stubDatabaseForReplaceLangModuleCapturingInsert();
    }

    /** Like stubDatabaseForReplaceLangModule(), but returns the stub to capture what was inserted into lng_modules. */
    private function stubDatabaseForReplaceLangModuleCapturingInsert(): ilDBInterface&Stub
    {
        $statement = $this->createStub(ilDBStatement::class);
        $db = $this->createStub(ilDBInterface::class);
        $db->method('queryF')->willReturn($statement);
        $db->method('fetchAssoc')->willReturn(['lang_array' => serialize([])]);
        $this->setGlobalVariable('ilDB', $db);

        return $db;
    }

    private function loadFixturePo(string $module, string $lang_key): \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog
    {
        return MigratedPoFixture::readPo($this->fixture_directory . '/' . $module . '_' . $lang_key . '.po');
    }

    private function loadOverlayPo(string $module, string $lang_key): \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog
    {
        return MigratedPoFixture::readPo($this->overlayBase($module, $lang_key) . '.po');
    }

    public function testUpdatesAnExistingEntryAndClearsItsFuzzyFlag(): void
    {
        $this->stubDatabaseForReplaceLangModule();
        $directory = $this->seedFixtureModule('wtest', 'de', [
            'greeting' => ['value' => 'Hallo', 'fuzzy' => true],
        ]);
        $this->bootstrapOverlayFromShipped('wtest', 'de');
        $this->registerDirectoryManager($directory);

        ilObjLanguage::replaceLangModule('de', 'wtest', ['greeting' => 'Hallo, geändert']);

        $translation = $this->loadOverlayPo('wtest', 'de')->find(null, 'greeting');
        $this->assertNotNull($translation);
        $this->assertSame('Hallo, geändert', $translation->getTranslation());
        $this->assertFalse($translation->hasFlag('fuzzy'));

        $shipped = $this->loadFixturePo('wtest', 'de')->find('wtest', 'greeting');
        $this->assertSame('Hallo', $shipped->getTranslation());
        $this->assertTrue($shipped->hasFlag('fuzzy'));
    }

    /**
     * A value unchanged from the shipped one, even with disallowed markup, is sanitised only for the DB mirror: sync() must
     * see the raw value, else it finds a fake delta against the raw shipped `.po` and writes an overlay.
     */
    public function testAnUnmodifiedShippedValueWithDisallowedMarkupIsSanitisedInTheDatabaseButNotInTheOverlay(): void
    {
        $bad = '<script>alert(1)</script>Hallo';
        $db = $this->stubDatabaseForReplaceLangModuleCapturingInsert();
        $captured_lang_array = null;
        $db->method('insert')->willReturnCallback(
            function (string $table, array $values) use (&$captured_lang_array): int {
                if ($table === 'lng_modules') {
                    $captured_lang_array = unserialize($values['lang_array'][1] ?? '', ['allowed_classes' => false]);
                }
                return 1;
            }
        );
        $directory = $this->seedFixtureModule('wtest', 'de', ['greeting' => ['value' => $bad]]);
        $this->ensureClientDataDir();
        $this->registerDirectoryManager($directory);

        ilObjLanguage::replaceLangModule('de', 'wtest', ['greeting' => $bad]);

        $this->assertStringNotContainsString('<script', $captured_lang_array['greeting'] ?? '');
        $this->assertStringContainsString('Hallo', $captured_lang_array['greeting'] ?? '');
        $this->assertFileDoesNotExist(
            $this->overlayBase('wtest', 'de') . '.po',
            'no fake local change may appear in the overlay merely because the DB mirror is sanitised'
        );
        $this->assertFileDoesNotExist(MigratedPoFixture::overlayMo($this->overlayBase('wtest', 'de')));
        $this->assertSame($bad, $this->loadFixturePo('wtest', 'de')->find('wtest', 'greeting')->getTranslation());
    }

    public function testAddsANewEntryThatDidNotExistInTheFileBefore(): void
    {
        $this->stubDatabaseForReplaceLangModule();
        $directory = $this->seedFixtureModule('wtest', 'de', [
            'greeting' => ['value' => 'Hallo'],
        ]);
        $this->bootstrapOverlayFromShipped('wtest', 'de');
        $this->registerDirectoryManager($directory);

        ilObjLanguage::replaceLangModule('de', 'wtest', [
            'greeting' => 'Hallo',
            'farewell' => 'Tschüss',
        ]);

        $translation = $this->loadOverlayPo('wtest', 'de')->find(null, 'farewell');
        $this->assertNotNull($translation);
        $this->assertSame('Tschüss', $translation->getTranslation());

        $this->assertNull($this->loadFixturePo('wtest', 'de')->find('wtest', 'farewell'));
    }

    public function testRemovesAnEntryThatIsNoLongerInTheReplacedArray(): void
    {
        $this->stubDatabaseForReplaceLangModule();
        $directory = $this->seedFixtureModule('wtest', 'de', [
            'greeting' => ['value' => 'Hallo'],
            'farewell' => ['value' => 'Tschüss'],
        ]);
        $this->bootstrapOverlayFromShipped('wtest', 'de');
        $this->registerDirectoryManager($directory);

        // "farewell" is deleted before replaceLangModule() rebuilds the row, like _deleteValues(); "greeting" is changed so the overlay has content
        ilObjLanguage::replaceLangModule('de', 'wtest', ['greeting' => 'Servus']);

        $this->assertNull($this->loadOverlayPo('wtest', 'de')->find(null, 'farewell'));
        $this->assertNotNull($this->loadOverlayPo('wtest', 'de')->find(null, 'greeting'));

        $this->assertNotNull($this->loadFixturePo('wtest', 'de')->find('wtest', 'farewell'));
        $this->assertNotNull($this->loadFixturePo('wtest', 'de')->find('wtest', 'greeting'));
    }

    /** An admin edit recompiles a missing overlay `.mo`: the overlay is always maintained for an installed language. */
    public function testRecompilesAMissingOverlayMoOnTheNextEdit(): void
    {
        $this->stubDatabaseForReplaceLangModule();
        $directory = $this->seedFixtureModule('wtest', 'de', ['greeting' => ['value' => 'Hallo']]);
        $this->bootstrapOverlayFromShipped('wtest', 'de');
        $this->registerDirectoryManager($directory);
        unlink(MigratedPoFixture::overlayMo($this->overlayBase('wtest', 'de')));

        ilObjLanguage::replaceLangModule('de', 'wtest', ['greeting' => 'Hallo, geändert']);

        $this->assertSame(
            ['greeting' => 'Hallo, geändert'],
            MigratedPoFixture::readMo(MigratedPoFixture::overlayMo($this->overlayBase('wtest', 'de')))
        );
        $this->assertSame(
            'Hallo, geändert',
            $this->loadOverlayPo('wtest', 'de')->find(null, 'greeting')->getTranslation()
        );
        $this->assertFileExists($this->fixture_directory . '/wtest_de.po');
        $this->assertSame(
            'Hallo',
            $this->loadFixturePo('wtest', 'de')->find('wtest', 'greeting')->getTranslation()
        );
    }

    public function testIsANoOpWhenTheModuleHasNoContributedDirectory(): void
    {
        $this->ensureClientDataDir();
        $this->stubDatabaseForReplaceLangModule();
        $directory = $this->seedFixtureModule('wtest', 'de', ['greeting' => ['value' => 'Hallo']]);
        $this->registerDirectoryManager($directory);

        ilObjLanguage::replaceLangModule('de', 'other_module', ['x' => 'y']);

        $this->assertSame(
            'Hallo',
            $this->loadFixturePo('wtest', 'de')->find('wtest', 'greeting')->getTranslation()
        );
        $this->assertFileDoesNotExist($this->fixture_directory . '/other_module_de.po');
        $this->assertDirectoryDoesNotExist(rtrim(CLIENT_DATA_DIR, '/') . '/lang/other_module');
    }

    public function testIsANoOpWhenNoDirectoryManagerIsRegisteredAtAll(): void
    {
        $this->ensureClientDataDir();
        $this->stubDatabaseForReplaceLangModule();

        ilObjLanguage::replaceLangModule('de', 'wtest', ['greeting' => 'Hallo']);

        $this->addToAssertionCount(1);
    }

    /** Without invalidateMigratedLanguageFileCache() ilLanguage would serve the value cached before the write; the overlay must exist for the "before" read. */
    public function testInvalidatesIlLanguagesCacheSoTheNewValueIsVisibleImmediately(): void
    {
        $this->stubDatabaseForReplaceLangModule();
        $directory = $this->seedFixtureModule('wtest', 'de', ['greeting' => ['value' => 'Hallo']]);
        $this->bootstrapOverlayFromShipped('wtest', 'de');
        $this->registerDirectoryManager($directory);
        MigratedPoFixture::build($GLOBALS['DIC'][LanguageFileDirectoryManager::class], ILIAS_ABSOLUTE_PATH);

        $before = (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor();
        (new ReflectionObject($before))->getProperty('lang_key')->setValue($before, 'de');
        $before->loadLanguageModule('wtest');
        $this->assertSame('Hallo', $before->txt('greeting'));

        ilObjLanguage::replaceLangModule('de', 'wtest', ['greeting' => 'Hallo, geändert']);

        $after = (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor();
        (new ReflectionObject($after))->getProperty('lang_key')->setValue($after, 'de');
        $after->loadLanguageModule('wtest');
        $this->assertSame('Hallo, geändert', $after->txt('greeting'));
    }

    /** Editing an entry away from its shipped "original" leaves a local_change timestamp in the overlay. */
    public function testWritingADifferentValueThanTheOriginalSetsALocalChangeTimestamp(): void
    {
        $this->stubDatabaseForReplaceLangModule();
        $directory = $this->seedFixtureModule('wtest', 'de', [
            'greeting' => ['value' => 'Hallo', 'original' => 'Hallo'],
        ]);
        $this->bootstrapOverlayFromShipped('wtest', 'de');
        $this->registerDirectoryManager($directory);

        ilObjLanguage::replaceLangModule('de', 'wtest', ['greeting' => 'Hallo, geändert']);

        $translation = $this->loadOverlayPo('wtest', 'de')->find(null, 'greeting');
        $this->assertNotNull($translation);
        $this->assertSame('Hallo', LocalChangeComments::getOriginal($translation));
        $this->assertNotNull(LocalChangeComments::getLocalChange($translation));

        $this->assertSame(
            'Hallo',
            $this->loadFixturePo('wtest', 'de')->find('wtest', 'greeting')->getTranslation()
        );
    }

    /** Writing the shipped value back clears the local_change marker, the equivalent of "reset to default". */
    public function testWritingBackTheOriginalValueClearsAnExistingLocalChangeTimestamp(): void
    {
        $this->stubDatabaseForReplaceLangModule();
        $directory = $this->seedFixtureModule('wtest', 'de', [
            'greeting' => ['value' => 'Hallo'],
        ]);
        $this->bootstrapOverlayFromShipped('wtest', 'de');
        $this->registerDirectoryManager($directory);

        ilObjLanguage::replaceLangModule('de', 'wtest', ['greeting' => 'Hallo, geändert']);
        $this->assertNotNull(LocalChangeComments::getLocalChange(
            $this->loadOverlayPo('wtest', 'de')->find(null, 'greeting')
        ));

        ilObjLanguage::replaceLangModule('de', 'wtest', ['greeting' => 'Hallo']);

        $this->assertFileDoesNotExist(rtrim(CLIENT_DATA_DIR, '/') . '/lang/wtest/de/wtest_de.po');
    }

    /** A key without "original" (e.g. added after migration) always counts as locally changed. */
    public function testANewEntryWithNoOriginalCommentIsAlwaysMarkedAsLocallyChanged(): void
    {
        $this->stubDatabaseForReplaceLangModule();
        $directory = $this->seedFixtureModule('wtest', 'de', [
            'greeting' => ['value' => 'Hallo', 'original' => 'Hallo'],
        ]);
        $this->bootstrapOverlayFromShipped('wtest', 'de');
        $this->registerDirectoryManager($directory);

        ilObjLanguage::replaceLangModule('de', 'wtest', [
            'greeting' => 'Hallo',
            'farewell' => 'Tschüss',
        ]);

        $translation = $this->loadOverlayPo('wtest', 'de')->find(null, 'farewell');
        $this->assertNotNull($translation);
        $this->assertNull(LocalChangeComments::getOriginal($translation));
        $this->assertNotNull(LocalChangeComments::getLocalChange($translation));
    }

    /** Must skip instead of writing into a foreign CLIENT_DATA_DIR (e.g. /var/iliasdata); needs its own process so setUp() has not defined it. */
    #[RunInSeparateProcess]
    public function testRefusesToWriteIntoAClientDataDirOutsideSysTempDir(): void
    {
        $foreign_dir = __DIR__ . '/tmp-not-a-temp-dir-' . bin2hex(random_bytes(4));
        $this->assertFalse(str_starts_with($foreign_dir, sys_get_temp_dir() . '/'));
        mkdir($foreign_dir, 0775, true);
        define('CLIENT_DATA_DIR', $foreign_dir);

        try {
            $this->seedFixtureModule('wtest', 'de', ['greeting' => ['value' => 'Hallo']]);
            try {
                $this->bootstrapOverlayFromShipped('wtest', 'de');
                $this->fail('bootstrapOverlayFromShipped() must refuse to write into a CLIENT_DATA_DIR outside sys_get_temp_dir().');
            } catch (SkippedWithMessageException $e) {
                $this->assertStringContainsString($foreign_dir, $e->getMessage());
            }

            $this->assertSame([], array_diff(scandir($foreign_dir) ?: [], ['.', '..']));
        } finally {
            if (is_dir($foreign_dir)) {
                array_map('unlink', glob($foreign_dir . '/*') ?: []);
                rmdir($foreign_dir);
            }
        }
    }
}
