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

namespace Logging;

use ilDBInterface;
use ilDBStatement;
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
use stdClass;

class TestLoggingDatabaseRepositoryTest extends ilTestBaseTestCase
{
    /**
     * @throws Exception|ReflectionException
     */
    public function testStoreTestAdministrationInteraction(): void
    {
        $next_id = 1;
        $storage_array = ['value' => $next_id];
        $test_administration_interaction = $this->createMock(TestAdministrationInteraction::class);
        $test_administration_interaction
            ->expects($this->once())
            ->method('toStorage')
            ->willReturn($storage_array);

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($storage_array, $next_id) {
            $result_array = array_merge($storage_array, ['id' => ['integer', $next_id]]);
            $table = 'tst_tst_admin_log';

            $mock
                ->expects($this->once())
                ->method('nextId')
                ->with($table)
                ->willReturn($next_id);

            $mock
                ->expects($this->once())
                ->method('insert')
                ->with($table, $result_array);
        });

        $test_logging_database_repository = $this->createInstanceOf(TestLoggingDatabaseRepository::class);
        $this->assertNull($test_logging_database_repository->storeTestAdministrationInteraction($test_administration_interaction));
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testStoreQuestionAdministrationInteraction(): void
    {
        $next_id = 1;
        $storage_array = ['value' => $next_id];
        $test_question_administration_interaction = $this->createMock(TestQuestionAdministrationInteraction::class);
        $test_question_administration_interaction
            ->expects($this->once())
            ->method('toStorage')
            ->willReturn($storage_array);

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($storage_array, $next_id) {
            $result_array = array_merge($storage_array, ['id' => ['integer', $next_id]]);
            $table = 'tst_qst_admin_log';

            $mock
                ->expects($this->once())
                ->method('nextId')
                ->with($table)
                ->willReturn($next_id);

            $mock
                ->expects($this->once())
                ->method('insert')
                ->with($table, $result_array);
        });

        $test_logging_database_repository = $this->createInstanceOf(TestLoggingDatabaseRepository::class);
        $this->assertNull($test_logging_database_repository->storeQuestionAdministrationInteraction($test_question_administration_interaction));
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testStoreParticipantInteraction(): void
    {
        $next_id = 1;
        $storage_array = ['value' => $next_id];
        $test_participant_interaction = $this->createMock(TestParticipantInteraction::class);
        $test_participant_interaction
            ->expects($this->once())
            ->method('toStorage')
            ->willReturn($storage_array);

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($storage_array, $next_id) {
            $result_array = array_merge($storage_array, ['id' => ['integer', $next_id]]);
            $table = 'tst_pax_log';

            $mock
                ->expects($this->once())
                ->method('nextId')
                ->with($table)
                ->willReturn($next_id);

            $mock
                ->expects($this->once())
                ->method('insert')
                ->with($table, $result_array);
        });

        $test_logging_database_repository = $this->createInstanceOf(TestLoggingDatabaseRepository::class);
        $this->assertNull($test_logging_database_repository->storeParticipantInteraction($test_participant_interaction));
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testStoreScoringInteraction(): void
    {
        $next_id = 1;
        $storage_array = ['value' => $next_id];
        $test_scoring_interaction = $this->createMock(TestScoringInteraction::class);
        $test_scoring_interaction
            ->expects($this->once())
            ->method('toStorage')
            ->willReturn($storage_array);

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($storage_array, $next_id) {
            $result_array = array_merge($storage_array, ['id' => ['integer', $next_id]]);
            $table = 'tst_mark_log';

            $mock
                ->expects($this->once())
                ->method('nextId')
                ->with($table)
                ->willReturn($next_id);

            $mock
                ->expects($this->once())
                ->method('insert')
                ->with($table, $result_array);
        });

