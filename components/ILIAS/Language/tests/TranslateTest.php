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
enum TranslateTestMigAIdentifier: string implements LanguageIdentifier
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

/** migrated, de only; the same identifier "shared" as mig_a */
enum TranslateTestMigBIdentifier: string implements LanguageIdentifier
{
    case SHARED = 'shared';

    public function module(): string
    {
        return 'mig_b';
    }
}

/** not migrated (lng_modules) */
enum TranslateTestLegacyIdentifier: string implements LanguageIdentifier
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
enum TranslateTestLegacy2Identifier: string implements LanguageIdentifier
{
    case SHARED = 'shared';

    public function module(): string
    {
        return 'legacy2';
    }
}

/**
 * ilLanguage::translate(): a LanguageIdentifier is read from exactly its module (migrated or not) without
 * loadLanguageModule() and without side effect on txt(); a string behaves like txt()/ntxt(). Every test in its own
 * process (CLIENT_DATA_DIR and ILIAS_ABSOLUTE_PATH cannot be redefined, see NtxtTest).
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class TranslateTest extends ilLanguageBaseTestCase
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
            define('ILIAS_LOG_ENABLED', true); // translate() logs a missing text
        }
        MigratedPoFixture::resetRuntime();
        MigratedPoFixture::ensureClientDataDirDefinedOrSkip($this);

        $this->fixture_directory = __DIR__ . '/tmp-translate-' . bin2hex(random_bytes(4));
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

    /** @param array<string, array<string, mixed>> $cached lng_modules of the language by module (the cache the constructor reads: cached_modules) */
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

    /** @return iterable<string, array{LanguageIdentifier, ?int, ?string, string}> */
    public static function migratedTexts(): iterable
    {
        yield 'own module, not mig_b' => [TranslateTestMigAIdentifier::SHARED, null, null, 'Mig A'];
        yield 'same identifier, other module' => [TranslateTestMigBIdentifier::SHARED, null, null, 'Mig B'];
        yield 'identifier only in mig_a' => [TranslateTestMigAIdentifier::ONLY_A, null, null, 'A'];
        yield 'plural n=1' => [TranslateTestMigAIdentifier::ITEM, 1, null, 'Eintrag'];
        yield 'plural n=0' => [TranslateTestMigAIdentifier::ITEM, 0, null, 'Einträge'];
        yield 'plural n=5' => [TranslateTestMigAIdentifier::ITEM, 5, null, 'Einträge'];
        yield 'plain identifier with n' => [TranslateTestMigAIdentifier::SHARED, 5, null, 'Mig A'];
        yield 'other language (build, not lng_data)' => [TranslateTestMigAIdentifier::SHARED, null, 'en', 'Mig A en'];
        yield 'other language, plural n=1' => [TranslateTestMigAIdentifier::ITEM, 1, 'en', 'Item'];
        yield 'other language, plural n=2' => [TranslateTestMigAIdentifier::ITEM, 2, 'en', 'Items'];
        yield 'current language given explicitly' => [TranslateTestMigAIdentifier::SHARED, null, 'de', 'Mig A'];
        yield 'default language fallback' => [TranslateTestMigAIdentifier::ONLY_EN, null, null, 'Only en'];
        yield 'missing everywhere' => [TranslateTestMigAIdentifier::MISSING, null, null, '-missing-'];
        yield 'missing, other language' => [TranslateTestMigAIdentifier::MISSING, 3, 'en', '-missing-'];
    }

    #[DataProvider('migratedTexts')]
    public function testMigratedModuleIsReadDirectlyWithoutLoadingAnything(LanguageIdentifier $key, ?int $n, ?string $lang, string $expected): void
    {
        $this->forbidDatabaseQueries();
        $language = $this->language();
        $before = self::state($language);

        $this->assertSame($expected, $language->translate($key, $n, $lang));

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

        $this->assertSame('Overlay A', $language->translate(TranslateTestMigAIdentifier::SHARED));
        $this->assertSame('Overlay-Eintrag', $language->translate(TranslateTestMigAIdentifier::ITEM, 1));
        $this->assertSame('Overlay-Einträge', $language->translate(TranslateTestMigAIdentifier::ITEM, 2));
        $this->assertSame('Mig B', $language->translate(TranslateTestMigBIdentifier::SHARED), 'another module is not affected');
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

        $this->assertSame('Legacy', $language->translate(TranslateTestLegacyIdentifier::SHARED));
        $this->assertSame('Legacy 2', $language->translate(TranslateTestLegacy2Identifier::SHARED));
        $this->assertSame('0', $language->translate(TranslateTestLegacyIdentifier::ZERO), '"0" is a value');
        $this->assertSame('Mig B', $language->txt('shared'), 'txt() still serves the module loaded last');
        $this->assertSame('Mig A', $language->translate(TranslateTestMigAIdentifier::SHARED));
    }

    public function testLegacyModuleWithoutAnyLoadedModule(): void
    {
        $language = $this->language(['legacy' => ['shared' => 'Legacy', 'blank' => '']]);

        $this->assertSame('Legacy', $language->translate(TranslateTestLegacyIdentifier::SHARED));
        $this->assertSame('-missing-', $language->translate(TranslateTestLegacyIdentifier::MISSING));
        $this->assertSame('-blank-', $language->translate(TranslateTestLegacyIdentifier::BLANK), 'an empty value is no text');
    }

    public function testLegacyModuleHasNoSideEffectOnTxtAndTheModuleAssignment(): void
    {
        $language = $this->language(['legacy' => ['shared' => 'Legacy']]);
        $language->loadLanguageModule('mig_a');
        $before = self::state($language);
        $this->assertSame('mig_a', $before['migrated_key_modules']['shared'] ?? null);

        $this->assertSame('Legacy', $language->translate(TranslateTestLegacyIdentifier::SHARED));

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

        $this->assertSame('Legacy', $language->translate(TranslateTestLegacyIdentifier::SHARED));
        $this->assertSame('0', $language->translate(TranslateTestLegacyIdentifier::ZERO));
        $this->assertSame('-missing-', $language->translate(TranslateTestLegacyIdentifier::MISSING));
        $this->assertCount(1, $this->queries, 'one query for the module, whatever the identifier');
        $this->assertSame('Legacy 2', $language->translate(TranslateTestLegacy2Identifier::SHARED));
        $this->assertCount(2, $this->queries);
        $this->assertSame('Legacy', $language->translate(TranslateTestLegacyIdentifier::SHARED));
        $this->assertCount(2, $this->queries);
    }

    public function testAModuleWithoutRowIsNotQueriedAgain(): void
    {
        $this->stubDatabase();
        $language = $this->language();

        $this->assertSame('-shared-', $language->translate(TranslateTestLegacyIdentifier::SHARED));
        $this->assertSame('-missing-', $language->translate(TranslateTestLegacyIdentifier::MISSING));

        $modules_queries = array_filter($this->queries, static fn(string $sql): bool => str_contains($sql, 'lng_modules'));
        $this->assertCount(1, $modules_queries);
    }

    public function testLoadLanguageModuleAfterTranslateStillLoadsTheModule(): void
    {
        $this->stubDatabase(['de|legacy' => ['shared' => 'Legacy']]);
        $language = $this->language();
        $this->assertSame('Legacy', $language->translate(TranslateTestLegacyIdentifier::SHARED));

        $language->loadLanguageModule('legacy');

        $this->assertSame(['legacy'], $language->loaded_modules);
        $this->assertSame('Legacy', $language->txt('shared'));
        $this->assertSame('Legacy', $language->translate(TranslateTestLegacyIdentifier::SHARED));
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

    public function testLegacyModuleInAnotherLanguageReadsLngDataAndFallsBackToTheDefaultLanguage(): void
    {
        $this->stubDatabase(['de|legacy' => ['shared' => 'Legacy de']], [
            'fr|legacy|shared' => 'Legacy fr',
            'en|legacy|zero' => '0',
            'en|legacy|blank' => 'Blank en',
        ]);
        $language = $this->language();

        $this->assertSame('Legacy fr', $language->translate(TranslateTestLegacyIdentifier::SHARED, null, 'fr'));
        $this->assertSame('0', $language->translate(TranslateTestLegacyIdentifier::ZERO, null, 'fr'), 'default language, value "0"');
        $this->assertSame('Blank en', $language->translate(TranslateTestLegacyIdentifier::BLANK, null, 'fr'));
        $this->assertSame('-missing-', $language->translate(TranslateTestLegacyIdentifier::MISSING, null, 'fr'));
        $this->assertSame('Legacy de', $language->translate(TranslateTestLegacyIdentifier::SHARED), 'the current language still comes from lng_modules');
    }

    public function testAMigratedModuleWithoutThatLanguageIsReadFromLngData(): void
    {
        $this->stubDatabase([], ['en|mig_b|shared' => 'Mig B en']);
        $language = $this->language();

        $this->assertSame('Mig B en', $language->translate(TranslateTestMigBIdentifier::SHARED, null, 'en'));
        $this->assertSame('Mig B', $language->translate(TranslateTestMigBIdentifier::SHARED), 'de is migrated: the build');
    }

    public function testPluralWithoutNIsTheFormTxtServes(): void
    {
        $language = $this->language();
        $language->loadLanguageModule('mig_a');

        $this->assertSame($language->txt('item'), $language->translate(TranslateTestMigAIdentifier::ITEM));
        $this->assertSame($language->txt('item'), $language->translate(TranslateTestMigAIdentifier::ITEM, null, 'de'));
    }

    public function testLegacyModuleOfTheCurrentLanguageFallsBackToTheDefaultLanguageFromLngData(): void
    {
        $this->stubDatabase(['de|legacy' => ['shared' => '']], ['en|legacy|shared' => 'Legacy en']);
        $language = $this->language();

        $this->assertSame('Legacy en', $language->translate(TranslateTestLegacyIdentifier::SHARED));
    }

    public function testNoFallbackQueryWhenTheLanguageIsTheDefaultLanguage(): void
    {
        $this->stubDatabase();
        $language = $this->language([], 'en', 'en');

        $this->assertSame('-shared-', $language->translate(TranslateTestLegacyIdentifier::SHARED));
        $this->assertSame('-shared-', $language->translate(TranslateTestLegacyIdentifier::SHARED, null, 'en'));
        $this->assertCount(1, $this->queries, 'lng_modules once, no lng_data');
    }

    /** @return iterable<string, array{list<string>, string, ?int}> */
    public static function stringLookups(): iterable
    {
        foreach ([
            'migrated then legacy' => ['mig_a', 'legacy'],
            'legacy then migrated' => ['legacy', 'mig_a'],
            'two migrated' => ['mig_a', 'mig_b'],
            'nothing loaded' => [],
        ] as $label => $modules) {
            foreach (['shared', 'only_a', 'item', 'zero', 'missing', 'direct'] as $identifier) {
                foreach ([null, 1, 5] as $n) {
                    yield $label . ' / ' . $identifier . ' / ' . var_export($n, true) => [$modules, $identifier, $n];
                }
            }
        }
    }

    /** @param list<string> $modules */
    #[DataProvider('stringLookups')]
    public function testAStringBehavesLikeTxtAndNtxt(array $modules, string $identifier, ?int $n): void
    {
        $language = $this->language(['legacy' => ['shared' => 'Legacy', 'zero' => '0']]);
        $language->text['direct'] = 'Direct';
        foreach ($modules as $module) {
            $language->loadLanguageModule($module);
        }
        $expected = $n === null ? $language->txt($identifier) : $language->ntxt($identifier, $n);

        $this->assertSame($expected, $language->translate($identifier, $n));
        $this->assertSame($expected, $language->translate($identifier, $n, 'de'), 'the current language given explicitly');
    }

    public function testAStringInAnotherLanguageUsesTheModuleResponsibleAndNeverAnEarlierOne(): void
    {
        $language = $this->language();
        $language->loadLanguageModule('mig_a');
        $language->loadLanguageModule('mig_b');

        $this->assertSame('-shared-', $language->translate('shared', null, 'en'));

        $reverse = $this->language();
        $reverse->loadLanguageModule('mig_b');
        $reverse->loadLanguageModule('mig_a');
        $this->assertSame('Mig A en', $reverse->translate('shared', null, 'en'));
        $this->assertSame('Items', $reverse->translate('item', 2, 'en'));
        $this->assertSame('Item', $reverse->translate('item', 1, 'en'));
    }

    /** Exactly one lookup, in the module txt() serves the identifier from (the one loaded last containing it), never the default language. */
    public function testAStringInAnotherLanguageReadsOnlyTheLegacyModuleTxtServesItFrom(): void
    {
        $this->stubDatabase([
            'de|legacy' => ['shared' => 'x', 'only_first' => 'x', 'both' => 'x'],
            'de|legacy2' => ['shared' => 'x', 'both' => 'x'],
        ], [
            'fr|legacy|shared' => 'Legacy fr',
            'fr|legacy2|shared' => 'Legacy 2 fr',
            'fr|legacy|only_first' => 'First only fr',
            'fr|legacy|both' => 'Earlier module fr',
            'en|legacy|nowhere' => 'Default language',
        ]);
        $language = $this->language();
        $language->loadLanguageModule('legacy');
        $language->loadLanguageModule('legacy2');
        $lng_data_queries = function (): array {
            return array_values(array_filter($this->queries, static fn(string $sql): bool => str_contains($sql, 'lng_data')));
        };

        $this->queries = [];
        $this->assertSame('Legacy 2 fr', $language->translate('shared', null, 'fr'), 'the module loaded last wins, as in txt()');
        $this->assertCount(1, $lng_data_queries(), 'one lookup');

        $this->queries = [];
        $this->assertSame('First only fr', $language->translate('only_first', null, 'fr'), 'the only module that contains it');
        $this->assertCount(1, $lng_data_queries());

        $this->queries = [];
        $this->assertSame('-both-', $language->translate('both', null, 'fr'), 'legacy2 is responsible and has no "fr" - never the earlier module');
        $this->assertCount(1, $lng_data_queries());

        $this->queries = [];
        $this->assertSame('-nowhere-', $language->translate('nowhere', null, 'fr'), 'no module responsible, no fallback to the default language');
        $this->assertSame([], $lng_data_queries(), 'no lookup without a responsible module');
    }

    public function testAStringInAnotherLanguageIgnoresTheLegacyModulesOfAMigratedIdentifier(): void
    {
        $this->stubDatabase([], ['fr|legacy|shared' => 'Legacy fr']);
        $language = $this->language(['legacy' => ['shared' => 'Legacy']]);
        $language->loadLanguageModule('legacy');
        $language->loadLanguageModule('mig_a');

        $this->assertSame('Mig A', $language->txt('shared'));
        $this->assertSame('-shared-', $language->translate('shared', null, 'fr'), 'mig_a is responsible, it has no "fr"');
    }

    public function testTheResponsibleModuleOfAStringFollowsTheLoadingOrderOfMigratedAndLegacyModules(): void
    {
        $this->stubDatabase([], ['en|legacy|shared' => 'Legacy en']);

        $migrated_last = $this->language(['legacy' => ['shared' => 'Legacy']]);
        $migrated_last->loadLanguageModule('legacy');
        $migrated_last->loadLanguageModule('mig_a');
        $this->queries = [];
        $this->assertSame('Mig A en', $migrated_last->translate('shared', null, 'en'));
        $this->assertSame([], $this->queries, 'a migrated module is read from the build, not from lng_data');

        $legacy_last = $this->language(['legacy' => ['shared' => 'Legacy']]);
        $legacy_last->loadLanguageModule('mig_a');
        $legacy_last->loadLanguageModule('legacy');
        $this->assertSame('Legacy', $legacy_last->txt('shared'));
        $this->assertSame('Legacy en', $legacy_last->translate('shared', null, 'en'));
        $this->assertCount(1, $this->queries, 'exactly one lookup, in the module loaded last');
        $this->assertStringContainsString("module = 'legacy'", $this->queries[0]);
    }

    public function testAModuleOnlyReadThroughAnIdentifierIsNotResponsibleForAString(): void
    {
        $this->stubDatabase([], ['fr|legacy|shared' => 'Legacy fr']);
        $language = $this->language(['legacy' => ['shared' => 'Legacy']]);

        $this->assertSame('Legacy', $language->translate(TranslateTestLegacyIdentifier::SHARED));
        $this->queries = [];

        $this->assertSame('-shared-', $language->translate('shared', null, 'fr'));
        $this->assertSame([], $this->queries, 'no module loaded, so none responsible: no lookup at all');
    }

    /** @return iterable<string, array{string}> */
    public static function invalidLanguageKeys(): iterable
    {
        foreach (['../x', 'DE', '', 'deu', 'd', 'de/', "fr' OR '1", 'x.'] as $lang) {
            yield var_export($lang, true) => [$lang];
        }
    }

    #[DataProvider('invalidLanguageKeys')]
    public function testAnInvalidLanguageIsNeverLookedUp(string $lang): void
    {
        $this->stubDatabase(['de|legacy' => ['shared' => 'Legacy']], ['en|legacy|shared' => 'Legacy en']);
        $language = $this->language();
        $language->loadLanguageModule('mig_a');
        $language->loadLanguageModule('legacy');
        $language->loadLanguageModule('mig_b');
        $this->queries = [];

        $this->assertSame('-shared-', $language->translate('shared', null, $lang), 'string: no text');
        $this->assertSame('Mig A en', $language->translate(TranslateTestMigAIdentifier::SHARED, null, $lang), 'migrated enum: default language');
        $this->assertSame('Mig A en', $language->translate(TranslateTestMigAIdentifier::SHARED, 1, $lang));
        $this->assertSame('Legacy en', $language->translate(TranslateTestLegacyIdentifier::SHARED, null, $lang), 'legacy enum: default language');
        $this->assertSame('-missing-', $language->translate(TranslateTestLegacyIdentifier::MISSING, null, $lang));

        foreach ($this->queries as $sql) {
            $this->assertStringNotContainsString("lang_key = '" . $lang . "'", $sql);
            $this->assertStringContainsString("lang_key = 'en'", $sql, 'only the default language is queried');
        }
    }

    public function testAnInvalidLanguageIsLoggedWithoutItsValue(): void
    {
        $logger = $this->createStub(ilLogger::class);
        $messages = [];
        $logger->method('debug')->willReturnCallback(static function (string $message) use (&$messages): void {
            $messages[] = $message;
        });
        $language = $this->language();
        (new ReflectionProperty(ilLanguage::class, 'log'))->setValue($language, $logger);

        $language->translate('nothing', null, "../x\nINJECTED");

        $this->assertCount(1, $messages);
        $this->assertStringNotContainsString('INJECTED', $messages[0]);
    }

    public function testAnEmptyIdentifierIsEmptyAndNotRecorded(): void
    {
        $this->forbidDatabaseQueries();
        $language = $this->language();
        self::enableUsageLog($language);

        $this->assertSame('', $language->translate(''));
        $this->assertSame('', $language->translate('', 3, 'fr'));
        $this->assertSame('', $language->translate(TranslateTestMigAIdentifier::EMPTY));
        $this->assertSame('', $language->translate(TranslateTestMigAIdentifier::EMPTY, 2, 'en'));
        $this->assertSame('', $language->translate('0'), 'empty() as in txt()');
        $this->assertSame('', $language->translate('0', 2, 'fr'));
        $this->assertSame('', $language->translate(TranslateTestMigAIdentifier::NUL));
        $this->assertSame('', $language->translate(TranslateTestMigAIdentifier::NUL, 1, 'en'));

        $this->assertSame([], $language->getUsedTopics());
        $this->assertSame([], $language->getUsedModules());
        $this->assertSame([], self::usageLog());
        self::enableUsageLog($language, false);
    }

    public function testUsageIsRecordedForTopicsAlwaysAndForModulesAndLogOnlyOnAHit(): void
    {
        $language = $this->language(['legacy' => ['shared' => 'Legacy']]);
        self::enableUsageLog($language);

        $language->translate(TranslateTestMigAIdentifier::MISSING);
        $this->assertSame(['missing' => 'missing'], $language->getUsedTopics());
        $this->assertSame([], $language->getUsedModules(), 'no hit, no module');
        $this->assertSame([], self::usageLog());

        $language->translate(TranslateTestMigAIdentifier::SHARED);
        $language->translate(TranslateTestLegacyIdentifier::SHARED);

        $this->assertEqualsCanonicalizing(['mig_a' => 'mig_a', 'legacy' => 'legacy'], $language->getUsedModules());
        $this->assertSame(['shared' => 'legacy'], self::usageLog(), 'the identifier is logged for the module read last');
        $this->assertSame(['missing' => 'missing', 'shared' => 'shared'], $language->getUsedTopics());
        self::enableUsageLog($language, false);
    }

    public function testUsageLogIsOffWhenDisabledAndRecordsTheModuleOfAFallbackHit(): void
    {
        $language = $this->language();

        $language->translate(TranslateTestMigAIdentifier::ONLY_EN);
        $this->assertSame([], self::usageLog(), 'usage log disabled');
        $this->assertSame(['mig_a' => 'mig_a'], $language->getUsedModules());

        self::enableUsageLog($language);
        $language->translate('only_a', null, 'en');
        $this->assertSame([], self::usageLog(), 'a miss is not logged');
        $language->loadLanguageModule('mig_a');
        $language->translate('only_a');
        $this->assertSame(['only_a' => 'mig_a'], self::usageLog());
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
