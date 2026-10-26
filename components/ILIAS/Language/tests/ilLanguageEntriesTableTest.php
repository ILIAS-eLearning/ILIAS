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

use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Component\Input\Input as UiInput;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\HTTP\Services as HttpServices;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Refinery\KindlyTo\Group as KindlyToGroup;
use ILIAS\Refinery\Transformation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

/** Static collaborators (ilObjLanguageAccess, ilObjLanguageExt::_getValues(), ilLanguageFile) are faked via DIC, session and a temp file. */
class ilLanguageEntriesTableTest extends ilLanguageBaseTestCase
{
    /** @var list<string> temp files created by writeLangFile(), removed in tearDown() */
    private array $temp_files_to_remove = [];
    /** @var list<string> temp directories created by tempDir(), rmdir()'d in tearDown() once their files are gone */
    private array $temp_dirs_to_remove = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setGlobalVariable('lng', $this->createMock(ilLanguage::class));

        if (!defined('ILIAS_HTTP_PATH')) {
            define('ILIAS_HTTP_PATH', 'http://ilias.de/');
        }
        if (!defined('ILIAS_VERSION')) {
            define('ILIAS_VERSION', '10.0');
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->temp_files_to_remove as $file) {
            @unlink($file);
        }
        $this->temp_files_to_remove = [];

        // rmdir() needs an empty directory
        foreach ($this->temp_dirs_to_remove as $dir) {
            @rmdir($dir);
        }
        $this->temp_dirs_to_remove = [];

        unset($_SESSION['lang_ext_maintenance']);

