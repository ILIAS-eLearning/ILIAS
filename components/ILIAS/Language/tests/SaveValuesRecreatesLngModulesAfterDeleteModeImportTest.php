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

/**
 * "delete"-mode import: importLanguageFile() wipes all lng_data and lng_modules rows of the language first, so _saveValues()
 * finds no lng_modules row per module. It must still reach ilObjLanguage::replaceLangModule(), the only place writing that
 * row, else txt() returns only "-topic-" for every non-migrated module (ilCachedLanguage builds from lng_modules).
 *
 * Drives _saveValues() directly with a DB mock that returns no row, not the full importLanguageFile() end-to-end, which
 * would additionally need _deleteLangData(), the global language file and _getModules() (see SyncMigratedFilesAfterDeleteModeImportTest).
 */
class SaveValuesRecreatesLngModulesAfterDeleteModeImportTest extends ilLanguageBaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // _saveValues() reads ILIAS_ABSOLUTE_PATH (via withOverlayLock())
        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', realpath(__DIR__ . '/../../../../'));
        }

        // ilCachedLanguage is a process-wide singleton per lang_key: reset it so an earlier test's instance cannot short-circuit the DB mock
        $instances = (new ReflectionClass(ilCachedLanguage::class))->getProperty('instances');
        $instances->setValue(null, []);
    }

    /** Seeds the static cache of ilLanguageFile::_getGlobalLanguageFile() with an empty fake, so no real lang file is read. */
    private function seedEmptyGlobalLanguageFile(string $lang_key): void
    {
        $fake_global_file = (new ReflectionClass(ilLanguageFile::class))->newInstanceWithoutConstructor();
        $fake_global_file->setAllValues([]);
        $fake_global_file->setAllComments([]);

        $cache = (new ReflectionClass(ilLanguageFile::class))->getProperty('global_file_objects');
        $existing = $cache->isInitialized() ? $cache->getValue() : [];
        $cache->setValue(null, $existing + [$lang_key => $fake_global_file]);
    }

    /**
     * ilDBInterface mock for _saveValues() -> replaceLangModule(): the three query() calls (lng_data values, lng_data
     * remarks, per-module lng_modules SELECT) are told apart by SQL text. What matters is insert() on "lng_modules", which
     * only replaceLangModule() issues.
     */
    private function mockDatabaseForSaveValues(): \PHPUnit\Framework\MockObject\MockObject
    {
        $empty_row_statement = $this->createStub(ilDBStatement::class);
        $empty_row_statement->method('fetchRow')->willReturn(null);

        $remarks_statement = $this->createStub(ilDBStatement::class);
        $lng_modules_select_statement = $this->createStub(ilDBStatement::class);
        $query_f_statement = $this->createStub(ilDBStatement::class);

        $db = $this->createMock(ilDBInterface::class);

        $db->method('quote')->willReturnCallback(
            static fn($value, string $type): string => "'" . (string) $value . "'"
        );

        $db->method('query')->willReturnCallback(
            static function (string $sql) use (
                $empty_row_statement,
                $remarks_statement,
                $lng_modules_select_statement
            ) {
                if (str_contains($sql, 'lng_modules')) {
                    return $lng_modules_select_statement;
                }
                if (str_contains($sql, 'remarks')) {
                    return $remarks_statement;
                }
                return $empty_row_statement;
            }
        );

        // used by ilCachedLanguage::readFromDB() and replaceLangModule()'s self-check; told apart via fetchAssoc()/fetchObject()
        $db->method('queryF')->willReturn($query_f_statement);

        // no pre-existing lng_modules row (as after the "delete" wipe) and no remarks
        $db->method('fetchAssoc')->willReturnCallback(
            static function (ilDBStatement $statement) use (
                $remarks_statement,
                $lng_modules_select_statement,
                $query_f_statement
            ) {
                if ($statement === $query_f_statement) {
                    // the self-check must unserialize to an array or the write counts as failed (mantis #20046/#19140)
                    return ['lang_array' => serialize(['greeting' => 'Hallo, neu'])];
                }
                if ($statement === $remarks_statement || $statement === $lng_modules_select_statement) {
                    return null;
                }
                return null;
            }
        );

        // fetchObject() stays unstubbed (null ends ilCachedLanguage::readFromDB()'s loop)
        $db->method('manipulate')->willReturn(0);
        $db->method('replace')->willReturn(1);

        $this->setGlobalVariable('ilDB', $db);

        return $db;
    }

    /** A module without lng_modules row but with entries to save must get its row (re-)created via replaceLangModule(), not skipped. */
    public function testRecreatesTheLngModulesRowForAModulePresentInTheImport(): void
    {
        $lang_key = 'zz_delete_import_test';
        $this->seedEmptyGlobalLanguageFile($lang_key);
        $db = $this->mockDatabaseForSaveValues();

        $lng = (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor();
        $this->setGlobalVariable('lng', $lng);

        $db->expects($this->once())
            ->method('insert')
            ->with(
                'lng_modules',
                $this->callback(static function (array $values) use ($lang_key): bool {
                    if (($values['lang_key'][1] ?? null) !== $lang_key) {
                        return false;
                    }
                    if (($values['module'][1] ?? null) !== 'testmodule') {
                        return false;
                    }
                    $unserialized = unserialize($values['lang_array'][1] ?? '', ['allowed_classes' => false]);
                    return $unserialized === ['greeting' => 'Hallo, neu'];
                })
            )
            ->willReturn(1);

        ilObjLanguageExt::_saveValues(
            $lang_key,
            ['testmodule' . $lng->separator . 'greeting' => 'Hallo, neu'],
            []
        );
    }

    /** With two modules each row is (re-)created independently, not only the first one of the loop. */
    public function testRecreatesTheLngModulesRowForEveryModulePresentInTheImport(): void
    {
        $lang_key = 'zz_delete_import_test_multi';
        $this->seedEmptyGlobalLanguageFile($lang_key);
        $db = $this->mockDatabaseForSaveValues();

        $lng = (new ReflectionClass(ilLanguage::class))->newInstanceWithoutConstructor();
        $this->setGlobalVariable('lng', $lng);

        $inserted_modules = [];
        $db->expects($this->exactly(2))
            ->method('insert')
            ->with(
                'lng_modules',
                $this->callback(function (array $values) use (&$inserted_modules): bool {
                    $inserted_modules[] = $values['module'][1] ?? null;
                    return true;
                })
            )
            ->willReturn(1);

        ilObjLanguageExt::_saveValues(
            $lang_key,
            [
                'moduleone' . $lng->separator . 'greeting' => 'Hallo',
                'moduletwo' . $lng->separator . 'farewell' => 'Tschüss',
            ],
            []
        );

        sort($inserted_modules);
        $this->assertSame(['moduleone', 'moduletwo'], $inserted_modules);
    }
}
