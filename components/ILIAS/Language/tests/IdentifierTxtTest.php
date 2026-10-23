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

use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\LanguageIdentifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/** migrated, de and en (plural "item") */
enum IdentifierTxtTestMigAIdentifier: string implements LanguageIdentifier
{
    case SHARED = 'shared';
    case ONLY_A = 'only_a';
    case ONLY_EN = 'only_en';
    case ITEM = 'item';
    case MISSING = 'missing';
    case EMPTY = '';
    case NUL = '0';

    public function module(): string
    {
        return 'mig_a';
    }
}

/** migrated, de only (ONLY_EN is in no build); the same identifier "shared" as mig_a */
enum IdentifierTxtTestMigBIdentifier: string implements LanguageIdentifier
{
    case SHARED = 'shared';
    case ONLY_EN = 'only_en';

    public function module(): string
    {
        return 'mig_b';
    }
}

/** not migrated (lng_modules) */
enum IdentifierTxtTestLegacyIdentifier: string implements LanguageIdentifier
{
    case SHARED = 'shared';
    case ZERO = 'zero';
    case BLANK = 'blank';
    case MISSING = 'missing';

    public function module(): string
    {
        return 'legacy';
    }
}

/** not migrated, the same identifier "shared" as legacy */
enum IdentifierTxtTestLegacy2Identifier: string implements LanguageIdentifier
{
    case SHARED = 'shared';

    public function module(): string
    {
        return 'legacy2';
    }
}