        parent::tearDown();
    }


    private function createLng(string $default_language = 'en'): ilLanguage&MockObject
    {
        $lng = $this->createMock(ilLanguage::class);
        $lng->method('txt')->willReturnCallback(static function (string $key): string {
            return match ($key) {
                'language_former_file_missing' => 'missing: %s',
                'language_former_file_equal' => 'equal: %s',
                default => $key,
            };
        });
        $lng->method('getDefaultLanguage')->willReturn($default_language);

        return $lng;
    }

    private function createObject(string $key = 'de'): ilObjLanguageExt&MockObject
    {
        $object = $this->createMock(ilObjLanguageExt::class);
        $object->key = $key;

        return $object;
    }

    private function createTable(
        ilObjLanguageExt $object,
        ilLanguage $lng,
        bool $langmode = false
    ): ilLanguageEntriesTable {
        return new ilLanguageEntriesTable(
            $object,
            $lng,
            $this->createMock(\ILIAS\UI\Factory::class),
            $this->createMock(URLBuilder::class),
            $this->createMock(URLBuilderToken::class),
            $this->createMock(URLBuilderToken::class),
            $langmode
        );
    }

    /**
         * @param array<string, mixed> $values field name => raw value
     * @return array<string, UiInput&MockObject>
     */
    private function filterData(array $values): array
    {
        $filter_data = [];
        foreach ($values as $key => $value) {
            $input = $this->createMock(UiInput::class);
            $input->method('getValue')->willReturn($value);
            $filter_data[$key] = $input;
        }

        return $filter_data;
    }


    private function setIsPageTranslation(bool $is_page_translation): void
    {
        $query_wrapper = $this->createMock(ArrayBasedRequestWrapper::class);
        if ($is_page_translation) {
            $query_wrapper->method('has')->willReturn(true);
            $query_wrapper->method('retrieve')->willReturnCallback(
                static fn(string $key): string => match ($key) {
                    'cmdClass' => 'ilobjlanguageextgui',
                    'view_mode' => 'translate',
                    default => '',
                }
            );
        } else {
            $query_wrapper->method('has')->willReturn(false);
        }

        $wrapper_factory = $this->createMock(WrapperFactory::class);
        $wrapper_factory->method('query')->willReturn($query_wrapper);

        $http = $this->createMock(HttpServices::class);
        $http->method('wrapper')->willReturn($wrapper_factory);

        $transformation = $this->createMock(Transformation::class);
        $kindly_to = $this->createMock(KindlyToGroup::class);
        $kindly_to->method('string')->willReturn($transformation);
        $refinery = $this->createMock(RefineryFactory::class);
        $refinery->method('kindlyTo')->willReturn($kindly_to);

        $this->setGlobalVariable('http', $http);
        $this->setGlobalVariable('refinery', $refinery);
        $this->setGlobalVariable('lng', $this->createLng());
    }

    private function setSavedModulesAndTopics(array $modules, array $topics): void
    {
        $_SESSION['lang_ext_maintenance'] = ['used_modules' => $modules, 'used_topics' => $topics];
    }


    /**
     * @param list<array{module: string, identifier: string, value: string}> $rows
     */
    private function mockDatabaseReturningRows(array $rows): void
    {
        // a fresh statement per query(): _getValues() runs twice per getRows()
        $db = $this->createMock(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($value, string $type): string => "'" . $value . "'");
        $db->method('in')->willReturn('1=1');
        $db->method('like')->willReturn('1=1');
        $db->method('query')->willReturnCallback(function () use ($rows) {
            $statement = $this->createMock(ilDBStatement::class);
            $statement->method('fetchRow')->willReturnOnConsecutiveCalls(...[...$rows, false]);
            return $statement;
        });

        $this->setGlobalVariable('ilDB', $db);
        $this->setGlobalVariable('lng', $this->createLng());
    }


    /**
     * @param array<string, string> $values module#:#topic => value
     */
    private function writeLangFile(string $path, array $values): void
    {
        $lines = ["<!-- language file start -->"];
        foreach ($values as $key => $value) {
            $lines[] = $key . '#:#' . $value;
        }
        file_put_contents($path, implode("\n", $lines) . "\n");
        $this->temp_files_to_remove[] = $path;
    }

    private function tempDir(): string
    {
        $dir = sys_get_temp_dir() . '/ilLanguageEntriesTableTest_' . bin2hex(random_bytes(8));
        mkdir($dir);
        $this->temp_dirs_to_remove[] = $dir;
        return $dir;
    }


    public function testAllModeReturnsGetAllValuesKeysByDefaultWhenNoModeFieldIsBuilt(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->expects($this->atLeastOnce())->method('getAllValues')->with([ilLanguageEntriesTable::DEFAULT_MODULE], '', [])->willReturn([
            'common#:#access' => 'Access',
            'common#:#other' => 'Other',
        ]);

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(
            ['common#:#access', 'common#:#other'],
            $table->getFilteredEntryNames($this->filterData([]))
        );
        self::assertSame(2, $table->getTotalRowCount(null, $this->filterData([]), null));
    }

    public function testUnknownModeStringFallsBackToAllLikeTheSwitchDefaultCaseDoes(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->method('getAllValues')->willReturn(['common#:#access' => 'Access']);

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(
            ['common#:#access'],
            $table->getFilteredEntryNames($this->filterData(['mode' => 'some_unknown_mode']))
        );
    }

    public function testChangedModeDelegatesToGetChangedValues(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->expects($this->once())->method('getChangedValues')
            ->with(['common'], 'pat', [])
            ->willReturn(['common#:#access' => 'Changed', 'common#:#other' => 'Other']);

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(
            ['common#:#access'],
            $table->getFilteredEntryNames($this->filterData([
                'mode' => 'changed',
                'pattern' => 'pat',
                'module' => 'common',
                'identifier' => 'access',
            ]))
        );
    }

    public function testAddedModeDelegatesToGetAddedValues(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->expects($this->once())->method('getAddedValues')
            ->willReturn(['common#:#new' => 'New']);

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(
            ['common#:#new'],
            $table->getFilteredEntryNames($this->filterData(['mode' => 'added']))
        );
    }

    public function testUnchangedModeDelegatesToGetUnchangedValues(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->expects($this->once())->method('getUnchangedValues')
            ->willReturn(['common#:#access' => 'Access']);

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(
            ['common#:#access'],
            $table->getFilteredEntryNames($this->filterData(['mode' => 'unchanged']))
        );
    }

    public function testCommentedModeDelegatesToGetCommentedValues(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->expects($this->once())->method('getCommentedValues')
            ->willReturn(['common#:#access' => 'Access']);

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(
            ['common#:#access'],
            $table->getFilteredEntryNames($this->filterData(['mode' => 'commented']))
        );
    }

    public function testDbremarksModeIntersectsAllValuesWithAllRemarksKeys(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->method('getAllValues')->willReturn([
            'common#:#access' => 'Access',
            'common#:#other' => 'Other',
        ]);
        $object->method('getAllRemarks')->willReturn([
            'common#:#other' => 'a remark',
        ]);
        $object->method('getShippedValues')->willReturn([]);

        $table = $this->createTable($object, $this->createLng(default_language: 'de'));

        self::assertSame(
            ['common#:#other' => 'Other'],
            $this->getFilteredEntriesViaRows($table, $this->filterData(['mode' => 'dbremarks', 'compare' => 'de']))
        );
    }

    public function testEqualModeIntersectsAllValuesWithCompareContentByValue(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn([
            'common#:#access' => 'SameValue',
            'common#:#other' => 'DifferentHere',
        ]);
        $object->method('getShippedValues')->willReturn([
            'common#:#access' => 'SameValue',
            'common#:#other' => 'DifferentThere',
        ]);

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(
            ['common#:#access' => 'SameValue'],
            $this->getFilteredEntriesViaRows($table, $this->filterData(['mode' => 'equal', 'compare' => 'de']))
        );
    }

    public function testDifferentModeDiffsAllValuesAgainstCompareContentByValue(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn([
            'common#:#access' => 'SameValue',
            'common#:#other' => 'DifferentHere',
        ]);
        $object->method('getShippedValues')->willReturn([
            'common#:#access' => 'SameValue',
            'common#:#other' => 'DifferentThere',
        ]);

        $table = $this->createTable($object, $this->createLng(default_language: 'de'));

        self::assertSame(
            ['common#:#other' => 'DifferentHere'],
            $this->getFilteredEntriesViaRows($table, $this->filterData(['mode' => 'different', 'compare' => 'de']))
        );
    }

    public function testCompareContentUsesTheOtherLanguagesDatabaseValuesWhenCompareDiffersFromTheObjectsOwnLanguage(): void
    {
        $this->setIsPageTranslation(false);
        $this->mockDatabaseReturningRows([
            ['module' => 'zztest', 'identifier' => 'access', 'value' => 'EnglishAccess'],
        ]);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn([
            'zztest#:#access' => 'EnglishAccess',
        ]);
        $object->expects($this->never())->method('getShippedValues');

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(
            ['zztest#:#access'],
            $table->getFilteredEntryNames($this->filterData(['mode' => 'equal', 'compare' => 'en']))
        );
    }

    public function testCompareFieldMissingFallsBackToTheLanguageObjectsDefaultLanguage(): void
    {
        self::assertSame('en', $this->captureCompareLangKeyQueriedForMissingCompareField('en'));
        self::assertSame('fr', $this->captureCompareLangKeyQueriedForMissingCompareField('fr'));
    }

    private function captureCompareLangKeyQueriedForMissingCompareField(string $default_language): ?string
    {
        $this->setIsPageTranslation(false);

        $captured_lang_key = null;
        $db = $this->createMock(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(
            function ($value, string $type) use (&$captured_lang_key): string {
                if ($captured_lang_key === null) {
                    $captured_lang_key = $value;
                }
                return "'" . $value . "'";
            }
        );
        $db->method('in')->willReturn('1=1');
        $db->method('like')->willReturn('1=1');
        $statement = $this->createMock(ilDBStatement::class);
        $statement->method('fetchRow')->willReturn(false);
        $db->method('query')->willReturn($statement);
        $this->setGlobalVariable('ilDB', $db);
        $this->setGlobalVariable('lng', $this->createLng());

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn(['common#:#access' => 'X']);

        $table = $this->createTable($object, $this->createLng(default_language: $default_language));
        $table->getFilteredEntryNames($this->filterData(['mode' => 'equal']));

        return $captured_lang_key;
    }


    public function testConflictsModeReturnsNoEntriesAndAFailureNoticeWhenTheFormerFileIsMissing(): void
    {
        $this->setIsPageTranslation(false);

        $dir = $this->tempDir(); // no ilias_de.lang written into it
        $object = $this->createObject('de');
        $object->method('getDataPath')->willReturn($dir);
        $object->expects($this->never())->method('getChangedValues');

        $table = $this->createTable($object, $this->createLng());
        $filter_data = $this->filterData(['mode' => 'conflicts']);

        self::assertSame([], $table->getFilteredEntryNames($filter_data));

        $notice = $table->getConflictsNotice($filter_data);
        self::assertSame('failure', $notice['type']);
        self::assertStringContainsString($dir . '/ilias_de.lang', $notice['text']);
        self::assertStringContainsString('language_former_file_description', $notice['text']);
    }

    public function testConflictsModeReturnsNoEntriesAndAnInfoNoticeWhenTheFormerFileIsIdenticalToTheGlobalFile(): void
    {
        $this->setIsPageTranslation(false);

        $dir = $this->tempDir();
        $former_file = $dir . '/ilias_de.lang';
        $this->writeLangFile($former_file, ['common#:#access' => 'SameValue']);

        $object = $this->createObject('de');
        $object->method('getDataPath')->willReturn($dir);
        $object->method('getShippedChangesSinceBackup')->willReturn([]);
        $object->expects($this->never())->method('getChangedValues');

        $table = $this->createTable($object, $this->createLng());
        $filter_data = $this->filterData(['mode' => 'conflicts']);

        self::assertSame([], $table->getFilteredEntryNames($filter_data));

        $notice = $table->getConflictsNotice($filter_data);
        self::assertSame('info', $notice['type']);
        self::assertStringContainsString($former_file, $notice['text']);
    }

    public function testConflictsModeReturnsChangedValuesIntersectedWithGlobalChangesAndNoNoticeWhenFilesDiffer(): void
    {
        $this->setIsPageTranslation(false);

        $dir = $this->tempDir();
        $former_file = $dir . '/ilias_de.lang';
        $this->writeLangFile($former_file, [
            'common#:#access' => 'OldAccess',
            'common#:#other' => 'SameOther',
        ]);

        $object = $this->createObject('de');
        $object->method('getDataPath')->willReturn($dir);
        $object->method('getShippedChangesSinceBackup')->willReturn(['common#:#access' => 'NewAccess']);
        $object->method('getChangedValues')->willReturn([
            'common#:#access' => 'LocallyChangedAccess',
            'common#:#other' => 'LocallyChangedOther',
        ]);

        $table = $this->createTable($object, $this->createLng());
        $filter_data = $this->filterData(['mode' => 'conflicts']);

        self::assertSame(
            ['common#:#access'],
            $table->getFilteredEntryNames($filter_data)
        );
        self::assertNull($table->getConflictsNotice($filter_data));
    }

    public function testGetConflictsNoticeReturnsNullWhenTheCurrentFilterModeIsNotConflicts(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->expects($this->never())->method('getDataPath');

        $table = $this->createTable($object, $this->createLng());

        self::assertNull($table->getConflictsNotice($this->filterData(['mode' => 'all'])));
        self::assertNull($table->getConflictsNotice($this->filterData([])));
    }


    public function testPageTranslationModeIgnoresTheModeFilterAndUsesSessionSavedModulesAndTopics(): void
    {
        $this->setIsPageTranslation(true);

        $first_in_calls = $this->captureDbInCallsForSavedModulesAndTopics(['common'], ['access']);
        self::assertSame(['module', ['common'], false, 'text'], $first_in_calls[0]);
        self::assertSame(['identifier', ['access'], false, 'text'], $first_in_calls[1]);

        $second_in_calls = $this->captureDbInCallsForSavedModulesAndTopics(['other'], ['xyz']);
        self::assertSame(['module', ['other'], false, 'text'], $second_in_calls[0]);
        self::assertSame(['identifier', ['xyz'], false, 'text'], $second_in_calls[1]);
    }

    /** @return list<array> */
    private function captureDbInCallsForSavedModulesAndTopics(array $modules, array $topics): array
    {
        $this->setSavedModulesAndTopics($modules, $topics);

        $captured_in_calls = [];
        $db = $this->createMock(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(static fn($value, string $type): string => "'" . $value . "'");
        $db->method('in')->willReturnCallback(function (...$args) use (&$captured_in_calls): string {
            $captured_in_calls[] = $args;
            return '1=1';
        });
        $db->method('like')->willReturn('1=1');
        $statement = $this->createMock(ilDBStatement::class);
        $statement->method('fetchRow')->willReturn(false);
        $db->method('query')->willReturn($statement);
        $this->setGlobalVariable('ilDB', $db);
        $this->setGlobalVariable('lng', $this->createLng());

        $object = $this->createObject('de');
        $object->expects($this->never())->method('getAllValues');
        $object->expects($this->never())->method('getChangedValues');

        $table = $this->createTable($object, $this->createLng());
        $table->getFilteredEntryNames($this->filterData(['mode' => 'changed', 'module' => 'other']));

        return $captured_in_calls;
    }


    private function getFilteredEntriesViaRows(ilLanguageEntriesTable $table, array $filter_data): array
    {
        $captured = [];
        $row_builder = $this->createMock(DataRowBuilder::class);
        $row_builder->method('buildDataRow')->willReturnCallback(
            function (string $id, array $record) use (&$captured) {
                $captured[$id] = $record['translation'];
                return $this->createMock(DataRow::class);
            }
        );

        iterator_to_array($table->getRows(
            $row_builder,
            [],
            new Range(0, 1000),
            new Order('module', Order::ASC),
            null,
            $filter_data,
            null
        ));

        return $captured;
    }

    public function testGetRowsSplitsTheEntryNameIntoModuleAndTopicUsingTheLanguagesSeparator(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn(['common#:#access' => 'Access']);
        $object->method('getShippedValues')->willReturn([]);

        $table = $this->createTable($object, $this->createLng(default_language: 'de'));

        $row_builder = $this->createMock(DataRowBuilder::class);
        $captured = [];
        $row_builder->method('buildDataRow')->willReturnCallback(
            function (string $id, array $record) use (&$captured) {
                $captured[] = [$id, $record];
                return $this->createMock(DataRow::class);
            }
        );

        iterator_to_array($table->getRows(
            $row_builder,
            [],
            new Range(0, 10),
            new Order('module', Order::ASC),
            null,
            $this->filterData(['compare' => 'de']),
            null
        ));

        self::assertCount(1, $captured);
        [$id, $record] = $captured[0];
        self::assertSame('common#:#access', $id);
        self::assertSame('common', $record['module']);
        self::assertSame('access', $record['topic']);
        self::assertSame('Access', $record['translation']);
        self::assertSame('', $record['default']); // no matching compare-content entry
    }

    public function testGetRowsFallsBackToEmptyStringsForAnEntryNameWithoutASeparator(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn(['malformed_key_without_separator' => 'Value']);
        $object->method('getShippedValues')->willReturn([]);

        $table = $this->createTable($object, $this->createLng(default_language: 'de'));

        $captured = [];
        $row_builder = $this->createMock(DataRowBuilder::class);
        $row_builder->method('buildDataRow')->willReturnCallback(
            function (string $id, array $record) use (&$captured) {
                $captured[] = $record;
                return $this->createMock(DataRow::class);
            }
        );

        iterator_to_array($table->getRows(
            $row_builder,
            [],
            new Range(0, 10),
            new Order('module', Order::ASC),
            null,
            $this->filterData(['compare' => 'de']),
            null
        ));

        self::assertSame('malformed_key_without_separator', $captured[0]['module']);
        self::assertSame('', $captured[0]['topic']);
    }

    public function testGetRowsAppliesRangeAsAnOffsetAndLengthOverTheFilteredEntries(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn([
            'common#:#a' => 'A',
            'common#:#b' => 'B',
            'common#:#c' => 'C',
        ]);
        $object->method('getShippedValues')->willReturn([]);

        $table = $this->createTable($object, $this->createLng(default_language: 'de'));

        $captured = [];
        $row_builder = $this->createMock(DataRowBuilder::class);
        $row_builder->method('buildDataRow')->willReturnCallback(
            function (string $id, array $record) use (&$captured) {
                $captured[] = $id;
                return $this->createMock(DataRow::class);
            }
        );

        iterator_to_array($table->getRows(
            $row_builder,
            [],
            new Range(1, 1),
            new Order('module', Order::ASC),
            null,
            $this->filterData([]),
            null
        ));

        self::assertSame(['common#:#b'], $captured);
    }

    public function testGetTotalRowCountIsNotLimitedByTheRequestedRange(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn([
            'common#:#a' => 'A',
            'common#:#b' => 'B',
            'common#:#c' => 'C',
        ]);

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(3, $table->getTotalRowCount(null, $this->filterData([]), null));
    }


    public function testModuleFilterValueAllIsResolvedToAnEmptyModulesList(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->expects($this->once())->method('getChangedValues')
            ->with([], '', [])
            ->willReturn([]);

        $table = $this->createTable($object, $this->createLng());

        $table->getFilteredEntryNames($this->filterData(['mode' => 'changed', 'module' => 'all']));
    }

    public function testEmptyIdentifierFilterValueIsResolvedToAnEmptyTopicsList(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->expects($this->once())->method('getChangedValues')
            ->with([], '', [])
            ->willReturn([]);

        $table = $this->createTable($object, $this->createLng());

        $table->getFilteredEntryNames($this->filterData(['mode' => 'changed', 'module' => 'all', 'identifier' => '']));
    }

    public function testIdentifierFilterMatchesSubstringsCaseInsensitively(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->expects($this->once())->method('getAllValues')
            ->with([], '', [])
            ->willReturn([
                'acc#:#acc_criterion_assignment_must_be_unique_insert' => 'A',
                'acc#:#acc_crit_type_usr_language' => 'B',
                'acc#:#acc_document' => 'C',
                'crit#:#no_match_in_identifier' => 'D',
                'common#:#CRITICAL' => 'E',
            ]);

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(
            [
                'acc#:#acc_criterion_assignment_must_be_unique_insert',
                'acc#:#acc_crit_type_usr_language',
                'common#:#CRITICAL',
            ],
            $table->getFilteredEntryNames($this->filterData(['module' => 'all', 'identifier' => 'crit']))
        );
    }

    public function testIdentifierFilterIsIgnoredInPageTranslationMode(): void
    {
        $this->setIsPageTranslation(true);
        $this->setSavedModulesAndTopics(['zztest'], ['access']);
        $this->mockDatabaseReturningRows([['module' => 'zztest', 'identifier' => 'access', 'value' => 'A']]);

        $table = $this->createTable($this->createObject(), $this->createLng());

        self::assertSame(
            ['zztest#:#access'],
            $table->getFilteredEntryNames($this->filterData(['identifier' => 'xyz']))
        );
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function identifierFilterLiteralNeedleProvider(): array
    {
        return [
            'underscore is no single-char wildcard' => ['a_b', ['m#:#xa_bx']],
            'percent is no multi-char wildcard' => ['%', ['m#:#100%']],
            'lone underscore only matches real underscores' => ['_', ['m#:#xa_bx']],
            'regex metacharacters are literal' => ['.*', []],
            'needle with separator part never matches across module' => ['m#:#', []],
            'surrounding whitespace is ignored' => [' item ', ['m#:#item0']],
            'whitespace-only needle applies no filter' => [
                ' ',
                ['m#:#xa_bx', 'm#:#xaZbx', 'm#:#100%', 'm#:#100', 'm#:#with space', 'm#:#item0', 'm#:#plain'],
            ],
            'zero is a real needle, not empty' => ['0', ['m#:#100%', 'm#:#100', 'm#:#item0']],
        ];
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('identifierFilterLiteralNeedleProvider')]
    public function testIdentifierFilterTreatsTheNeedleLiterally(string $needle, array $expected): void
    {
        $this->setIsPageTranslation(false);

        $entries = [
            'm#:#xa_bx' => 'A',
            'm#:#xaZbx' => 'B',
            'm#:#100%' => 'C',
            'm#:#100' => 'D',
            'm#:#with space' => 'E',
            'm#:#item0' => 'F',
            'm#:#plain' => 'G',
        ];
        $object = $this->createObject();
        $object->method('getAllValues')->willReturn($entries);

        $table = $this->createTable($object, $this->createLng());

        $names = $table->getFilteredEntryNames($this->filterData(['identifier' => $needle]));
        self::assertSame($expected, $names);
    }

    public function testIdentifierFilterDoesNotMatchTheModuleName(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->method('getAllValues')->willReturn([
            'access#:#document' => 'A',
            'common#:#access_log' => 'B',
        ]);

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(
            ['common#:#access_log'],
            $table->getFilteredEntryNames($this->filterData(['identifier' => 'access']))
        );
        $other_table = $this->createTable($object, $this->createLng());
        self::assertSame([], $other_table->getFilteredEntryNames($this->filterData(['identifier' => 'common'])));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function identifierFilterModeProvider(): array
    {
        return [
            'changed' => ['changed'],
            'added' => ['added'],
            'unchanged' => ['unchanged'],
            'commented' => ['commented'],
            'all' => ['all'],
        ];
    }

    #[DataProvider('identifierFilterModeProvider')]
    public function testIdentifierFilterAppliesInEveryDelegatingModeAndKeepsModuleAndPatternArguments(string $mode): void
    {
        $this->setIsPageTranslation(false);

        $method = match ($mode) {
            'changed' => 'getChangedValues',
            'added' => 'getAddedValues',
            'unchanged' => 'getUnchangedValues',
            'commented' => 'getCommentedValues',
            default => 'getAllValues',
        };
        $object = $this->createObject();
        $object->expects($this->once())->method($method)
            ->with(['common'], 'pat', [])
            ->willReturn(['common#:#Access' => 'A', 'common#:#other' => 'B']);

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(
            ['common#:#Access'],
            $table->getFilteredEntryNames($this->filterData([
                'mode' => $mode,
                'pattern' => 'pat',
                'module' => 'common',
                'identifier' => 'ACC',
            ]))
        );
    }

    public function testIdentifierFilterAppliesToDbremarksAndConflictsModes(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn(['common#:#access' => 'A', 'common#:#other' => 'B']);
        $object->method('getAllRemarks')->willReturn(['common#:#access' => 'r', 'common#:#other' => 'r']);
        $table = $this->createTable($object, $this->createLng());
        self::assertSame(
            ['common#:#other'],
            $table->getFilteredEntryNames($this->filterData(['mode' => 'dbremarks', 'identifier' => 'OTH']))
        );

        $dir = $this->tempDir();
        $this->writeLangFile($dir . '/ilias_de.lang', ['common#:#access' => 'Old', 'common#:#other' => 'Old']);
        $object = $this->createObject('de');
        $object->method('getDataPath')->willReturn($dir);
        $object->method('getShippedChangesSinceBackup')->willReturn(['common#:#access' => 'New', 'common#:#other' => 'New']);
        $object->method('getChangedValues')->willReturn(['common#:#access' => 'L', 'common#:#other' => 'L']);
        $table = $this->createTable($object, $this->createLng());
        self::assertSame(
            ['common#:#access'],
            $table->getFilteredEntryNames($this->filterData(['mode' => 'conflicts', 'identifier' => 'acc']))
        );
    }

    public function testIdentifierFilterCombinesWithEqualAndDifferentModesAndKeepsCompareContentIntact(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn([
            'common#:#access' => 'Same',
            'common#:#access_log' => 'Mine',
            'common#:#other' => 'Same',
        ]);
        $object->method('getShippedValues')->willReturn([
            'common#:#access' => 'Same',
            'common#:#access_log' => 'Theirs',
            'common#:#other' => 'Same',
        ]);

        $equal = $this->createTable($object, $this->createLng(default_language: 'de'));
        self::assertSame(
            ['common#:#access' => 'Same'],
            $this->getFilteredEntriesViaRows(
                $equal,
                $this->filterData(['mode' => 'equal', 'compare' => 'de', 'identifier' => 'access'])
            )
        );

        $different = $this->createTable($object, $this->createLng(default_language: 'de'));
        self::assertSame(
            ['common#:#access_log' => 'Mine'],
            $this->getFilteredEntriesViaRows(
                $different,
                $this->filterData(['mode' => 'different', 'compare' => 'de', 'identifier' => 'access'])
            )
        );
    }

    public function testRowsAndTotalRowCountAreConsistentWithTheIdentifierFilterAndPagination(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn([
            'a#:#crit_1' => 'A',
            'a#:#other' => 'B',
            'b#:#crit_2' => 'C',
            'c#:#crit_3' => 'D',
        ]);
        $object->method('getShippedValues')->willReturn(['b#:#crit_2' => 'compare-value']);

        $table = $this->createTable($object, $this->createLng(default_language: 'de'));
        $filter_data = $this->filterData(['compare' => 'de', 'identifier' => 'crit']);

        self::assertSame(3, $table->getTotalRowCount(null, $filter_data, null));

        $captured = [];
        $row_builder = $this->createMock(DataRowBuilder::class);
        $row_builder->method('buildDataRow')->willReturnCallback(
            function (string $id, array $record) use (&$captured) {
                $captured[$id] = $record;
                return $this->createMock(DataRow::class);
            }
        );
        iterator_to_array($table->getRows(
            $row_builder,
            [],
            new Range(1, 5),
            new Order('module', Order::ASC),
            null,
            $filter_data,
            null
        ));

        self::assertSame(['b#:#crit_2', 'c#:#crit_3'], array_keys($captured));
        self::assertSame('compare-value', $captured['b#:#crit_2']['default']);
        self::assertSame('crit_2', $captured['b#:#crit_2']['topic']);
    }

    public function testFilterValueFallsBackToDefaultWhenTheWholeFilterDataIsNotAnArray(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->expects($this->atLeastOnce())->method('getAllValues')->with([ilLanguageEntriesTable::DEFAULT_MODULE], '', [])->willReturn(['common#:#a' => 'A']);

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(['common#:#a'], $table->getFilteredEntryNames(null));
    }


    public function testGetRowsMarksCommentedColumnTrueOnlyForEntriesThatHaveARemark(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn([
            'common#:#access' => 'Access',
            'common#:#other' => 'Other',
        ]);
        $object->method('getEffectiveRemarks')->willReturn([
            'common#:#access' => 'a remark',
        ]);
        $object->method('getShippedValues')->willReturn([]);

        $table = $this->createTable($object, $this->createLng(default_language: 'de'), true);

        $captured = [];
        $row_builder = $this->createMock(DataRowBuilder::class);
        $row_builder->method('buildDataRow')->willReturnCallback(
            function (string $id, array $record) use (&$captured) {
                $captured[$id] = $record['commented'];
                return $this->createMock(DataRow::class);
            }
        );

        iterator_to_array($table->getRows(
            $row_builder,
            [],
            new Range(0, 10),
            new Order('module', Order::ASC),
            null,
            $this->filterData(['compare' => 'de']),
            null
        ));

        self::assertTrue($captured['common#:#access']);
        self::assertFalse($captured['common#:#other']);
    }


    public function testCommentedColumnAndRecordFieldExistOnlyInLangmode(): void
    {
        $this->setIsPageTranslation(false);

        foreach ([true, false] as $langmode) {
            $captured_titles = [];
            [$ui_factory] = $this->stubUiFactoryCapturingColumnTitles($captured_titles);
            $object = $this->createObject('de');
            $object->method('getAllValues')->willReturn(['common#:#access' => 'Access']);
            if ($langmode) {
                $object->method('getEffectiveRemarks')->willReturn(['common#:#access' => 'remark']);
            } else {
                $object->expects($this->never())->method('getEffectiveRemarks');
            }
            $object->method('getShippedValues')->willReturn([]);

            $table = new ilLanguageEntriesTable(
                $object,
                $this->createLng(),
                $ui_factory,
                $this->createMock(URLBuilder::class),
                $this->createMock(URLBuilderToken::class),
                $this->createMock(URLBuilderToken::class),
                $langmode
            );

            $filter_data = $this->filterData(['compare' => 'de']);
            $columns = $this->invokeProtectedMethod($table, 'getColumns', [$filter_data]);

            $record = null;
            $row_builder = $this->createMock(DataRowBuilder::class);
            $row_builder->method('buildDataRow')->willReturnCallback(
                function (string $id, array $r) use (&$record) {
                    $record = $r;
                    return $this->createMock(DataRow::class);
                }
            );
            iterator_to_array($table->getRows(
                $row_builder,
                [],
                new Range(0, 10),
                new Order('module', Order::ASC),
                null,
                $filter_data,
                null
            ));

            $expected = $langmode
                ? ['module', 'topic', 'translation', 'default', 'commented']
                : ['module', 'topic', 'translation', 'default'];
            self::assertSame($expected, array_keys($columns), 'columns, langmode=' . var_export($langmode, true));
            self::assertSame($expected, array_keys($record), 'record, langmode=' . var_export($langmode, true));
        }
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function commentFilterProvider(): array
    {
        return [
            'substring hit' => ['remark', ['a#:#one', 'a#:#three']],
            'case-insensitive' => ['REMARK', ['a#:#one', 'a#:#three']],
            'umlaut case-insensitive' => ['ärger', ['a#:#two']],
            'surrounding whitespace trimmed' => ['  remark ', ['a#:#one', 'a#:#three']],
            'no hit' => ['nothing', []],
            'empty is no filter' => ['', ['a#:#one', 'a#:#two', 'a#:#three', 'a#:#four']],
        ];
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('commentFilterProvider')]
    public function testCommentFilterMatchesTheDatabaseRemarkInLangmode(string $needle, array $expected): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->method('getAllValues')->willReturn([
            'a#:#one' => '1',
            'a#:#two' => '2',
            'a#:#three' => '3',
            'a#:#four' => '4',
        ]);
        $object->method('getEffectiveRemarks')->willReturn([
            'a#:#one' => 'Some remark',
            'a#:#two' => 'viel Ärger',
            'a#:#three' => 'another REMARK here',
        ]);

        $table = $this->createTable($object, $this->createLng(), true);

        self::assertSame($expected, $table->getFilteredEntryNames($this->filterData(['comment' => $needle])));
    }

    public function testCommentFilterIsIgnoredOutsideLangmode(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->method('getAllValues')->willReturn(['a#:#one' => '1', 'a#:#two' => '2']);
        $object->expects($this->never())->method('getEffectiveRemarks');

        $table = $this->createTable($object, $this->createLng(), false);

        self::assertSame(['a#:#one', 'a#:#two'], $table->getFilteredEntryNames($this->filterData(['comment' => 'x'])));
    }

    public function testCommentFilterIsIgnoredInPageTranslationMode(): void
    {
        $this->setIsPageTranslation(true);
        $this->setSavedModulesAndTopics(['zztest'], ['access']);
        $this->mockDatabaseReturningRows([['module' => 'zztest', 'identifier' => 'access', 'value' => 'A']]);

        $object = $this->createObject();
        $object->expects($this->never())->method('getEffectiveRemarks');

        $table = $this->createTable($object, $this->createLng(), true);

        self::assertSame(
            ['zztest#:#access'],
            $table->getFilteredEntryNames($this->filterData(['comment' => 'xyz']))
        );
    }


    public function testGetCompareTitleAddsTheHintOnlyWhenTheCompareLanguageIsTheObjectsOwn(): void
    {
        $table = $this->createTable($this->createObject('de'), $this->createLng());

        self::assertSame('meta_l_de language_default_entries', $table->getCompareTitle($this->filterData(['compare' => 'de'])));
        self::assertSame('meta_l_en', $table->getCompareTitle($this->filterData(['compare' => 'en'])));
    }

    public function testGetCompareTitleFallsBackToTheDefaultLanguageWithoutCompareField(): void
    {
        $table = $this->createTable($this->createObject('de'), $this->createLng('fr'));

        self::assertSame('meta_l_fr', $table->getCompareTitle($this->filterData([])));
    }

    public function testGetCompareValuesReturnsTheShippedValuesForTheObjectsOwnLanguage(): void
    {
        $this->setIsPageTranslation(false);
        $object = $this->createObject('de');
        $object->method('getShippedValues')->willReturn(['common#:#access' => 'Zugang']);

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(['common#:#access' => 'Zugang'], $table->getCompareValues($this->filterData(['compare' => 'de'])));
    }

    public function testGetCompareValuesReturnsTheDatabaseValuesOfAnotherLanguage(): void
    {
        $this->setIsPageTranslation(false);
        $this->mockDatabaseReturningRows([['module' => 'zztest', 'identifier' => 'access', 'value' => 'Access']]);
        $object = $this->createObject('de');
        $object->expects($this->never())->method('getShippedValues');

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(['zztest#:#access' => 'Access'], $table->getCompareValues($this->filterData(['compare' => 'en'])));
    }


    public static function sortEntryNamesProvider(): array
    {
        return [
            'module asc' => ['module', Order::ASC, ['aaa#:#zzz', 'bbb#:#yyy', 'ccc#:#xxx']],
            'module desc' => ['module', Order::DESC, ['ccc#:#xxx', 'bbb#:#yyy', 'aaa#:#zzz']],
            'topic asc' => ['topic', Order::ASC, ['ccc#:#xxx', 'bbb#:#yyy', 'aaa#:#zzz']],
            'topic desc' => ['topic', Order::DESC, ['aaa#:#zzz', 'bbb#:#yyy', 'ccc#:#xxx']],
            'translation asc' => ['translation', Order::ASC, ['bbb#:#yyy', 'ccc#:#xxx', 'aaa#:#zzz']],
            'translation desc' => ['translation', Order::DESC, ['aaa#:#zzz', 'ccc#:#xxx', 'bbb#:#yyy']],
            'default asc' => ['default', Order::ASC, ['ccc#:#xxx', 'bbb#:#yyy', 'aaa#:#zzz']],
            'default desc' => ['default', Order::DESC, ['aaa#:#zzz', 'bbb#:#yyy', 'ccc#:#xxx']],
        ];
    }

    #[DataProvider('sortEntryNamesProvider')]
    public function testGetRowsSortsEntriesByTheRequestedFieldAndDirection(
        string $field,
        string $direction,
        array $expected_order
    ): void {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn([
            'aaa#:#zzz' => 'C',
            'bbb#:#yyy' => 'A',
            'ccc#:#xxx' => 'B',
        ]);
        $object->method('getShippedValues')->willReturn([
            'aaa#:#zzz' => 'Z',
            'bbb#:#yyy' => 'Y',
            'ccc#:#xxx' => 'X',
        ]);

        $table = $this->createTable($object, $this->createLng(default_language: 'de'));

        self::assertSame(
            $expected_order,
            $this->getEntryIdOrderViaRows(
                $table,
                $this->filterData(['compare' => 'de']),
                new Order($field, $direction)
            )
        );
    }

    /** DESC must flip the comparator, not array_reverse() the ASC result, which flips ties. */
    public function testGetRowsSortDescPreservesTheOriginalRelativeOrderOfEntriesWithATiedSortKey(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn([
            'same#:#first' => 'A',
            'same#:#second' => 'B',
            'other#:#x' => 'C',
        ]);
        $object->method('getShippedValues')->willReturn([]);

        $table = $this->createTable($object, $this->createLng(default_language: 'de'));

        self::assertSame(
            ['same#:#first', 'same#:#second', 'other#:#x'],
            $this->getEntryIdOrderViaRows(
                $table,
                $this->filterData(['compare' => 'de']),
                new Order('module', Order::DESC)
            )
        );
    }

    /** @return list<string> */
    private function getEntryIdOrderViaRows(ilLanguageEntriesTable $table, array $filter_data, Order $order): array
    {
        $captured = [];
        $row_builder = $this->createMock(DataRowBuilder::class);
        $row_builder->method('buildDataRow')->willReturnCallback(
            function (string $id, array $record) use (&$captured) {
                $captured[] = $id;
                return $this->createMock(DataRow::class);
            }
        );

        iterator_to_array($table->getRows(
            $row_builder,
            [],
            new Range(0, 100),
            $order,
            null,
            $filter_data,
            null
        ));

        return $captured;
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}> module sort direction, entries in that order
     */
    public static function naturalCaseInsensitiveSortProvider(): array
    {
        return [
            'item2 before item10 ascending' => [Order::ASC, ['m#:#item2', 'm#:#item10', 'm#:#Item11']],
            'item11 first descending' => [Order::DESC, ['m#:#Item11', 'm#:#item10', 'm#:#item2']],
        ];
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('naturalCaseInsensitiveSortProvider')]
    public function testGetRowsSortsNaturallyAndIgnoringCase(string $direction, array $expected): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn(['m#:#item10' => 'x', 'm#:#Item11' => 'x', 'm#:#item2' => 'x']);
        $global_file = $this->createMock(ilLanguageFile::class);
        $global_file->method('getAllValues')->willReturn([]);
        $object->method('getGlobalLanguageFile')->willReturn($global_file);
        $table = $this->createTable($object, $this->createLng(default_language: 'de'));

        self::assertSame(
            $expected,
            $this->getEntryIdOrderViaRows($table, $this->filterData(['compare' => 'de']), new Order('topic', $direction))
        );
    }

    public function testGetRowsIgnoresCaseWhenSorting(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn(['b#:#t' => 'x', 'A#:#t' => 'x', 'c#:#t' => 'x']);
        $global_file = $this->createMock(ilLanguageFile::class);
        $global_file->method('getAllValues')->willReturn([]);
        $object->method('getGlobalLanguageFile')->willReturn($global_file);
        $table = $this->createTable($object, $this->createLng(default_language: 'de'));

        self::assertSame(
            ['A#:#t', 'b#:#t', 'c#:#t'],
            $this->getEntryIdOrderViaRows($table, $this->filterData(['compare' => 'de']), new Order('module', Order::ASC))
        );
    }

    public function testGetRowsSortsANumericEntryNameWhichPhpStoresAsAnIntKey(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn(['m#:#a' => 'x', '12' => 'x']);
        $global_file = $this->createMock(ilLanguageFile::class);
        $global_file->method('getAllValues')->willReturn([]);
        $object->method('getGlobalLanguageFile')->willReturn($global_file);
        $table = $this->createTable($object, $this->createLng(default_language: 'de'));

        self::assertSame(
            ['12', 'm#:#a'],
            $this->getEntryIdOrderViaRows($table, $this->filterData(['compare' => 'de']), new Order('module', Order::ASC))
        );
    }

    public function testGetRowsEscapesModuleTopicTranslationAndDefaultButKeepsTheRowIdRaw(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn(['<b>m#:#"t"' => '<b>x</b> & \'y\'']);
        $object->method('getShippedValues')->willReturn(['<b>m#:#"t"' => '<script>alert(1)</script>']);
        $table = $this->createTable($object, $this->createLng(default_language: 'de'));

        $captured = [];
        $row_builder = $this->createMock(DataRowBuilder::class);
        $row_builder->method('buildDataRow')->willReturnCallback(
            function (string $id, array $record) use (&$captured) {
                $captured = [$id, $record];
                return $this->createMock(DataRow::class);
            }
        );
        iterator_to_array($table->getRows(
            $row_builder,
            [],
            new Range(0, 10),
            new Order('module', Order::ASC),
            null,
            $this->filterData(['compare' => 'de']),
            null
        ));

        self::assertSame('<b>m#:#"t"', $captured[0]);
        self::assertSame('&lt;b&gt;m', $captured[1]['module']);
        self::assertSame('&quot;t&quot;', $captured[1]['topic']);
        self::assertSame('&lt;b&gt;x&lt;/b&gt; &amp; &#039;y&#039;', $captured[1]['translation']);
        self::assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', $captured[1]['default']);
    }

    /** Escaping must come after sorting: raw '"' sorts before raw '<', '&quot;' after '&lt;'. */
    public function testGetRowsSortsOnTheRawValuesNotOnTheEscapedOnes(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn(['m#:#lt' => '<', 'm#:#quot' => '"']);
        $global_file = $this->createMock(ilLanguageFile::class);
        $global_file->method('getAllValues')->willReturn([]);
        $object->method('getGlobalLanguageFile')->willReturn($global_file);
        $table = $this->createTable($object, $this->createLng(default_language: 'de'));

        self::assertSame(
            ['m#:#quot', 'm#:#lt'],
            $this->getEntryIdOrderViaRows(
                $table,
                $this->filterData(['compare' => 'de']),
                new Order('translation', Order::ASC)
            )
        );
    }

    public function testIdentifierFilterMatchesMarkupCharactersOnTheRawIdentifier(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->method('getAllValues')->willReturn(['m#:#x<b>y' => 'A', 'm#:#plain' => 'B']);
        $table = $this->createTable($object, $this->createLng());

        self::assertSame(
            ['m#:#x<b>y'],
            $table->getFilteredEntryNames($this->filterData(['identifier' => '<b>']))
        );
    }


    public function testFilteredEntriesAreMemoizedAcrossGetTotalRowCountAndGetRows(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->expects($this->once())->method('getAllValues')->willReturn([
            'common#:#access' => 'Access',
        ]);
        $object->method('getShippedValues')->willReturn([]);

        $table = $this->createTable($object, $this->createLng(default_language: 'de'));
        $filter_data = $this->filterData(['compare' => 'de']);

        self::assertSame(1, $table->getTotalRowCount(null, $filter_data, null));

        $row_builder = $this->createMock(DataRowBuilder::class);
        $row_builder->method('buildDataRow')->willReturn($this->createMock(DataRow::class));
        iterator_to_array($table->getRows(
            $row_builder,
            [],
            new Range(0, 10),
            new Order('module', Order::ASC),
            null,
            $filter_data,
            null
        ));
    }


    private function invokeProtectedMethod(object $object, string $method_name, array $args = []): mixed
    {
        return (new ReflectionMethod($object, $method_name))->invoke($object, ...$args);
    }

    /** @param list<string> $captured_titles */
    /**
     * The Table\Factory mock is returned too: $ui_factory->table() is typed without method().
     *
     * @return array{0: \ILIAS\UI\Factory, 1: \ILIAS\UI\Component\Table\Factory&MockObject}
     */
    private function stubUiFactoryCapturingColumnTitles(array &$captured_titles): array
    {
        $text_column = $this->createMock(\ILIAS\UI\Component\Table\Column\Text::class);

        $column_factory = $this->createMock(\ILIAS\UI\Component\Table\Column\Factory::class);
        $column_factory->method('text')->willReturnCallback(
            function (string $title) use (&$captured_titles, $text_column) {
                $captured_titles[] = $title;
                return $text_column;
            }
        );
        $bool_column = $this->createMock(\ILIAS\UI\Component\Table\Column\Boolean::class);
        $bool_column->method('withIsSortable')->willReturn($bool_column);
        $column_factory->method('boolean')->willReturn($bool_column);

        $icon = $this->createMock(\ILIAS\UI\Component\Symbol\Icon\Custom::class);
        $icon_factory = $this->createMock(\ILIAS\UI\Component\Symbol\Icon\Factory::class);
        $icon_factory->method('custom')->willReturn($icon);
        $symbol_factory = $this->createMock(\ILIAS\UI\Component\Symbol\Factory::class);
        $symbol_factory->method('icon')->willReturn($icon_factory);

        $table_factory = $this->createMock(\ILIAS\UI\Component\Table\Factory::class);
        $table_factory->method('column')->willReturn($column_factory);

        $ui_factory = $this->createMock(\ILIAS\UI\Factory::class);
        $ui_factory->method('table')->willReturn($table_factory);
        $ui_factory->method('symbol')->willReturn($symbol_factory);

        return [$ui_factory, $table_factory];
    }

    public function testGetColumnsDefaultTitleUsesCompareLanguageAndAddsHintWhenCompareEqualsOwnLanguage(): void
    {
        $captured_titles = [];
        [$ui_factory] = $this->stubUiFactoryCapturingColumnTitles($captured_titles);
        $object = $this->createObject('de');

        $table = new ilLanguageEntriesTable(
            $object,
            $this->createLng(),
            $ui_factory,
            $this->createMock(URLBuilder::class),
            $this->createMock(URLBuilderToken::class),
            $this->createMock(URLBuilderToken::class),
            false
        );

        $this->invokeProtectedMethod($table, 'getColumns', [$this->filterData(['compare' => 'de'])]);

        self::assertSame('meta_l_de language_default_entries', $captured_titles[3]);
    }

    public function testGetColumnsDefaultTitleHasNoHintWhenCompareDiffersFromOwnLanguage(): void
    {
        $captured_titles = [];
        [$ui_factory] = $this->stubUiFactoryCapturingColumnTitles($captured_titles);
        $object = $this->createObject('de');

        $table = new ilLanguageEntriesTable(
            $object,
            $this->createLng(),
            $ui_factory,
            $this->createMock(URLBuilder::class),
            $this->createMock(URLBuilderToken::class),
            $this->createMock(URLBuilderToken::class),
            false
        );

        $this->invokeProtectedMethod($table, 'getColumns', [$this->filterData(['compare' => 'en'])]);

        self::assertSame('meta_l_en', $captured_titles[3]);
    }


    /**
     * @return array{0: \ILIAS\UI\Factory, 1: \ILIAS\UI\Component\Table\Data&MockObject}
     */
    private function stubUiFactoryForGetTable(): array
    {
        $captured_titles = [];
        [$ui_factory, $table_factory] = $this->stubUiFactoryCapturingColumnTitles($captured_titles);

        $action = $this->createMock(\ILIAS\UI\Component\Table\Action\Standard::class);
        $action_factory = $this->createMock(\ILIAS\UI\Component\Table\Action\Factory::class);
        $action_factory->method('standard')->willReturn($action);

        $data_table = $this->createMock(\ILIAS\UI\Component\Table\Data::class);
        $data_table->method('withActions')->willReturn($data_table);
        $data_table->method('withId')->willReturn($data_table);

        $table_factory->method('action')->willReturn($action_factory);
        $table_factory->method('data')->willReturn($data_table);

        return [$ui_factory, $data_table];
    }

    public function testGetTableUsesThePageTranslationTableIdWhenIsPageTranslationIsTrue(): void
    {
        $this->setIsPageTranslation(true);
        $this->setSavedModulesAndTopics([], []);

        [$ui_factory, $data_table] = $this->stubUiFactoryForGetTable();
        $data_table->expects($this->once())->method('withId')->with('lang_ext_entries_trans')->willReturn($data_table);

        $object = $this->createObject('de');
        $table = new ilLanguageEntriesTable(
            $object,
            $this->createLng(),
            $ui_factory,
            $this->createMock(URLBuilder::class),
            $this->createMock(URLBuilderToken::class),
            $this->createMock(URLBuilderToken::class),
            false
        );

        $table->getTable($this->filterData([]));
    }

    public function testGetTableUsesTheAdminTableIdWhenIsPageTranslationIsFalse(): void
    {
        $this->setIsPageTranslation(false);

        [$ui_factory, $data_table] = $this->stubUiFactoryForGetTable();
        $data_table->expects($this->once())->method('withId')->with('lang_ext_entries_admin')->willReturn($data_table);

        $object = $this->createObject('de');
        $table = new ilLanguageEntriesTable(
            $object,
            $this->createLng(),
            $ui_factory,
            $this->createMock(URLBuilder::class),
            $this->createMock(URLBuilderToken::class),
            $this->createMock(URLBuilderToken::class),
            false
        );

        $table->getTable($this->filterData([]));
    }

    public function testTableIdIsThePageTranslationIdWhenIsPageTranslationIsTrue(): void
    {
        $this->setIsPageTranslation(true);

        self::assertSame('lang_ext_entries_trans', ilLanguageEntriesTable::tableId());
    }

    public function testTableIdIsTheAdminIdWhenIsPageTranslationIsFalse(): void
    {
        $this->setIsPageTranslation(false);

        self::assertSame('lang_ext_entries_admin', ilLanguageEntriesTable::tableId());
    }
}
