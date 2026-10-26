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

/**
 * Tests for ilLanguageEntriesTable, the ILIAS\UI\Component\Table\DataRetrieval
 * backing the KS Data Table that replaced ilLanguageExtTableGUI (see
 * ilObjLanguageExtGUI::viewObject()/editSelectedObject()).
 *
 * Three static collaborators are exercised for real rather than mocked away,
 * because they are called directly by production code that is not injectable
 * (ilObjLanguageAccess::_isPageTranslation()/_getSavedModules()/_getSavedTopics(),
 * ilObjLanguageExt::_getValues(), and a real ilLanguageFile reading an actual
 * file from disk in the "conflicts" mode):
 *
 * - ilObjLanguageAccess::_isPageTranslation() reads $DIC->http()->wrapper()
 *   ->query() - faked via setIsPageTranslation()/the base class'
 *   setGlobalVariable().
 * - ilObjLanguageAccess::_getSavedModules()/_getSavedTopics() read
 *   $_SESSION['lang_ext_maintenance'] directly - faked via
 *   setSavedModulesAndTopics()/tearDown().
 * - ilObjLanguageExt::_getValues() (the "compare == other language" branch of
 *   getCompareContent(), and the page-translation branch of
 *   getFilteredEntries()) hits $DIC->database() directly - faked via
 *   mockDatabaseReturningRows().
 * - The "conflicts" mode's resolveConflictsCheck() instantiates a real
 *   ilLanguageFile for the former distributed language file - a real
 *   temporary file is written per test (writeLangFile()) and removed in
 *   tearDown().
 */
class ilLanguageEntriesTableTest extends ilLanguageBaseTestCase
{
    /** @var list<string> temp files created by writeLangFile(), removed in tearDown() */
    private array $temp_files_to_remove = [];
    /** @var list<string> temp directories created by tempDir(), rmdir()'d in tearDown() once their files are gone */
    private array $temp_dirs_to_remove = [];

    /**
     * resolveConflictsCheck() instantiates a real ilLanguageFile to read the
     * former distributed language file from disk - its constructor and
     * read() both reach into `global $DIC->language()` for the separator
     * (only txt() on the invalid-file error path, not exercised here, needs
     * anything more) - set up unconditionally so every test can trigger the
     * "conflicts" file-reading path without repeating this wiring.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setGlobalVariable('lng', $this->createMock(ilLanguage::class));

        // ilLanguageFile's constructor (real instances of which
        // resolveConflictsCheck() creates for the former distributed
        // language file) unconditionally reads these two global constants -
        // same guarded-define idiom used by other component tests (e.g.
        // ilContactGUITest, ilServicesMainMenuTest) that construct classes
        // needing them outside the full ILIAS bootstrap.
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

        // Removed only after the files above are gone - rmdir() (unlike
        // unlink()) requires the directory to be empty, and would otherwise
        // silently fail (left as a stale temp directory) exactly like the
        // former @unlink()-on-a-directory approach did.
        foreach ($this->temp_dirs_to_remove as $dir) {
            @rmdir($dir);
        }
        $this->temp_dirs_to_remove = [];

        unset($_SESSION['lang_ext_maintenance']);

        parent::tearDown();
    }

    // -----------------------------------------------------------------
    // construction helpers
    // -----------------------------------------------------------------

    private function createLng(string $default_language = 'en'): ilLanguage&MockObject
    {
        $lng = $this->createMock(ilLanguage::class);
        $lng->method('txt')->willReturnCallback(static function (string $key): string {
            // The two keys getConflictsNotice() sprintf()s the former-file
            // path into - a plain "%s" placeholder lets the test assert the
            // path actually reached the message, not just that *some*
            // message was built.
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
     * @param array<string, mixed> $values field name => raw value (or null
     *        for "field not built at all", handled by simply omitting it)
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

    // -----------------------------------------------------------------
    // ilObjLanguageAccess::_isPageTranslation() / saved modules+topics fakes
    // -----------------------------------------------------------------

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
        // Registered unconditionally: resolveConflictsCheck() ("conflicts"
        // mode) constructs a real ilLanguageFile for the former distributed
        // language file, and ilLanguageFile's constructor unconditionally
        // reads $DIC->language()->separator/comment_separator - independent
        // of the ilLanguage instance handed directly to the table under
        // test. mockDatabaseReturningRows() overwrites this with its own
        // instance where that matters (it doesn't - both are plain mocks
        // with the real class' default property values).
        $this->setGlobalVariable('lng', $this->createLng());
    }

    private function setSavedModulesAndTopics(array $modules, array $topics): void
    {
        $_SESSION['lang_ext_maintenance'] = ['used_modules' => $modules, 'used_topics' => $topics];
    }

    // -----------------------------------------------------------------
    // ilObjLanguageExt::_getValues() (static, hits $DIC->database()) fake
    // -----------------------------------------------------------------

    /**
     * @param list<array{module: string, identifier: string, value: string}> $rows
     */
    private function mockDatabaseReturningRows(array $rows): void
    {
        // A fresh ilDBStatement (with its own fetchRow() cursor) per query()
        // call - getRows() for "equal"/"different" modes calls
        // getCompareContent() twice (once inside getFilteredEntries(), once
        // directly for the "default" column), so the static _getValues() is
        // invoked, and therefore queried, twice per getRows() call. A single
        // shared statement would exhaust its consecutive-call queue after
        // the first query and error on the second, unlike a real DB result
        // set, which always starts a fresh cursor per query().
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
        // ilObjLanguageExt::_getValues() also reads $DIC->language()->separator
        // to build its result keys - independent of the ilLanguage instance
        // handed to the table under test.
        $this->setGlobalVariable('lng', $this->createLng());
    }

