<?php

namespace Logging;

use ilDBInterface;
use ILIAS\Test\Logging\Factory;
use ILIAS\Test\Logging\TestAdministrationInteraction;
use ILIAS\Test\Logging\TestError;
use ILIAS\Test\Logging\TestLoggingDatabaseRepository;
use ILIAS\Test\Logging\TestParticipantInteraction;
use ILIAS\Test\Logging\TestQuestionAdministrationInteraction;
use ILIAS\Test\Logging\TestScoringInteraction;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class TestLoggingDatabaseRepositoryTest extends ilTestBaseTestCase
{
    private TestLoggingDatabaseRepository $testLoggingDatabaseRepository;

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testLoggingDatabaseRepository = $this->createInstanceOf(TestLoggingDatabaseRepository::class);
    }

    public static function provideLogTableName(): array
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
     * @throws Exception
     * @throws \Exception
     */
    public function test_storeTest_and_storeQuestionAdministrationInteraction(string $table, string $method, string $class): void
    {
        $storageArray = ["value" => "7"];
        $interaction = $this->createMock($class);
        $interaction
            ->expects($this->once())
            ->method("toStorage")
            ->willReturn($storageArray);

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($table, $storageArray) {
            $nextId = 7;
            $resultArray = array_merge($storageArray, ["id" => ["integer", $nextId]]);

            $mock
                ->expects($this->once())
                ->method('nextId')
                ->with($table)
                ->willReturn($nextId);

            $mock
                ->expects($this->once())
                ->method('insert')
                ->with($table, $resultArray);
        });

        $this->testLoggingDatabaseRepository->$method($interaction);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function test_getLogsCountWithoutFilter(): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) {
            $statement = $this->createMock(\ilDBStatement::class);

            $stdclass = $this->createMock(\stdClass::class);
            $stdclass->cnt = 15;

            $mock
                ->expects($this->once())
                ->method('query')
                ->willReturn($statement);

            $mock
                ->expects($this->once())
                ->method('fetchObject')
                ->with($statement)
                ->willReturn($stdclass);
        });

        $this->assertSame(15, $this->testLoggingDatabaseRepository->getLogsCount(['tai' => [], 'qai' => [], 'pi' => [], 'si' => [], 'te' => []]));
    }

    /**
     * @throws \Exception|Exception
     */
    public function test_getLogsCountWithLogEntryTypeFilterAndNoResults(): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) {
            $statement = $this->createMock(\ilDBStatement::class);

            $stdclass = $this->createMock(\stdClass::class);
            $stdclass->cnt = 15;

            $mock
                ->expects($this->never())
                ->method('query')
                ->willReturn($statement);

            $mock
                ->expects($this->never())
                ->method('fetchObject')
                ->with($statement)
                ->willReturn($stdclass);
        });

        $this->assertSame(0, $this->testLoggingDatabaseRepository->getLogsCount(['tai' => []], null, null, null, null, null, null, null, []));
    }

    /**
     * @throws \Exception|Exception
     */
    public function test_getLogsCountWithPaxFilterAndNoResults(): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) {
            $statement = $this->createMock(\ilDBStatement::class);

            $stdclass = $this->createMock(\stdClass::class);
            $stdclass->cnt = 15;

            $mock
                ->expects($this->never())
                ->method('query')
                ->willReturn($statement);

            $mock
                ->expects($this->never())
                ->method('fetchObject')
                ->with($statement)
                ->willReturn($stdclass);
        });

        $this->assertSame(0, $this->testLoggingDatabaseRepository->getLogsCount(['tai' => []], null, null, null, null, []));
    }

    /**
     * @throws \Exception|Exception
     */
    public function test_getLogsCountWithFilterAndResults(): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) {
            $statement = $this->createMock(\ilDBStatement::class);

            $stdclass = $this->createMock(\stdClass::class);
            $stdclass->cnt = 15;

            $mock
                ->expects($this->once())
                ->method('query')
                ->willReturn($statement);

            $mock
                ->expects($this->once())
                ->method('fetchObject')
                ->with($statement)
                ->willReturn($stdclass);
        });

        $this->assertSame(15, $this->testLoggingDatabaseRepository->getLogsCount(['tai' => [], 'qai' => [], 'te' => []], null, null, null, null, null, null, null, ['qai']));
    }

    /**
     * @throws \Exception|Exception
     */
    public function test_getLogsCountWithWrongIdentifier(): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) {
            $statement = $this->createMock(\ilDBStatement::class);

            $stdclass = $this->createMock(\stdClass::class);
            $stdclass->cnt = 15;

            $mock
                ->expects($this->never())
                ->method('query')
                ->willReturn($statement);

            $mock
                ->expects($this->never())
                ->method('fetchObject')
                ->with($statement)
                ->willReturn($stdclass);
        });

        $this->expectExceptionMessage("Unknown Identifier Type");

        $this->testLoggingDatabaseRepository->getLogsCount(['tai' => [], 'qai' => [], 'te' => [], "wrong" => []], );
    }

    public function test_getLogWithWrongId(): void
    {
        $this->assertNull($this->testLoggingDatabaseRepository->getLog("wrong_1"));
    }

    public function test_getLogWithWrongIdFormat(): void
    {
        $this->assertNull($this->testLoggingDatabaseRepository->getLog("tai.1"));
    }

    public function test_getLogWithoutNumber(): void
    {
        $this->assertNull($this->testLoggingDatabaseRepository->getLog("tai_qai"));
    }

    /**
     * @dataProvider provideInteractionIdentifierAndFactoryMethod
     * @throws \Exception|Exception
     */
    public function test_getLogEmpty(string $id): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) {
            $mock
                ->expects($this->once())
                ->method('numRows')
                ->willReturn(0);
        });

        $this->assertNull($this->testLoggingDatabaseRepository->getLog($id . "_1"));
    }

    /**
     * @dataProvider provideInteractionIdentifierAndFactoryMethod
     * @throws \Exception|Exception
     */
    public function test_getLog(string $id, string $method, string $interaction): void
    {
        $stdClass = $this->createMock(\stdClass::class);

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($stdClass) {
            $query = $this->createMock(\ilDBStatement::class);

            $mock
                ->expects($this->once())
                ->method('numRows')
                ->willReturn(1);

            $mock
                ->expects($this->once())
                ->method('queryF')
                ->willReturn($query);

            $mock
                ->expects($this->once())
                ->method('fetchObject')
                ->with($query)
                ->willReturn($stdClass);
        });

        $interaction = $this->createMock($interaction);

        $factory = $this->createMock(Factory::class);
        $factory
            ->expects($this->once())
            ->method($method)
            ->with($stdClass)
            ->willReturn($interaction);

        $testLoggingDatabaseRepository = $this->createInstanceOf(TestLoggingDatabaseRepository::class, [
            'factory' => $factory
        ]);

        $this->assertSame($interaction, $testLoggingDatabaseRepository->getLog($id . "_1"));
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
     * @throws \Exception
     */
    public function test_deleteLogs(array $ids, string $with): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($with) {
            $mock
                ->expects($this->once())
                ->method('manipulate')
                ->with($with);

            $mock
                ->method('in')
                ->willReturnCallback(fn($id, array $values, $negate, $type) => implode(", ", $values));
        });

        $this->testLoggingDatabaseRepository->deleteLogs($ids);
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

    /**
     * @throws \Exception
     */
    public function test_deleteLogsWithInvalidIds(): void
    {
        $this->expectExceptionMessage("Unknown Identifier Type");

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) {
            $mock
                ->expects($this->never())
                ->method('manipulate');

            $mock
                ->method('in')
                ->willReturnCallback(fn($id, array $values, $negate, $type) => implode(", ", $values));
        });

        $this->testLoggingDatabaseRepository->deleteLogs(['tai_7', 'qai_9', 'wrong', 'te_wrong', 'wrong_18']);
    }

    /**
     * @throws \Exception|Exception
     */
    public function test_testHasParticipantInteractions_withAvailableInteractions(): void
    {
        $id = 5;

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($id) {
            $statement = $this->createMock(\ilDBStatement::class);

            $stdclass = $this->createMock(\stdClass::class);
            $stdclass->cnt = 15;

            $mock
                ->method('queryF')
                ->with('SELECT COUNT(id) AS cnt FROM tst_pax_log WHERE ref_id=%s', ['integer'], [$id])
                ->willReturn($statement);

            $mock
                ->expects($this->once())
                ->method('fetchObject')
                ->with($statement)
                ->willReturn($stdclass);
        });

        $this->assertTrue($this->testLoggingDatabaseRepository->testHasParticipantInteractions($id));

    }

    /**
     * @throws \Exception|Exception
     */
    public function test_testHasParticipantInteractions_withNoAvailableInteractions(): void
    {
        $id = 5;

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($id) {
            $statement = $this->createMock(\ilDBStatement::class);

            $stdclass = $this->createMock(\stdClass::class);
            $stdclass->cnt = 0;

            $mock
                ->method('queryF')
                ->with('SELECT COUNT(id) AS cnt FROM tst_pax_log WHERE ref_id=%s', ['integer'], [$id])
                ->willReturn($statement);

            $mock
                ->expects($this->once())
                ->method('fetchObject')
                ->with($statement)
                ->willReturn($stdclass);
        });

        $this->assertFalse($this->testLoggingDatabaseRepository->testHasParticipantInteractions($id));
    }

    /**
     * @throws \Exception
     */
    public function test_deleteParticipantInteractionsForTest(): void
    {
        $id = 5;

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($id) {
            $mock
                ->expects($this->once())
                ->method('manipulateF')
                ->with('DELETE FROM tst_pax_log WHERE ref_id=%s', ['integer'], [$id])
                ->willReturn(1);
        });

        $this->testLoggingDatabaseRepository->deleteParticipantInteractionsForTest($id);

    }

    /**
     * @throws \Exception|Exception
     */
    public function test_getLegacyLogsForObjId(): void
    {
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

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($id) {
            $where = ' WHERE obj_fi = ' . $id;

            $count = 0;
            $callback = function () use (&$count) {
                if ($count < 5) {
                    $count++;

                    return [
                        "tstamp" => $count,
                        "test" => "test" . $count
                    ];
                }

                return [];
            };

            $statement = $this->createMock(\ilDBStatement::class);

            $mock
                ->method('fetchAssoc')
                ->willReturnCallback($callback);

            $mock
                ->method('query')
                ->with("SELECT * FROM ass_log" . $where . " ORDER BY tstamp")
                ->willReturn($statement);
        });

        $this->assertSame($result, $this->testLoggingDatabaseRepository->getLegacyLogsForObjId($id));
    }
}
