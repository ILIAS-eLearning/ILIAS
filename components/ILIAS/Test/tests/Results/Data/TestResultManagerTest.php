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
use ILIAS\Refinery\Transformation;
use ILIAS\Test\Results\Data\TestPassResult;
use ILIAS\Test\Results\Data\TestResult;
use ILIAS\Test\Results\Data\TestResultManager;
use ILIAS\Test\Scoring\Marks\MarkSchema;
use ILIAS\Test\Scoring\Marks\MarksRepository;
use PHPUnit\Framework\MockObject\MockObject;

class TestResultManagerTest extends \ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $manager = $this->createInstance();
        $this->assertInstanceOf(TestResultManager::class, $manager);
    }

    /**
     * @dataProvider provideTestResultCache
     */
    public function testGetTestResult(array $query_result, array $expected): void
    {
        $this->mockGetTestResultQuery($query_result);
        $manager = $this->createInstance();

        $actual = $manager->getTestResult($query_result['active_fi']);

        $this->assertNotNull($actual);
        $this->assertInstanceOf(TestResult::class, $actual);
        foreach ($expected as $method => $value) {
            $this->assertEquals($value, $actual->$method());
        }
    }

    public function testGetTestResultNotFound(): void
    {
        $this->mockGetTestResultQuery(null);
        $manager = $this->createInstance();

        $actual = $manager->getTestResult(1000);

        $this->assertNull($actual);
    }

    /**
     * @dataProvider provideFetchedTestPassResult
     */
    public function testFailedPassed(array $query_result, array $expected): void
    {
        $this->mockUpdateTestResultCache($query_result);
        $manager = $this->createInstance();
        $manager->updateTestResultCache($query_result['active_fi']);

        $user_id = $query_result['user_id'];
        $test_obj_id = $query_result['test_obj_id'];

        $this->assertEquals($expected['isPassed'], $manager->isPassed($user_id, $test_obj_id));
        $this->assertEquals($expected['isFailed'], $manager->isFailed($user_id, $test_obj_id));
        //TODO: has finished pass
    }

    public function testFailedPassedNotFound(): void
    {
        $manager = $this->createInstance();

        $this->assertFalse($manager->isPassed(100, 200));
        $this->assertFalse($manager->isFailed(100, 200));
        $this->assertFalse($manager->hasFinished(100, 200));
    }

    /**
     * @dataProvider provideCachedStatus
     */
    public function testReadFromCache(array $query, array $cached_status, array $expected): void {
        $manager = $this->createInstance();
        $user_id = $query['user_id'];
        $test_obj_id = $query['test_obj_id'];

        // Ensure the database is not queried
        $this->adaptDICServiceMock(
            \ilDBInterface::class,
            function (\ilDBInterface|MockObject $mock) {
                $mock->expects($this->exactly(0))->method('queryF');
                $mock->expects($this->exactly(0))->method('fetchAssoc');
            }
        );

        /** @var Container $cache */
        $cache = $this->getNonPublicPropertyValue($manager, 'cache');
        $cache->set("$user_id:$test_obj_id", $cached_status);

        $this->assertEquals($expected['isPassed'], $manager->isPassed($user_id, $test_obj_id));
        $this->assertEquals($expected['isFailed'], $manager->isFailed($user_id, $test_obj_id));
        $this->assertEquals($expected['hasFinished'], $manager->hasFinished($user_id, $test_obj_id));
    }

    /**
     * @dataProvider provideFetchedTestPassResult
     */
    public function testUpdateTestResultCache(array $query_result, array $expected): void
    {
        $this->mockUpdateTestResultCache($query_result);
        $manager = $this->createInstance();

        $actual = $manager->updateTestResultCache($query_result['active_fi']);

        $this->assertNotNull($actual);
        $this->assertInstanceOf(TestResult::class, $actual);
        foreach ($expected as $method => $value) {
            $this->assertEquals($value, $actual->$method());
        }

        /** @var Container $cache */
        $cache = $this->getNonPublicPropertyValue($manager, 'cache');
        $this->assertTrue($cache->has("{$query_result['user_id']}:{$query_result['test_obj_id']}"));
    }

    /**
     * @dataProvider provideTestPassResult
     */
    public function testGetTestPassResult(array $query_result, array $expected): void
    {
        $this->mockGetTestPassResultQuery($query_result);
        $manager = $this->createInstance();

        $actual = $manager->getTestPassResults($query_result['active_fi']);

        $this->assertNotNull($actual);
        $this->assertInstanceOf(TestPassResult::class, $actual);
        foreach ($expected as $method => $value) {
            $this->assertEquals($value, $actual->$method());
        }
    }

    public function testGetTestPassResultNotFound(): void
    {
        $this->mockGetTestPassResultQuery(null);
        $manager = $this->createInstance();

        $actual = $manager->getTestPassResults(1000);

        $this->assertNull($actual);
    }

    /**
     * @dataProvider provideFetchedTestResult
     */
    public function testUpdateTestPassResult(
        array $parameters,
        array $test_result,
        array $test_config_result,
        array $working_time_result,
        array $expected
    ): void {
        $this->mockUpdateTestPassResult($test_result, $test_config_result, $working_time_result);
        $manager = $this->createInstance();

        $actual = $manager->updateTestPassResults(
            $parameters['active_id'],
            $parameters['pass'],
            null,
            $parameters['test_obj_id'],
            false
        );

        $this->assertNotNull($actual);
        $this->assertInstanceOf(TestPassResult::class, $actual);
        $this->assertEqualsWithDelta(time(), $actual->getTimestamp(), 5);
        foreach ($expected as $method => $value) {
            $this->assertEquals($value, $actual->$method());
        }
    }

    /*
        Mocking
     */

    private function createInstance(): TestResultManager
    {
        $global_cache = $this->createConfiguredMock(
            \ILIAS\Cache\Services::class,
            ['get' => $this->createCacheMock()]
        );

        return $this->createInstanceOf(
            TestResultManager::class,
            ['marks_repository' => $this->createMarksRepositoryMock(), 'global_cache' => $global_cache]
        );
    }

    private function createMarksRepositoryMock(): MarksRepository
    {
        return new class () implements MarksRepository {
            public function getMarkSchemaFor(int $test_id): MarkSchema
            {
                return (new MarkSchema($test_id))->createSimpleSchema();
            }

            public function storeMarkSchema(MarkSchema $mark_schema): void
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

            public function set(string $key, int|bool|array|string|null $value): void
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
     * @see TestResultManager::getTestResult
     */
    private function mockGetTestResultQuery(?array $fetch_assoc_return): void
    {
        $this->mockGetResultQuery('tst_result_cache', $fetch_assoc_return);
    }

    /**
     * @see TestResultManager::getTestPassResults
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
                    ->method('queryF')
                    ->with($this->equalTo("SELECT * FROM $table WHERE active_fi = %s"));

                $mock
                    ->expects($this->once())
                    ->method('fetchAssoc')
                    ->willReturn($fetch_assoc_return);
            }
        );
    }

    /**
     * @see TestResultManager::updateTestResultCache
     */
    private function mockUpdateTestResultCache(?array $test_pass_result, bool $passed_once = false): void
    {
        $fetch_assoc_mocks = [
            ['pass_scoring' => \ilObjTest::SCORE_LAST_PASS],   // \ilObjTest::_getPassScoring
            ['maxpass' => 0],                                   // \ilObjTest::_getMaxPass
            $test_pass_result,                                  // TestResultManager::fetchTestPassResult
            ['passed_once' => $passed_once ? 1 : 0]             // TestResultManager::buildTestResultObject
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

    /**
     * @see TestResultManager::updateTestPassResults
     */
    private function mockUpdateTestPassResult(?array $test_result, ?array $test_config, ?array $working_time): void
    {
        $fetch_assoc_mocks = [
            $test_result,                                                   // TestResultManager::fetchTestResult
            ['question_set_type' => \ilObjTest::QUESTION_SET_TYPE_FIXED],   // TestResultManager::fetchAdditionalTestData (1)
            $test_config,                                                   // TestResultManager::fetchAdditionalTestData (2)
            $working_time,                                                  // TestResultManager::fetchWorkingTime
            null                                                            // TestResultManager::fetchWorkingTime (i2)
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

    public static function provideCachedStatus(): array {
        return [
            [
                ['user_id' => 1, 'test_obj_id' => 100],
                ['passed' => true, 'failed' => false, 'finished' => false],
                ['isPassed' => true, 'isFailed' => false, 'hasFinished' => false],
            ],
            [
                ['user_id' => 10, 'test_obj_id' => 100],
                ['passed' => false, 'failed' => true, 'finished' => false],
                ['isPassed' => false, 'isFailed' => true, 'hasFinished' => false],
            ],
            [
                ['user_id' => 1, 'test_obj_id' => 250],
                ['passed' => false, 'failed' => true, 'finished' => true],
                ['isPassed' => false, 'isFailed' => true, 'hasFinished' => true],
            ]
        ];
    }

    /**
     * This method returns sample data for this query:
     *
     *  SELECT * FROM tst_result_cache WHERE active_fi = %s
     *
     * @see TestResultManager::getTestResult()
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
                    'hint_count' => 0,
                    'hint_points' => 0,
                    'passed_once' => 0
                ],
                [
                    'getActiveId' => 10,
                    'isPassed' => false,
                    'isPassedOnce' => false,
                    'isFailed' => true,
                    'getPass' => 0,
                    'getMaxPoints' => 25,
                    'getReachedPoints' => 0,
                    'getMarkShort' => 'failed',
                    'getMarkOfficial' => 'failed',
                    'getTimestamp' => 1740557748,
                    'getHintCount' => 0,
                    'getHintPoints' => 0,
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
     * @see TestResultManager::fetchTestPassResult
     */
    public static function provideFetchedTestPassResult(): array
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
                    'hint_count' => 0,
                    'hint_points' => 0,
                    'exam_id' => 'I0_T334_A41_P0',
                    'finalized_by' => null,
                    'last_finished_pass' => null,
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
                    'getPass' => 0,
                    'getMaxPoints' => 25,
                    'getReachedPoints' => 0,
                    'getMarkShort' => 'failed',
                    'getMarkOfficial' => 'failed',
                    'getTimestamp' => 1740557748,
                    'getHintCount' => 0,
                    'getHintPoints' => 0,
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
                    'hint_count' => 0,
                    'hint_points' => 0,
                    'exam_id' => 'I0_T334_A41_P0',
                    'finalized_by' => null,
                    'last_finished_pass' => null,
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
                    'getPass' => 0,
                    'getMaxPoints' => 25,
                    'getReachedPoints' => 25,
                    'getMarkShort' => 'passed',
                    'getMarkOfficial' => 'passed',
                    'getTimestamp' => 1740557748,
                    'getHintCount' => 0,
                    'getHintPoints' => 0,
                ]
            ]
        ];
    }

    /**
     * This method returns sample data for this query:
     *
     *  SELECT * FROM tst_result_cache WHERE active_fi = %s
     *
     * @see TestResultManager::getTestPassResults
     */
    public static function provideTestPassResult(): array
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
                    'hint_count' => 0,
                    'hint_points' => 0,
                    'exam_id' => 'I0_T334_A41_P0',
                    'finalized_by' => null,
                ],
                [
                    'getActiveId' => 10,
                    'getPass' => 0,
                    'getMaxPoints' => 25,
                    'getReachedPoints' => 0,
                    'getQuestionCount' => 3,
                    'getAnsweredQuestions' => 2,
                    'getWorkingTime' => 12,
                    'getTimestamp' => 1740557748,
                    'getHintCount' => 0,
                    'getHintPoints' => 0,
                    'getExamId' => 'I0_T334_A41_P0',
                    'getFinalizedBy' => null
                ]
            ],
        ];
    }

    /**
     * This method returns sample data for these queries:
     *
     *  SELECT pass, SUM(points) AS points, SUM(hint_count) AS hint_count, SUM(hint_points) AS hint_points,
     *  COUNT(DISTINCT(question_fi)) answeredquestions FROM tst_test_result WHERE active_fi = %s AND pass = %s
     *
     *  SELECT started, finished FROM tst_times WHERE active_fi = %s AND pass = %s ORDER BY started
     *
     * @see TestResultManager::fetchTestResult, TestResultManager::fetchAdditionalTestData,TestResultManager::fetchWorkingTime
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
                    'hint_count' => 0,
                    'hint_points' => 0,
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
                    'getPass' => 0,
                    'getMaxPoints' => 25,
                    'getReachedPoints' => 10,
                    'getQuestionCount' => 3,
                    'getAnsweredQuestions' => 2,
                    'getWorkingTime' => 12,
                    'getHintCount' => 0,
                    'getHintPoints' => 0,
                    'getExamId' => 'I_T100_A10_P0', // see \ilObjTest::buildExamId
                    'getFinalizedBy' => null
                ],
            ]
        ];
    }
}