        $test_logging_database_repository = $this->createInstanceOf(TestLoggingDatabaseRepository::class);
        $this->assertNull($test_logging_database_repository->storeScoringInteraction($test_scoring_interaction));
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testStoreError(): void
    {
        $next_id = 1;
        $storage_array = ['value' => $next_id];
        $test_error = $this->createMock(TestError::class);
        $test_error
            ->expects($this->once())
            ->method('toStorage')
            ->willReturn($storage_array);

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($storage_array, $next_id) {
            $result_array = array_merge($storage_array, ['id' => ['integer', $next_id]]);
            $table = 'tst_error_log';

            $mock
                ->expects($this->once())
                ->method('nextId')
                ->with($table)
                ->willReturn($next_id);

            $mock
                ->expects($this->once())
                ->method('insert')
                ->with($table, $result_array);
        });

        $test_logging_database_repository = $this->createInstanceOf(TestLoggingDatabaseRepository::class);
        $this->assertNull($test_logging_database_repository->storeError($test_error));
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testGetLogsCountWithoutFilter(): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) {
            $statement = $this->createMock(ilDBStatement::class);

            $std_class = $this->createMock(stdClass::class);
            $std_class->cnt = 15;

            $mock
                ->expects($this->once())
                ->method('query')
                ->willReturn($statement);

            $mock
                ->expects($this->once())
                ->method('fetchObject')
                ->with($statement)
                ->willReturn($std_class);
        });

        $test_logging_database_repository = $this->createInstanceOf(TestLoggingDatabaseRepository::class);
        $this->assertSame(15, $test_logging_database_repository->getLogsCount(['tai' => [], 'qai' => [], 'pi' => [], 'si' => [], 'te' => []]));
    }

    /**
     * @throws \Exception|Exception
     */
    public function testGetLogsCountWithLogEntryTypeFilterAndNoResults(): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) {
            $statement = $this->createMock(ilDBStatement::class);

            $std_class = $this->createMock(stdClass::class);
            $std_class->cnt = 15;

            $mock
                ->expects($this->never())
                ->method('query')
                ->willReturn($statement);

            $mock
                ->expects($this->never())
                ->method('fetchObject')
                ->with($statement)
                ->willReturn($std_class);
        });

        $test_logging_database_repository = $this->createInstanceOf(TestLoggingDatabaseRepository::class);
        $this->assertSame(0, $test_logging_database_repository->getLogsCount(['tai' => []], null, null, null, null, null, null, null, []));
    }

    /**
     * @throws \Exception|Exception
     */
    public function testGetLogsCountWithPaxFilterAndNoResults(): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) {
            $statement = $this->createMock(ilDBStatement::class);

            $std_class = $this->createMock(stdClass::class);
            $std_class->cnt = 15;

            $mock
                ->expects($this->never())
                ->method('query')
                ->willReturn($statement);

            $mock
                ->expects($this->never())
                ->method('fetchObject')
                ->with($statement)
                ->willReturn($std_class);
        });

        $test_logging_database_repository = $this->createInstanceOf(TestLoggingDatabaseRepository::class);
        $this->assertSame(0, $test_logging_database_repository->getLogsCount(['tai' => []], null, null, null, null, []));
    }

    /**
     * @throws \Exception|Exception
     */
    public function testGetLogsCountWithFilterAndResults(): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) {
            $statement = $this->createMock(ilDBStatement::class);

            $std_class = $this->createMock(stdClass::class);
            $std_class->cnt = 15;

            $mock
                ->expects($this->once())
                ->method('query')
                ->willReturn($statement);

            $mock
                ->expects($this->once())
                ->method('fetchObject')
                ->with($statement)
                ->willReturn($std_class);
        });

        $test_logging_database_repository = $this->createInstanceOf(TestLoggingDatabaseRepository::class);
        $this->assertSame(15, $test_logging_database_repository->getLogsCount(['tai' => [], 'qai' => [], 'te' => []], null, null, null, null, null, null, null, ['qai']));
    }

    /**
     * @throws \Exception|Exception
     */
    public function testGetLogsCountWithWrongIdentifier(): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) {
            $statement = $this->createMock(ilDBStatement::class);

            $std_class = $this->createMock(stdClass::class);
            $std_class->cnt = 15;

            $mock
                ->expects($this->never())
                ->method('query')
                ->willReturn($statement);

            $mock
                ->expects($this->never())
                ->method('fetchObject')
                ->with($statement)
                ->willReturn($std_class);
        });

        $this->expectExceptionMessage('Unknown Identifier Type');

        $test_logging_database_repository = $this->createInstanceOf(TestLoggingDatabaseRepository::class);
        $test_logging_database_repository->getLogsCount(['tai' => [], 'qai' => [], 'te' => [], 'wrong' => []]);
    }

    /**
     * @dataProvider getLogDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetLog(array $input, ?string $output): void
    {
        $std_class = $this->createMock(stdClass::class);

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($std_class, $output) {
            $query = $this->createMock(ilDBStatement::class);

            $exactly = is_null($output) ? $this->never() : $this->exactly(2);

            $mock
                ->method('numRows')
                ->willReturn(1);

            $mock
                ->expects($exactly)
                ->method('queryF')
                ->willReturn($query);

            $mock
                ->expects($exactly)
                ->method('fetchObject')
                ->with($query)
                ->willReturn($std_class);
        });

        $factory = $this->createMock(Factory::class);

        if (!is_null($output)) {
            $interaction = $this->createMock($output);
            $factory
                ->expects($this->once())
                ->method($input['method'])
                ->with($std_class)
                ->willReturn($interaction);
        }

        $test_logging_database_repository = $this->createInstanceOf(TestLoggingDatabaseRepository::class, [
            'factory' => $factory
        ]);

        if (is_null($output)) {
            $this->assertNull($test_logging_database_repository->getLog($input['unique_id']));
            return;
        }

        $this->assertEquals($interaction, $test_logging_database_repository->getLog($input['unique_id']));
    }

    public static function getLogDataProvider(): array
    {
        return [
            'wrong_1_empty' => [['unique_id' => 'wrong_1', 'method' => ''], null],
            'tai.1_empty' => [['unique_id' => 'tai.1', 'method' => ''], null],
            'tai_qai_empty' => [['unique_id' => 'tai_qai', 'method' => ''], null],
            'tai_1_buildTestAdministrationInteractionFromDBValues' => [['unique_id' => 'tai_1', 'method' => 'buildTestAdministrationInteractionFromDBValues'], TestAdministrationInteraction::class],
            'qai_1_buildQuestionAdministrationInteractionFromDBValues' => [['unique_id' => 'qai_1', 'method' => 'buildQuestionAdministrationInteractionFromDBValues'], TestQuestionAdministrationInteraction::class],
            'pi_1_buildParticipantInteractionFromDBValues' => [['unique_id' => 'pi_1', 'method' => 'buildParticipantInteractionFromDBValues'], TestParticipantInteraction::class],
            'si_1_buildScoringInteractionFromDBValues' => [['unique_id' => 'si_1', 'method' => 'buildScoringInteractionFromDBValues'], TestScoringInteraction::class],
            'te_1_buildErrorFromDBValues' => [['unique_id' => 'te_1', 'method' => 'buildErrorFromDBValues'], TestError::class]
        ];
    }

    /**
     * @dataProvider deleteLogsDataProvider
     * @throws \Exception|Exception
     */
    public function testDeleteLogs(array $input): void
    {
        $with = $input['with'];

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($with) {
            $mock
                ->expects(is_null($with) ? $this->never(): $this->once())
                ->method('manipulate')
                ->with($with);

            $mock
                ->method('in')
                ->willReturnCallback(fn($id, array $values, $negate, $type) => implode(', ', $values));
        });

        if (is_null($with)) {
            $this->expectExceptionMessage('Unknown Identifier Type');
        }

        $test_logging_database_repository = $this->createInstanceOf(TestLoggingDatabaseRepository::class);
        $this->assertNull($test_logging_database_repository->deleteLogs($input['ids']));
    }

    public static function deleteLogsDataProvider(): array
    {
        return [
            'all_objects' => [[
                'ids' => ['ALL_OBJECTS'],
                'with' => 'TUNCATE TABLE tst_tst_admin_log;TUNCATE TABLE tst_qst_admin_log;TUNCATE TABLE tst_pax_log;TUNCATE TABLE tst_mark_log;TUNCATE TABLE tst_error_log;'
            ]],
            'valid_key_variation' => [[
                'ids' => ['tai_7', 'qai_9', 'wrong', 'te_wrong'],
                'with' => 'DELETE FROM tst_tst_admin_log WHERE 7;' . PHP_EOL . 'DELETE FROM tst_qst_admin_log WHERE 9;' . PHP_EOL
            ]],
            'invalid_key_variation' => [[
                'ids' => ['tai_7', 'qai_9', 'wrong', 'te_wrong', 'wrong_18'],
                'with' => null
            ]]
        ];
    }

    /**
     * @dataProvider hasParticipantInteractionsDataProvider
     * @throws \Exception|Exception
     */
    public function testTestHasParticipantInteractions(array $input, bool $output): void
    {
        $id = $input['id'];

        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($input, $id) {
            $statement = $this->createMock(ilDBStatement::class);

            $std_class = $this->createMock(stdClass::class);
            $std_class->cnt = $input['count'];

            $mock
                ->method('queryF')
                ->with('SELECT COUNT(id) AS cnt FROM tst_pax_log WHERE ref_id=%s', ['integer'], [$id])
                ->willReturn($statement);

            $mock
                ->expects($this->once())
                ->method('fetchObject')
                ->with($statement)
                ->willReturn($std_class);
        });

        $test_logging_database_repository = $this->createInstanceOf(TestLoggingDatabaseRepository::class);
        $this->assertEquals($output, $test_logging_database_repository->testHasParticipantInteractions($id));
    }

    public static function hasParticipantInteractionsDataProvider(): array
    {
        return [
            'negative_one_negative_one' => [['id' => -1, 'count' => -1], false],
            'negative_one_zero' => [['id' => -1, 'count' => 0], false],
            'negative_one_one' => [['id' => -1, 'count' => 1], true],
            'zero_negative_one' => [['id' => 0, 'count' => -1], false],
            'zero_zero' => [['id' => 0, 'count' => 0], false],
            'zero_zero_one' => [['id' => 0, 'count' => 1], true],
            'one_negative_one' => [['id' => 1, 'count' => -1], false],
            'one_zero' => [['id' => 1, 'count' => 0], false],
            'one_one' => [['id' => 1, 'count' => 1], true]
        ];
    }

    /**
     * @dataProvider deleteParticipantInteractionsForTestDataProvider
     * @throws \Exception|Exception
     */
    public function testDeleteParticipantInteractionsForTest(int $input): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) use ($input) {
            $mock
                ->expects($this->once())
                ->method('manipulateF')
                ->with('DELETE FROM tst_pax_log WHERE ref_id=%s', ['integer'], [$input]);
        });

        $test_logging_database_repository = $this->createInstanceOf(TestLoggingDatabaseRepository::class);
        $this->assertNull($test_logging_database_repository->deleteParticipantInteractionsForTest($input));
    }

    public static function deleteParticipantInteractionsForTestDataProvider(): array
    {
        return [
            'negative_one' => [-1],
            'zero' => [0],
            'one' => [1]
        ];
    }

    /**
     * @throws \Exception|Exception
     */
    public function testGetLegacyLogsForObjId(): void
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
                        'tstamp' => $count,
                        'test' => 'test' . $count
                    ];
                }

                return [];
            };

            $statement = $this->createMock(ilDBStatement::class);

            $mock
                ->method('fetchAssoc')
                ->willReturnCallback($callback);

            $mock
                ->method('query')
                ->with('SELECT * FROM ass_log' . $where . ' ORDER BY tstamp')
                ->willReturn($statement);
        });

        $test_logging_database_repository = $this->createInstanceOf(TestLoggingDatabaseRepository::class);
        $this->assertSame($result, $test_logging_database_repository->getLegacyLogsForObjId($id));
    }
}