/**
 * ilLanguage::txt() with a LanguageIdentifier. Own process per test: CLIENT_DATA_DIR and ILIAS_ABSOLUTE_PATH
 * cannot be redefined (see PluralTxtTest).
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class IdentifierTxtTest extends ilLanguageBaseTestCase
{
    private ?string $fixture_directory = null;

    /** @var list<string> the SQL every query of the ilDB stub received */
    private array $queries = [];

    /** @var list<string>|null kind (modules|data), lang_key, module[, identifier] of the last query */
    private ?array $last_query = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        if (!defined('ILIAS_LOG_ENABLED')) {
            define('ILIAS_LOG_ENABLED', true); // a missing text is logged
        }
        MigratedPoFixture::resetRuntime();
        MigratedPoFixture::ensureClientDataDirDefinedOrSkip($this);

        $this->fixture_directory = __DIR__ . '/tmp-identifier-txt-' . bin2hex(random_bytes(4));
        $relative_path = 'components/ILIAS/Language/tests/' . basename($this->fixture_directory) . '/';
        MigratedPoFixture::writeShippedPo($relative_path, 'mig_a', 'de', self::catalog('mig_a', ['shared' => 'Mig A', 'only_a' => 'A'], ['Eintrag', 'Einträge']));
        MigratedPoFixture::writeShippedPo($relative_path, 'mig_a', 'en', self::catalog('mig_a', ['shared' => 'Mig A en', 'only_en' => 'Only en'], ['Item', 'Items']));
        MigratedPoFixture::writeShippedPo($relative_path, 'mig_b', 'de', self::catalog('mig_b', ['shared' => 'Mig B']));

        $this->setGlobalVariable(
            LanguageFileDirectoryManager::class,
            new LanguageFileDirectoryManager(
                new CustomizingLanguageFileDirectory(),
                MigratedPoFixture::directory('mig_a', $relative_path),
                MigratedPoFixture::directory('mig_b', $relative_path)
            )
        );
        $this->setGlobalVariable('ilClientIniFile', $this->createStub(ilIniFile::class));
        $this->setGlobalVariable('ilDB', $this->createStub(ilDBInterface::class));
        $factory = $this->createStub(ilLoggerFactory::class);
        $factory->method('getComponentLogger')->willReturn($this->createStub(ilLogger::class));
        $this->setGlobalVariable('ilLoggerFactory', $factory);
    }

    protected function tearDown(): void
    {
        if ($this->fixture_directory !== null) {
            MigratedPoFixture::removeShippedDirectory('components/ILIAS/Language/tests/' . basename($this->fixture_directory));
        }
        if (defined('CLIENT_DATA_DIR') && is_dir(CLIENT_DATA_DIR)) {
            MigratedPoFixture::removeDirectory(CLIENT_DATA_DIR);
        }

        parent::tearDown();
    }

    /**
     * @param array<string, string> $values
     * @param list<string>|null $item_forms the two forms of the plural message "item" (none: without)
     */
    private static function catalog(string $module, array $values, ?array $item_forms = null): TranslationCatalog
    {
        $catalog = MigratedPoFixture::catalog($module, $values);
        if ($item_forms !== null) {
            $catalog->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
            $entry = new TranslationEntry($module, 'item');
            $entry->setPlural('items', $item_forms);
            $catalog->add($entry);
        }

        return $catalog;
    }

    /** @param array<string, array<string, mixed>> $cached lng_modules of the language by module */
    private function language(array $cached = [], string $lang_key = 'de', string $default = 'en'): ilLanguage
    {
        global $DIC;
        MigratedPoFixture::build($DIC[LanguageFileDirectoryManager::class], (string) constant('ILIAS_ABSOLUTE_PATH'));
        $language = (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor();
        $reflected = new ReflectionObject($language);
        foreach (['lang_key', 'lang_user'] as $property) {
            $reflected->getProperty($property)->setValue($language, $lang_key);
        }
        $reflected->getProperty('lang_default')->setValue($language, $default);
        $reflected->getProperty('log')->setValue($language, $this->createStub(ilLogger::class));
        $reflected->getProperty('cached_modules')->setValue($language, $cached);

        return $language;
    }

    /**
     * lng_modules ("modules": "<lang>|<module>" => values, a missing one has no row) and lng_data
     * ("data": "<lang>|<module>|<identifier>" => value) of the database stub; $this->queries records every query.
     *
     * @param array<string, array<string, mixed>> $modules
     * @param array<string, string> $data
     */
    private function stubDatabase(array $modules = [], array $data = []): void
    {
        $statement = $this->createStub(ilDBStatement::class);
        $statement->method('fetchRow')->willReturnCallback(function () use ($modules) {
            $key = $this->last_query[1] . '|' . $this->last_query[2];

            return isset($modules[$key]) ? ['lang_array' => serialize($modules[$key])] : false;
        });
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn(mixed $value): string => "'" . (string) $value . "'");
        $db->method('query')->willReturnCallback(function (string $sql) use ($statement) {
            $this->queries[] = $sql;
            if (preg_match("/FROM lng_modules WHERE lang_key = '([^']*)' AND module = '([^']*)'/", $sql, $m) === 1) {
                $this->last_query = ['modules', $m[1], $m[2]];
            } elseif (preg_match("/FROM lng_data WHERE module = '([^']*)' AND lang_key = '([^']*)' AND identifier = '([^']*)'/", $sql, $m) === 1) {
                $this->last_query = ['data', $m[2], $m[1], $m[3]];
            } else {
                $this->fail('unexpected query: ' . $sql);
            }

            return $statement;
        });
        $db->method('fetchAssoc')->willReturnCallback(function () use ($data) {
            $value = $data[$this->last_query[1] . '|' . $this->last_query[2] . '|' . $this->last_query[3]] ?? null;

            return $value === null ? null : ['value' => $value];
        });
        $this->setGlobalVariable('ilDB', $db);
    }

    /** @return array{loaded_modules: list<string>, text: array<string, mixed>, migrated_key_modules: mixed} */
    private static function state(ilLanguage $language): array
    {
        return [
            'loaded_modules' => $language->loaded_modules,
            'text' => $language->text,
            'migrated_key_modules' => (new ReflectionProperty(ilLanguage::class, 'migrated_key_modules'))->getValue($language),
        ];
    }

    /** @return array<string, string> */
    private static function usageLog(): array
    {
        return (new ReflectionProperty(ilLanguage::class, 'lng_log'))->getValue();
    }

    private static function enableUsageLog(ilLanguage $language, bool $enabled = true): void
    {
        (new ReflectionProperty(ilLanguage::class, 'usage_log_enabled'))->setValue($language, $enabled);
    }

    /** @return iterable<string, array{LanguageIdentifier, ?int, string, string, string}> */
    public static function migratedTexts(): iterable
    {
        yield 'own module, not mig_b' => [IdentifierTxtTestMigAIdentifier::SHARED, null, 'de', 'en', 'Mig A'];
        yield 'same identifier, other module' => [IdentifierTxtTestMigBIdentifier::SHARED, null, 'de', 'en', 'Mig B'];
        yield 'identifier only in mig_a' => [IdentifierTxtTestMigAIdentifier::ONLY_A, null, 'de', 'en', 'A'];
        yield 'plural n=1' => [IdentifierTxtTestMigAIdentifier::ITEM, 1, 'de', 'en', 'Eintrag'];
        yield 'plural n=0' => [IdentifierTxtTestMigAIdentifier::ITEM, 0, 'de', 'en', 'Einträge'];
        yield 'plural n=5' => [IdentifierTxtTestMigAIdentifier::ITEM, 5, 'de', 'en', 'Einträge'];
        yield 'plain identifier with n' => [IdentifierTxtTestMigAIdentifier::SHARED, 5, 'de', 'en', 'Mig A'];
        yield 'other current language (build, not lng_data)' => [IdentifierTxtTestMigAIdentifier::SHARED, null, 'en', 'de', 'Mig A en'];
        yield 'other current language, plural n=1' => [IdentifierTxtTestMigAIdentifier::ITEM, 1, 'en', 'de', 'Item'];
        yield 'other current language, plural n=2' => [IdentifierTxtTestMigAIdentifier::ITEM, 2, 'en', 'de', 'Items'];
        yield 'default language fallback' => [IdentifierTxtTestMigAIdentifier::ONLY_EN, null, 'de', 'en', 'Only en'];
        yield 'default language fallback, other direction' => [IdentifierTxtTestMigAIdentifier::ONLY_A, null, 'en', 'de', 'A'];
        yield 'missing everywhere' => [IdentifierTxtTestMigAIdentifier::MISSING, null, 'de', 'en', '-missing-'];
        yield 'missing, plural' => [IdentifierTxtTestMigAIdentifier::MISSING, 3, 'en', 'de', '-missing-'];
    }

    #[DataProvider('migratedTexts')]
    public function testMigratedModuleIsReadDirectlyWithoutLoadingAnything(LanguageIdentifier $key, ?int $n, string $lang, string $default, string $expected): void
    {
        $this->forbidDatabaseQueries();
        $language = $this->language([], $lang, $default);
        $before = self::state($language);

        $this->assertSame($expected, $language->txt($key, $n));

        $this->assertSame($before, self::state($language), 'no module is loaded, nothing is assigned to a module');
    }

    private function forbidDatabaseQueries(): void
    {
        $db = $this->createMock(ilDBInterface::class);
        $db->expects($this->never())->method('query');
        $this->setGlobalVariable('ilDB', $db);
    }

    public function testTheOverlayWinsOverTheBuildForTextAndPluralForm(): void
    {
        $overlay = self::catalog('mig_a', ['shared' => 'Overlay A', 'only_a' => 'A'], ['Overlay-Eintrag', 'Overlay-Einträge']);
        MigratedPoFixture::writePair(rtrim(CLIENT_DATA_DIR, '/') . '/lang/mig_a/de/mig_a_de', $overlay);
        $language = $this->language();

        $this->assertSame('Overlay A', $language->txt(IdentifierTxtTestMigAIdentifier::SHARED));
        $this->assertSame('Overlay-Eintrag', $language->txt(IdentifierTxtTestMigAIdentifier::ITEM, 1));
        $this->assertSame('Overlay-Einträge', $language->txt(IdentifierTxtTestMigAIdentifier::ITEM, 2));
        $this->assertSame('Mig B', $language->txt(IdentifierTxtTestMigBIdentifier::SHARED), 'another module is not affected');
    }

    public function testLegacyModuleServesItsOwnValueWhateverModuleWasLoadedLast(): void
    {
        $language = $this->language([
            'legacy' => ['shared' => 'Legacy', 'zero' => '0'],
            'legacy2' => ['shared' => 'Legacy 2'],
        ]);
        $language->loadLanguageModule('mig_a');
        $language->loadLanguageModule('legacy');
        $language->loadLanguageModule('legacy2');
        $language->loadLanguageModule('mig_b');

        $this->assertSame('Legacy', $language->txt(IdentifierTxtTestLegacyIdentifier::SHARED));
        $this->assertSame('Legacy 2', $language->txt(IdentifierTxtTestLegacy2Identifier::SHARED));
        $this->assertSame('0', $language->txt(IdentifierTxtTestLegacyIdentifier::ZERO), '"0" is a value');
        $this->assertSame('Mig B', $language->txt('shared'), 'txt() still serves the module loaded last');
        $this->assertSame('Mig A', $language->txt(IdentifierTxtTestMigAIdentifier::SHARED));
    }

    public function testLegacyModuleWithoutAnyLoadedModule(): void
    {
        $language = $this->language(['legacy' => ['shared' => 'Legacy', 'blank' => '']]);

        $this->assertSame('Legacy', $language->txt(IdentifierTxtTestLegacyIdentifier::SHARED));
        $this->assertSame('-missing-', $language->txt(IdentifierTxtTestLegacyIdentifier::MISSING));
        $this->assertSame('-blank-', $language->txt(IdentifierTxtTestLegacyIdentifier::BLANK), 'an empty value is no text');
    }

    public function testLegacyModuleHasNoSideEffectOnTxtAndTheModuleAssignment(): void
    {
        $language = $this->language(['legacy' => ['shared' => 'Legacy']]);
        $language->loadLanguageModule('mig_a');
        $before = self::state($language);
        $this->assertSame('mig_a', $before['migrated_key_modules']['shared'] ?? null);

        $this->assertSame('Legacy', $language->txt(IdentifierTxtTestLegacyIdentifier::SHARED));

        $this->assertSame($before, self::state($language), 'loaded_modules, text and the module assignment are unchanged');
        $this->assertSame('Mig A', $language->txt('shared'), 'loadLanguageModule("legacy") would have taken the identifier over');
        $this->assertSame(['mig_a'], $language->loaded_modules);
    }

    public function testLegacyModuleReadsLngModulesOncePerModuleAndRequest(): void
    {
        $this->stubDatabase([
            'de|legacy' => ['shared' => 'Legacy', 'zero' => '0'],
            'de|legacy2' => ['shared' => 'Legacy 2'],
        ]);
        $language = $this->language([], 'de', 'de');

        $this->assertSame('Legacy', $language->txt(IdentifierTxtTestLegacyIdentifier::SHARED));
        $this->assertSame('0', $language->txt(IdentifierTxtTestLegacyIdentifier::ZERO));
        $this->assertSame('-missing-', $language->txt(IdentifierTxtTestLegacyIdentifier::MISSING));
        $this->assertCount(1, $this->queries, 'one query for the module, whatever the identifier');
        $this->assertSame('Legacy 2', $language->txt(IdentifierTxtTestLegacy2Identifier::SHARED));
        $this->assertCount(2, $this->queries);
        $this->assertSame('Legacy', $language->txt(IdentifierTxtTestLegacyIdentifier::SHARED));
        $this->assertCount(2, $this->queries);
    }

    public function testAModuleWithoutRowIsNotQueriedAgain(): void
    {
        $this->stubDatabase();
        $language = $this->language();

        $this->assertSame('-shared-', $language->txt(IdentifierTxtTestLegacyIdentifier::SHARED));
        $this->assertSame('-missing-', $language->txt(IdentifierTxtTestLegacyIdentifier::MISSING));

        $modules_queries = array_filter($this->queries, static fn(string $sql): bool => str_contains($sql, 'lng_modules'));
        $this->assertCount(1, $modules_queries);
    }

    public function testLoadLanguageModuleAfterIdentifierLookupStillLoadsTheModule(): void
    {
        $this->stubDatabase(['de|legacy' => ['shared' => 'Legacy']]);
        $language = $this->language();
        $this->assertSame('Legacy', $language->txt(IdentifierTxtTestLegacyIdentifier::SHARED));

        $language->loadLanguageModule('legacy');

        $this->assertSame(['legacy'], $language->loaded_modules);
        $this->assertSame('Legacy', $language->txt('shared'));
        $this->assertSame('Legacy', $language->txt(IdentifierTxtTestLegacyIdentifier::SHARED));
    }

    public function testLoadLanguageModuleReadsTheDatabaseOnlyForAModuleThatIsNotMigrated(): void
    {
        $this->stubDatabase(['de|legacy' => ['only_legacy' => 'L']]);
        $language = $this->language();

        $language->loadLanguageModule('mig_a');
        $this->assertSame([], $this->queries, 'a migrated module is never read from lng_modules');
        $language->loadLanguageModule('legacy');

        $this->assertCount(1, $this->queries);
        $this->assertSame('L', $language->txt('only_legacy'));
    }

    public function testLegacyModuleFallsBackToTheDefaultLanguageFromLngData(): void
    {
        $this->stubDatabase(['fr|legacy' => ['shared' => 'Legacy fr', 'blank' => '']], [
            'en|legacy|zero' => '0',
            'en|legacy|blank' => 'Blank en',
        ]);
        $language = $this->language([], 'fr', 'en');

        $this->assertSame('Legacy fr', $language->txt(IdentifierTxtTestLegacyIdentifier::SHARED), 'the current language comes from lng_modules');
        $this->assertSame('0', $language->txt(IdentifierTxtTestLegacyIdentifier::ZERO), 'default language, value "0"');
        $this->assertSame('Blank en', $language->txt(IdentifierTxtTestLegacyIdentifier::BLANK), 'an empty value is no text');
        $this->assertSame('-missing-', $language->txt(IdentifierTxtTestLegacyIdentifier::MISSING));
    }

    public function testAMigratedModuleWithoutTheCurrentLanguageFallsBackToTheDefaultLanguageBuild(): void
    {
        $this->stubDatabase();
        $language = $this->language([], 'en', 'de');

        $this->assertSame('Mig B', $language->txt(IdentifierTxtTestMigBIdentifier::SHARED), 'mig_b has only de');
    }

    public function testAMigratedModuleWithoutABuildInTheDefaultLanguageFallsBackToLngData(): void
    {
        $this->stubDatabase([], ['en|mig_b|only_en' => 'Only en from the database']);
        $language = $this->language([], 'de', 'en');

        // mig_b is shipped: never lng_data, also without a build for the default language
        $this->assertSame('-only_en-', $language->txt(IdentifierTxtTestMigBIdentifier::ONLY_EN));
        $data_queries = array_filter($this->queries, static fn(string $sql): bool => str_contains($sql, 'lng_data'));
        $this->assertCount(0, $data_queries);
    }

    public function testAnInvalidDefaultLanguageIsNeverQueried(): void
    {
        $this->stubDatabase();
        $language = $this->language([], 'de', 'deu');

        $this->assertSame('-only_en-', $language->txt(IdentifierTxtTestMigBIdentifier::ONLY_EN));
        foreach ($this->queries as $sql) {
            $this->assertStringNotContainsString("'deu'", $sql);
        }
    }

    public function testPluralIdentifierWithoutNIsTheFormTxtServes(): void
    {
        $language = $this->language();
        $language->loadLanguageModule('mig_a');

        $this->assertSame($language->txt('item'), $language->txt(IdentifierTxtTestMigAIdentifier::ITEM));
    }

    public function testNoFallbackQueryWhenTheLanguageIsTheDefaultLanguage(): void
    {
        $this->stubDatabase();
        $language = $this->language([], 'en', 'en');

        $this->assertSame('-shared-', $language->txt(IdentifierTxtTestLegacyIdentifier::SHARED));
        $this->assertCount(1, $this->queries, 'lng_modules once, no lng_data');
    }

    public function testAnEmptyIdentifierIsEmptyAndNotRecorded(): void
    {
        $this->forbidDatabaseQueries();
        $language = $this->language();
        self::enableUsageLog($language);

        $this->assertSame('', $language->txt(IdentifierTxtTestMigAIdentifier::EMPTY));
        $this->assertSame('', $language->txt(IdentifierTxtTestMigAIdentifier::EMPTY, 2));
        $this->assertSame('', $language->txt(IdentifierTxtTestMigAIdentifier::NUL));
        $this->assertSame('', $language->txt(IdentifierTxtTestMigAIdentifier::NUL, 1));

        $this->assertSame([], $language->getUsedTopics());
        $this->assertSame([], $language->getUsedModules());
        $this->assertSame([], self::usageLog());
        self::enableUsageLog($language, false);
    }

    public function testUsageIsRecordedForTopicsAlwaysAndForModulesAndLogOnlyOnAHit(): void
    {
        $language = $this->language(['legacy' => ['shared' => 'Legacy']]);
        self::enableUsageLog($language);

        $language->txt(IdentifierTxtTestMigAIdentifier::MISSING);
        $this->assertSame(['missing' => 'missing'], $language->getUsedTopics());
        $this->assertSame([], $language->getUsedModules(), 'no hit, no module');
        $this->assertSame([], self::usageLog());

        $language->txt(IdentifierTxtTestMigAIdentifier::SHARED);
        $language->txt(IdentifierTxtTestLegacyIdentifier::SHARED);

        $this->assertEqualsCanonicalizing(['mig_a' => 'mig_a', 'legacy' => 'legacy'], $language->getUsedModules());
        $this->assertSame(['shared' => 'legacy'], self::usageLog(), 'the identifier is logged for the module read last');
        $this->assertSame(['missing' => 'missing', 'shared' => 'shared'], $language->getUsedTopics());
        self::enableUsageLog($language, false);
    }

    public function testUsageLogIsOffWhenDisabledAndRecordsTheModuleOfAFallbackHit(): void
    {
        $language = $this->language();

        $language->txt(IdentifierTxtTestMigAIdentifier::ONLY_EN);
        $this->assertSame([], self::usageLog(), 'usage log disabled');
        $this->assertSame(['mig_a' => 'mig_a'], $language->getUsedModules());

        self::enableUsageLog($language);
        $language->txt(IdentifierTxtTestMigAIdentifier::MISSING);
        $this->assertSame([], self::usageLog(), 'a miss is not logged');
        $language->txt(IdentifierTxtTestMigAIdentifier::ONLY_EN);
        $this->assertSame(['only_en' => 'mig_a'], self::usageLog());
        self::enableUsageLog($language, false);
    }

    /** @return iterable<string, array{?string, string}> */
    public static function lookupEntryValues(): iterable
    {
        yield 'value' => ['Wert', 'Wert'];
        yield 'zero' => ['0', '0'];
        yield 'empty' => ['', '-id-'];
        yield 'no row' => [null, '-id-'];
    }

    #[DataProvider('lookupEntryValues')]
    public function testLookupEntryReadsLngData(?string $stored, string $expected): void
    {
        $this->stubDatabase([], $stored === null ? [] : ['fr|legacy|id' => $stored]);
        $this->language();

        $this->assertSame($expected, ilLanguage::_lookupEntry('fr', 'legacy', 'id'));
        $this->assertCount(1, $this->queries);
    }

    public function testLookupEntryRecordsTheUsageOnlyForAHit(): void
    {
        $this->stubDatabase([], ['fr|legacy|id' => 'Wert']);
        $language = $this->language();

        ilLanguage::_lookupEntry('fr', 'legacy', 'nothing');
        $this->assertSame([], $language->getUsedModules());
        ilLanguage::_lookupEntry('fr', 'legacy', 'id');
        $this->assertSame(['legacy' => 'legacy'], $language->getUsedModules());
        $this->assertSame(['id' => 'id'], $language->getUsedTopics());
    }
}
