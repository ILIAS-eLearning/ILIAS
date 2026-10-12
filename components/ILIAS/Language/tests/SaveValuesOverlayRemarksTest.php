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
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\Stub;

/**
 * _saveValues()/_deleteValues() writing an administrator's remark (lng_data.remarks, mirrored into the overlay) for a PO
 * module: a pure remark change must reach both without faking a value change, and deleting one form of a plural message
 * must not remove the message's overlay remark.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class SaveValuesOverlayRemarksTest extends ilLanguageBaseTestCase
{
    private const string MODULE = 'ptest4';
    private const string LANG_KEY = 'de';

    private ?string $fixture_directory = null;
    private bool $created_client_data_dir_root = false;
    private ?LanguageFileDirectoryManager $manager = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }
        $this->created_client_data_dir_root = MigratedPoFixture::ensureClientDataDirDefinedOrSkip($this);

        MigratedPoFixture::resetRuntime();
        (new ReflectionClass(ilCachedLanguage::class))->getProperty('instances')->setValue(null, []);
    }

    protected function tearDown(): void
    {
        if ($this->fixture_directory !== null) {
            MigratedPoFixture::removeShippedDirectory('components/ILIAS/Language/tests/' . $this->fixture_directory);
            MigratedPoFixture::removeDirectory(rtrim(CLIENT_DATA_DIR, '/') . '/lang');
        }
        if ($this->created_client_data_dir_root && defined('CLIENT_DATA_DIR') && is_dir(CLIENT_DATA_DIR)) {
            MigratedPoFixture::removeDirectory(CLIENT_DATA_DIR);
        }

        parent::tearDown();
    }

    /** Ships self::MODULE/self::LANG_KEY with a singular ("greeting") and a plural message ("item"/"items") and registers its directory. */
    private function seedShippedModule(): LanguageFileDirectory
    {
        $this->fixture_directory = 'tmp-savevalues-remarks-fixtures-' . bin2hex(random_bytes(4));
        $relative_path = 'components/ILIAS/Language/tests/' . $this->fixture_directory . '/';

        $catalog = new TranslationCatalog();
        $catalog->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        $catalog->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
        $greeting = new TranslationEntry(self::MODULE, 'greeting');
        $greeting->translate('Hallo');
        $catalog->add($greeting);
        $item = new TranslationEntry(self::MODULE, 'item');
        $item->setPlural('items', ['Eintrag', 'Einträge']);
        $catalog->add($item);
        MigratedPoFixture::writeShippedPo($relative_path, self::MODULE, self::LANG_KEY, $catalog);

        $directory = MigratedPoFixture::directory(self::MODULE, $relative_path);
        $this->manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $directory);
        $this->setGlobalVariable(LanguageFileDirectoryManager::class, $this->manager);

        return $directory;
    }

    private function stubLng(): ilLanguage
    {
        $lng = (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor();
        $this->setGlobalVariable('lng', $lng);

        return $lng;
    }

    /** normalizedRemarks() reads $DIC->logger()->forComponent('lang') for an invalid remark and throws without it. */
    private function stubComponentLogger(): void
    {
        $factory = $this->createStub(ilLoggerFactory::class);
        $factory->method('getComponentLogger')->willReturn($this->createStub(ilLogger::class));
        $this->setGlobalVariable('ilLoggerFactory', $factory);
    }

    private function seedEmptyGlobalLanguageFile(string $lang_key): void
    {
        $fake_global_file = (new ReflectionClass(ilLanguageFile::class))->newInstanceWithoutConstructor();
        $fake_global_file->setAllValues([]);
        $fake_global_file->setAllComments([]);

        $cache = (new ReflectionClass(ilLanguageFile::class))->getProperty('global_file_objects');
        $existing = $cache->isInitialized() ? $cache->getValue() : [];
        $cache->setValue(null, $existing + [$lang_key => $fake_global_file]);
    }

    private function overlayRemark(string $identifier): ?string
    {
        return (MigratedLanguageFileSync::loadRemarks($this->manager, self::LANG_KEY, self::MODULE, CLIENT_DATA_DIR) ?? [])[$identifier] ?? null;
    }

    /**
     * @param list<array{0: string, 1: array<string, mixed>}> $replace_calls
     * @param list<string> $manipulate_calls every manipulate() SQL string, in order
     * @param list<array{module: string, identifier: string, remarks: string}> $existing_remarks rows
     *        _getRemarks()'s "remarks IS NOT NULL" SELECT returns
     */
    private function mockDatabaseForWrites(array &$replace_calls, array &$manipulate_calls, array $existing_remarks = []): ilDBInterface&Stub
    {
        $lng_modules_stmt = $this->createStub(ilDBStatement::class);
        $remarks_stmt = $this->createStub(ilDBStatement::class);
        $lng_data_stmt = $this->createStub(ilDBStatement::class);
        $query_f_stmt = $this->createStub(ilDBStatement::class);

        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($value, string $type = ''): string => $value === null ? 'NULL' : "'" . (string) $value . "'");
        $db->method('in')->willReturn("module IN ('placeholder')");
        $db->method('query')->willReturnCallback(
            static function (string $sql) use ($lng_modules_stmt, $remarks_stmt, $lng_data_stmt): ilDBStatement {
                if (str_contains($sql, 'lng_modules')) {
                    return $lng_modules_stmt;
                }
                return str_contains($sql, 'remarks') ? $remarks_stmt : $lng_data_stmt;
            }
        );
        $db->method('queryF')->willReturn($query_f_stmt);
        $remaining_remarks = $existing_remarks;
        $db->method('fetchAssoc')->willReturnCallback(
            static function (ilDBStatement $statement) use ($query_f_stmt, $remarks_stmt, &$remaining_remarks): ?array {
                if ($statement === $query_f_stmt) {
                    return ['lang_array' => serialize([])];
                }
                if ($statement === $remarks_stmt) {
                    return array_shift($remaining_remarks) ?: null;
                }
                return null;
            }
        );
        $db->method('manipulate')->willReturnCallback(
            static function (string $sql) use (&$manipulate_calls): int {
                $manipulate_calls[] = $sql;
                return 1;
            }
        );
        $db->method('insert')->willReturn(1);
        $db->method('replace')->willReturnCallback(
            static function (string $table, array $keys, array $values) use (&$replace_calls): int {
                if ($table === 'lng_data') {
                    $replace_calls[] = [$keys['identifier'][1] ?? null, [
                        'value' => $values['value'][1] ?? null,
                        'local_change' => $values['local_change'][1] ?? null,
                        'remarks' => $values['remarks'][1] ?? null,
                    ]];
                }
                return 1;
            }
        );

        $this->setGlobalVariable('ilDB', $db);

        return $db;
    }

    /** A pure remark change on a singular identifier goes through updateRemark() (UPDATE of "remarks" only), not replace(). */
    public function testPureRemarkChangeOnASingularIdentifierUpdatesRemarksAloneAndWritesTheOverlay(): void
    {
        $this->seedShippedModule();
        $lng = $this->stubLng();
        $this->seedEmptyGlobalLanguageFile(self::LANG_KEY);
        $replace_calls = [];
        $manipulate_calls = [];
        $this->mockDatabaseForWrites($replace_calls, $manipulate_calls);

        ilObjLanguageExt::_saveValues(
            self::LANG_KEY,
            [self::MODULE . $lng->separator . 'greeting' => 'Hallo'],
            [self::MODULE . $lng->separator . 'greeting' => 'Bitte prüfen']
        );

        $greeting_replace_calls = array_values(array_filter($replace_calls, static fn(array $call): bool => $call[0] === 'greeting'));
        $this->assertSame([], $greeting_replace_calls, 'no fake value write via replace()');
        $remark_updates = array_values(array_filter(
            $manipulate_calls,
            static fn(string $sql): bool => str_contains($sql, 'UPDATE lng_data') && str_contains($sql, 'remarks')
        ));
        $this->assertNotEmpty($remark_updates, 'lng_data.remarks must be updated');
        $this->assertStringContainsString("'Bitte prüfen'", $remark_updates[0]);
        $this->assertSame('Bitte prüfen', $this->overlayRemark('greeting'), 'mirrored into the overlay');
    }

    /** A remark that is not valid UTF-8 is dropped with a warning; the save still succeeds. */
    public function testSaveValuesDropsAnInvalidUtf8RemarkWithoutSavingItAnywhere(): void
    {
        $this->seedShippedModule();
        $lng = $this->stubLng();
        $this->seedEmptyGlobalLanguageFile(self::LANG_KEY);
        $this->stubComponentLogger();
        $replace_calls = [];
        $manipulate_calls = [];
        $this->mockDatabaseForWrites($replace_calls, $manipulate_calls);

        $unwritten = ilObjLanguageExt::_saveValues(
            self::LANG_KEY,
            [self::MODULE . $lng->separator . 'greeting' => 'Hallo'],
            [self::MODULE . $lng->separator . 'greeting' => "Ung\xFCltig"]
        );

        $this->assertSame([], $unwritten, 'the save itself must still succeed');
        $this->assertNull($this->overlayRemark('greeting'));
        $remark_updates = array_values(array_filter(
            $manipulate_calls,
            static fn(string $sql): bool => str_contains($sql, 'UPDATE lng_data') && str_contains($sql, 'remarks')
        ));
        $this->assertSame([], $remark_updates, 'nothing must be written for the dropped remark');
    }

    /** A remark longer than 250 characters is cut to 250 (lng_data's column limit) before the comparison and the write. */
    public function testSaveValuesCutsALongRemarkToTwoHundredAndFiftyCharacters(): void
    {
        $this->seedShippedModule();
        $lng = $this->stubLng();
        $this->seedEmptyGlobalLanguageFile(self::LANG_KEY);
        $replace_calls = [];
        $manipulate_calls = [];
        $this->mockDatabaseForWrites($replace_calls, $manipulate_calls);
        $long_remark = str_repeat('x', 300);

        ilObjLanguageExt::_saveValues(
            self::LANG_KEY,
            [self::MODULE . $lng->separator . 'greeting' => 'Hallo'],
            [self::MODULE . $lng->separator . 'greeting' => $long_remark]
        );

        $this->assertSame(str_repeat('x', 250), $this->overlayRemark('greeting'));
    }

    public function testDeleteValuesOfASingularKeyRemovesItsOverlayRemark(): void
    {
        $this->seedShippedModule();
        $lng = $this->stubLng();
        $this->seedEmptyGlobalLanguageFile(self::LANG_KEY);
        $replace_calls = [];
        $manipulate_calls = [];
        $this->mockDatabaseForWrites($replace_calls, $manipulate_calls);
        ilObjLanguageExt::_saveValues(
            self::LANG_KEY,
            [self::MODULE . $lng->separator . 'greeting' => 'Hallo'],
            [self::MODULE . $lng->separator . 'greeting' => 'Bitte prüfen']
        );
        $this->assertSame('Bitte prüfen', $this->overlayRemark('greeting'), 'precondition');

        $replace_calls_2 = [];
        $manipulate_calls_2 = [];
        $this->mockDatabaseForWrites($replace_calls_2, $manipulate_calls_2);
        ilObjLanguageExt::_deleteValues(self::LANG_KEY, [self::MODULE . $lng->separator . 'greeting' => '']);

        $this->assertNull($this->overlayRemark('greeting'));
    }

    /** Deleting one form of a plural message keeps the message's overlay remark: it belongs to the whole message. */
    public function testDeleteValuesOfOnePluralFormKeepsTheMessagesOverlayRemark(): void
    {
        $this->seedShippedModule();
        $lng = $this->stubLng();
        $this->seedEmptyGlobalLanguageFile(self::LANG_KEY);
        $replace_calls = [];
        $manipulate_calls = [];
        $this->mockDatabaseForWrites($replace_calls, $manipulate_calls);
        ilObjLanguageExt::_saveValues(
            self::LANG_KEY,
            [
                self::MODULE . $lng->separator . 'item [0]' => 'Eintrag',
                self::MODULE . $lng->separator . 'item [1]' => 'Einträge',
            ],
            [
                self::MODULE . $lng->separator . 'item [0]' => '',
                self::MODULE . $lng->separator . 'item [1]' => 'Bitte prüfen',
            ]
        );
        $this->assertSame('Bitte prüfen', $this->overlayRemark('item'), 'precondition');

        $replace_calls_2 = [];
        $manipulate_calls_2 = [];
        $this->mockDatabaseForWrites($replace_calls_2, $manipulate_calls_2);
        ilObjLanguageExt::_deleteValues(self::LANG_KEY, [self::MODULE . $lng->separator . 'item [1]' => '']);

        $this->assertSame('Bitte prüfen', $this->overlayRemark('item'), 'the plural message\'s remark must survive deleting one form');
    }

    public function testGetRemarksShowsALngDataOnlyRemarkWithoutAnOverlayOne(): void
    {
        $this->seedShippedModule();
        $lng = $this->stubLng();
        $replace_calls = [];
        $manipulate_calls = [];
        $this->mockDatabaseForWrites($replace_calls, $manipulate_calls, [
            ['module' => self::MODULE, 'identifier' => 'greeting', 'remarks' => 'DB-Bemerkung'],
        ]);

        $remarks = ilObjLanguageExt::_getRemarks(self::LANG_KEY);

        $this->assertSame('DB-Bemerkung', $remarks[self::MODULE . $lng->separator . 'greeting'] ?? null);
    }

    /** An overlay remark wins over lng_data's: the overlay is the source of truth for a PO module. */
    public function testGetRemarksOverlayRemarkWinsOverAStaleLngDataOne(): void
    {
        $this->seedShippedModule();
        $lng = $this->stubLng();
        $this->seedEmptyGlobalLanguageFile(self::LANG_KEY);
        $save_calls = [];
        $save_manipulate_calls = [];
        $this->mockDatabaseForWrites($save_calls, $save_manipulate_calls);
        ilObjLanguageExt::_saveValues(
            self::LANG_KEY,
            [self::MODULE . $lng->separator . 'greeting' => 'Hallo'],
            [self::MODULE . $lng->separator . 'greeting' => 'Overlay-Bemerkung']
        );

        $replace_calls = [];
        $manipulate_calls = [];
        $this->mockDatabaseForWrites($replace_calls, $manipulate_calls, [
            ['module' => self::MODULE, 'identifier' => 'greeting', 'remarks' => 'Veraltete DB-Bemerkung'],
        ]);

        $remarks = ilObjLanguageExt::_getRemarks(self::LANG_KEY);

        $this->assertSame('Overlay-Bemerkung', $remarks[self::MODULE . $lng->separator . 'greeting'] ?? null);
    }

    /** A shipped "fuzzy" entry gets the "not translated" marker (lng_not_translated) appended to its comment. */
    public function testGetShippedMigratedCommentsAppendsTheFuzzyMarkerForAFuzzyEntryOnly(): void
    {
        $this->fixture_directory = 'tmp-savevalues-remarks-fuzzy-fixtures-' . bin2hex(random_bytes(4));
        $relative_path = 'components/ILIAS/Language/tests/' . $this->fixture_directory . '/';
        $lng = $this->stubLng();

        $catalog = new TranslationCatalog();
        $greeting = new TranslationEntry(self::MODULE, 'greeting');
        $greeting->translate('Hallo');
        $catalog->add($greeting);
        $fuzzy = new TranslationEntry(self::MODULE, 'fuzzy_one');
        $fuzzy->translate('Noch nicht übersetzt');
        $fuzzy->addFlag('fuzzy');
        $catalog->add($fuzzy);
        MigratedPoFixture::writeShippedPo($relative_path, self::MODULE, self::LANG_KEY, $catalog);
        $this->manager = new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            MigratedPoFixture::directory(self::MODULE, $relative_path)
        );
        $this->setGlobalVariable(LanguageFileDirectoryManager::class, $this->manager);

        $marker_lng = $this->createStub(ilLanguage::class);
        $marker_lng->method('txt')->willReturnArgument(0);
        $this->setGlobalVariable('lng', $marker_lng);

        $comments = ilObjLanguageExt::_getShippedMigratedComments(self::LANG_KEY);

        $this->assertSame('lng_not_translated', $comments[self::MODULE . $lng->separator . 'fuzzy_one'] ?? null);
        $this->assertArrayNotHasKey(self::MODULE . $lng->separator . 'greeting', $comments);
    }
}
