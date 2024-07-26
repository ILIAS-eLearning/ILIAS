<?php

namespace Logging;

use ILIAS\Test\Logging\Factory;
use ILIAS\Test\Logging\TestAdministrationInteraction;
use ILIAS\Test\Logging\TestError;
use ILIAS\Test\Logging\TestLoggingDatabaseRepository;
use ILIAS\Test\Logging\TestParticipantInteraction;
use ILIAS\Test\Logging\TestQuestionAdministrationInteraction;
use ILIAS\Test\Logging\TestScoringInteraction;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;

class TestLoggingDatabaseRepositoryTest extends ilTestBaseTestCase
{
    public static function provideLogTableName()
    {
        return [
            "test" => [
                "table" => "tst_tst_admin_log",
                "method" => "storeTestAdministrationInteraction",
                "class" => TestAdministrationInteraction::class
            ],
            "question" => [
                "table" => "tst_qst_admin_log",
                "method" => "storeQuestionAdministrationInteraction",
                "class" => TestQuestionAdministrationInteraction::class
            ],
            "participant" => [
                "table" => "tst_pax_log",
                "method" => "storeParticipantInteraction",
                "class" => TestParticipantInteraction::class
            ],
            "scoring" => [
                "table" => "tst_mark_log",
                "method" => "storeScoringInteraction",
                "class" => TestScoringInteraction::class
            ],
            "error" => [
                "table" => "tst_error_log",
                "method" => "storeError",
                "class" => TestError::class
            ]
        ];
    }


    /**
     * @dataProvider provideLogTableName
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function test_storeTest_and_storeQuestionAdministrationInteraction(string $table, string $method, string $class): void
    {
        $storageArray = ["value" => "7"];
        $nextId = 7;
        $resultArray = array_merge($storageArray, ["id" => ["integer", $nextId]]);
        $factory = $this->createMock(Factory::class);
        $interaction = $this->createMock($class);
        $interaction
            ->expects($this->once())
            ->method("toStorage")
            ->willReturn($storageArray);

        $this->mockDBNextId($this->once(), $table, $nextId);
        $this->mockDBInsert($this->once(), $table, $resultArray);

        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $repo->$method($interaction);
    }


    /**
     * @throws Exception
     */
    public function test_getLogsCountWithoutFilter(): void
    {
        $factory = $this->createMock(Factory::class);

        $statement = $this->createMock(\ilDBStatement::class);

        $stdclass = $this->createMock(\stdClass::class);
        $stdclass->cnt = 15;
        $this->mockDBQuery($this->once(), $statement);
        $this->mockDBFetchObject($this->once(), $statement, $stdclass);

        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $this->assertSame(15, $repo->getLogsCount(['tai' => [], 'qai' => [], 'pi' => [], 'si' => [], 'te' => []]));
    }

    /**
     * @throws Exception
     */
    public function test_getLogsCountWithLogEntryTypeFilterAndNoResults(): void
    {
        $factory = $this->createMock(Factory::class);

        $statement = $this->createMock(\ilDBStatement::class);

        $stdclass = $this->createMock(\stdClass::class);
        $stdclass->cnt = 15;
        $this->mockDBQuery($this->never(), $statement);
        $this->mockDBFetchObject($this->never(), $statement, $stdclass);

        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $this->assertSame(0, $repo->getLogsCount(['tai' => []], null, null, null, null, null, null, null, []));
    }

    public function test_getLogsCountWithPaxFilterAndNoResults(): void
    {
        $factory = $this->createMock(Factory::class);

        $statement = $this->createMock(\ilDBStatement::class);

        $stdclass = $this->createMock(\stdClass::class);
        $stdclass->cnt = 15;
        $this->mockDBQuery($this->never(), $statement);
        $this->mockDBFetchObject($this->never(), $statement, $stdclass);

        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $this->assertSame(0, $repo->getLogsCount(['tai' => []], null, null, null, null, []));
    }

