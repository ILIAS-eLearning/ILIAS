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

use ILIAS\Language\ComponentTranslation\ComponentLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\SkippedWithMessageException;

/**
 * ilLanguage::loadLanguageModule() reads a module whose component contributes a LanguageFileDirectory from the build
 * instead of lng_modules; everything else falls through unchanged. Uses the real contributed 'tos' directory, so it
 * also guards that contribution.
 *
 * Separate processes, because CLIENT_DATA_DIR cannot be redefined and an earlier test class of a full-suite run
 * may already have defined it as /var/iliasdata, which would make every test skip.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class PoMigrationLoadLanguageModuleTest extends ilLanguageBaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        // CLIENT_DATA_DIR is deliberately not defined here: ensureRealTosDeMoFileExists()/contributeFixtureModule()
        // define it, and testRefusesToWriteIntoAClientDataDirOutsideSysTempDir() needs it still undefined.

        // the migrated-file cache is static and shared by all tests of a PHPUnit process: reset it so no result leaks between tests
        MigratedPoFixture::resetRuntime();
    }

    protected function tearDown(): void
    {
        if (isset($this->fixture_directory) && is_dir($this->fixture_directory)) {
            array_map('unlink', glob($this->fixture_directory . '/*') ?: []);
            rmdir($this->fixture_directory);
        }
        if (isset($this->fixture_directory)) {
            MigratedPoFixture::removeShippedDirectory('components/ILIAS/Language/tests/' . basename($this->fixture_directory));
        }

        // remove only a .mo this test compiled, never an existing one (e.g. a local installation of 'de')
        if ($this->created_real_tos_mo !== null && is_file($this->created_real_tos_mo)) {
            unlink($this->created_real_tos_mo);
            $overlay_dir = dirname($this->created_real_tos_mo);
            if (is_dir($overlay_dir) && glob($overlay_dir . '/*') === []) {
                rmdir($overlay_dir);
            }
        }
        $this->created_real_tos_mo = null;

        // never remove a foreign CLIENT_DATA_DIR
        if ($this->created_client_data_dir_root && defined('CLIENT_DATA_DIR') && is_dir(CLIENT_DATA_DIR)) {
            MigratedPoFixture::removeDirectory(CLIENT_DATA_DIR);
        }

        parent::tearDown();
    }

    private ?string $fixture_directory = null;
    private ?string $created_real_tos_mo = null;
    /** True if this test defined CLIENT_DATA_DIR itself; tearDown() removes only such a root. */
    private bool $created_client_data_dir_root = false;

    /**
     * Compiles tos_de.mo at the overlay location under CLIENT_DATA_DIR, as an installation of 'de' would (only .pot/.po
     * are shipped). Cleaned up in tearDown().
     */
    private function ensureRealTosDeMoFileExists(): void
    {
        $created = MigratedPoFixture::ensureClientDataDirDefinedOrSkip($this);
        $this->created_client_data_dir_root = $this->created_client_data_dir_root || $created;
    }

    /** A migrated module with test-local .mo content (not tos's real keys), built at the overlay location. Cleaned up in tearDown(). */
    private function contributeFixtureModule(string $module, \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog $translations): LanguageFileDirectory
    {
        $created = MigratedPoFixture::ensureClientDataDirDefinedOrSkip($this);
        $this->created_client_data_dir_root = $this->created_client_data_dir_root || $created;
        $this->fixture_directory ??= __DIR__ . '/tmp-fixtures-' . bin2hex(random_bytes(4));

        $relative_path = 'components/ILIAS/Language/tests/' . basename($this->fixture_directory) . '/';
        MigratedPoFixture::writeShippedPo($relative_path, $module, 'de', $translations);

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

    /** contributeFixtureModule() with independent shipped `.po` and overlay `.mo` content, to prove the array_replace() merge. */
    private function contributeFixtureModuleWithDistinctShippedAndOverlay(
        string $module,
        \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog $shipped,
        \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog $overlay
    ): LanguageFileDirectory {
        $created = MigratedPoFixture::ensureClientDataDirDefinedOrSkip($this);
        $this->created_client_data_dir_root = $this->created_client_data_dir_root || $created;
        $this->fixture_directory ??= __DIR__ . '/tmp-fixtures-' . bin2hex(random_bytes(4));

        $relative_path = 'components/ILIAS/Language/tests/' . basename($this->fixture_directory) . '/';
        MigratedPoFixture::writePair(rtrim(CLIENT_DATA_DIR, '/') . '/lang/' . $module . '/de/' . $module . '_de', $overlay);
        MigratedPoFixture::writeShippedPo($relative_path, $module, 'de', $shipped);

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

    /** Overlay-only keys are added, shipped-only keys kept, keys of both won by the overlay. */
    public function testMergesTheOverlayOntoTheShippedStateKeepingKeysUniqueToEitherSide(): void
    {
        $shipped = MigratedPoFixture::catalog('merge', [
            'only_shipped' => 'Nur im Shipped-Stand',
            'both' => 'Shipped-Wert',
        ]);
        $overlay = MigratedPoFixture::catalog('merge', [
            'only_overlay' => 'Nur im Overlay',
            'both' => 'Lokal geändert',
        ]);
        $directory = $this->contributeFixtureModuleWithDistinctShippedAndOverlay('merge', $shipped, $overlay);
        $this->setGlobalVariable('ilDB', $this->createStub(ilDBInterface::class));
        $this->registerDirectoryManager($directory);

        $result = $this->callLoadFromMigratedLanguageFile('merge', 'de');

        $this->assertSame('Nur im Shipped-Stand', $result['only_shipped'] ?? null);
        $this->assertSame('Lokal geändert', $result['both'] ?? null);
        $this->assertSame('Nur im Overlay', $result['only_overlay'] ?? null);
        $this->assertCount(3, $result);
    }

    private function buildLanguageWithoutRunningConstructor(string $lang_key, ?string $lang_default = null): ilLanguage
    {
        global $DIC;
        if ($DIC->offsetExists(LanguageFileDirectoryManager::class)) {
            MigratedPoFixture::build($DIC[LanguageFileDirectoryManager::class], (string) ILIAS_ABSOLUTE_PATH);
        }
        $language = (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor();
        $reflected = new ReflectionObject($language);
        $reflected->getProperty('lang_key')->setValue($language, $lang_key);
        $reflected->getProperty('lang_user')->setValue($language, $lang_key);
        $reflected->getProperty('lang_default')->setValue($language, $lang_default ?? $lang_key);

        return $language;
    }

    private function registerDirectoryManager(LanguageFileDirectory ...$contributed): void
    {
        $this->setGlobalVariable(
            LanguageFileDirectoryManager::class,
            new LanguageFileDirectoryManager(
                new CustomizingLanguageFileDirectory(),
                ...$contributed
            )
        );
    }

    /** A match in _lookupEntry() reaches ilLanguage::isUsageLogEnabled(), which reads $DIC->clientIni() and ->database(). */
    private function stubUsageLogDependencies(): void
    {
        $this->setGlobalVariable('ilClientIniFile', $this->createStub(ilIniFile::class));
        $this->setGlobalVariable('ilDB', $this->createStub(ilDBInterface::class));
    }

    /** What the runtime serves for $module/$lang_key, see MigratedPoFixture::servedTexts(). */
    private function callLoadFromMigratedLanguageFile(string $module, string $lang_key): ?array
    {
        return MigratedPoFixture::servedTexts($module, $lang_key);
    }

    public function testFullyIntegratedThroughTheLoadLanguageModuleThatCallersActuallyUse(): void
    {
        $this->ensureRealTosDeMoFileExists();
        $this->setGlobalVariable('ilDB', $this->createStub(ilDBInterface::class));
        $this->registerDirectoryManager(
            new ComponentLanguageFileDirectory(new \ILIAS\TermsOfService(), 'tos')
        );

        $language = $this->buildLanguageWithoutRunningConstructor('de');
        $language->loadLanguageModule('tos');

        $this->assertContains('tos', $language->loaded_modules);
        $this->assertSame('Nutzungsvereinbarung', $language->txt('tos_agreement'));
        $this->assertSame(
            'Nutzungsvereinbarung zugestimmt am',
            $language->txt('tos_agree_date')
        );
    }

    public function testReadsAllSeventeenKnownTosKeysForTheReferenceLanguage(): void
    {
        $this->ensureRealTosDeMoFileExists();
        $this->registerDirectoryManager(
            new ComponentLanguageFileDirectory(new \ILIAS\TermsOfService(), 'tos')
        );

        $language = $this->buildLanguageWithoutRunningConstructor('de');
        $text = $this->callLoadFromMigratedLanguageFile('tos', 'de');

        $this->assertIsArray($text);
        $this->assertCount(17, $text);
        $this->assertArrayHasKey('tos_withdrawal_usr_deletion_desc', $text);
    }

    public function testAShippedModuleWithoutABuildHasNoIdentifiersAndIsReported(): void
    {
        $this->registerDirectoryManager(
            new ComponentLanguageFileDirectory(new \ILIAS\TermsOfService(), 'tos')
        );

        // a shipped module (lang/poll.pot) without a build for it: no identifiers, never the database
        $this->expectOutputRegex('/Module "poll" ships a language file for "de" that is not built/');
        $this->assertSame([], $this->callLoadFromMigratedLanguageFile('poll', 'de'));
    }

    /** "Migrated for a language" means a shipped `.po` exists: its absence yields no identifiers, not a missing overlay `.mo`. */
    public function testNoIdentifiersAndNoReportWhenNoShippedPoExistsForThisLanguage(): void
    {
        $this->registerDirectoryManager(
            new ComponentLanguageFileDirectory(new \ILIAS\TermsOfService(), 'tos')
        );

        $this->assertSame([], $this->callLoadFromMigratedLanguageFile('tos', 'xx'));
    }

    public function testAShippedModuleIsRecognisedByItsTemplateWithoutADirectoryManager(): void
    {
        // tos is recognised by its shipped template without the component graph; there is no build
        $this->expectOutputRegex('/There is no build of the language files/');
        $this->assertSame([], $this->callLoadFromMigratedLanguageFile('tos', 'de'));
    }

    /**
     * An invalid $lang_key (path traversal included) makes MigratedLanguageFilePaths throw \InvalidArgumentException,
     * which is caught: it is never migrated. It arrives untrusted via the public _lookupEntry().
     *
     * @param non-empty-string $invalid_lang_key
     */
    #[DataProvider('invalidLangKeys')]
    public function testAnInvalidLangKeyYieldsNoIdentifiersInsteadOfThrowing(string $invalid_lang_key): void
    {
        $this->registerDirectoryManager(
            new ComponentLanguageFileDirectory(new \ILIAS\TermsOfService(), 'tos')
        );

        $this->assertSame([], $this->callLoadFromMigratedLanguageFile('tos', $invalid_lang_key));
    }

    /**
     * _lookupEntry() must not throw for an invalid $a_lang_key and must not read lng_data for a shipped module.
     *
     * @param non-empty-string $invalid_lang_key
     */
    #[DataProvider('invalidLangKeys')]
    public function testLookupEntryNeverReadsTheDatabaseForAnInvalidLangKeyOfAShippedModule(string $invalid_lang_key): void
    {
        $this->registerDirectoryManager(
            new ComponentLanguageFileDirectory(new \ILIAS\TermsOfService(), 'tos')
        );
        $this->stubUsageLogDependencies();
        $statement = $this->createStub(ilDBStatement::class);
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn(mixed $value): string => "'" . (string) $value . "'");
        $db->method('query')->willReturn($statement);
        $db->method('fetchAssoc')->willReturn(['value' => 'from the database']);
        $this->setGlobalVariable('ilDB', $db);

        // a shipped module is never read from lng_data
        $this->assertSame(
            '-sometopic-',
            ilLanguage::_lookupEntry($invalid_lang_key, 'tos', 'sometopic')
        );
    }

    /** @return array<string, array{0: string}> */
    public static function invalidLangKeys(): array
    {
        return [
            'path traversal' => ['../x'],
            'uppercase' => ['DE'],
            'locale-style with region' => ['de_DE'],
            'empty' => [''],
        ];
    }

    /** A cross-language read (txtlng()) of a migrated module's topic also comes from the build, via _lookupEntry(). */
    public function testTxtlngReadsTheMigratedMoFileForALanguageOtherThanTheCurrentSessionLanguage(): void
    {
        $this->ensureRealTosDeMoFileExists();
        $this->stubUsageLogDependencies();
        $this->registerDirectoryManager(
            new ComponentLanguageFileDirectory(new \ILIAS\TermsOfService(), 'tos')
        );

        // the session language "en" differs from the requested "de" to force the cross-language branch
        $language = $this->buildLanguageWithoutRunningConstructor('en');

        $this->assertSame('Nutzungsvereinbarung', $language->txtlng('tos', 'tos_agreement', 'de'));
    }

    /**
     * The build is checked before $cached_modules: ilCachedLanguage is always active and a migrated module keeps its
     * lng_modules row (dual-write), so the old order never read the build in production.
     */
    public function testTheMigratedMoFileWinsOverStaleCachedModulesFromIlCachedLanguage(): void
    {
        $this->ensureRealTosDeMoFileExists();
        $this->setGlobalVariable('ilDB', $this->createStub(ilDBInterface::class));
        $this->registerDirectoryManager(
            new ComponentLanguageFileDirectory(new \ILIAS\TermsOfService(), 'tos')
        );

        $language = $this->buildLanguageWithoutRunningConstructor('de');
        (new ReflectionObject($language))->getProperty('cached_modules')->setValue(
            $language,
            ['tos' => ['tos_agreement' => 'STALE PRE-MIGRATION TEXT FROM lng_modules CACHE']]
        );

        $language->loadLanguageModule('tos');

        $this->assertSame('Nutzungsvereinbarung', $language->txt('tos_agreement'));
    }

    /** A non-migrated module is still served from cached_modules. */
    public function testFallsBackToCachedModulesForANonMigratedModule(): void
    {
        $this->setGlobalVariable('ilDB', $this->createStub(ilDBInterface::class));

        $language = $this->buildLanguageWithoutRunningConstructor('de');
        (new ReflectionObject($language))->getProperty('cached_modules')->setValue(
            $language,
            ['not_migrated' => ['some_topic' => 'From the whole-language DB cache']]
        );

        $language->loadLanguageModule('not_migrated');

        $this->assertSame('From the whole-language DB cache', $language->txt('some_topic'));
    }





    private static function truncate(string $strategy, string $mo): string
    {
        return match ($strategy) {
            'first_10_bytes' => substr($mo, 0, 10),
            'first_40_bytes' => substr($mo, 0, 40),
            'first_half' => substr($mo, 0, intdiv(strlen($mo), 2)),
            'all_but_last_3_bytes' => substr($mo, 0, -3),
            'empty' => '',
            default => throw new \LogicException('Unknown truncation strategy "' . $strategy . '".'),
        };
    }

    /**
     * Mirrors what ilLanguage's constructor stores, without constructing one (which pulls in unrelated $DIC services).
     *
     * @param list<string> $lang_keys
     */
    private function setInstalledLanguages(array $lang_keys): void
    {
        (new ReflectionProperty(ilLanguage::class, 'installed_languages'))->setValue(null, $lang_keys);
    }

    public function testMigratedStateIsServedForAnInstalledLanguage(): void
    {
        $this->ensureRealTosDeMoFileExists();
        $this->registerDirectoryManager(new ComponentLanguageFileDirectory(new \ILIAS\TermsOfService(), 'tos'));
        $this->setInstalledLanguages(['de', 'en']);

        $this->assertSame(
            'Nutzungsvereinbarung',
            $this->callLoadFromMigratedLanguageFile('tos', 'de')['tos_agreement']
        );
    }

    /** A language not in the installed-languages list has no identifiers (never lng_modules), even if its shipped `.po` and overlay are readable. */
    public function testNoIdentifiersForALanguageNotInTheInstalledLanguagesList(): void
    {
        $this->ensureRealTosDeMoFileExists();
        $this->registerDirectoryManager(new ComponentLanguageFileDirectory(new \ILIAS\TermsOfService(), 'tos'));
        $this->setInstalledLanguages(['en', 'fr']);

        $this->assertSame([], $this->callLoadFromMigratedLanguageFile('tos', 'de'));
    }

    /** The restriction applies through _lookupEntry() too: a not-installed language never reads the database. */
    public function testTxtlngNeverReadsTheDatabaseForALanguageNotInTheInstalledLanguagesList(): void
    {
        $this->ensureRealTosDeMoFileExists();
        $this->registerDirectoryManager(new ComponentLanguageFileDirectory(new \ILIAS\TermsOfService(), 'tos'));
        $this->setInstalledLanguages(['en']);
        $this->stubUsageLogDependencies();
        $statement = $this->createStub(ilDBStatement::class);
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn(mixed $value): string => "'" . (string) $value . "'");
        $db->method('query')->willReturn($statement);
        $db->method('fetchAssoc')->willReturn(['value' => 'from the database']);
        $this->setGlobalVariable('ilDB', $db);

        $language = $this->buildLanguageWithoutRunningConstructor('en');

        $this->assertSame('-tos_agreement-', $language->txtlng('tos', 'tos_agreement', 'de'), 'never lng_data for a shipped module');
    }

    /** forgetInstalledLanguage() takes effect for the rest of the request: it also drops what was cached for the language. */
    public function testForgetInstalledLanguageStopsServingTheMigratedStateWithinTheSameRequest(): void
    {
        $this->ensureRealTosDeMoFileExists();
        $this->registerDirectoryManager(new ComponentLanguageFileDirectory(new \ILIAS\TermsOfService(), 'tos'));
        $this->setInstalledLanguages(['de']);
        $before = $this->callLoadFromMigratedLanguageFile('tos', 'de');
        $this->assertSame('Nutzungsvereinbarung', $before['tos_agreement']);

        ilLanguage::forgetInstalledLanguage('de');

        $this->assertSame(
            [],
            $this->callLoadFromMigratedLanguageFile('tos', 'de'),
            'both the stale cache entry and the list membership must be gone'
        );
    }

    /** Without a known list (no ilLanguage constructed in this request) nothing is restricted. */
    public function testWithoutAnyKnownInstalledLanguagesListNoRestrictionApplies(): void
    {
        $this->ensureRealTosDeMoFileExists();
        $this->registerDirectoryManager(new ComponentLanguageFileDirectory(new \ILIAS\TermsOfService(), 'tos'));

        $this->assertSame(
            'Nutzungsvereinbarung',
            $this->callLoadFromMigratedLanguageFile('tos', 'de')['tos_agreement']
        );
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
            try {
                $this->ensureRealTosDeMoFileExists();
                $this->fail('ensureRealTosDeMoFileExists() must refuse to write into a CLIENT_DATA_DIR outside sys_get_temp_dir().');
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