    // -----------------------------------------------------------------
    // real ilLanguageFile fixture for the "conflicts" mode
    // -----------------------------------------------------------------

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

    // -----------------------------------------------------------------
    // getFilteredEntryNames() / getTotalRowCount(): mode dispatch
    // -----------------------------------------------------------------

    public function testAllModeReturnsGetAllValuesKeysByDefaultWhenNoModeFieldIsBuilt(): void
    {
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->method('getAllValues')->with([ilLanguageEntriesTable::DEFAULT_MODULE], '', [])->willReturn([
            'common#:#access' => 'Access',
            'common#:#other' => 'Other',
        ]);

        $table = $this->createTable($object, $this->createLng());

        // No 'mode' field at all - filterValue() must fall back to 'all',
        // exactly like an empty filter array does.
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
        // getRows() unconditionally builds the "default" compare column too
        // (regardless of filter mode) - irrelevant to this test, so aligned
        // to the object's own language ('compare' === object->key) to go
        // through getShippedValues() rather than a real DB call (see
        // testCompareContentUsesTheOtherLanguagesDatabaseValues... below for
        // that branch).
        $object->method('getShippedValues')->willReturn([]);

        // getRows() always builds the "default" compare column too, even
        // though this test's mode isn't about it - aligning the language's
        // default language with the object's own key ('de') keeps that
        // column on the getShippedValues() branch instead of the "other
        // language" one, which would need a real/mocked DB (see
        // testCompareContentUsesTheOtherLanguagesDatabaseValues...).
        $table = $this->createTable($object, $this->createLng(default_language: 'de'));

        // Only the entry that HAS a remark survives - not the union, and not
        // the remark text itself (array_intersect_key keeps the left-hand
        // side's values).
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

        // compare === object->key ('de') -> compares against the GLOBAL file,
        // not another language's DB values.
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

        // getRows() always builds the "default" compare column too, even
        // though this test's mode isn't about it - aligning the language's
        // default language with the object's own key ('de') keeps that
        // column on the getShippedValues() branch instead of the "other
        // language" one, which would need a real/mocked DB (see
        // testCompareContentUsesTheOtherLanguagesDatabaseValues...).
        $table = $this->createTable($object, $this->createLng(default_language: 'de'));

        self::assertSame(
            ['common#:#other' => 'DifferentHere'],
            $this->getFilteredEntriesViaRows($table, $this->filterData(['mode' => 'different', 'compare' => 'de']))
        );
    }

