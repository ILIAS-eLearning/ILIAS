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

namespace ILIAS\Test\Tests\Results\Data;

use ILIAS\Cache\Container\Container;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\Test\Results\Data\AttemptResult;
use ILIAS\Test\Results\Data\ParticipantResult;
use ILIAS\Test\Results\Data\Repository;
use ILIAS\Test\Scoring\Marks\Mark;
use ILIAS\Test\Scoring\Marks\MarkSchema;
use ILIAS\Test\Scoring\Marks\MarkSchemaFactory;
use ILIAS\Test\Scoring\Marks\MarksRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;

class TestResultRepositoryTest extends \ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $repository = $this->createInstance();
        $this->assertInstanceOf(Repository::class, $repository);
    }

    #[DataProvider('providePassedParticipants')]
    public function testGetPassedParticipants(int $test_obj_id, array $query_result): void
    {
        $this->mockGetPassedParticipants($query_result);
        $repository = $this->createInstance();

        $actual = $repository->getPassedParticipants($test_obj_id);
        foreach ($actual as $index => $participant) {
            $this->assertEquals($participant['active_id'], $query_result[$index]['active_id']);
            $this->assertEquals($participant['user_id'], $query_result[$index]['user_id']);
        }
    }

    #[DataProvider('provideTestResultCache')]
    public function testGetTestResult(array $query_result, array $expected): void
    {
        $this->mockGetTestResultQuery($query_result);
        $repository = $this->createInstance($query_result);

        $actual = $repository->getTestResult($query_result['active_fi']);

        $this->assertNotNull($actual);
        $this->assertInstanceOf(ParticipantResult::class, $actual);
        foreach ($expected as $method => $value) {
            $this->assertEquals($value, $actual->$method());
        }
    }

    public function testGetTestResultNotFound(): void
    {
        $this->mockGetTestResultQuery(null);
        $repository = $this->createInstance();

        $actual = $repository->getTestResult(1000);

        $this->assertNull($actual);
    }

    #[DataProvider('provideFetchedTestAttemptResult')]
    public function testReadStatus(array $query_result, array $expected): void
    {
        $this->mockUpdateTestResultCache($query_result);
        $repository = $this->createInstance();
        $repository->updateTestResultCache($query_result['active_fi']);

        $user_id = $query_result['user_id'];
        $test_obj_id = $query_result['test_obj_id'];

        $this->assertEquals($expected['isPassed'], $repository->isPassed($user_id, $test_obj_id));
        $this->assertEquals($expected['isFailed'], $repository->isFailed($user_id, $test_obj_id));
        $this->assertEquals($expected['hasFinished'], $repository->hasFinished($user_id, $test_obj_id));
    }

    public function testFailedPassedNotFound(): void
    {
        $repository = $this->createInstance();

        $this->assertFalse($repository->isPassed(100, 200));
        $this->assertFalse($repository->isFailed(100, 200));
        $this->assertFalse($repository->hasFinished(100, 200));
    }

    #[DataProvider('provideCachedStatus')]
    public function testReadFromCache(array $query, array $cached_status, array $expected): void
    {
        $repository = $this->createInstance();
        $user_id = $query['user_id'];
        $test_obj_id = $query['test_obj_id'];

        // Ensure the data is queried from the database, as it is not yet in the cache
        $this->mockReadResultStatusQuery($cached_status);

        $this->assertEquals($expected['isPassed'], $repository->isPassed($user_id, $test_obj_id));
        $this->assertEquals($expected['isFailed'], $repository->isFailed($user_id, $test_obj_id));
        $this->assertEquals($expected['hasFinished'], $repository->hasFinished($user_id, $test_obj_id));

        // Ensure the database is not queried again
        $this->adaptDICServiceMock(
            \ilDBInterface::class,
            function (\ilDBInterface|MockObject $mock) {
                $mock->expects($this->exactly(0))->method('queryF');
                $mock->expects($this->exactly(0))->method('fetchAssoc');
            }
        );

        $this->assertEquals($expected['isPassed'], $repository->isPassed($user_id, $test_obj_id));
        $this->assertEquals($expected['isFailed'], $repository->isFailed($user_id, $test_obj_id));
        $this->assertEquals($expected['hasFinished'], $repository->hasFinished($user_id, $test_obj_id));
    }

    #[DataProvider('provideCachedStatus')]
    public function testRemoveTestResults(array $query, array $cached_status, array $expected): void
    {
        $repository = $this->createInstance();
        $user_id = $query['user_id'];
        $active_id = $query['active_id'];
        $test_obj_id = $query['test_obj_id'];

        // Ensure the data is loaded from the database, as it is not yet in the cache
        $this->mockReadResultStatusQuery($cached_status);

        $this->assertEquals($expected['isPassed'], $repository->isPassed($user_id, $test_obj_id));
        $this->assertEquals($expected['isFailed'], $repository->isFailed($user_id, $test_obj_id));
        $this->assertEquals($expected['hasFinished'], $repository->hasFinished($user_id, $test_obj_id));

        $this->mockGetUserIds([['user_fi' => $user_id]]);
        $repository->removeTestResults([$active_id], $test_obj_id);

        // Ensure the data is queried again
        $this->mockReadResultStatusQuery($cached_status);

        $this->assertEquals($expected['isPassed'], $repository->isPassed($user_id, $test_obj_id));
        $this->assertEquals($expected['isFailed'], $repository->isFailed($user_id, $test_obj_id));
        $this->assertEquals($expected['hasFinished'], $repository->hasFinished($user_id, $test_obj_id));
    }

    #[DataProvider('provideTestAttemptResult')]
    public function testGetTestAttemptResult(array $query_result, array $expected): void
    {
        $this->mockGetTestPassResultQuery($query_result);
        $repository = $this->createInstance();

        $actual = $repository->getTestAttemptResult($query_result['active_fi']);

        $this->assertNotNull($actual);
        $this->assertInstanceOf(AttemptResult::class, $actual);
        foreach ($expected as $method => $value) {
            $this->assertEquals($value, $actual->$method());
        }
    }

    public function testGetTestAttemptResultNotFound(): void
    {
        $this->mockGetTestPassResultQuery(null);
        $repository = $this->createInstance();

        $actual = $repository->getTestAttemptResult(1000);

        $this->assertNull($actual);
    }

    #[DataProvider('provideFetchedTestResult')]
    public function testUpdateTestAttemptResult(
        array $parameters,
        array $test_result,
        array $test_config_result,
        array $working_time_result,
        array $expected
    ): void {
        $this->mockUpdateTestAttemptResult($test_result, $test_config_result, $working_time_result);
        $repository = $this->createInstance();

        $actual = $repository->updateTestAttemptResult(
            $parameters['active_id'],
            $parameters['pass'],
            null,
            $parameters['test_obj_id'],
            false
        );

        $this->assertNotNull($actual);
        $this->assertInstanceOf(AttemptResult::class, $actual);
        $this->assertEqualsWithDelta(time(), $actual->getTimestamp(), 5);
        foreach ($expected as $method => $value) {
            $this->assertEquals($value, $actual->$method());
        }
    }

    /*
        Mocking
     */

    private function createInstance(?array $mock_data = null): Repository
    {
        global $DIC;

        $global_cache = $this->createConfiguredMock(
            \ILIAS\Cache\Services::class,
            ['get' => $this->createCacheMock()]
        );

        $partial_mock = $this->getMockBuilder(Repository::class)
            ->disableOriginalClone()
            ->setConstructorArgs([
                $DIC->database(),
                $this->createMock(Refinery::class),
                $this->createMarksRepositoryMock($mock_data),
                $global_cache
            ])
            ->onlyMethods(['lookupAttempt'])
            ->getMock();
        $partial_mock->method('lookupAttempt')->willReturn(0);

        return $partial_mock;
    }

    private function createMarksRepositoryMock(?array $mock_data): MarksRepository
    {
        if ($mock_data) {
            $mock = new Mark(
                $mock_data['mark_short'],
                $mock_data['mark_official'],
                0.0,
                (bool) $mock_data['passed']
            );
            $mark_schema = $this->createConfiguredMock(
                MarkSchema::class,
                ['getMatchingMark' => $mock]
            );
        } else {
            $mark_schema = (new MarkSchemaFactory())->createSimpleSchema(0);
        }

        return new class ($mark_schema) implements MarksRepository {
            public function __construct(protected MarkSchema $mark_schema)
            {
            }

            public function getMarkSchemaFor(int $test_id): MarkSchema
            {
                return $this->mark_schema;
            }

            public function storeMarkSchema(MarkSchema $mark_schema): array
            {
                throw new \Error('Not implemented');
            }

            public function getMarkSchemaBySteps(array $step_ids): MarkSchema
            {
                throw new \Error('Not implemented');
            }

            public function deleteSteps(array $step_ids): void
            {
                throw new \Error('Not implemented');
            }
        };
    }

    private function createCacheMock(): Container
    {
        return new class () implements Container {
            private array $cache = [];

            public function lock(float $seconds): void
            {
                throw new \Error('Not implemented');
            }

            public function isLocked(): bool
            {
                throw new \Error('Not implemented');
            }

            public function has(string $key): bool
            {
                return isset($this->cache[$key]);
            }

            public function get(string $key, Transformation $transformation): string|int|array|bool|null
            {
                return $this->cache[$key] ?? null;
            }

            public function set(string $key, string|int|array|bool|null $value, ?int $ttl = null): void
            {
                $this->cache[$key] = $value;
            }

            public function delete(string $key): void
            {
                unset($this->cache[$key]);
            }

            public function flush(): void
            {
                $this->cache = [];
            }

            public function getAdaptorName(): string
            {
                throw new \Error('Not implemented');
            }

            public function getContainerName(): string
            {
                throw new \Error('Not implemented');
            }
        };
    }

    /*
        Database Mocking
     */

    /**
     * @see Repository::getPassedParticipants
     */
    private function mockGetPassedParticipants(array $fetch_all_return): void
    {
        $this->adaptDICServiceMock(
            \ilDBInterface::class,
            function (\ilDBInterface|MockObject $mock) use ($fetch_all_return) {
                $mock
                    ->expects($this->once())
                    ->method('queryF')
                    ->with($this->stringContains("WHERE tst_tests.obj_fi = %s AND tst_result_cache.passed_once = 1"));

                $mock
                    ->expects($this->once())
                    ->method('fetchAll')
                    ->willReturn($fetch_all_return);
            }
        );
    }

    /**
     * @see Repository::getTestResult
     */
    private function mockGetTestResultQuery(?array $fetch_assoc_return): void
    {
        if ($fetch_assoc_return) {
            $fetch_assoc_return['test_id'] = 0;
        }
        $this->mockGetResultQuery('tst_result_cache', $fetch_assoc_return);
    }

    /**
     * @see Repository::getTestAttemptResult
     */
    private function mockGetTestPassResultQuery(?array $fetch_assoc_return): void
    {
        $this->mockGetResultQuery('tst_pass_result', $fetch_assoc_return);
    }

    private function mockGetResultQuery(string $table, ?array $fetch_assoc_return): void
    {
        $this->adaptDICServiceMock(
            \ilDBInterface::class,
            function (\ilDBInterface|MockObject $mock) use ($table, $fetch_assoc_return) {
                $mock
                    ->expects($this->once())
                    ->method('queryF');

                $mock
                    ->expects($this->once())
                    ->method('fetchAssoc')
                    ->willReturn($fetch_assoc_return);
            }
        );
    }

    /**
     * @see Repository::readOrQueryStatus
     */
    private function mockReadResultStatusQuery(?array $fetch_assoc_return): void
    {
        $this->adaptDICServiceMock(
            \ilDBInterface::class,
            function (\ilDBInterface|MockObject $mock) use ($fetch_assoc_return) {
                $mock->expects($this->atLeastOnce())->method('queryF');
                $mock->expects($this->atLeastOnce())->method('fetchAssoc')->willReturn($fetch_assoc_return);
            }
        );
    }

    /**
     * @see Repository::invalidateStatusCache
     */
    private function mockGetUserIds(?array $fetch_all_return): void
    {
        $this->adaptDICServiceMock(
            \ilDBInterface::class,
            function (\ilDBInterface|MockObject $mock) use ($fetch_all_return) {
                $mock->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo("SELECT user_fi FROM tst_active WHERE\n"));
                $mock->expects($this->once())->method('fetchAll')->willReturn($fetch_all_return);
            }
        );
    }

    /**
     * @see Repository::updateTestResultCache
     */
    private function mockUpdateTestResultCache(?array $test_attempt_result, bool $passed_once = false): void
    {
        $this->adaptDICServiceMock(
            \ilDBInterface::class,
            function (\ilDBInterface|MockObject $mock) use ($test_attempt_result) {
                // Ensures that the check whether results are available is mocked
                $mocked_stmt = $this->createConfiguredMock(\ilDBStatement::class, [
                    'numRows' => 1,
                ]);
                $mock->method('queryF')->willReturn($mocked_stmt);

                // TestResultRepository::fetchTestPassResult
                $mock->expects($this->exactly(1))
                    ->method('fetchAssoc')
                    ->willReturn($test_attempt_result);

                $mock->expects($this->exactly(1))->method('replace');
            }
        );
    }

    /**
     * @see Repository::updateTestAttemptResult
     */
    private function mockUpdateTestAttemptResult(?array $test_result, ?array $test_config, ?array $working_time): void
    {
        $fetch_assoc_mocks = [
            $test_result,                                                   // TestResultRepository::fetchTestResult
            ['question_set_type' => \ilObjTest::QUESTION_SET_TYPE_FIXED],   // TestResultRepository::fetchAdditionalTestData (1)
            $test_config,                                                   // TestResultRepository::fetchAdditionalTestData (2)
            $working_time,                                                  // TestResultRepository::fetchWorkingTime
            null                                                            // TestResultRepository::fetchWorkingTime (i2)
        ];

        $this->adaptDICServiceMock(
            \ilDBInterface::class,
            function (\ilDBInterface|MockObject $mock) use ($fetch_assoc_mocks) {
                // Ensures that the check whether results are available is mocked
                $mocked_stmt = $this->createConfiguredMock(\ilDBStatement::class, [
                    'numRows' => 1,
                ]);
                $mock->method('queryF')->willReturn($mocked_stmt);

                $mock->expects($this->exactly(count($fetch_assoc_mocks)))
                    ->method('fetchAssoc')
                    ->willReturnOnConsecutiveCalls(...$fetch_assoc_mocks);

                $mock->expects($this->exactly(1))->method('replace');
            }
        );
    }

    /*
        Data Provider
     */

    /**
     * This method returns test parameter, sample data and expected results for testing status cache.
     */
    public static function provideCachedStatus(): array
    {
        return [
            [
                ['user_id' => 1, 'test_obj_id' => 100, 'active_id' => 1000],
                ['passed' => true, 'failed' => false, 'finished' => false],
                ['isPassed' => true, 'isFailed' => false, 'hasFinished' => false],
            ],
            [
                ['user_id' => 10, 'test_obj_id' => 100, 'active_id' => 1000],
                ['passed' => false, 'failed' => true, 'finished' => false],
                ['isPassed' => false, 'isFailed' => true, 'hasFinished' => false],
            ],
            [
                ['user_id' => 1, 'test_obj_id' => 250, 'active_id' => 1400],
                ['passed' => false, 'failed' => true, 'finished' => true],
                ['isPassed' => false, 'isFailed' => true, 'hasFinished' => true],
            ]
        ];
    }

    /**
     * This method returns sample data for this query:
     *
     *  SELECT tst_result_cache.active_fi AS active_id, tst_active.user_fi AS user_id FROM tst_result_cache
     *  INNER JOIN tst_active ON tst_active.active_id = tst_result_cache.active_fi INNER JOIN tst_tests ON
     *  tst_tests.test_id = tst_active.test_fi WHERE tst_tests.obj_fi = %s AND tst_result_cache.passed_once = 1
     *
     * @see Repository::getPassedParticipants()
     */
    public static function providePassedParticipants(): array
    {
        return [
            [
                10,
                [
                    ['user_id' => 1, 'active_id' => 100],
                    ['user_id' => 2, 'active_id' => 200],
                    ['user_id' => 3, 'active_id' => 101],
                    ['user_id' => 4, 'active_id' => 201],
                    ['user_id' => 5, 'active_id' => 0],
                ]
            ],
        ];
    }

    /**
     * This method returns sample data for this query:
     *
     *  SELECT * FROM tst_result_cache WHERE active_fi = %s
     *
     * @see Repository::getTestResult()
     */
    public static function provideTestResultCache(): array
    {
        return [
            // Dataset #1: failed result
            [
                [
                    'active_fi' => 10,
                    'pass' => 0,
                    'max_points' => 25,
                    'reached_points' => 0,
                    'mark_short' => 'failed',
                    'mark_official' => 'failed',
                    'passed' => 0,
                    'failed' => 1,
                    'tstamp' => 1740557748,
                    'passed_once' => 0
                ],
                [
                    'getActiveId' => 10,
                    'isPassed' => false,
                    'isPassedOnce' => false,
                    'isFailed' => true,
                    'getAttempt' => 0,
                    'getMaxPoints' => 25,
                    'getReachedPoints' => 0,
                    'getMarkShort' => 'failed',
                    'getMarkOfficial' => 'failed',
                    'getTimestamp' => 1740557748,
                ]
            ],
        ];
    }

    /**
     * This method returns sample data for this query:
     *
     *  SELECT tst_pass_result.*, tst_active.last_finished_pass, tst_active.user_fi AS user_id,  tst_tests.test_id,
     *  tst_tests.obj_fi AS test_obj_id, tst_pass_result.maxpoints AS max_points, points AS reached_points
     *  FROM tst_pass_result INNER JOIN tst_active ON tst_pass_result.active_fi = tst_active.active_id
     *  INNER JOIN tst_tests ON tst_tests.test_id = tst_active.test_fi WHERE active_fi = %s AND pass = %s
     *
     * @see Repository::fetchTestAttemptResult()
     */
    public static function provideFetchedTestAttemptResult(): array
    {
        return [
            // Dataset #1: failed result
            [
                [
                    'active_fi' => 10,
                    'pass' => 0,
                    'maxpoints' => 0,
                    'questioncount' => 2,
                    'answeredquestions' => 2,
                    'workingtime' => 12,
                    'tstamp' => 1740557748,
                    'exam_id' => 'I0_T334_A41_P0',
                    'finalized_by' => null,
                    'last_finished_pass' => 0,
                    'user_id' => 1,
                    'test_id' => 5,
                    'test_obj_id' => 100,
                    'max_points' => 25,
                    'reached_points' => 0,
                ],
                [
                    'getActiveId' => 10,
                    'isPassed' => false,
                    'isPassedOnce' => false,
                    'isFailed' => true,
                    'hasFinished' => true,
                    'getAttempt' => 0,
                    'getMaxPoints' => 25,
                    'getReachedPoints' => 0,
                    'getMarkShort' => 'failed',
                    'getMarkOfficial' => 'failed',
                    'getTimestamp' => 1740557748,
                ]
            ],
            // Dataset #2: success result
            [
                [
                    'active_fi' => 11,
                    'pass' => 0,
                    'maxpoints' => 0,
                    'questioncount' => 2,
                    'answeredquestions' => 2,
                    'workingtime' => 12,
                    'tstamp' => 1740557748,
                    'exam_id' => 'I0_T334_A41_P0',
                    'finalized_by' => null,
                    'last_finished_pass' => 1,
                    'user_id' => 1,
                    'test_id' => 5,
                    'test_obj_id' => 100,
                    'max_points' => 25,
                    'reached_points' => 25,
                ],
                [
                    'getActiveId' => 11,
                    'isPassed' => true,
                    'isPassedOnce' => true,
                    'isFailed' => false,
                    'hasFinished' => true,
                    'getAttempt' => 0,
                    'getMaxPoints' => 25,
                    'getReachedPoints' => 25,
                    'getMarkShort' => 'passed',
                    'getMarkOfficial' => 'passed',
                    'getTimestamp' => 1740557748,
                ]
            ]
        ];
    }

    /**
     * This method returns sample data for this query:
     *
     *  SELECT * FROM tst_result_cache WHERE active_fi = %s
     *
     * @see Repository::getTestAttemptResult
     */
    public static function provideTestAttemptResult(): array
    {
        return [
            [
                [
                    'active_fi' => 10,
                    'pass' => 0,
                    'points' => 0,
                    'maxpoints' => 25,
                    'questioncount' => 3,
                    'answeredquestions' => 2,
                    'workingtime' => 12,
                    'tstamp' => 1740557748,
                    'exam_id' => 'I0_T334_A41_P0',
                    'finalized_by' => null,
                ],
                [
                    'getActiveId' => 10,
                    'getAttempt' => 0,
                    'getMaxPoints' => 25,
                    'getReachedPoints' => 0,
                    'getQuestionCount' => 3,
                    'getAnsweredQuestions' => 2,
                    'getWorkingTime' => 12,
                    'getTimestamp' => 1740557748,
                    'getExamId' => 'I0_T334_A41_P0',
                    'getFinalizedBy' => null
                ]
            ],
        ];
    }

    /**
     * This method returns sample data for these queries:
     *
     *  SELECT pass, SUM(points) AS points, COUNT(DISTINCT(question_fi)) answeredquestions FROM tst_test_result WHERE
     *  active_fi = %s AND pass = %s
     *
     *  SELECT started, finished FROM tst_times WHERE active_fi = %s AND pass = %s ORDER BY started
     *
     * @see Repository::fetchTestResult, Repository::fetchAdditionalTestData,Repository::fetchWorkingTime
     */
    public static function provideFetchedTestResult(): array
    {
        return [
            [
                // Test Parameters
                [
                    'active_id' => 10,
                    'pass' => 0,
                    'test_obj_id' => 100,
                ],
                // Results of query 1
                [
                    'pass' => 0,
                    'points' => 10,
                    'answeredquestions' => 2,
                ],
                // Result of query 2
                [
                    'qcount' => 3,
                    'qsum' => 25
                ],
                // Result of query 3
                [
                    'started' => '2024-01-01 04:05:05',
                    'finished' => '2024-01-01 04:05:17'
                ],
                // Expected Results
                [
                    'getActiveId' => 10,
                    'getAttempt' => 0,
                    'getMaxPoints' => 25,
                    'getReachedPoints' => 10,
                    'getQuestionCount' => 3,
                    'getAnsweredQuestions' => 2,
                    'getWorkingTime' => 12,
                    'getExamId' => 'I_T100_A10_P0', // see \ilObjTest::buildExamId
                    'getFinalizedBy' => null
                ],
            ]
        ];
    }
}
