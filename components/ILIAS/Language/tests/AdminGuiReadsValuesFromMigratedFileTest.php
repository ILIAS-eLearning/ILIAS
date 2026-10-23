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
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\SkippedWithMessageException;

/**
 * Admin-GUI read side: ilObjLanguageExt::_getValues()/_getModules() read a migrated module from its .po, others from lng_data.
 *
 * Separate processes, because CLIENT_DATA_DIR cannot be redefined and an earlier test class of a full-suite run
 * may already have defined it as /var/iliasdata, which would make every test skip.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class AdminGuiReadsValuesFromMigratedFileTest extends ilLanguageBaseTestCase
{
    private ?string $fixture_directory = null;
    /** True if this test defined CLIENT_DATA_DIR itself; tearDown() removes only such a root. */
    private bool $created_client_data_dir_root = false;
    private array $like_calls = [];
    private array $sent_queries = [];
    /** @var list<string> the modules of every `in("module", …, negate: true)`, which the query stub applies to its rows */
    private array $excluded_modules = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        // CLIENT_DATA_DIR is deliberately not defined here: seedFixtureModule() defines it, and
        // testRefusesToWriteIntoAClientDataDirOutsideSysTempDir() needs it still undefined.
    }

    protected function tearDown(): void
    {
        if (isset($this->fixture_directory)) {
            MigratedPoFixture::removeDirectory(rtrim(CLIENT_DATA_DIR, '/') . '/lang');
            MigratedPoFixture::removeShippedDirectory('components/ILIAS/Language/tests/' . $this->fixture_directory);
        }
        // never remove a foreign CLIENT_DATA_DIR
        if ($this->created_client_data_dir_root && defined('CLIENT_DATA_DIR') && is_dir(CLIENT_DATA_DIR)) {
            MigratedPoFixture::removeDirectory(CLIENT_DATA_DIR);
        }

        parent::tearDown();
    }

    /**
     * Writes a .po/.mo pair for a throwaway module at the overlay location (the only one the read side uses).
     *
     * @param array<string, array{value: string, original?: string}> $entries keyed by identifier;
     *   'original' seeds LocalChangeComments::setOriginal() so a value change counts as local change.
     * @param list<string> $changed_identifiers identifiers whose value differs from 'original' and need a
     *   local_change comment.
     */
    private function seedFixtureModule(
        string $module,
        string $lang_key,
        array $entries,
        array $changed_identifiers = []
    ): LanguageFileDirectory {
        $created = MigratedPoFixture::ensureClientDataDirDefinedOrSkip($this);
        $this->created_client_data_dir_root = $this->created_client_data_dir_root || $created;
        $this->fixture_directory ??= 'tmp-admingui-values-fixtures-' . bin2hex(random_bytes(4));
        $overlay_dir = rtrim(CLIENT_DATA_DIR, '/') . '/lang/' . $module . '/' . $lang_key;
        if (!is_dir($overlay_dir)) {
            mkdir($overlay_dir, 0775, true);
        }

        $now = new DateTimeImmutable('2026-01-02T03:04:05Z', new DateTimeZone('UTC'));
        $translations = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog();
        foreach ($entries as $identifier => $entry) {
            $translation = MigratedPoFixture::entry($module, $identifier, $entry['value']);
            if (isset($entry['original'])) {
                LocalChangeComments::setOriginal($translation, $entry['original']);
            }
            if (in_array($identifier, $changed_identifiers, true)) {
                $previous = $entry['original'] ?? '';
                LocalChangeComments::refresh(
                    $translation,
                    $previous === $entry['value'] ? $previous . ' (old)' : $previous,
                    $entry['value'],
                    $now
                );
            }
            $translations->add($translation);
        }

        $base_path = $overlay_dir . '/' . $module . '_' . $lang_key;
        MigratedPoFixture::writePo($base_path . '.po', $translations);
        MigratedPoFixture::writeOverlayRevision($base_path, $translations);

        $relative_path = 'components/ILIAS/Language/tests/' . $this->fixture_directory . '/';
        MigratedPoFixture::writeShippedPo($relative_path, $module, $lang_key, $translations);

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

    private function overlayFile(string $module, string $lang_key, string $extension): string
    {
        $base = rtrim(CLIENT_DATA_DIR, '/') . '/lang/' . $module . '/' . $lang_key . '/' . $module . '_' . $lang_key;

        return $extension === 'mo' ? MigratedPoFixture::overlayCurrent($base) : $base . '.' . $extension;
    }

    private function registerDirectoryManager(LanguageFileDirectory ...$contributed): void
    {
        $this->setGlobalVariable(
            LanguageFileDirectoryManager::class,
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), ...$contributed)
        );
    }

    /**
     * query() returns a fresh statement per call, so repeated _getValues()/_getModules() calls each see all rows.
     *
     * @param list<array{module: string, identifier: string, value: string}> $db_rows rows lng_data would return
     */
    private function stubDatabase(array $db_rows): void
    {
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(
            static fn(mixed $value, string $type = ''): string => "'" . (string) $value . "'"
        );
        $db->method('in')->willReturnCallback(function (string $field, array $values, bool $negate = false): string {
            if ($negate && $field === 'module') {
                $this->excluded_modules = [...$this->excluded_modules, ...array_map('strval', $values)];
            }

            return "module IN ('placeholder')";
        });
        $db->method('like')->willReturnCallback(function (string $column, string $type, string $value = '?'): string {
            $this->like_calls[] = [$column, $value];
            return "value LIKE '%placeholder%'";
        });
        $db->method('query')->willReturnCallback(function (string $query) use ($db_rows): ilDBStatement {
            $this->sent_queries[] = $query;
            $statement = $this->createStub(ilDBStatement::class);
            $calls = array_values(array_filter(
                $db_rows,
                fn(array $row): bool => !in_array($row['module'], $this->excluded_modules, true)
            ));
            $calls[] = false;
            $statement->method('fetchRow')->willReturnOnConsecutiveCalls(...$calls);

            return $statement;
        });

        $this->setGlobalVariable('ilDB', $db);

        // _getValues() builds its keys from $lng->separator, whose default needs no constructor
        $this->setGlobalVariable('lng', (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor());
    }

    public function testLoadModuleTranslationsReturnsValueAndLocalChangeFlagPerIdentifier(): void
    {
        $directory = $this->seedFixtureModule(
            'mtest',
            'de',
            [
                'greeting' => ['value' => 'Hallo, geändert', 'original' => 'Hallo'],
                'farewell' => ['value' => 'Tschüss', 'original' => 'Tschüss'],
            ],
            ['greeting']
        );
        $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $directory);

        $result = MigratedLanguageFileSync::loadModuleTranslations($manager, 'de', 'mtest', CLIENT_DATA_DIR);

        $this->assertSame(
            [
                'greeting' => [
                    'value' => 'Hallo, geändert',
                    'local_change' => true,
                    'local_change_date' => '2026-01-02 03:04:05',
                    'original' => 'Hallo',
                ],
                'farewell' => [
                    'value' => 'Tschüss',
                    'local_change' => false,
                    'local_change_date' => null,
                    'original' => 'Tschüss',
                ],
            ],
            $result
        );
    }

    public function testLoadModuleTranslationsReturnsNullWhenTheModuleHasNoContributedDirectory(): void
    {
        $this->created_client_data_dir_root = MigratedPoFixture::ensureClientDataDirDefinedOrSkip($this);
        $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory());

        $this->assertNull(
            MigratedLanguageFileSync::loadModuleTranslations($manager, 'de', 'mtest', CLIENT_DATA_DIR)
        );
    }

    public function testLoadModuleTranslationsReturnsTheShippedStateWithoutOverlay(): void
    {
        $directory = $this->seedFixtureModule('mtest', 'de', ['greeting' => ['value' => 'Hallo']]);
        unlink($this->overlayFile('mtest', 'de', 'mo'));
        unlink($this->overlayFile('mtest', 'de', 'po'));
        $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $directory);

        $this->assertSame(
            ['greeting' => ['value' => 'Hallo', 'local_change' => false, 'local_change_date' => null, 'original' => 'Hallo']],
            MigratedLanguageFileSync::loadModuleTranslations($manager, 'de', 'mtest', CLIENT_DATA_DIR)
        );
    }

    public function testGetMigratedModulesListsEveryContributedModuleWithAShippedPo(): void
    {
        $with_mo = $this->seedFixtureModule('mone', 'de', ['greeting' => ['value' => 'Hallo']]);
        $without_mo = $this->seedFixtureModule('mtwo', 'de', ['greeting' => ['value' => 'Hallo']]);
        unlink($this->overlayFile('mtwo', 'de', 'mo'));
        $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $with_mo, $without_mo);

        $this->assertSame(['mone', 'mtwo'], MigratedLanguageFileSync::getMigratedModules($manager, 'de', CLIENT_DATA_DIR));
        $this->assertSame([], MigratedLanguageFileSync::getMigratedModules($manager, 'en', CLIENT_DATA_DIR));
    }

    public function testValueForAMigratedModuleComesFromThePoFileNotFromTheStaleDbRow(): void
    {
        $directory = $this->seedFixtureModule('mtest', 'de', ['greeting' => ['value' => 'Aktuell']]);
        $this->registerDirectoryManager($directory);
        $this->stubDatabase([['module' => 'mtest', 'identifier' => 'greeting', 'value' => 'VERALTET']]);

        $values = ilObjLanguageExt::_getValues('de');

        $this->assertSame(['mtest#:#greeting' => 'Aktuell'], $values);
    }

    public function testValuesForANonMigratedModuleStillComeFromLngData(): void
    {
        $this->stubDatabase([['module' => 'rep_robj_xtst', 'identifier' => 'hello', 'value' => 'Welt']]);

        $values = ilObjLanguageExt::_getValues('de');

        $this->assertSame(['rep_robj_xtst#:#hello' => 'Welt'], $values);
    }

    public function testMergesAMigratedModuleWithANonMigratedOne(): void
    {
        $directory = $this->seedFixtureModule('mtest', 'de', ['greeting' => ['value' => 'Aktuell']]);
        $this->registerDirectoryManager($directory);
        $this->stubDatabase([['module' => 'common', 'identifier' => 'hello', 'value' => 'Welt']]);

        $values = ilObjLanguageExt::_getValues('de');

        $this->assertSame(
            ['common#:#hello' => 'Welt', 'mtest#:#greeting' => 'Aktuell'],
            $values
        );
    }

    public function testAppliesThePatternFilterToTheMigratedModulesValues(): void
    {
        $directory = $this->seedFixtureModule('mtest', 'de', [
            'greeting' => ['value' => 'Hallo Welt'],
            'farewell' => ['value' => 'Tschüss'],
        ]);
        $this->registerDirectoryManager($directory);
        $this->stubDatabase([]);

        $values = ilObjLanguageExt::_getValues('de', [], [], 'Welt');

        $this->assertSame(['mtest#:#greeting' => 'Hallo Welt'], $values);
    }

    /**
     * Search in a migrated module behaves like `UPPER(value) LIKE UPPER('%pattern%')` on lng_data: case-insensitive
     * (also non-ASCII), "%" any sequence, "_" one character, "\" escapes, other regex characters literal.
     *
     * @param list<string> $expected identifiers expected to match
     */
    #[DataProvider('likePatterns')]
    public function testThePatternFilterOfAMigratedModuleFollowsSqlLikeSemantics(string $pattern, array $expected): void
    {
        $directory = $this->seedFixtureModule('mtest', 'de', array_map(static fn(string $v): array => ['value' => $v], self::likeValues()));
        $this->registerDirectoryManager($directory);
        $this->stubDatabase([]);

        $found = array_map(
            static fn(string $key): string => substr($key, strlen('mtest#:#')),
            array_keys(ilObjLanguageExt::_getValues('de', [], [], $pattern))
        );

        sort($found);
        sort($expected);
        $this->assertSame($expected, $found);
    }

    /** @return array<string, string> identifier => value the LIKE patterns are matched against */
    private static function likeValues(): array
    {
        return [
            'plain' => 'Hallo Welt',
            'umlaut' => 'ÄRGER über Öl',
            'percent' => 'Rabatt 50% heute',
            'fifty' => 'Rabatt 50 heute',
            'underscore' => 'snake_case',
            'snakeXcase' => 'snakeXcase',
            'regex' => 'a.b*c (d) [e] $f ^g |h /i',
            'backslash' => 'C:\\temp',
            'multiline' => "erste Zeile\nzweite Zeile",
        ];
    }

    public static function likePatterns(): array
    {
        return [
            'case-insensitive ascii' => ['hallo WELT', ['plain']],
            'case-insensitive umlaut' => ['ärger ÜBER öl', ['umlaut']],
            'substring anywhere' => ['lo We', ['plain']],
            'percent is a wildcard' => ['Hallo%Welt', ['plain']],
            'percent wildcard across the value' => ['Rabatt%heute', ['fifty', 'percent']],
            'escaped percent is literal' => ['50\\%', ['percent']],
            'underscore is exactly one character' => ['snake_case', ['snakeXcase', 'underscore']],
            'underscore does not match zero characters' => ['Hallo_Welt', ['plain']],
            'escaped underscore is literal' => ['snake\\_case', ['underscore']],
            'two underscores need two characters' => ['Hallo__Welt', []],
            'two underscores match exactly two characters' => ['Hall__Welt', ['plain']],
            'regex meta characters are literal' => ['a.b*c (d) [e] $f ^g |h /i', ['regex']],
            'a dot is not a wildcard' => ['Hallo.Welt', []],
            'escaped backslash' => ['C:\\\\temp', ['backslash']],
            'wildcard spans a newline' => ['erste%zweite', ['multiline']],
            'consecutive percent signs match like one' => ['Hallo%%Welt', ['plain']],
            'only percent signs match everything' => ['%%', array_keys(self::likeValues())],
            'escaped percent followed by a wildcard' => ['50\\%%heute', ['percent']],
            'no match' => ['gibt es nicht', []],
        ];
    }

    #[DataProvider('consecutivePercentPatterns')]
    public function testConsecutivePercentSignsMatchLikeASinglePercentSign(string $value, string $pattern, bool $expected): void
    {
        $matches = new ReflectionMethod(ilObjLanguageExt::class, 'matchesLikePattern');

        $this->assertSame($expected, $matches->invoke(null, $value, $pattern));
        if (!str_contains($pattern, '\\')) {
            $this->assertSame(
                $expected,
                $matches->invoke(null, $value, (string) preg_replace('/%+/', '%', $pattern)),
                'same result as with single percent signs'
            );
        }
    }

    public static function consecutivePercentPatterns(): array
    {
        return [
            '%% matches an empty value' => ['', '%%', true],
            '%% matches any value' => ['beliebig', '%%', true],
            'a%%%b: nothing in between' => ['ab', 'a%%%b', true],
            'a%%%b: something in between' => ['aXYZb', 'a%%%b', true],
            'a%%%b: order matters' => ['ba', 'a%%%b', false],
            'a%%%b: across a line break' => ["a\nb", 'a%%%b', true],
            '%%a%% anywhere' => ['xxaxx', '%%a%%', true],
            '%%a%% not contained' => ['xxbxx', '%%a%%', false],
            'escaped percent then wildcard' => ['50% Rabatt', '50\\%%Rabatt', true],
            'escaped percent is still required' => ['50 Rabatt', '50\\%%Rabatt', false],
            'underscore between percent runs' => ['ab', 'a%%_%%b', false],
            'underscore between percent runs, one char' => ['axb', 'a%%_%%b', true],
        ];
    }

    /** Merged-in migrated modules are sorted like lng_data's ORDER BY under *_unicode_ci (a plain ksort() is case-sensitive). */
    public function testValuesAreSortedCaseInsensitivelyByModuleAndIdentifier(): void
    {
        $directory = $this->seedFixtureModule('mtest', 'de', [
            'beta' => ['value' => 'b'],
            'Alpha' => ['value' => 'A'],
            'gamma' => ['value' => 'g'],
            'Delta' => ['value' => 'D'],
        ]);
        $this->registerDirectoryManager($directory);
        $this->stubDatabase([
            ['module' => 'aaa', 'identifier' => 'Zeta', 'value' => 'z'],
            ['module' => 'zzz', 'identifier' => 'eta', 'value' => 'e'],
        ]);

        $this->assertSame(
            ['aaa#:#Zeta', 'mtest#:#Alpha', 'mtest#:#beta', 'mtest#:#Delta', 'mtest#:#gamma', 'zzz#:#eta'],
            array_keys(ilObjLanguageExt::_getValues('de'))
        );
    }

    /** The SQL branch used to check the pattern for truthiness, so "0" returned every row. */
    public function testTheSearchPatternZeroIsAppliedToLngDataAndToMigratedModules(): void
    {
        $directory = $this->seedFixtureModule('mtest', 'de', [
            'with_zero' => ['value' => 'Version 10'],
            'without_zero' => ['value' => 'Version eins'],
        ]);
        $this->registerDirectoryManager($directory);
        $this->stubDatabase([]);

        $values = ilObjLanguageExt::_getValues('de', [], [], '0');

        $this->assertSame(['mtest#:#with_zero' => 'Version 10'], $values);
        $this->assertSame([['value', '%0%']], $this->like_calls);
        $this->assertStringContainsString("value LIKE '%placeholder%'", $this->sent_queries[0]);
    }

    public function testAnEmptySearchPatternDoesNotFilter(): void
    {
        $this->stubDatabase([['module' => 'rep_robj_xtst', 'identifier' => 'hello', 'value' => 'Welt']]);

        $this->assertSame(['rep_robj_xtst#:#hello' => 'Welt'], ilObjLanguageExt::_getValues('de', [], [], ''));
        $this->assertSame([], $this->like_calls);
    }

    public function testAnUnreadableOverlayShowsTheShippedValuesNeverLngDataWithAWarning(): void
    {
        $directory = $this->seedFixtureModule('mtest', 'de', ['greeting' => ['value' => 'Aus dem Overlay']]);
        file_put_contents($this->overlayFile('mtest', 'de', 'po'), "msgid \"kaputt\n");
        $this->registerDirectoryManager($directory);
        $this->stubDatabase([['module' => 'mtest', 'identifier' => 'greeting', 'value' => 'Aus lng_data']]);
        $logger = $this->createMock(ilLogger::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('"mtest"'));
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);

        // the shipped values (the fixture ships the overlay's values), never lng_data
        $this->assertSame(['mtest#:#greeting' => 'Aus dem Overlay'], ilObjLanguageExt::_getValues('de'));
    }

    public function testAppliesTheTopicsFilterToTheMigratedModulesValues(): void
    {
        $directory = $this->seedFixtureModule('mtest', 'de', [
            'greeting' => ['value' => 'Hallo'],
            'farewell' => ['value' => 'Tschüss'],
        ]);
        $this->registerDirectoryManager($directory);
        $this->stubDatabase([]);

        $values = ilObjLanguageExt::_getValues('de', [], ['farewell']);

        $this->assertSame(['mtest#:#farewell' => 'Tschüss'], $values);
    }

    public function testAppliesTheChangedStateFilterToTheMigratedModulesValues(): void
    {
        $directory = $this->seedFixtureModule(
            'mtest',
            'de',
            [
                'greeting' => ['value' => 'Hallo, geändert', 'original' => 'Hallo'],
                'farewell' => ['value' => 'Tschüss', 'original' => 'Tschüss'],
            ],
            ['greeting']
        );
        $this->registerDirectoryManager($directory);
        $this->stubDatabase([]);

        $this->assertSame(
            ['mtest#:#greeting' => 'Hallo, geändert'],
            ilObjLanguageExt::_getValues('de', [], [], '', 'changed')
        );
        $this->assertSame(
            ['mtest#:#farewell' => 'Tschüss'],
            ilObjLanguageExt::_getValues('de', [], [], '', 'unchanged')
        );
    }

    public function testWhenSpecificModulesAreRequestedAMigratedOneAmongThemStillComesFromThePoFile(): void
    {
        $directory = $this->seedFixtureModule('mtest', 'de', ['greeting' => ['value' => 'Aktuell']]);
        $this->registerDirectoryManager($directory);
        $this->stubDatabase([['module' => 'common', 'identifier' => 'hello', 'value' => 'Welt']]);

        $values = ilObjLanguageExt::_getValues('de', ['mtest', 'common']);

        $this->assertSame(
            ['common#:#hello' => 'Welt', 'mtest#:#greeting' => 'Aktuell'],
            $values
        );
    }

    /** Without the directories nothing says which `.po` to read: the rows of a shipped module are dropped (reported), others stay. */
    public function testWithoutDirectoryManagerOnlyTheRowsOfModulesOfNoComponentAreShown(): void
    {
        $this->stubDatabase([
            ['module' => 'poll', 'identifier' => 'hello', 'value' => 'Aus lng_data'],
            ['module' => 'rep_robj_xtst', 'identifier' => 'hello', 'value' => 'Welt'],
        ]);

        $this->expectOutputRegex('/Without the language file directories the values of module "poll" are not shown/');
        $values = ilObjLanguageExt::_getValues('de');

        $this->assertSame(['rep_robj_xtst#:#hello' => 'Welt'], $values);
    }

    /** A shipped `.po` that cannot be read: the module is missing (reported), its lng_data rows are not shown instead. */
    public function testAnUnreadableShippedPoHidesTheModuleAndNeverShowsItsLngDataRows(): void
    {
        $directory = $this->seedFixtureModule('mtest', 'de', ['greeting' => ['value' => 'Hallo']]);
        file_put_contents(
            constant('ILIAS_ABSOLUTE_PATH') . '/components/ILIAS/Language/tests/' . $this->fixture_directory . '/mtest_de.po',
            "msgid \"kaputt\n"
        );
        $this->registerDirectoryManager($directory);
        $this->stubDatabase([
            ['module' => 'mtest', 'identifier' => 'greeting', 'value' => 'Aus lng_data'],
            ['module' => 'rep_robj_xtst', 'identifier' => 'hello', 'value' => 'Welt'],
        ]);

        $this->expectOutputRegex('/The language file of module "mtest" for "de" cannot be read, its values are not shown/');
        $this->assertSame(['rep_robj_xtst#:#hello' => 'Welt'], ilObjLanguageExt::_getValues('de'));
    }

    public function testGetModulesIncludesAMigratedModuleEvenWithoutAnyLngDataRow(): void
    {
        $directory = $this->seedFixtureModule('mtest', 'de', ['greeting' => ['value' => 'Hallo']]);
        $this->registerDirectoryManager($directory);
        $this->stubDatabase([['module' => 'common']]);

        $modules = ilObjLanguageExt::_getModules('de');

        $this->assertSame(['common', 'mtest'], $modules);
    }

    public function testGetModulesFallsBackToDbOnlyBehaviorWhenNoDirectoryManagerIsRegisteredAtAll(): void
    {
        $this->stubDatabase([['module' => 'common']]);

        $this->assertSame(['common'], ilObjLanguageExt::_getModules('de'));
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
                $this->seedFixtureModule('mtest', 'de', ['greeting' => ['value' => 'Hallo']]);
                $this->fail('seedFixtureModule() must refuse to write into a CLIENT_DATA_DIR outside sys_get_temp_dir().');
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