    /**
     * @throws Exception
     */
    public function test_getLogsCountWithFilterAndResults(): void
    {
        $factory = $this->createMock(Factory::class);

        $statement = $this->createMock(\ilDBStatement::class);

        $stdclass = $this->createMock(\stdClass::class);
        $stdclass->cnt = 15;
        $this->mockDBQuery($this->once(), $statement);
        $this->mockDBFetchObject($this->once(), $statement, $stdclass);

        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $this->assertSame(15, $repo->getLogsCount(['tai' => [], 'qai' => [], 'te' => []], null, null, null, null, null, null, null, ['qai']));
    }

    /**
     * @throws Exception
     */
    public function test_getLogsCountWithWrongIdentifier(): void
    {
        $factory = $this->createMock(Factory::class);

        $statement = $this->createMock(\ilDBStatement::class);

        $stdclass = $this->createMock(\stdClass::class);
        $this->mockDBQuery($this->never(), $statement);
        $this->mockDBFetchObject($this->never(), $statement, $stdclass);

        $this->expectExceptionMessage("Unknown Identifier Type");

        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $repo->getLogsCount(['tai' => [], 'qai' => [], 'te' => [], "wrong" => []], );
    }

    public function test_getLogWithWrongId(): void
    {
        $factory = $this->createMock(Factory::class);

        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $this->assertNull($repo->getLog("wrong_1"));
    }

    public function test_getLogWithWrongIdFormat(): void
    {
        $factory = $this->createMock(Factory::class);

        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $this->assertNull($repo->getLog("tai.1"));
    }

    public function test_getLogWithoutNumber(): void
    {
        $factory = $this->createMock(Factory::class);

        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $this->assertNull($repo->getLog("tai_qai"));
    }

    /**
     * @dataProvider provideInteractionIdentifierAndFactoryMethod
     * @throws Exception
     */
    public function test_getLogEmpty(string $id): void
    {
        $factory = $this->createMock(Factory::class);

        $this->mockDBNumRows($this->once(), 0);
        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $this->assertNull($repo->getLog($id . "_1"));
    }

    /**
     * @dataProvider provideInteractionIdentifierAndFactoryMethod
     * @throws Exception
     */
    public function test_getLog(string $id, string $method, string $interaction): void
    {
        $interaction = $this->createMock($interaction);
        $this->mockDBNumRows($this->once(), 1);
        $query = $this->createMock(\ilDBStatement::class);
        $this->mockDBQueryF($this->once(), $query);
        $stdClass = $this->createMock(\stdClass::class);
        $this->mockDBFetchObject($this->once(), $query, $stdClass);

        $factory = $this->createMock(Factory::class);
        $factory
            ->expects($this->once())
            ->method($method)
            ->with($stdClass)
            ->willReturn($interaction);
        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $this->assertSame($interaction, $repo->getLog($id . "_1"));
    }

    public static function provideInteractionIdentifierAndFactoryMethod(): array
    {
        return [
            "test" => [
                "id" => "tai",
                "method" => "buildTestAdministrationInteractionFromDBValues",
                "interaction" => TestAdministrationInteraction::class
            ],
            "question" => [
                "id" => "qai",
                "method" => "buildQuestionAdministrationInteractionFromDBValues",
                "interaction" => TestQuestionAdministrationInteraction::class
            ],
            "participant" => [
                "id" => "pi",
                "method" => "buildParticipantInteractionFromDBValues",
                "interaction" => TestParticipantInteraction::class
            ],
            "scoring" => [
                "id" => "si",
                "method" => "buildScoringInteractionFromDBValues",
                "interaction" => TestScoringInteraction::class
            ],
            "error" => [
                "id" => "te",
                "method" => "buildErrorFromDBValues",
                "interaction" => TestError::class
            ],
        ];

    }

    /**
     * @dataProvider provideDataForDeleteLogs
     * @throws Exception
     */
    public function test_deleteLogs(array $ids, string $with): void
    {

        $factory = $this->createMock(Factory::class);

        $this->mockDBManipulate($this->once(), $with);
        $this->mockDBIn($this->any(), function ($id, array $values, $negate, $type) {
            return implode(", ", $values);
        });
        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $repo->deleteLogs($ids);
    }

