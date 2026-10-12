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
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\Stub;

/**
 * ilObjLanguageExt::currentModuleContent() (what a partial save/delete is applied to) and the
 * $merge_onto_current_content behavior of saveValues()/_deleteValues(): a readable overlay is preferred over a differing
 * lng_modules row, lng_data is the fallback, and "delete"-mode imports never merge onto the existing content.
 *
 * Separate processes, because CLIENT_DATA_DIR cannot be redefined and an earlier test class of a full-suite run
 * may already have defined it as /var/iliasdata, which would make every test skip.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class SaveValuesMergesOntoOverlayTest extends ilLanguageBaseTestCase
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
        // never remove a foreign CLIENT_DATA_DIR
        if ($this->created_client_data_dir_root && defined('CLIENT_DATA_DIR') && is_dir(CLIENT_DATA_DIR)) {
            MigratedPoFixture::removeDirectory(CLIENT_DATA_DIR);
        }

        parent::tearDown();
    }

    /**
     * @param array<string, string> $shipped_entries
     * @param array<string, string>|null $overlay_entries null => no overlay at all (not even a readable one)
     */
    private function seedShippedAndOverlay(
        string $module,
        array $shipped_entries,
        ?array $overlay_entries
    ): LanguageFileDirectory {
        $this->fixture_directory ??= 'tmp-savevalues-fixtures-' . bin2hex(random_bytes(4));
        $relative_path = 'components/ILIAS/Language/tests/' . $this->fixture_directory . '/';

        MigratedPoFixture::writeShippedPo(
            $relative_path,
            $module,
            'de',
            MigratedPoFixture::catalog($module, $shipped_entries)
        );

        if ($overlay_entries !== null) {
            MigratedPoFixture::writePair(
                rtrim(CLIENT_DATA_DIR, '/') . '/lang/' . $module . '/de/' . $module . '_de',
                MigratedPoFixture::catalog($module, $overlay_entries)
            );
        }

        return MigratedPoFixture::directory($module, $relative_path);
    }

    private function registerDirectoryManager(LanguageFileDirectory ...$contributed): void
    {
        $this->setGlobalVariable(
            LanguageFileDirectoryManager::class,
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), ...$contributed)
        );
    }

    private function callCurrentModuleContent(string $lang_key, string $module): array
    {
        return (new ReflectionClass(ilObjLanguageExt::class))
            ->getMethod('currentModuleContent')
            ->invoke(null, $lang_key, $module);
    }

    /** @return array{0: string, 1: string} module/topic separator, an ilLanguage stand-in whose constructor never ran */
    private function stubLng(): ilLanguage
    {
        $lng = (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor();
        $this->setGlobalVariable('lng', $lng);

        return $lng;
    }

    /**
     * $ilDB mock with a controllable lng_modules row and lng_data rows; the three SELECTs (lng_modules row, lng_data
     * "remarks", lng_data values) are told apart by SQL text.
     *
     * @param array<string, string>|null $lng_modules_row the (unserialized) content of the existing row; null = no row
     * @param list<array{identifier: string, value: string}> $lng_data_rows rows of the plain lng_data values SELECT
     */
    private function mockDatabaseForWrites(?array $lng_modules_row, array $lng_data_rows = []): ilDBInterface&Stub
    {
        $lng_modules_stmt = $this->createStub(ilDBStatement::class);
        $remarks_stmt = $this->createStub(ilDBStatement::class);
        $lng_data_stmt = $this->createStub(ilDBStatement::class);
        $query_f_stmt = $this->createStub(ilDBStatement::class);

        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($value, string $type = ''): string => "'" . (string) $value . "'");
        $db->method('in')->willReturn("module IN ('placeholder')");
        $db->method('query')->willReturnCallback(
            static function (string $sql) use ($lng_modules_stmt, $remarks_stmt, $lng_data_stmt): ilDBStatement {
                if (str_contains($sql, 'lng_modules')) {
                    return $lng_modules_stmt;
                }
                if (str_contains($sql, 'remarks')) {
                    return $remarks_stmt;
                }
                return $lng_data_stmt;
            }
        );
        $db->method('queryF')->willReturn($query_f_stmt);

        $rows = $lng_data_rows;
        $db->method('fetchAssoc')->willReturnCallback(
            function (ilDBStatement $statement) use (
                $lng_modules_stmt,
                $remarks_stmt,
                $lng_data_stmt,
                $query_f_stmt,
                $lng_modules_row,
                &$rows
            ) {
                if ($statement === $query_f_stmt) {
                    return ['lang_array' => serialize([])];
                }
                if ($statement === $lng_modules_stmt) {
                    return $lng_modules_row === null ? null : ['lang_array' => serialize($lng_modules_row)];
                }
                if ($statement === $remarks_stmt) {
                    return null;
                }
                if ($statement === $lng_data_stmt) {
                    $row = array_shift($rows);
                    return $row ?? null;
                }
                return null;
            }
        );
        $db->method('manipulate')->willReturn(0);
        $db->method('insert')->willReturn(1);

        $this->setGlobalVariable('ilDB', $db);

        return $db;
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

    private function callSaveValues(
        string $lang_key,
        array $values,
        array $remarks,
        bool $merge_onto_current_content
    ): array {
        return (new ReflectionClass(ilObjLanguageExt::class))
            ->getMethod('saveValues')
            ->invoke(null, $lang_key, $values, $remarks, $merge_onto_current_content);
    }

    /** `->never()` on query(): once a readable overlay is found the database must not be queried at all. */
    public function testOverlayValueIsUsedEvenWhenItDiffersFromTheLngModulesRowAndTheDatabaseIsNeverConsulted(): void
    {
        $directory = $this->seedShippedAndOverlay(
            'ctest',
            ['greeting' => 'Shipped'],
            ['greeting' => 'Aus dem Overlay']
        );
        $this->registerDirectoryManager($directory);

        $db = $this->createMock(ilDBInterface::class);
        $db->expects($this->never())->method('query');
        $this->setGlobalVariable('ilDB', $db);

        $this->assertSame(
            ['greeting' => 'Aus dem Overlay'],
            $this->callCurrentModuleContent('de', 'ctest')
        );
    }

    /** A migrated module with neither readable overlay nor lng_modules row is rebuilt from lng_data, minus the deleted key. */
    public function testDeleteValuesWithoutOverlayAndWithoutLngModulesRowFallsBackToLngDataExcludingDeletedKeys(): void
    {
        $directory = $this->seedShippedAndOverlay('ctest2', ['greeting' => 'Shipped'], ['greeting' => 'Lokal']);
        file_put_contents(
            rtrim(CLIENT_DATA_DIR, '/') . '/lang/ctest2/de/ctest2_de.po',
            "msgid \"kaputt\n"
        );
        $this->registerDirectoryManager($directory);
        $lng = $this->stubLng();
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($this->createStub(ilLogger::class));
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);

        $db = $this->mockDatabaseForWrites(null, [
            ['identifier' => 'greeting', 'value' => 'Aus lng_data'],
            ['identifier' => 'farewell', 'value' => 'Tschüss aus lng_data'],
        ]);

        $captured = null;
        $db->method('insert')->willReturnCallback(
            static function (string $table, array $values) use (&$captured): int {
                if ($table === 'lng_modules') {
                    $captured = unserialize($values['lang_array'][1] ?? '', ['allowed_classes' => false]);
                }
                return 1;
            }
        );

        ilObjLanguageExt::_deleteValues('de', ['ctest2' . $lng->separator . 'greeting' => 'irrelevant']);

        $this->assertSame(['farewell' => 'Tschüss aus lng_data'], $captured);
    }

    /**
     * An overlay write failure never undoes the database write: the module is reported back in the returned list.
     * Root-proof: a regular file sits where the overlay's language directory must be created.
     */
    public function testDeleteValuesReportsTheModuleWhoseOverlayCouldNotBeWritten(): void
    {
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            $this->markTestSkipped('A non-writable directory cannot be forced for root (no root-proof variant here).');
        }
        // a real local change ("greeting") must survive, else sync() has an empty delta and writes nothing
        $directory = $this->seedShippedAndOverlay(
            'ctest3',
            ['greeting' => 'Shipped', 'farewell' => 'Tschüss'],
            ['greeting' => 'Lokal']
        );
        $this->registerDirectoryManager($directory);
        $lng = $this->stubLng();
        $logger = $this->createMock(ilLogger::class);
        $logger->expects($this->atLeastOnce())->method('warning');
        $logger_factory = $this->createStub(ilLoggerFactory::class);
        $logger_factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);
        $this->mockDatabaseForWrites(['greeting' => 'Lokal', 'farewell' => 'Tschüss']);

        // the files stay readable, but their directory is non-writable, so the write itself fails
        $overlay_directory = rtrim(CLIENT_DATA_DIR, '/') . '/lang/ctest3/de';
        chmod($overlay_directory, 0555);

        set_error_handler(static fn(): bool => true, E_WARNING);
        try {
            $unwritten = ilObjLanguageExt::_deleteValues('de', ['ctest3' . $lng->separator . 'farewell' => 'irrelevant']);
        } finally {
            restore_error_handler();
            chmod($overlay_directory, 0775);
        }

        $this->assertSame(['ctest3'], $unwritten);
    }

    /** The expensive reads must not run when every value passes the plain markup check (an empty $GLOBALS['DIC'] would fatal). */
    public function testFindInvalidMarkupOfChangedValuesNeverReadsCurrentOrShippedValuesWhenNothingIsSuspicious(): void
    {
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();

        $result = ilObjLanguageExt::findInvalidMarkupOfChangedValues('zz_no_db_needed', [
            'common#:#greeting' => 'Hallo',
            'common#:#formatting' => '<b>Hallo</b>',
        ]);

        $this->assertSame([], $result);
    }

    /** A value failing the plain check is rejected only if it also differs from both the current and the shipped value. */
    public function testFindInvalidMarkupOfChangedValuesAppliesTheCurrentAndShippedComparison(): void
    {
        $lang_key = 'zz_changed_markup_test';
        $bad = '<script>alert(1)</script>';
        $this->seedEmptyGlobalLanguageFile($lang_key);
        $this->stubLng();

        $rows = [
            ['module' => 'common', 'identifier' => 'unmodified', 'value' => $bad],
            ['module' => 'common', 'identifier' => 'changed', 'value' => 'Hallo'],
        ];
        $statement = $this->createStub(ilDBStatement::class);
        $statement->method('fetchRow')->willReturnCallback(static function () use (&$rows) {
            return array_shift($rows);
        });
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($value, string $type = ''): string => "'" . (string) $value . "'");
        $db->method('in')->willReturn("module IN ('placeholder')");
        $db->method('like')->willReturn('1=1');
        $db->method('query')->willReturn($statement);
        $this->setGlobalVariable('ilDB', $db);

        $result = ilObjLanguageExt::findInvalidMarkupOfChangedValues($lang_key, [
            'common#:#unmodified' => $bad,
            'common#:#changed' => $bad,
        ]);

        $this->assertArrayNotHasKey('common#:#unmodified', $result, 'unchanged from the current lng_data value - not rejected');
        $this->assertArrayHasKey('common#:#changed', $result, 'genuinely changed to a disallowed value - rejected');
    }

    /** With $merge_onto_current_content=false ("delete"-mode import) $to_save is the complete new content: absent keys are gone. */
    public function testMergeOntoCurrentContentFalseDropsAKeyThatIsNotInTheNewEntries(): void
    {
        $lang_key = 'zz_merge_false_test';
        $this->seedEmptyGlobalLanguageFile($lang_key);
        $lng = $this->stubLng();
        $db = $this->mockDatabaseForWrites(['greeting' => 'Alt', 'farewell' => 'Tschüss']);

        $captured = null;
        $db->method('insert')->willReturnCallback(
            static function (string $table, array $values) use (&$captured): int {
                if ($table === 'lng_modules') {
                    $captured = unserialize($values['lang_array'][1] ?? '', ['allowed_classes' => false]);
                }
                return 1;
            }
        );

        $this->callSaveValues(
            $lang_key,
            ['wtest' . $lng->separator . 'greeting' => 'Neu'],
            [],
            false
        );

        $this->assertSame(['greeting' => 'Neu'], $captured);
    }

    /** The default (true) contrasted with the test above: the untouched "farewell" survives. */
    public function testMergeOntoCurrentContentTrueKeepsAKeyThatIsNotInTheNewEntries(): void
    {
        $lang_key = 'zz_merge_true_test';
        $this->seedEmptyGlobalLanguageFile($lang_key);
        $lng = $this->stubLng();
        $db = $this->mockDatabaseForWrites(['greeting' => 'Alt', 'farewell' => 'Tschüss']);

        $captured = null;
        $db->method('insert')->willReturnCallback(
            static function (string $table, array $values) use (&$captured): int {
                if ($table === 'lng_modules') {
                    $captured = unserialize($values['lang_array'][1] ?? '', ['allowed_classes' => false]);
                }
                return 1;
            }
        );

        $this->callSaveValues(
            $lang_key,
            ['wtest' . $lng->separator . 'greeting' => 'Neu'],
            [],
            true
        );

        $this->assertSame(['greeting' => 'Neu', 'farewell' => 'Tschüss'], $captured);
    }
}