    public function testCompareContentUsesTheOtherLanguagesDatabaseValuesWhenCompareDiffersFromTheObjectsOwnLanguage(): void
    {
        $this->setIsPageTranslation(false);
        // The compare-language branch is the only one hitting
        // ilObjLanguageExt::_getValues() (static, real DB call) - see this
        // test class' docblock.
        $this->mockDatabaseReturningRows([
            ['module' => 'zztest', 'identifier' => 'access', 'value' => 'EnglishAccess'],
        ]);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn([
            'zztest#:#access' => 'EnglishAccess',
        ]);
        // Must NOT be called for the "other language" branch.
        $object->expects($this->never())->method('getShippedValues');

        $table = $this->createTable($object, $this->createLng());

        // getFilteredEntryNames() (unlike getRows()) calls getCompareContent()
        // only once, keeping this test's DB fake simple (a single query
        // cycle) - the "equal" intersection only succeeds if the DB-sourced
        // compare value ('EnglishAccess') actually matched, so this still
        // pins down that the DB values (not the global file) were used.
        self::assertSame(
            ['zztest#:#access'],
            $table->getFilteredEntryNames($this->filterData(['mode' => 'equal', 'compare' => 'en']))
        );
    }

    public function testCompareFieldMissingFallsBackToTheLanguageObjectsDefaultLanguage(): void
    {
        // Strengthened regression test: the ORIGINAL version only asserted
        // an empty diff result, which a regression falling back to some
        // OTHER fixed string (e.g. '' or the object's own key) could still
        // have produced by coincidence (e.g. if the fake DB answers empty
        // regardless of the language key actually queried, or if a '' - the
        // object's own key branch - fallback happened to also yield an
        // empty diff). This version instead captures the exact language key
        // ilObjLanguageExt::_getValues() passes to $ilDB->quote() as the
        // WHERE lang_key value, and proves it is the REAL getDefaultLanguage()
        // value - not just "some" value - across two DIFFERENT defaults.
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
                // The very first quote() call _getValues() makes is
                // "WHERE lang_key = <quoted>" - capture only that one, not
                // any later quote() calls a different code path might add.
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

        // getDefaultLanguage() !== object's own key 'de' -> the "other
        // language" branch is taken, which is the only branch that reaches
        // _getValues() (and therefore the mocked DB) at all.
        $table = $this->createTable($object, $this->createLng(default_language: $default_language));
        $table->getFilteredEntryNames($this->filterData(['mode' => 'equal']));

        return $captured_lang_key;
    }

    // -----------------------------------------------------------------
    // "conflicts" mode (getFilteredEntries() + getConflictsNotice())
    // -----------------------------------------------------------------

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
        // shipped entries that differ from the backup, see ilObjLanguageExt::getShippedChangesSinceBackup()
        $object->method('getShippedChangesSinceBackup')->willReturn(['common#:#access' => 'NewAccess']);
        // Only entries a local translator changed AND that also changed
        // between the former/global distributed files are "conflicts".
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
        // resolveConflictsCheck()'s file access must not even be attempted.
        $object->expects($this->never())->method('getDataPath');

        $table = $this->createTable($object, $this->createLng());

