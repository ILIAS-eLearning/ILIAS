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
use ILIAS\Language\ComponentTranslation\LocalChangeComments;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * The admin GUI's readers of the shipped values (getShippedValues()/getShippedComments() and everything built on them) and
 * the developer mode's "merge" into the global language file: for a PO module its lines in lang/ilias_xx.lang are not its
 * source, the shipped .po is. A migrated module is read from shipped .po plus overlay, every other module from lng_data.
 *
 * Separate processes, because CLIENT_DATA_DIR cannot be redefined and an earlier test class of a full-suite run
 * may already have defined it as /var/iliasdata, which would make every test skip.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ShippedValuesOfMigratedModulesTest extends ilLanguageBaseTestCase
{
    private const string LANG = 'zz';

    /** Nullable so tearDown() survives a skip in setUp() before the assignment (an uninitialized typed property would be a fatal \Error). */
    private ?string $fixture_directory = null;
    private string $global_file;
    /** True if this test defined CLIENT_DATA_DIR itself; tearDown() removes only such a root. */
    private bool $created_client_data_dir_root = false;
    /** @var list<array{module: string, identifier: string, value: string}> */
    private array $value_rows = [];
    /** @var list<array{module: string, identifier: string, remarks: ?string}> */
    private array $remark_rows = [];
    /** @var list<string> modules of the lng_data rows with a local_change, for the "SELECT 1" of hasLocalChangesOutsidePoModules() */
    private array $local_change_modules = [];
    /** @var array<int, list<array<string, ?string>>> rows fetchAssoc() hands out per statement */
    private array $statement_rows = [];
    /** @var list<string> every manipulate() SQL string, in call order */
    private array $manipulate_calls = [];

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
        // registerDirectoryManager() contributes a migrated module in every test, so a foreign CLIENT_DATA_DIR must skip the whole class
        $this->created_client_data_dir_root = MigratedPoFixture::ensureClientDataDirDefinedOrSkip($this);

        (new ReflectionClass(ilCachedLanguage::class))->getProperty('instances')->setValue(null, []);
        MigratedPoFixture::resetRuntime();

        $this->fixture_directory = __DIR__ . '/tmp-shipped-values-fixtures-' . bin2hex(random_bytes(4));
        $shipped = MigratedPoFixture::catalog('itest', [
            'greeting' => 'Aus der PO',
            'po_only' => 'Nur in der PO',
            'farewell' => 'Tschüss',
        ]);
        $shipped->find('itest', 'greeting')->addExtractedComment('Begrüßung auf der Startseite');
        $shipped->find('itest', 'greeting')->addExtractedComment('kurz halten');
        MigratedPoFixture::writePo($this->shippedPo(), $shipped);
        $this->registerDirectoryManager();

        $this->setGlobalVariable('lng', $this->createStub(ilLanguage::class));
        $this->setGlobalVariable('ilDB', $this->database());
        $this->setGlobalVariable('ilUser', $this->createStub(ilObjUser::class));
        $this->setGlobalVariable('ilErr', $this->createStub(ilErrorHandling::class));
        $component_factory = $this->createStub(ilComponentFactory::class);
        $component_factory->method('getActivePluginsInSlot')->willReturn(new ArrayIterator([]));
        $this->setGlobalVariable('component.factory', $component_factory);

        $this->global_file = $this->fixture_directory . '/ilias_' . self::LANG . '.lang';
        $this->writeGlobalFile([
            'common#:#yes#:#Ja###Zustimmung',
            'itest#:#greeting#:#Veraltet in lang/###alter lang-Kommentar',
            'itest#:#stale#:#Nur noch in lang/###Kommentar einer entfernten Variable',
            'itest#:#farewell#:#Tschüss',
            'common#:#no#:#Nein',
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->fixture_directory !== null) {
            MigratedPoFixture::removeDirectory($this->fixture_directory);
        }
        // never remove a foreign CLIENT_DATA_DIR
        if ($this->created_client_data_dir_root && defined('CLIENT_DATA_DIR') && is_dir(CLIENT_DATA_DIR)) {
            MigratedPoFixture::removeDirectory(CLIENT_DATA_DIR);
        }
        (new ReflectionClass(ilLanguageFile::class))->getProperty('global_file_objects')->setValue(null, []);

        parent::tearDown();
    }

    private function shippedPo(): string
    {
        return $this->fixture_directory . '/itest_' . self::LANG . '.po';
    }

    /**
     * Local changes of the migrated module are read from the overlay, so they are seeded there.
     *
     * @param array<string, string> $entries
     */
    private function seedOverlay(array $entries): void
    {
        MigratedPoFixture::writePair(
            rtrim(CLIENT_DATA_DIR, '/') . '/lang/itest/' . self::LANG . '/itest_' . self::LANG,
            MigratedPoFixture::catalog('itest', $entries)
        );
    }

    private function registerDirectoryManager(): void
    {
        $this->setGlobalVariable(
            LanguageFileDirectoryManager::class,
            new LanguageFileDirectoryManager(
                new CustomizingLanguageFileDirectory(),
                MigratedPoFixture::directory('itest', 'components/ILIAS/Language/tests/' . basename($this->fixture_directory) . '/')
            )
        );
    }

    /**
     * Writes lang/ilias_zz.lang and makes it the (freshly read) global language file.
     *
     * @param list<string> $lines raw lines after the header
     */
    private function writeGlobalFile(array $lines): void
    {
        if (!is_dir(dirname($this->global_file))) {
            mkdir(dirname($this->global_file), 0775, true);
        }
        file_put_contents(
            $this->global_file,
            "<?php exit; ?>\n/**\n* @module language file\n*/\n<!-- language file start -->\n" . implode("\n", $lines)
        );
        $global = new ilLanguageFile($this->global_file, self::LANG, 'global');
        $this->assertTrue($global->read(), $global->getErrorMessage());
        (new ReflectionClass(ilLanguageFile::class))->getProperty('global_file_objects')->setValue(null, [self::LANG => $global]);
    }

    private function database(): ilDBInterface
    {
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($value): string => "'" . (string) $value . "'");
        // a negated "in" keeps its values so the fake can evaluate it, the others stay "1 = 1"
        $db->method('in')->willReturnCallback(
            static fn(string $field, array $values, bool $negate = false): string => $negate
                ? $field . ' NOT IN (' . implode(',', $values) . ')'
                : '1 = 1'
        );
        $db->method('query')->willReturnCallback(function (string $sql): ilDBStatement {
            $value_rows = str_starts_with($sql, /** @lang text */ 'SELECT module, identifier, value FROM lng_data') ? $this->value_rows : [];
            $statement = $this->createStub(ilDBStatement::class);
            if (str_starts_with($sql, /** @lang text */ 'SELECT 1 FROM lng_data') && str_contains($sql, 'local_change IS NOT NULL')) {
                preg_match('/module NOT IN \(([^)]*)\)/', $sql, $excluded);
                $outside = array_diff($this->local_change_modules, explode(',', $excluded[1] ?? ''));
                $this->statement_rows[spl_object_id($statement)] = $outside === [] ? [] : [['1' => '1']];
                return $statement;
            }
            $statement->method('fetchRow')->willReturnCallback(static function () use (&$value_rows): ?array {
                return array_shift($value_rows);
            });
            $this->statement_rows[spl_object_id($statement)] = str_starts_with($sql, /** @lang text */ 'SELECT module, identifier, remarks')
                ? $this->remark_rows
                : [];
            return $statement;
        });
        $db->method('fetchAssoc')->willReturnCallback(function (ilDBStatement $statement): ?array {
            return array_shift($this->statement_rows[spl_object_id($statement)]);
        });
        $db->method('manipulate')->willReturnCallback(function (string $sql): int {
            $this->manipulate_calls[] = $sql;
            return 0;
        });

        return $db;
    }

    private function languageObject(): ilObjLanguageExt
    {
        $object = (new ReflectionClass(ilObjLanguageExt::class))->newInstanceWithoutConstructor();
        $object->key = self::LANG;
        $object->separator = '#:#';
        // passed unconditionally to ShippedPoMerger::merge() as backup directory, never written to here (no overlay for "itest")
        $object->cust_lang_path = $this->fixture_directory;

        return $object;
    }

    private function expectAWarningAboutTheUnreadablePo(): void
    {
        $logger = $this->createMock(ilLogger::class);
        $logger->expects($this->atLeastOnce())->method('warning')->with($this->stringContains('"itest"'));
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);
    }

    /**
     * @param array<string, string> $values
     * @return array<string, string>
     */
    private static function sorted(array $values): array
    {
        ksort($values);
        return $values;
    }

    public function testShippedValuesTakeAMigratedModuleFromItsShippedPoInsteadOfItsLangLines(): void
    {
        $object = $this->languageObject();

        $this->assertSame(
            [
                'common#:#no' => 'Nein',
                'common#:#yes' => 'Ja',
                'itest#:#farewell' => 'Tschüss',
                'itest#:#greeting' => 'Aus der PO',
                'itest#:#po_only' => 'Nur in der PO',
            ],
            self::sorted($object->getShippedValues())
        );
        $this->assertSame(['itest'], $object->getModulesMaintainedInPoFiles());
        $this->assertSame([], $object->getUnreadableShippedPoModules());
    }

    /** A PO module contributes the extracted comments of its shipped .po, never its .lang comments. */
    public function testShippedCommentsTakeAMigratedModuleFromTheExtractedCommentsOfItsShippedPo(): void
    {
        $this->assertSame(
            [
                'common#:#yes' => 'Zustimmung',
                'itest#:#greeting' => 'Begrüßung auf der Startseite kurz halten',
            ],
            self::sorted($this->languageObject()->getShippedComments())
        );
    }

    /** getShippedComments() never carries the "not translated yet" marker; only getShippedCommentsForDisplay() does, display-only. */
    public function testShippedCommentsNeverIncludeTheFuzzyMarker(): void
    {
        $shipped = MigratedPoFixture::readPo($this->shippedPo());
        $shipped->find('itest', 'farewell')?->addFlag('fuzzy');
        MigratedPoFixture::writePo($this->shippedPo(), $shipped);

        $this->assertArrayNotHasKey('itest#:#farewell', $this->languageObject()->getShippedComments());
    }

    /** getShippedCommentsForDisplay() appends the marker for a "fuzzy" entry, to its own comment or alone. */
    public function testShippedCommentsForDisplayAppendsTheFuzzyMarker(): void
    {
        $shipped = MigratedPoFixture::readPo($this->shippedPo());
        $shipped->find('itest', 'greeting')?->addFlag('fuzzy');
        $shipped->find('itest', 'farewell')?->addFlag('fuzzy');
        MigratedPoFixture::writePo($this->shippedPo(), $shipped);

        $lng = $this->createStub(ilLanguage::class);
        $lng->method('txt')->willReturnArgument(0);
        $this->setGlobalVariable('lng', $lng);

        $comments = $this->languageObject()->getShippedCommentsForDisplay();

        $this->assertSame('Begrüßung auf der Startseite kurz halten - lng_not_translated', $comments['itest#:#greeting'] ?? null);
        $this->assertSame('lng_not_translated', $comments['itest#:#farewell'] ?? null);
        $this->assertArrayNotHasKey('itest#:#po_only', $comments, 'not fuzzy - no marker');
    }

    /** An unreadable shipped .po keeps the module's .lang lines as approximation, logged and reported as GUI warning. */
    public function testAnUnreadableShippedPoFallsBackToTheLangLinesAndIsReported(): void
    {
        file_put_contents($this->shippedPo(), "msgid \"kaputt\n");
        $this->expectAWarningAboutTheUnreadablePo();
        $object = $this->languageObject();

        $this->assertSame(
            [
                'common#:#no' => 'Nein',
                'common#:#yes' => 'Ja',
                'itest#:#farewell' => 'Tschüss',
                'itest#:#greeting' => 'Veraltet in lang/',
                'itest#:#stale' => 'Nur noch in lang/',
            ],
            self::sorted($object->getShippedValues())
        );
        $this->assertSame('alter lang-Kommentar', $object->getShippedComments()['itest#:#greeting']);
        $this->assertSame(['itest'], $object->getUnreadableShippedPoModules());
        $this->assertSame(['itest'], $object->getModulesMaintainedInPoFiles());
    }

    public function testWithoutDirectoryManagerTheShippedValuesAreThePlainGlobalLanguageFile(): void
    {
        unset($GLOBALS['DIC'][LanguageFileDirectoryManager::class]);
        $object = $this->languageObject();

        $this->assertSame(ilLanguageFile::_getGlobalLanguageFile(self::LANG)->getAllValues(), $object->getShippedValues());
        $this->assertSame(ilLanguageFile::_getGlobalLanguageFile(self::LANG)->getAllComments(), $object->getShippedComments());
        $this->assertSame([], $object->getModulesMaintainedInPoFiles());
    }

    /** "Added" = not shipped: a stale .lang line of a migrated module counts as added, a value the .po ships does not. */
    public function testAddedValuesAreComparedAgainstTheShippedPoOfAMigratedModule(): void
    {
        $this->value_rows = [
            ['module' => 'common', 'identifier' => 'custom', 'value' => 'Eigene'],
            ['module' => 'common', 'identifier' => 'yes', 'value' => 'Ja'],
            ['module' => 'itest', 'identifier' => 'greeting', 'value' => 'Aus der PO'],
            ['module' => 'itest', 'identifier' => 'po_only', 'value' => 'Nur in der PO'],
            ['module' => 'itest', 'identifier' => 'stale', 'value' => 'Nur noch in lang/'],
        ];

        $this->assertSame(
            ['common#:#custom' => 'Eigene', 'itest#:#stale' => 'Nur noch in lang/'],
            $this->languageObject()->getAddedValues()
        );
    }

    public function testCommentedValuesAreThoseWithAShippedCommentOfTheShippedPoForAMigratedModule(): void
    {
        $this->value_rows = [
            ['module' => 'common', 'identifier' => 'no', 'value' => 'Nein'],
            ['module' => 'common', 'identifier' => 'yes', 'value' => 'Ja'],
            ['module' => 'itest', 'identifier' => 'farewell', 'value' => 'Tschüss'],
            ['module' => 'itest', 'identifier' => 'greeting', 'value' => 'Servus'],
            ['module' => 'itest', 'identifier' => 'stale', 'value' => 'Nur noch in lang/'],
        ];
        $this->seedOverlay(['greeting' => 'Servus']);

        $this->assertSame(
            ['common#:#yes' => 'Ja', 'itest#:#greeting' => 'Servus'],
            $this->languageObject()->getCommentedValues()
        );
    }

    public function testMergedValuesAreTheShippedValuesOverriddenByTheLocalOnes(): void
    {
        $this->value_rows = [
            ['module' => 'common', 'identifier' => 'custom', 'value' => 'Eigene'],
            ['module' => 'itest', 'identifier' => 'greeting', 'value' => 'Servus'],
        ];
        $this->seedOverlay(['greeting' => 'Servus']);

        $this->assertSame(
            [
                'common#:#custom' => 'Eigene',
                'common#:#no' => 'Nein',
                'common#:#yes' => 'Ja',
                'itest#:#farewell' => 'Tschüss',
                'itest#:#greeting' => 'Servus',
                'itest#:#po_only' => 'Nur in der PO',
            ],
            self::sorted($this->languageObject()->getMergedValues())
        );
    }

    public function testMergedRemarksAreTheShippedCommentsOverriddenByTheLocalRemarks(): void
    {
        $this->remark_rows = [
            ['module' => 'common', 'identifier' => 'yes', 'remarks' => 'lokale Bemerkung'],
            ['module' => 'itest', 'identifier' => 'farewell', 'remarks' => null],
        ];

        $this->assertSame(
            [
                'common#:#yes' => 'lokale Bemerkung',
                'itest#:#farewell' => null,
                'itest#:#greeting' => 'Begrüßung auf der Startseite kurz halten',
            ],
            self::sorted($this->languageObject()->getMergedRemarks())
        );
    }

    public function testTheEffectiveRemarkIsTheShippedNoteWithoutALocalOneAndTheLocalOneOtherwise(): void
    {
        $this->remark_rows = [['module' => 'itest', 'identifier' => 'farewell', 'remarks' => 'lokal']];

        $this->assertSame(
            ['itest#:#farewell' => 'lokal', 'itest#:#greeting' => 'Begrüßung auf der Startseite kurz halten'],
            self::sorted($this->languageObject()->getEffectiveRemarks())
        );

        $this->remark_rows = [['module' => 'itest', 'identifier' => 'greeting', 'remarks' => 'lokal']];

        $this->assertSame('lokal', $this->languageObject()->getEffectiveRemarks()['itest#:#greeting']);
    }

    public function testAnUnreadableShippedPoHasNoNoteForTheEffectiveRemarks(): void
    {
        file_put_contents($this->shippedPo(), "msgid \"kaputt\n");
        $this->expectAWarningAboutTheUnreadablePo();

        $this->assertSame([], $this->languageObject()->getEffectiveRemarks());
    }

    public function testALocalRemarkOfAPluralMessageSuppressesTheNotesOfItsForms(): void
    {
        $catalog = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog();
        $catalog->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        $catalog->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
        $entry = new \ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry('itest', 'item');
        $entry->setPlural('items', ['Eintrag', 'Einträge']);
        $entry->addExtractedComment('Notiz zur Nachricht');
        $catalog->add($entry);
        MigratedPoFixture::writePo($this->shippedPo(), $catalog);

        $notes = $this->languageObject()->getEffectiveRemarks();
        $this->assertSame('Notiz zur Nachricht', $notes['itest#:#item [0]'] ?? null);
        $this->assertSame('Notiz zur Nachricht', $notes['itest#:#item [1]'] ?? null);

        $this->remark_rows = [['module' => 'itest', 'identifier' => 'item', 'remarks' => 'lokal']];
        $effective = $this->languageObject()->getEffectiveRemarks();
        $this->assertSame('lokal', $effective['itest#:#item'] ?? null);
        $this->assertArrayNotHasKey('itest#:#item [0]', $effective);
        $this->assertArrayNotHasKey('itest#:#item [1]', $effective);
    }

    /** @return list<string> the entry lines of the written global language file */
    private function entryLinesOfTheGlobalFile(): array
    {
        $content = (string) file_get_contents($this->global_file);
        $start = strpos($content, '<!-- language file start -->');
        $this->assertNotFalse($start);

        return explode("\n", ltrim(substr($content, $start + strlen('<!-- language file start -->')), "\n"));
    }

    /** A PO module is skipped entirely: its lines stay byte for byte, nothing is written or added; other modules are merged as before. */
    public function testMergeSkipsModulesMaintainedInPoFilesAndKeepsTheirLinesByteForByte(): void
    {
        $this->writeGlobalFile([
            'common#:#yes#:#Ja###Zustimmung',
            "itest#:#greeting#:#Veraltet in lang/   \t",
            ' itest#:#stale#:#Alt###Kommentar ',
            'common#:#no#:#Nein  ',
            'itest#:#farewell#:#Tschüss',
        ]);
        $this->value_rows = [
            ['module' => 'common', 'identifier' => 'new', 'value' => 'Neu'],
            ['module' => 'common', 'identifier' => 'yes', 'value' => 'Jawohl'],
            ['module' => 'itest', 'identifier' => 'farewell', 'value' => 'Servus'],
            ['module' => 'itest', 'identifier' => 'greeting', 'value' => 'Lokal geändert'],
            ['module' => 'itest', 'identifier' => 'local_only', 'value' => 'Nur lokal'],
        ];
        $this->remark_rows = [
            ['module' => 'common', 'identifier' => 'yes', 'remarks' => 'lokale Bemerkung'],
            ['module' => 'itest', 'identifier' => 'farewell', 'remarks' => 'lokale Bemerkung einer PO-Variable'],
        ];
        // a "lang" component logger is always requested once the directory manager is registered
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($this->createStub(ilLogger::class));
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);

        $result = $this->languageObject()->mergeLocalChangesIntoGlobalLanguageFile();

        // "itest" has no overlay (its lng_data rows are only the dual-write fallback): nothing to merge, so not reported as skipped
        $this->assertSame(
            ['written' => [], 'skipped' => [], 'invalid_markup' => [], 'not_merged' => [], 'unwritten_database' => []],
            $result
        );
        $this->assertSame(
            [
                'common#:#yes#:#Jawohl###lokale Bemerkung',
                "itest#:#greeting#:#Veraltet in lang/   \t",
                ' itest#:#stale#:#Alt###Kommentar ',
                'common#:#no#:#Nein',
                'itest#:#farewell#:#Tschüss',
                'common#:#new#:#Neu',
            ],
            $this->entryLinesOfTheGlobalFile()
        );
    }

    public function testMergeWithoutModulesMaintainedInPoFilesMergesEverything(): void
    {
        unset($GLOBALS['DIC'][LanguageFileDirectoryManager::class]);
        $this->value_rows = [
            ['module' => 'itest', 'identifier' => 'greeting', 'value' => 'Lokal geändert'],
        ];

        $this->assertSame(
            ['written' => [], 'skipped' => [], 'invalid_markup' => [], 'not_merged' => [], 'unwritten_database' => []],
            $this->languageObject()->mergeLocalChangesIntoGlobalLanguageFile()
        );
        $this->assertSame(
            [
                'common#:#yes#:#Ja###Zustimmung',
                'itest#:#greeting#:#Lokal geändert###alter lang-Kommentar',
                'itest#:#stale#:#Nur noch in lang/###Kommentar einer entfernten Variable',
                'itest#:#farewell#:#Tschüss',
                'common#:#no#:#Nein',
            ],
            $this->entryLinesOfTheGlobalFile()
        );
    }

    /** After a successful merge (mergeIntoDatabase()) the lng_data row of every taken-over value and remark loses local_change/remarks. */
    public function testMergeUpdatesLngDataOfTheTakenOverValueAndRemarkToNull(): void
    {
        $overlay = MigratedPoFixture::catalog('itest', ['greeting' => 'Lokal geändert']);
        LocalChangeComments::setRemark($overlay->find('itest', 'greeting'), 'lokale Bemerkung');
        MigratedPoFixture::writePair(
            rtrim(CLIENT_DATA_DIR, '/') . '/lang/itest/' . self::LANG . '/itest_' . self::LANG,
            $overlay
        );
        $logger = $this->createStub(ilLogger::class);
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);

        $object = $this->languageObject();
        $object->cust_lang_path = $this->fixture_directory;
        $result = $object->mergeLocalChangesIntoGlobalLanguageFile();

        $this->assertNotSame([], $result['written'], 'the shipped .po was actually written');
        $this->assertSame(
            [
                "UPDATE lng_data SET local_change = NULL WHERE lang_key = 'zz' AND module = 'itest' AND 1 = 1",
                "UPDATE lng_data SET remarks = NULL WHERE lang_key = 'zz' AND module = 'itest' AND 1 = 1",
            ],
            $this->manipulate_calls
        );
    }

    public function testMergeWithoutWritingTheGlobalFileStillWritesThePoAndResetsLngData(): void
    {
        $overlay = MigratedPoFixture::catalog('itest', ['greeting' => 'Lokal geändert']);
        LocalChangeComments::setRemark($overlay->find('itest', 'greeting'), 'lokale Bemerkung');
        MigratedPoFixture::writePair(
            rtrim(CLIENT_DATA_DIR, '/') . '/lang/itest/' . self::LANG . '/itest_' . self::LANG,
            $overlay
        );
        $this->value_rows = [
            ['module' => 'common', 'identifier' => 'yes', 'value' => 'Jawohl'],
            ['module' => 'common', 'identifier' => 'new', 'value' => 'Neu'],
        ];
        $this->remark_rows = [['module' => 'common', 'identifier' => 'yes', 'remarks' => 'lokale Bemerkung']];
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($this->createStub(ilLogger::class));
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);
        $lines_before = $this->entryLinesOfTheGlobalFile();
        $content_before = file_get_contents($this->global_file);

        $result = $this->languageObject()->mergeLocalChangesIntoGlobalLanguageFile(false);

        $this->assertSame($content_before, file_get_contents($this->global_file));
        $this->assertSame($lines_before, $this->entryLinesOfTheGlobalFile());
        $this->assertNotSame([], $result['written'], 'the shipped .po was written');
        $this->assertSame(
            [
                "UPDATE lng_data SET local_change = NULL WHERE lang_key = 'zz' AND module = 'itest' AND 1 = 1",
                "UPDATE lng_data SET remarks = NULL WHERE lang_key = 'zz' AND module = 'itest' AND 1 = 1",
            ],
            $this->manipulate_calls
        );
    }

    /** @param list<string> $local_change_modules modules of the lng_data rows with a local_change */
    #[DataProvider('localChangeModulesAndWhetherTheyAreOutsideThePoModules')]
    public function testLocalChangesOutsideThePoModulesAreDetected(array $local_change_modules, bool $expected): void
    {
        $this->local_change_modules = $local_change_modules;

        $this->assertSame($expected, $this->languageObject()->hasLocalChangesOutsidePoModules());
    }

    /** @return array<string, array{0: list<string>, 1: bool}> */
    public static function localChangeModulesAndWhetherTheyAreOutsideThePoModules(): array
    {
        return [
            'no row with a local change (e.g. only a shipped remark)' => [[], false],
            'only a module maintained in PO files' => [['itest'], false],
            'a module outside the PO files' => [['common'], true],
            'both kinds' => [['itest', 'common'], true],
        ];
    }
}