    public static function provideDataForDeleteLogs(): array
    {
        return [
            "all Objects" => [
                "ids" => ['ALL_OBJECTS'],
                "with" => "TUNCATE TABLE tst_tst_admin_log;TUNCATE TABLE tst_qst_admin_log;TUNCATE TABLE tst_pax_log;TUNCATE TABLE tst_mark_log;TUNCATE TABLE tst_error_log;"
            ],
            "valid key variation" => [
                "ids" => ['tai_7', 'qai_9', 'wrong', 'te_wrong'],
                "with" => "DELETE FROM tst_tst_admin_log WHERE 7;\nDELETE FROM tst_qst_admin_log WHERE 9;\n"
            ],
        ];
    }

    public function test_deleteLogsWithInvalidIds(): void
    {
        $this->expectExceptionMessage("Unknown Identifier Type");

        $factory = $this->createMock(Factory::class);

        $this->mockDBManipulate($this->never(), "");
        $this->mockDBIn($this->any(), function ($id, array $values, $negate, $type) {
            return implode(", ", $values);
        });
        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $repo->deleteLogs(['tai_7', 'qai_9', 'wrong', 'te_wrong', 'wrong_18']);
    }

    public function test_testHasParticipantInteractions_withAvailableInteractions(): void
    {
        $id = 5;
        $factory = $this->createMock(Factory::class);

        $statement = $this->createMock(\ilDBStatement::class);

        $stdclass = $this->createMock(\stdClass::class);
        $stdclass->cnt = 15;
        $this->mockDBQueryF($this->once(), $statement, 'SELECT COUNT(id) AS cnt FROM tst_pax_log WHERE ref_id=%s', ['integer'], [$id]);
        $this->mockDBFetchObject($this->once(), $statement, $stdclass);

        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $this->assertTrue($repo->testHasParticipantInteractions($id));

    }

    public function test_testHasParticipantInteractions_withNoAvailableInteractions(): void
    {
        $id = 5;
        $factory = $this->createMock(Factory::class);

        $statement = $this->createMock(\ilDBStatement::class);

        $stdclass = $this->createMock(\stdClass::class);
        $stdclass->cnt = 0;
        $this->mockDBQueryF($this->once(), $statement, 'SELECT COUNT(id) AS cnt FROM tst_pax_log WHERE ref_id=%s', ['integer'], [$id]);
        $this->mockDBFetchObject($this->once(), $statement, $stdclass);

        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $this->assertFalse($repo->testHasParticipantInteractions($id));
    }

    public function test_deleteParticipantInteractionsForTest(): void
    {
        $id = 5;
        $factory = $this->createMock(Factory::class);

        $this->mockDBManipulateF($this->once(), 1, 'DELETE FROM tst_pax_log WHERE ref_id=%s', ['integer'], [$id]);

        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $repo->deleteParticipantInteractionsForTest($id);

    }

    public function test_getLegacyLogsForObjId(): void
    {
        $count = 0;
        $result = [
            0 => [
                'tstamp' => 5,
                'test' => 'test5',
            ],
            1 => [
                'tstamp' => 4,
                'test' => 'test4',
            ],
            2 => [
                'tstamp' => 3,
                'test' => 'test3',
            ],
            3 => [
                'tstamp' => 2,
                'test' => 'test2',
            ],
            4 => [
                'tstamp' => 1,
                'test' => 'test1',
            ],
        ];
        $id = 1;
        $where = ' WHERE obj_fi = ' . $id;

        $factory = $this->createMock(Factory::class);
        $statement = $this->createMock(\ilDBStatement::class);

        $this->mockDBFetchAssoc(willReturnCallable: function () use (&$count) {
            if ($count < 5) {
                $count++;

                return [
                    "tstamp" => $count,
                    "test" => "test" . $count
                ];
            }

            return [];
        });

        $this->mockDBQuery($this->once(), $statement, "SELECT * FROM ass_log" . $where . ' ORDER BY tstamp');

        global $DIC;

        $repo = new TestLoggingDatabaseRepository($factory, $DIC['ilDB']);
        $this->assertSame($result, $repo->getLegacyLogsForObjId($id));
    }
}
