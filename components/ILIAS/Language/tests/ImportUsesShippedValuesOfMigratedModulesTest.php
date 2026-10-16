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
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * For a PO module the shipped `.po` is the only source of its shipped values: in the "clear" import
 * (importLanguageFile(..., 'replace', true)) and in _saveValues()' local_change comparison. Without the refresh flag
 * (uploaded file) the file's own values are kept.
 *
 * Separate processes, because CLIENT_DATA_DIR cannot be redefined and an earlier test class of a full-suite run
 * may already have defined it as /var/iliasdata, which would make every test skip.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ImportUsesShippedValuesOfMigratedModulesTest extends ilLanguageBaseTestCase
{
    private const string LANG = 'zz';

    /** Nullable so tearDown() survives a skip in setUp() before the assignment (an uninitialized typed property would be a fatal \Error). */
    private ?string $fixture_directory = null;
    /** True if this test defined CLIENT_DATA_DIR itself; tearDown() removes only such a root. */
    private bool $created_client_data_dir_root = false;
    private string $upload_file;
    /** @var array<string, array<string, string>> module => lang_array written to lng_modules */
    private array $lng_modules = [];
    /** @var array<string, ?string> "module#:#identifier" => local_change written to lng_data */
    private array $local_changes = [];
    private ?string $last_lang_array = null;
    /** @var array<string, ?string> "module#:#identifier" => remarks written to lng_data */
    private array $replaced_remarks = [];
    /** @var list<array{module: string, identifier: string, value: string}> lng_data rows _getValues() reads */
    private array $db_rows = [];
    /** @var list<string> every writing database call (manipulate/insert/replace), in call order */
    private array $writes = [];
    /** Whether the "SELECT lang_array FROM lng_modules" lookup of _deleteValues() answers with $lng_modules_row */
    private bool $answer_lng_modules_lookup = false;
    /** @var array{lang_array: mixed}|null the lng_modules row that lookup returns (null = no row) */
    private ?array $lng_modules_row = null;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'ILIAS_ABSOLUTE_PATH' => realpath(__DIR__ . '/../../../../'),
            'ILIAS_HTTP_PATH' => 'http://localhost',
            'ILIAS_VERSION' => 'test',
        ] as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }
        // writes the overlay under CLIENT_DATA_DIR on every test: a foreign CLIENT_DATA_DIR must skip, not be written to
        $this->created_client_data_dir_root = MigratedPoFixture::ensureClientDataDirDefinedOrSkip($this);

        (new ReflectionClass(ilCachedLanguage::class))->getProperty('instances')->setValue(null, []);
        MigratedPoFixture::resetRuntime();

        $this->fixture_directory = __DIR__ . '/tmp-import-fixtures-' . bin2hex(random_bytes(4));
        MigratedPoFixture::writePo(
            $this->fixture_directory . '/itest_' . self::LANG . '.po',
            MigratedPoFixture::catalog('itest', ['greeting' => 'Aus der PO', 'farewell' => 'Tschüss aus der PO'])
        );
        $this->setGlobalVariable(
            LanguageFileDirectoryManager::class,
            new LanguageFileDirectoryManager(
                new CustomizingLanguageFileDirectory(),
                MigratedPoFixture::directory('itest', 'components/ILIAS/Language/tests/' . basename($this->fixture_directory) . '/')
            )
        );
        $this->setGlobalVariable('lng', (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor());
        $this->setGlobalVariable('ilErr', $this->createStub(ilErrorHandling::class));
        $this->setGlobalVariable('ilDB', $this->database());

        $this->upload_file = $this->fixture_directory . '/ilias_' . self::LANG . '.lang';
        file_put_contents(
            $this->upload_file,
            "/* header */\n<!-- language file start -->\n"
            . "itest#:#greeting#:#Aus der lang-Datei\n"
            . "common#:#yes#:#Ja\n"
        );
    }

    protected function tearDown(): void
    {
        if ($this->fixture_directory !== null) {
            MigratedPoFixture::removeDirectory($this->fixture_directory);
            MigratedPoFixture::removeDirectory(rtrim(CLIENT_DATA_DIR, '/') . '/lang');
        }
        // never remove a foreign CLIENT_DATA_DIR
        if ($this->created_client_data_dir_root && defined('CLIENT_DATA_DIR') && is_dir(CLIENT_DATA_DIR)) {
            MigratedPoFixture::removeDirectory(CLIENT_DATA_DIR);
        }
        (new ReflectionClass(ilLanguageFile::class))->getProperty('global_file_objects')->setValue(null, []);

        parent::tearDown();
    }

    /** @param array<string, string> $values "module#:#identifier" => value of lang/ilias_zz.lang */
    private function seedGlobalLanguageFile(array $values): void
    {
        $global = (new ReflectionClass(ilLanguageFile::class))->newInstanceWithoutConstructor();
        $global->setAllValues($values);
        $global->setAllComments([]);
        (new ReflectionClass(ilLanguageFile::class))->getProperty('global_file_objects')->setValue(null, [self::LANG => $global]);
    }

    private function database(): ilDBInterface
    {
        $self_check = $this->createStub(ilDBStatement::class);

        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($value): string => "'" . (string) $value . "'");
        $db->method('in')->willReturn('1 = 1');
        $lng_modules_lookup = $this->createStub(ilDBStatement::class);
        $db->method('query')->willReturnCallback(function (string $sql) use ($lng_modules_lookup): ilDBStatement {
            if ($this->answer_lng_modules_lookup && str_starts_with($sql, /** @lang text */ 'SELECT lang_array FROM lng_modules')) {
                return $lng_modules_lookup;
            }
            $rows = str_starts_with($sql, 'SELECT module, identifier, value FROM lng_data') ? $this->db_rows : [];
            $statement = $this->createStub(ilDBStatement::class);
            $statement->method('fetchRow')->willReturnCallback(static function () use (&$rows): ?array {
                return array_shift($rows);
            });
            return $statement;
        });
        $db->method('queryF')->willReturn($self_check);
        $db->method('manipulate')->willReturnCallback(function (string $sql): int {
            $this->writes[] = $sql;
            return 1;
        });
        $db->method('fetchAssoc')->willReturnCallback(
            fn(ilDBStatement $statement): ?array => match ($statement) {
                $self_check => ['lang_array' => $this->last_lang_array],
                $lng_modules_lookup => $this->lng_modules_row,
                default => null,
            }
        );
        $db->method('insert')->willReturnCallback(function (string $table, array $values): int {
            $this->writes[] = 'insert ' . $table;
            if ($table === 'lng_modules') {
                $this->last_lang_array = $values['lang_array'][1];
                $this->lng_modules[$values['module'][1]] = unserialize($values['lang_array'][1]);
            }
            return 1;
        });
        $db->method('replace')->willReturnCallback(function (string $table, array $keys, array $values): int {
            $this->writes[] = 'replace ' . $table;
            $this->local_changes[$keys['module'][1] . '#:#' . $keys['identifier'][1]] = $values['local_change'][1];
            $this->replaced_remarks[$keys['module'][1] . '#:#' . $keys['identifier'][1]] = $values['remarks'][1] ?? null;
            return 1;
        });

        return $db;
    }

    private function languageObject(): ilObjLanguageExt
    {
        $object = (new ReflectionClass(ilObjLanguageExt::class))->newInstanceWithoutConstructor();
        $object->key = self::LANG;
        $object->separator = '#:#';

        return $object;
    }

    public function testAnImportWithoutRefreshKeepsTheValuesOfTheFile(): void
    {
        $this->seedGlobalLanguageFile([]);

        $this->languageObject()->importLanguageFile($this->upload_file, 'replace');

        $this->assertSame(['greeting' => 'Aus der lang-Datei', 'farewell' => 'Tschüss aus der PO'], $this->lng_modules['itest']);
    }

    public function testSaveValuesComparesAMigratedModuleAgainstTheShippedPoNotTheLangFile(): void
    {
        $this->seedGlobalLanguageFile([
            'itest#:#greeting' => 'Veraltet in lang/',
            'common#:#yes' => 'Ja',
        ]);
        $this->db_rows = [
            ['module' => 'itest', 'identifier' => 'greeting', 'value' => 'Alt in der DB'],
            ['module' => 'common', 'identifier' => 'yes', 'value' => 'Alt in der DB'],
        ];
        MigratedPoFixture::writePair(
            $this->overlayDirectory() . '/itest_' . self::LANG,
            MigratedPoFixture::catalog('itest', ['greeting' => 'Alt im Overlay'])
        );

        ilObjLanguageExt::_saveValues(self::LANG, [
            'itest#:#greeting' => 'Aus der PO',
            'common#:#yes' => 'Ja',
        ]);

        $this->assertNull($this->local_changes['itest#:#greeting'], 'equals the shipped .po value: not a local change');
        $this->assertNull($this->local_changes['common#:#yes'], 'non-migrated: compared against lang/ as before');
    }

    public function testSaveValuesMarksAValueThatOnlyEqualsTheStaleLangLineAsLocalChange(): void
    {
        $this->seedGlobalLanguageFile(['itest#:#greeting' => 'Veraltet in lang/']);
        $this->db_rows = [['module' => 'itest', 'identifier' => 'greeting', 'value' => 'Alt in der DB']];

        ilObjLanguageExt::_saveValues(self::LANG, ['itest#:#greeting' => 'Veraltet in lang/']);

        $this->assertNotNull($this->local_changes['itest#:#greeting']);
    }

    /** The table sends no `#.` note of the shipped .po as remark: an unchanged entry with one is no local change. */
    public function testSavingAnUnchangedEntryWithAShippedNoteIsNoLocalChange(): void
    {
        $this->seedGlobalLanguageFile([]);
        $catalog = MigratedPoFixture::catalog('itest', ['greeting' => 'Aus der PO']);
        $catalog->find('itest', 'greeting')?->addExtractedComment('Notiz des Entwicklers');
        MigratedPoFixture::writePo($this->fixture_directory . '/itest_' . self::LANG . '.po', $catalog);

        ilObjLanguageExt::_saveValues(self::LANG, ['itest#:#greeting' => 'Aus der PO'], ['itest#:#greeting' => '']);
        $this->assertArrayNotHasKey('itest#:#greeting', $this->local_changes, 'unchanged value and remark: nothing written');

        ilObjLanguageExt::_saveValues(self::LANG, ['itest#:#greeting' => 'Geändert'], ['itest#:#greeting' => '']);
        $this->assertNotNull($this->local_changes['itest#:#greeting'], 'a changed value');
    }

    public function testSavingALocallyChangedValueBackToTheShippedOneEndsTheLocalChange(): void
    {
        $this->seedGlobalLanguageFile([]);
        MigratedPoFixture::writePair(
            $this->overlayDirectory() . '/itest_' . self::LANG,
            MigratedPoFixture::catalog('itest', ['greeting' => 'Lokal'])
        );
        $manager = $GLOBALS['DIC'][LanguageFileDirectoryManager::class];
        $this->assertTrue(MigratedLanguageFileSync::hasOverlay($manager, self::LANG, 'itest', CLIENT_DATA_DIR), 'precondition');

        ilObjLanguageExt::_saveValues(self::LANG, ['itest#:#greeting' => 'Aus der PO'], ['itest#:#greeting' => '']);

        $this->assertArrayHasKey('itest#:#greeting', $this->local_changes);
        $this->assertNull($this->local_changes['itest#:#greeting']);
        $this->assertFalse(MigratedLanguageFileSync::hasOverlay($manager, self::LANG, 'itest', CLIENT_DATA_DIR), 'no delta left');
    }

    public function testChangingOnlyTheRemarkOfAnEntryWithAShippedNoteIsNoLocalChange(): void
    {
        $this->seedGlobalLanguageFile([]);
        $catalog = MigratedPoFixture::catalog('itest', ['greeting' => 'Aus der PO']);
        $catalog->find('itest', 'greeting')?->addExtractedComment('Notiz des Entwicklers');
        MigratedPoFixture::writePo($this->fixture_directory . '/itest_' . self::LANG . '.po', $catalog);

        ilObjLanguageExt::_saveValues(self::LANG, ['itest#:#greeting' => 'Aus der PO'], ['itest#:#greeting' => 'Eigene Bemerkung']);

        $this->assertArrayNotHasKey('itest#:#greeting', $this->local_changes);
        $this->assertNotEmpty(array_filter($this->writes, static fn(string $sql): bool => str_contains($sql, 'remarks') && str_contains($sql, 'Eigene Bemerkung')));
    }

    private function shipPoWithNote(string $note = 'Notiz des Entwicklers'): void
    {
        $catalog = MigratedPoFixture::catalog('itest', ['greeting' => 'Aus der PO']);
        $catalog->find('itest', 'greeting')?->addExtractedComment($note);
        MigratedPoFixture::writePo($this->fixture_directory . '/itest_' . self::LANG . '.po', $catalog);
    }

    private function overlayRemark(string $identifier): ?string
    {
        $manager = $GLOBALS['DIC'][LanguageFileDirectoryManager::class];

        return (MigratedLanguageFileSync::loadRemarks($manager, self::LANG, 'itest', CLIENT_DATA_DIR) ?? [])[$identifier] ?? null;
    }

    public function testSavingTheShippedNoteUnchangedWritesNothing(): void
    {
        $this->seedGlobalLanguageFile([]);
        $this->shipPoWithNote();

        ilObjLanguageExt::_saveValues(self::LANG, ['itest#:#greeting' => 'Aus der PO'], ['itest#:#greeting' => 'Notiz des Entwicklers']);

        $this->assertSame([], $this->local_changes);
        $this->assertSame([], array_filter($this->writes, static fn(string $sql): bool => str_contains($sql, 'remarks')));
        $this->assertNull($this->overlayRemark('greeting'));
    }

    public function testSavingALongShippedNoteCutToTheFieldLengthWritesNothing(): void
    {
        $this->seedGlobalLanguageFile([]);
        $note = str_repeat('n', 300);
        $this->shipPoWithNote($note);

        ilObjLanguageExt::_saveValues(self::LANG, ['itest#:#greeting' => 'Aus der PO'], ['itest#:#greeting' => substr($note, 0, 250)]);

        $this->assertNull($this->overlayRemark('greeting'));
        $this->assertSame([], array_filter($this->writes, static fn(string $sql): bool => str_contains($sql, 'remarks')));
    }

    public function testANewRemarkOverANoteIsStoredLocallyAndInTheOverlay(): void
    {
        $this->seedGlobalLanguageFile([]);
        $this->shipPoWithNote();

        ilObjLanguageExt::_saveValues(self::LANG, ['itest#:#greeting' => 'Aus der PO'], ['itest#:#greeting' => 'Eigene Bemerkung']);

        $this->assertNotEmpty(array_filter($this->writes, static fn(string $sql): bool => str_contains($sql, 'remarks') && str_contains($sql, 'Eigene Bemerkung')));
        $this->assertSame('Eigene Bemerkung', $this->overlayRemark('greeting'));
    }

    public function testAnEmptyRemarkRemovesTheLocalRemarkAndOtherwiseWritesNothing(): void
    {
        $this->seedGlobalLanguageFile([]);
        $this->shipPoWithNote();
        $catalog = MigratedPoFixture::catalog('itest', ['greeting' => 'Aus der PO']);
        \ILIAS\Language\ComponentTranslation\LocalChangeComments::setRemark($catalog->find('itest', 'greeting'), 'Lokal');
        MigratedPoFixture::writePair($this->overlayDirectory() . '/itest_' . self::LANG, $catalog);
        $this->assertSame('Lokal', $this->overlayRemark('greeting'), 'precondition');

        ilObjLanguageExt::_saveValues(self::LANG, ['itest#:#greeting' => 'Aus der PO'], ['itest#:#greeting' => '']);

        $this->assertNull($this->overlayRemark('greeting'));
        $this->assertNotEmpty(array_filter($this->writes, static fn(string $sql): bool => str_contains($sql, 'remarks')));
    }

    public function testAnEmptyRemarkWithoutALocalOneWritesNothing(): void
    {
        $this->seedGlobalLanguageFile([]);
        $this->shipPoWithNote();

        ilObjLanguageExt::_saveValues(self::LANG, ['itest#:#greeting' => 'Aus der PO'], ['itest#:#greeting' => '']);

        $this->assertSame([], array_filter($this->writes, static fn(string $sql): bool => str_contains($sql, 'remarks') || str_contains($sql, 'lng_data')));
        $this->assertSame([], $this->local_changes);
    }

    public function testSavingTheNoteOfEveryFormOfAPluralMessageUnchangedWritesNothing(): void
    {
        $this->seedGlobalLanguageFile([]);
        $catalog = MigratedPoFixture::catalog('itest', []);
        $catalog->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
        $entry = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry('itest', 'item');
        $entry->setPlural('items', ['Eintrag', 'Einträge']);
        $entry->addExtractedComment('Notiz zur Nachricht');
        $catalog->add($entry);
        MigratedPoFixture::writePo($this->fixture_directory . '/itest_' . self::LANG . '.po', $catalog);

        ilObjLanguageExt::_saveValues(
            self::LANG,
            ['itest#:#item [0]' => 'Eintrag', 'itest#:#item [1]' => 'Einträge'],
            ['itest#:#item [0]' => 'Notiz zur Nachricht', 'itest#:#item [1]' => 'Notiz zur Nachricht']
        );

        $this->assertNull($this->overlayRemark('item'));
        $this->assertSame([], array_filter($this->writes, static fn(string $sql): bool => str_contains($sql, 'remarks')));
        $this->assertSame([], $this->local_changes);
    }

    public function testAnImportedCommentEqualToTheShippedNoteIsNoLocalRemark(): void
    {
        $this->seedGlobalLanguageFile([]);
        $this->shipPoWithNote();
        file_put_contents(
            $this->upload_file,
            "<!-- language file start -->\nitest#:#greeting#:#Aus der PO###Notiz des Entwicklers\n"
        );

        $this->languageObject()->importLanguageFile($this->upload_file, 'delete');

        $this->assertNull($this->overlayRemark('greeting'));
        $this->assertNotContains('Notiz des Entwicklers', $this->replaced_remarks);
    }

    /** "Delete" mode wipes lng_data and with it the overlay's remarks: only the imported ones remain. */
    public function testDeleteModeImportDropsTheFormerOverlayRemarksAndKeepsTheImportedOnes(): void
    {
        $this->seedGlobalLanguageFile([]);
        $overlay = MigratedPoFixture::catalog('itest', ['greeting' => 'Lokal', 'farewell' => 'Tschüss aus der PO']);
        \ILIAS\Language\ComponentTranslation\LocalChangeComments::setRemark($overlay->find('itest', 'greeting'), 'Alte Bemerkung');
        \ILIAS\Language\ComponentTranslation\LocalChangeComments::setRemark($overlay->find('itest', 'farewell'), 'Alte Bemerkung 2');
        MigratedPoFixture::writePair($this->overlayDirectory() . '/itest_' . self::LANG, $overlay);
        $this->assertSame('Alte Bemerkung 2', $this->overlayRemark('farewell'), 'precondition');
        file_put_contents(
            $this->upload_file,
            "<!-- language file start -->\nitest#:#greeting#:#Lokal###Importierte Bemerkung\nitest#:#farewell#:#Tschüss aus der PO\n"
        );

        $this->languageObject()->importLanguageFile($this->upload_file, 'delete');

        $this->assertSame('Importierte Bemerkung', $this->overlayRemark('greeting'));
        $this->assertNull($this->overlayRemark('farewell'), 'not imported, so gone with the wiped lng_data');
    }

    /** The remark of a plural message is one for the whole message: imported with its forms, it replaces the former one. */
    public function testDeleteModeImportReplacesTheOverlayRemarkOfAPluralMessage(): void
    {
        $this->seedGlobalLanguageFile([]);
        $catalog = MigratedPoFixture::catalog('itest', []);
        $catalog->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
        $entry = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry('itest', 'item');
        $entry->setPlural('items', ['Eintrag', 'Einträge']);
        $catalog->add($entry);
        MigratedPoFixture::writePo($this->fixture_directory . '/itest_' . self::LANG . '.po', $catalog);
        $overlay = clone $catalog;
        \ILIAS\Language\ComponentTranslation\LocalChangeComments::setRemark($overlay->find('itest', 'item'), 'Alte Bemerkung');
        MigratedPoFixture::writePair($this->overlayDirectory() . '/itest_' . self::LANG, $overlay);
        $this->assertSame('Alte Bemerkung', $this->overlayRemark('item'), 'precondition');
        file_put_contents(
            $this->upload_file,
            "<!-- language file start -->\nitest#:#item [0]#:#Eintrag###Neue Bemerkung\nitest#:#item [1]#:#Einträge###Neue Bemerkung\n"
        );

        $this->languageObject()->importLanguageFile($this->upload_file, 'delete');

        $this->assertSame('Neue Bemerkung', $this->overlayRemark('item'));
    }

    /** "Delete" mode wiped lng_data: every file line is written for a migrated module too, a deviation from the shipped value is a local change. */
    public function testDeleteModeImportWritesEveryRowOfAMigratedModule(): void
    {
        $this->seedGlobalLanguageFile([]);
        MigratedPoFixture::writePair(
            $this->overlayDirectory() . '/itest_' . self::LANG,
            MigratedPoFixture::catalog('itest', ['greeting' => 'Lokal'])
        );
        file_put_contents(
            $this->upload_file,
            "<!-- language file start -->\nitest#:#greeting#:#Lokal\nitest#:#farewell#:#Tschüss aus der PO\n"
        );

        $this->languageObject()->importLanguageFile($this->upload_file, 'delete');

        $this->assertNotNull($this->local_changes['itest#:#greeting'] ?? null, 'differs from the shipped value');
        $this->assertArrayHasKey('itest#:#farewell', $this->local_changes, 'equal to the shipped value, written anyway');
        $this->assertNull($this->local_changes['itest#:#farewell']);
    }

    public static function importModes(): array
    {
        return [
            'keepall' => ['keepall'],
            'keepnew' => ['keepnew'],
            'replace' => ['replace'],
            'delete' => ['delete'],
        ];
    }

    public function testAnImportWithoutRefreshIsNotBlockedByAnUnreadableShippedPo(): void
    {
        $this->seedGlobalLanguageFile([]);
        file_put_contents($this->fixture_directory . '/itest_' . self::LANG . '.po', "msgid \"kaputt\n");
        $this->stubLogger();

        set_error_handler(static fn(): bool => true, E_WARNING);
        try {
            $this->languageObject()->importLanguageFile($this->upload_file, 'replace');
        } finally {
            restore_error_handler();
        }

        // the content of a shipped module is never rebuilt from the database: its module row is not written
        $this->assertArrayNotHasKey('itest', $this->lng_modules);
    }

    private function overlayDirectory(): string
    {
        return rtrim(CLIENT_DATA_DIR, '/') . '/lang/itest/' . self::LANG;
    }

    /** Root-proof: a regular file occupies the overlay directory, so no user can create it. Removed by the returned closure. */
    private function blockOverlayDirectory(): \Closure
    {
        $blocked = $this->overlayDirectory();
        if (!is_dir(dirname($blocked))) {
            mkdir(dirname($blocked), 0775, true);
        }
        file_put_contents($blocked, 'not a directory');

        return static function () use ($blocked): void {
            if (is_file($blocked)) {
                unlink($blocked);
            }
        };
    }

    private function stubLogger(): ilLogger&\PHPUnit\Framework\MockObject\Stub
    {
        $logger = $this->createStub(ilLogger::class);
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);

        return $logger;
    }

    /** The database is written regardless (dual-write), the module whose overlay failed is returned by the import and _saveValues(). */
    public function testAnImportReturnsTheMigratedModuleWhoseOverlayCouldNotBeWritten(): void
    {
        $this->seedGlobalLanguageFile([]);
        $this->stubLogger();
        $unblock = $this->blockOverlayDirectory();

        set_error_handler(static fn(): bool => true, E_WARNING);
        try {
            $unwritten = $this->languageObject()->importLanguageFile($this->upload_file, 'replace');
        } finally {
            restore_error_handler();
            $unblock();
        }

        $this->assertSame(['itest'], $unwritten);
        $this->assertSame(['yes' => 'Ja'], $this->lng_modules['common'], 'the database is written regardless');
        $this->assertSame(
            ['farewell' => 'Tschüss aus der PO', 'greeting' => 'Aus der lang-Datei'],
            $this->sorted($this->lng_modules['itest'])
        );
    }

    public function testSaveValuesReturnsOnlyTheMigratedModuleWhoseOverlayCouldNotBeWrittenAndLogsIt(): void
    {
        $this->seedGlobalLanguageFile(['common#:#yes' => 'Ja']);
        $logger = $this->createMock(ilLogger::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('"itest"'));
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);
        $unblock = $this->blockOverlayDirectory();

        set_error_handler(static fn(): bool => true, E_WARNING);
        try {
            $unwritten = ilObjLanguageExt::_saveValues(self::LANG, [
                'itest#:#greeting' => 'Servus',
                'common#:#yes' => 'Jawohl',
            ]);
        } finally {
            restore_error_handler();
            $unblock();
        }

        $this->assertSame(['itest'], $unwritten);
        $this->assertSame(
            ['greeting' => 'Servus', 'farewell' => 'Tschüss aus der PO'],
            $this->lng_modules['itest'],
            'the database is written regardless'
        );
        $this->assertSame(['yes' => 'Jawohl'], $this->lng_modules['common']);
    }

    public function testSaveValuesReturnsNothingWhenEveryOverlayWasWritten(): void
    {
        $this->seedGlobalLanguageFile([]);

        $this->assertSame([], ilObjLanguageExt::_saveValues(self::LANG, ['itest#:#greeting' => 'Servus', 'common#:#yes' => 'Ja']));
        $this->assertFileExists(MigratedPoFixture::overlayMo($this->overlayDirectory() . '/itest_' . self::LANG));
    }

    /** replaceLangModule() reports an overlay failure as `false` (database written), `true` for a non-PO module. */
    public function testReplaceLangModuleReturnsFalseOnlyForAMigratedModuleWhoseOverlayCouldNotBeWritten(): void
    {
        $this->stubLogger();
        $unblock = $this->blockOverlayDirectory();

        set_error_handler(static fn(): bool => true, E_WARNING);
        try {
            $migrated = ilObjLanguage::replaceLangModule(self::LANG, 'itest', ['greeting' => 'Servus']);
            $not_migrated = ilObjLanguage::replaceLangModule(self::LANG, 'common', ['yes' => 'Ja']);
        } finally {
            restore_error_handler();
            $unblock();
        }

        $this->assertFalse($migrated);
        $this->assertTrue($not_migrated);
        $this->assertSame(['greeting' => 'Servus'], $this->lng_modules['itest'], 'the database is written regardless');

        $this->assertTrue(ilObjLanguage::replaceLangModule(self::LANG, 'itest', ['greeting' => 'Servus']), 'writable again');
    }

    /** The GUI's "clear" asks getUnreadableShippedPoModules() after the exception: answered from the per-object cache, without parsing and logging again. */

    /** @param list<string> $warnings collects every PHP warning/notice raised during $action */
    private function collectingWarnings(array &$warnings, \Closure $action): mixed
    {
        set_error_handler(static function (int $errno, string $message) use (&$warnings): bool {
            $warnings[] = $message;
            return true;
        });
        try {
            return $action();
        } finally {
            restore_error_handler();
        }
    }

    /** Without an lng_modules row the module is rewritten empty: entries to delete are not written back, no warning. */
    public function testDeleteValuesWithoutLngModulesRowWritesAnEmptyModuleWithoutWarning(): void
    {
        $this->answer_lng_modules_lookup = true;
        $this->lng_modules_row = null;
        $warnings = [];

        $unwritten = $this->collectingWarnings(
            $warnings,
            static fn(): array => ilObjLanguageExt::_deleteValues(self::LANG, ['common#:#yes' => 'Ja', 'common#:#no' => 'Nein'])
        );

        $this->assertSame([], $this->lng_modules['common']);
        $this->assertSame([], $unwritten);
        $this->assertSame([], $warnings);
    }

    public function testDeleteValuesWithAnUnreadableLngModulesRowWritesAnEmptyModule(): void
    {
        $this->answer_lng_modules_lookup = true;
        $this->lng_modules_row = ['lang_array' => null];
        $warnings = [];

        $this->collectingWarnings(
            $warnings,
            static fn(): array => ilObjLanguageExt::_deleteValues(self::LANG, ['common#:#yes' => 'Ja'])
        );

        $this->assertSame([], $this->lng_modules['common']);
        $this->assertSame([], $warnings);
    }

    public function testDeleteValuesKeepsTheRemainingEntriesOfTheLngModulesRow(): void
    {
        $this->answer_lng_modules_lookup = true;
        $this->lng_modules_row = ['lang_array' => serialize(['yes' => 'Ja', 'no' => 'Nein', 'maybe' => 'Vielleicht'])];

        $unwritten = ilObjLanguageExt::_deleteValues(self::LANG, ['common#:#yes' => 'Ja', 'common#:#unknown' => 'x']);

        $this->assertSame(['no' => 'Nein', 'maybe' => 'Vielleicht'], $this->lng_modules['common']);
        $this->assertSame([], $unwritten);
    }

    public function testDeleteValuesReturnsTheMigratedModuleWhoseOverlayCouldNotBeWritten(): void
    {
        $this->stubLogger();
        $this->answer_lng_modules_lookup = true;
        $this->lng_modules_row = ['lang_array' => serialize(['greeting' => 'Hallo', 'farewell' => 'Tschüss'])];
        $unblock = $this->blockOverlayDirectory();

        set_error_handler(static fn(): bool => true, E_WARNING);
        try {
            $unwritten = ilObjLanguageExt::_deleteValues(self::LANG, [
                'itest#:#farewell' => 'Tschüss',
                'common#:#yes' => 'Ja',
            ]);
        } finally {
            restore_error_handler();
            $unblock();
        }

        $this->assertSame([], $unwritten);
        $this->assertSame(['greeting' => 'Aus der PO'], $this->lng_modules['itest'], 'the database is written regardless');
    }

    /**
     * @param array<string, string> $values
     * @return array<string, string>
     */
    private function sorted(array $values): array
    {
        ksort($values);
        return $values;
    }

    /** One valid and one invalid entry, both new keys, so both count as "changed" and the invalid one is checked. */
    private function writeUploadFileWithOneInvalidEntry(): string
    {
        $file = $this->fixture_directory . '/ilias_invalid_' . self::LANG . '.lang';
        file_put_contents(
            $file,
            "/* header */\n<!-- language file start -->\n"
            . "common#:#yes#:#Ja\n"
            . "common#:#bad#:#<script>alert(1)</script>\n"
        );

        return $file;
    }

    /** "delete" mode wipes lng_data/lng_modules up front, so the markup check must run before: a rejected value leaves the database untouched. */
    #[DataProvider('importModes')]
    public function testInvalidMarkupAbortsBeforeAnyChangeIncludingTheDeleteModeWipe(string $mode): void
    {
        $this->seedGlobalLanguageFile([]);
        $file = $this->writeUploadFileWithOneInvalidEntry();

        try {
            $this->languageObject()->importLanguageFile($file, $mode);
            $this->fail('Expected an ilLanguageInvalidMarkupException');
        } catch (ilLanguageInvalidMarkupException $e) {
            $this->assertSame(['common#:#bad'], array_keys($e->getInvalidValues()));
            $this->assertStringNotContainsString('alert(1)', $e->getMessage());
            $this->assertStringNotContainsString('script', $e->getMessage());
        }

        $this->assertSame([], $this->writes, 'no database write at all - not even the "delete" mode wipe');
        $this->assertSame([], $this->lng_modules);
    }

    /** With $skipInvalidMarkup (customizing-file/installation path) the invalid entry is left out and reported, no exception. */
    public function testSkipInvalidMarkupImportsOnlyTheValidEntriesAndReportsTheSkippedOne(): void
    {
        $this->seedGlobalLanguageFile([]);
        $file = $this->writeUploadFileWithOneInvalidEntry();

        $object = $this->languageObject();
        $object->importLanguageFile($file, 'replace', true);

        $this->assertSame('Ja', $this->lng_modules['common']['yes']);
        $this->assertArrayNotHasKey('bad', $this->lng_modules['common']);
        $skipped = $object->getSkippedInvalidMarkupValues();
        $this->assertArrayHasKey('common#:#bad', $skipped);
        $this->assertNotEmpty($skipped['common#:#bad']);
    }

    /** In "keepall"/"keepnew" a kept key must not block the import because the file carries a disallowed value for it: that value is never applied. */
    public function testAnInvalidFileValueForAKeyThatModeKeepsDoesNotBlockTheImport(): void
    {
        $this->seedGlobalLanguageFile(['common#:#yes' => 'Ja']);
        $this->db_rows = [['module' => 'common', 'identifier' => 'yes', 'value' => 'Aus der Datenbank']];
        $file = $this->fixture_directory . '/ilias_kept_' . self::LANG . '.lang';
        file_put_contents(
            $file,
            "/* header */\n<!-- language file start -->\n"
            . "common#:#yes#:#<script>alert(1)</script>\n"
        );

        $this->languageObject()->importLanguageFile($file, 'keepall');

        $this->assertArrayNotHasKey('common', $this->lng_modules, 'the already-correct, kept value needs no rewrite');
    }

    private function directoryManager(): LanguageFileDirectoryManager
    {
        return new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            MigratedPoFixture::directory('itest', 'components/ILIAS/Language/tests/' . basename((string) $this->fixture_directory) . '/')
        );
    }

    /** @return array<string, string> */
    private function loadRemarks(): array
    {
        return \ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync::loadRemarks(
            $this->directoryManager(),
            self::LANG,
            'itest',
            CLIENT_DATA_DIR,
            ILIAS_ABSOLUTE_PATH
        ) ?? [];
    }

    /** A "###" line comment of a migrated module becomes a remark like for any module; importLanguageFile() has no "clear" branch that strips it. */
    public function testImportTakesOverALangLineCommentOfAMigratedModuleAsARemark(): void
    {
        $this->seedGlobalLanguageFile([]);
        file_put_contents(
            $this->upload_file,
            "/* header */\n<!-- language file start -->\n"
            . "itest#:#greeting#:#Aus der lang-Datei###Bitte pruefen\n"
            . "common#:#yes#:#Ja\n"
        );

        $this->languageObject()->importLanguageFile($this->upload_file, 'replace', false);

        $this->assertSame('Bitte pruefen', $this->loadRemarks()['greeting'] ?? null);
    }
}