        self::assertNull($table->getConflictsNotice($this->filterData(['mode' => 'all'])));
        self::assertNull($table->getConflictsNotice($this->filterData([])));
    }

    // -----------------------------------------------------------------
    // page-translation mode
    // -----------------------------------------------------------------

    public function testPageTranslationModeIgnoresTheModeFilterAndUsesSessionSavedModulesAndTopics(): void
    {
        $this->setIsPageTranslation(true);

        // Strengthened regression test: the ORIGINAL version of this test
        // only checked the *result* of a canned DB-mock answer, without ever
        // proving that different session-saved modules/topics actually reach
        // the query differently - a table that (by regression) ignored the
        // session and used some fixed/hardcoded list would have passed it
        // just the same. This version instead captures the exact arguments
        // ilObjLanguageExt::_getValues() passes to $ilDB->in() for
        // "module"/"identifier", and proves that two DIFFERENT session
        // values produce two DIFFERENT sets of query arguments.
        $first_in_calls = $this->captureDbInCallsForSavedModulesAndTopics(['common'], ['access']);
        self::assertSame(['module', ['common'], false, 'text'], $first_in_calls[0]);
        self::assertSame(['identifier', ['access'], false, 'text'], $first_in_calls[1]);

        $second_in_calls = $this->captureDbInCallsForSavedModulesAndTopics(['other'], ['xyz']);
        self::assertSame(['module', ['other'], false, 'text'], $second_in_calls[0]);
        self::assertSame(['identifier', ['xyz'], false, 'text'], $second_in_calls[1]);
    }

    /**
     * @return list<array> the two argument lists $ilDB->in() was called with
     *         (module, then identifier), in call order
     */
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
        // Even though the filter selects a completely different mode, and
        // getAllValues()/getChangedValues() etc. exist as mocked methods,
        // none of the per-mode branches are reachable in page-translation
        // mode - _getValues() (real DB call) is used instead.
        $object->expects($this->never())->method('getAllValues');
        $object->expects($this->never())->method('getChangedValues');

        $table = $this->createTable($object, $this->createLng());
        $table->getFilteredEntryNames($this->filterData(['mode' => 'changed', 'module' => 'other']));

        return $captured_in_calls;
    }

    // -----------------------------------------------------------------
    // getRows(): pagination, module/topic split, translation/default mapping
    // -----------------------------------------------------------------

    private function getFilteredEntriesViaRows(ilLanguageEntriesTable $table, array $filter_data): array
    {
        // getFilteredEntries() is private; getRows() is the only public
        // method that exposes both keys AND values (getFilteredEntryNames()
        // only exposes keys) - used here as a state-based way to assert on
        // the full name=>value map without touching implementation details.
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

        // getRows() always builds the "default" compare column too, even
        // though this test's mode isn't about it - aligning the language's
        // default language with the object's own key ('de') keeps that
        // column on the getShippedValues() branch instead of the "other
        // language" one, which would need a real/mocked DB (see
        // testCompareContentUsesTheOtherLanguagesDatabaseValues...).
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
        // Regression/defensive test: a malformed key (no module#:#topic
        // structure at all) must not emit a PHP warning/notice for a missing
        // array index - the `?? ''` fallbacks in getRows() must cover this.
        $this->setIsPageTranslation(false);

        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn(['malformed_key_without_separator' => 'Value']);
        $object->method('getShippedValues')->willReturn([]);

        // getRows() always builds the "default" compare column too, even
        // though this test's mode isn't about it - aligning the language's
        // default language with the object's own key ('de') keeps that
        // column on the getShippedValues() branch instead of the "other
        // language" one, which would need a real/mocked DB (see
        // testCompareContentUsesTheOtherLanguagesDatabaseValues...).
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
        // Three entries; requesting range(1, 1) must yield exactly the
        // second one - an off-by-one here (e.g. start used as an inclusive
        // 1-based index) would instead yield the first or third.
        $object->method('getAllValues')->willReturn([
            'common#:#a' => 'A',
            'common#:#b' => 'B',
            'common#:#c' => 'C',
        ]);
        $object->method('getShippedValues')->willReturn([]);

        // getRows() always builds the "default" compare column too, even
        // though this test's mode isn't about it - aligning the language's
        // default language with the object's own key ('de') keeps that
        // column on the getShippedValues() branch instead of the "other
        // language" one, which would need a real/mocked DB (see
        // testCompareContentUsesTheOtherLanguagesDatabaseValues...).
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
        // getTotalRowCount() has no Range parameter at all - this pins down
        // that it must report the FULL filtered set, not e.g. accidentally
        // be wired to whatever a caller might (wrongly) slice beforehand.
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

    // -----------------------------------------------------------------
    // resolveModulesAndTopics() / filterValue() edge cases, exercised
    // through getFilteredEntryNames()'s "changed" mode (which forwards
    // modules/pattern/topics straight through to getChangedValues()).
    // -----------------------------------------------------------------

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
        // new instance: the entries cache is deliberately key-less per instance
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

        // dbremarks
        $object = $this->createObject('de');
        $object->method('getAllValues')->willReturn(['common#:#access' => 'A', 'common#:#other' => 'B']);
        $object->method('getAllRemarks')->willReturn(['common#:#access' => 'r', 'common#:#other' => 'r']);
        $table = $this->createTable($object, $this->createLng());
        self::assertSame(
            ['common#:#other'],
            $table->getFilteredEntryNames($this->filterData(['mode' => 'dbremarks', 'identifier' => 'OTH']))
        );

        // conflicts
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

        // range offset 1 applies to the FILTERED list (a#:#other never counts)
        self::assertSame(['b#:#crit_2', 'c#:#crit_3'], array_keys($captured));
        self::assertSame('compare-value', $captured['b#:#crit_2']['default']);
        self::assertSame('crit_2', $captured['b#:#crit_2']['topic']);
    }

    public function testFilterValueFallsBackToDefaultWhenTheWholeFilterDataIsNotAnArray(): void
    {
        // Defensive/boundary case: filterValue() explicitly guards against a
        // non-array $filter_data (e.g. null) with `!is_array($filter_data)`.
        $this->setIsPageTranslation(false);

        $object = $this->createObject();
        $object->method('getAllValues')->with([ilLanguageEntriesTable::DEFAULT_MODULE], '', [])->willReturn(['common#:#a' => 'A']);

        $table = $this->createTable($object, $this->createLng());

        self::assertSame(['common#:#a'], $table->getFilteredEntryNames(null));
    }

    // -----------------------------------------------------------------
    // getRows(): "commented" column
    // -----------------------------------------------------------------

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

    // -----------------------------------------------------------------
    // langmode: "commented" column and "comment" filter
    // -----------------------------------------------------------------

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

    // -----------------------------------------------------------------
    // getCompareTitle() / getCompareValues()
    // -----------------------------------------------------------------

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

    // -----------------------------------------------------------------
    // getRows(): sortEntryNames() - sortability across all four columns
    // -----------------------------------------------------------------

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

    /**
     * Three entries with deliberately criss-crossing module/topic/
     * translation/default values (e.g. the module-alphabetically-first
     * entry has the topic-alphabetically-last identifier, and the
     * translation/default values follow yet another order again) - so a
     * mistake sorting by the wrong field, or forgetting to reverse for
     * DESC, produces a visibly wrong order rather than one that happens to
     * coincide with another field's order.
     *
     */
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

    /**
     * Regression test for the DESC sort mechanism itself: sorting DESC must
     * flip the COMPARATOR, not sort ASC and array_reverse() the result
     * afterwards - the two are NOT equivalent for tied keys under a stable
     * sort (usort() since PHP 8.0). Two entries share the same module
     * ("same"), so a DESC sort must still yield them in their original
     * relative order (first, then second) among themselves - array_reverse()
     * of the ASC result would instead flip that tie (second, then first),
     * even though the "other" group correctly sorts after "same" either way.
     */
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

    /**
     * @return list<string> entry names (row ids) in the order getRows()
     *         actually yielded them
     */
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

    // -----------------------------------------------------------------
    // performance memoization (see the class' own docblock on
    // $filtered_entries_cache/$conflicts_check_cache/$remarks_cache)
    // -----------------------------------------------------------------

    /**
     * getTotalRowCount() and getRows(), called in sequence on the SAME table
     * instance with the SAME $filter_data (exactly how the GUI actually uses
     * this class - see ilObjLanguageExtGUI::viewObject()), must not re-run
     * the underlying getAllValues() DB query a second time - regression test
     * for $filtered_entries_cache.
     */
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

    // -----------------------------------------------------------------
    // getColumns() - "translation"/"default" column titles
    // -----------------------------------------------------------------

    private function invokeProtectedMethod(object $object, string $method_name, array $args = []): mixed
    {
        return (new ReflectionMethod($object, $method_name))->invoke($object, ...$args);
    }

    /**
     * @param list<string> $captured_titles filled, in call order, with every
     *        title text() was called with (module, topic, translation,
     *        default - "commented" uses boolean(), not text())
     */
    /**
     * Returns both the `ILIAS\UI\Factory` mock and the `Table\Factory` mock
     * wired into its `table()` method - callers that need to add further
     * expectations on the table factory (action()/data(), see
     * stubUiFactoryForGetTable()) must use the second value rather than
     * calling `$ui_factory->table()` again themselves: that getter is
     * statically typed to the real `ILIAS\UI\Component\Table\Factory`
     * interface (which has no `method()`), so re-fetching it that way loses
     * the mock's actual type and cannot be used to stub further methods.
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

        // Index 3: module, topic, translation, then default.
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

    // -----------------------------------------------------------------
    // tableId() / getTable() - table/filter id keyed on
    // _isPageTranslation() (see initFilter()'s equivalent use on the GUI
    // side); langmode plays no part in the id.
    // -----------------------------------------------------------------

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

        // $table_factory is the actual mock returned above (properly typed
        // as Table\Factory&MockObject) - adding action()/data() expectations
        // here, on top of the column() wiring stubUiFactoryCapturingColumnTitles()
        // already set up on the very same mock, rather than re-fetching it
        // via $ui_factory->table() (which is statically typed to the real
        // Table\Factory interface and has no method()).
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
