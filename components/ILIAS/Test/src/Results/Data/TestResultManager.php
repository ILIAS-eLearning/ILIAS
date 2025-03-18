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

namespace ILIAS\Test\Results\Data;

use ILIAS\Cache\Container\BaseRequest;
use ILIAS\Cache\Container\Container;
use ILIAS\Cache\Services;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Test\Scoring\Marks\MarksRepository;

class TestResultManager
{
    protected Container $cache;

    public function __construct(
        protected readonly \ilDBInterface $db,
        protected readonly Refinery $refinery,
        protected readonly MarksRepository $marks_repository,
        Services $global_cache
    ) {
        $this->cache = $global_cache->get(new BaseRequest('test_result'));
    }

    /**
     * @return array{active_id: int, user_id: int}[]
     */
    public function getPassedParticipants(int $test_obj_id): array
    {
        $result = $this->db->queryF(
            "SELECT tst_result_cache.active_fi AS active_id, tst_active.user_fi AS user_id FROM tst_result_cache 
                    INNER JOIN tst_active ON tst_active.active_id = tst_result_cache.active_fi
                    INNER JOIN tst_tests ON tst_tests.test_id = tst_active.test_fi
                    WHERE tst_tests.obj_fi = %s AND tst_result_cache.passed_once = 1",
            [\ilDBConstants::T_INTEGER],
            [$test_obj_id]
        );
        return $this->db->fetchAll($result);
    }

    public function isPassed(int $user_id, int $test_obj_id): bool
    {
        return ($status = $this->readOrQueryStatus($user_id, $test_obj_id)) !== null && $status['passed'];
    }

    public function isFailed(int $user_id, int $test_obj_id): bool
    {
        return ($status = $this->readOrQueryStatus($user_id, $test_obj_id)) !== null && $status['failed'];
    }

    public function hasFinished(int $user_id, int $test_obj_id): bool
    {
        return ($status = $this->readOrQueryStatus($user_id, $test_obj_id)) !== null && $status['finished'];
    }


    public function getTestResult(int $active_id): ?TestResult
    {
        $result = $this->db->queryF(
            "SELECT * FROM tst_result_cache WHERE active_fi = %s",
            [\ilDBConstants::T_INTEGER],
            [$active_id]
        );

        return self::toTestResult($this->db->fetchAssoc($result));
    }

    public function getTestResultByParticipant(int $user_id, int $test_id): ?TestResult
    {
        $result = $this->db->queryF(
            "SELECT tst_result_cache.*  FROM tst_result_cache
                    INNER JOIN tst_active ON tst_active.active_id = tst_result_cache.active_fi
                    WHERE tst_active.user_fi = %s AND tst_active.test_fi = %s",
            [\ilDBConstants::T_INTEGER, \ilDBConstants::T_INTEGER],
            [$user_id, $test_id]
        );
        return self::toTestResult($this->db->fetchAssoc($result));
    }

    public function updateTestResultCache(int $active_id, ?\ilAssQuestionProcessLocker $process_locker = null): ?TestResult
    {
        $pass = \ilObjTest::_getResultPass($active_id);
        $pass_result = $this->fetchTestPassResult($active_id, $pass);
        if (!$pass_result) {
            return null;
        }

        $result = $this->buildTestResultObject($pass_result);
        $callback = function () use ($result) {
            $values = [
                'active_fi' => [\ilDBConstants::T_INTEGER, $result->getActiveId()],
                'pass' => [\ilDBConstants::T_INTEGER, $result->getPass()],
                'max_points' => [\ilDBConstants::T_FLOAT, $result->getMaxPoints()],
                'reached_points' => [\ilDBConstants::T_FLOAT, $result->getReachedPoints()],
                'mark_short' => [\ilDBConstants::T_TEXT, $result->getMarkShort()],
                'mark_official' => [\ilDBConstants::T_TEXT, $result->getMarkOfficial()],
                'passed_once' => [\ilDBConstants::T_INTEGER, $result->isPassedOnce()],
                'passed' => [\ilDBConstants::T_INTEGER, (int) $result->isPassed()],
                'failed' => [\ilDBConstants::T_INTEGER, (int) $result->isFailed()],
                'tstamp' => [\ilDBConstants::T_INTEGER, time()],
                'hint_count' => [\ilDBConstants::T_INTEGER, $result->getHintCount()],
                'hint_points' => [\ilDBConstants::T_FLOAT, $result->getHintPoints()]
            ];
            $this->db->replace('tst_result_cache', ['active_fi' => $result->getActiveId()], $values);
        };

        if (is_object($process_locker)) {
            $process_locker->executeUserTestResultUpdateLockOperation($callback);
        } else {
            $callback();
        }

        $this->updateStatusCache($pass_result['user_id'], $pass_result['test_obj_id'], [
            'passed' => $result->isPassed(),
            'failed' => $result->isFailed(),
            'finished' => $pass_result['last_finished_pass'] !== null
        ]);

        return $result;
    }

    public function getTestPassResults(int $active_id): ?TestPassResult
    {
        $result = $this->db->queryF(
            "SELECT * FROM tst_pass_result WHERE active_fi = %s",
            [\ilDBConstants::T_INTEGER],
            [$active_id]
        );
        return self::toTestPassResult($this->db->fetchAssoc($result));
    }

    public function updateTestPassResults(
        int $active_id,
        int $pass,
        ?\ilAssQuestionProcessLocker $process_locker = null,
        ?int $test_obj_id = null,
        bool $update_cache = true
    ): ?TestPassResult {
        $test_result = $this->fetchTestResult($active_id, $pass);
        if (!$test_result) {
            return null;
        }

        $object = $this->buildTestPassResultObject($active_id, $test_result, $test_obj_id);
        $callback = function () use ($object, $pass) {
            $this->db->replace(
                'tst_pass_result',
                [
                    'active_fi' => [\ilDBConstants::T_INTEGER, $object->getActiveId()],
                    'pass' => [\ilDBConstants::T_INTEGER, $pass]
                ],
                [
                    'points' => [\ilDBConstants::T_FLOAT, $object->getReachedPoints()],
                    'maxpoints' => [\ilDBConstants::T_FLOAT, $object->getMaxPoints()],
                    'questioncount' => [\ilDBConstants::T_INTEGER, $object->getQuestionCount()],
                    'answeredquestions' => [\ilDBConstants::T_INTEGER, $object->getAnsweredQuestions()],
                    'workingtime' => [\ilDBConstants::T_INTEGER, $object->getWorkingTime()],
                    'tstamp' => [\ilDBConstants::T_INTEGER, time()],
                    'hint_count' => [\ilDBConstants::T_INTEGER, $object->getHintCount()],
                    'hint_points' => [\ilDBConstants::T_FLOAT, $object->getHintPoints()],
                    'exam_id' => [\ilDBConstants::T_TEXT, $object->getExamId()],
                ]
            );
        };

        if (is_object($process_locker)) {
            $process_locker->executeUserPassResultUpdateLockOperation($callback);
        } else {
            $callback();
        }

        if($update_cache) {
            $this->updateTestResultCache($active_id, $process_locker);
        }

        return $object;
    }

    private function fetchTestPassResult(int $active_id, int $pass): ?array
    {
        return $this->db->fetchAssoc($this->db->queryF(
            "SELECT tst_pass_result.*, tst_active.last_finished_pass, tst_active.user_fi AS user_id,  tst_tests.test_id, 
                    tst_tests.obj_fi AS test_obj_id, tst_pass_result.maxpoints AS max_points, points AS reached_points
                    FROM tst_pass_result
                    INNER JOIN tst_active ON tst_pass_result.active_fi = tst_active.active_id
                    INNER JOIN tst_tests ON tst_tests.test_id = tst_active.test_fi
                    WHERE active_fi = %s AND pass = %s",
            [\ilDBConstants::T_INTEGER,\ilDBConstants::T_INTEGER],
            [$active_id, $pass]
        ));
    }

    private function buildTestResultObject(array $test_pass_result): TestResult
    {
        $object = $this->toTestResult($test_pass_result);
        $mark = $this->marks_repository->getMarkSchemaFor($test_pass_result['test_id'])
            ->getMatchingMark($object->getPercentage());
        $object = $object->withMark($mark);

        $is_passed = $object->getPass() <= $test_pass_result['last_finished_pass'] && $mark->getPassed();
        $passed_once_before = $this->db->fetchAssoc(
            $this->db->queryF(
                "SELECT passed_once FROM tst_result_cache WHERE active_fi = %s",
                [\ilDBConstants::T_INTEGER],
                [$test_pass_result['active_fi']]
            )
        )['passed_once'] ?? false;
        return $object->withPassedOnce($is_passed || $passed_once_before);
    }

    private function fetchTestResult(int $active_id, int $pass): ?array
    {
        return $this->db->fetchAssoc($this->db->queryF(
            "SELECT pass, SUM(points) AS points, SUM(hint_count) AS hint_count, 
                    SUM(hint_points) AS hint_points, COUNT(DISTINCT(question_fi)) answeredquestions
                    FROM tst_test_result
                    WHERE active_fi = %s AND pass = %s",
            [\ilDBConstants::T_INTEGER,\ilDBConstants::T_INTEGER],
            [$active_id, $pass]
        ));
    }

    private function buildTestPassResultObject(int $active_id, array $test_result, ?int $test_obj_id): TestPassResult
    {
        $test_result['active_fi'] = $active_id;
        $object = $this->toTestPassResult($test_result);
        $additional_data = $this->fetchAdditionalTestData($object->getActiveId(), $object->getPass());

        return $object->withMaxPoints($additional_data['max_points'])
            ->withQuestionCount($additional_data['question_count'])
            ->withWorkingTime($this->fetchWorkingTime($object->getActiveId(), $object->getPass()))
            ->withExamId(\ilObjTest::buildExamId($object->getActiveId(), $object->getPass(), $test_obj_id))
            ->withTimestamp();
    }

    /**
     * @return array{max_points: float, question_count: int}
     */
    private function fetchAdditionalTestData(int $active_id, int $pass): array
    {
        $result = $this->db->queryF(
            "SELECT tst_tests.question_set_type FROM tst_active
                    INNER JOIN tst_tests ON tst_active.test_fi = tst_tests.test_id
                    WHERE tst_active.active_id = %s",
            [\ilDBConstants::T_INTEGER],
            [$active_id]
        );
        $question_set_type = $result->numRows() > 0 ? $this->db->fetchAssoc($result)['question_set_type'] : '';

        $result = match ($question_set_type) {
            \ilObjTest::QUESTION_SET_TYPE_RANDOM => $this->db->queryF(
                "SELECT tst_test_rnd_qst.pass, COUNT(tst_test_rnd_qst.question_fi) qcount, SUM(qpl_questions.points) qsum
						FROM tst_test_rnd_qst, qpl_questions
						WHERE tst_test_rnd_qst.question_fi = qpl_questions.question_id 
						    AND tst_test_rnd_qst.active_fi = %s AND	pass = %s
						GROUP BY tst_test_rnd_qst.active_fi, tst_test_rnd_qst.pass",
                [\ilDBConstants::T_INTEGER, \ilDBConstants::T_INTEGER],
                [$active_id, $pass]
            ),
            \ilObjTest::QUESTION_SET_TYPE_FIXED => $this->db->queryF(
                "SELECT COUNT(tst_test_question.question_fi) qcount, SUM(qpl_questions.points) qsum
						FROM tst_test_question, qpl_questions, tst_active
						WHERE tst_test_question.question_fi = qpl_questions.question_id
						    AND	tst_test_question.test_fi = tst_active.test_fi AND tst_active.active_id = %s
						GROUP BY	tst_test_question.test_fi",
                [\ilDBConstants::T_INTEGER],
                [$active_id]
            ),
            default => throw new \ilTestException("not supported question set type: $question_set_type"),
        };

        $row = $this->db->fetchAssoc($result);
        return is_array($row)
            ? ['question_count' => (int) $row['qcount'], 'max_points' => (float) $row['qsum']]
            : ['question_count' => 0, 'max_points' => 0.0];
    }

    public function fetchWorkingTime(int $active_id, int $pass): int
    {
        $result = $this->db->queryF(
            "SELECT started, finished FROM tst_times WHERE active_fi = %s AND pass = %s ORDER BY started",
            [\ilDBConstants::T_INTEGER, \ilDBConstants::T_INTEGER],
            [$active_id, $pass]
        );

        $time = 0;
        while ($row = $this->db->fetchAssoc($result)) {
            $time += (strtotime($row['finished']) - strtotime($row['started']));
        }
        return $time;
    }


    private function toTestResult(?array $row): ?TestResult
    {
        if ($row === null) {
            return null;
        }

        return new TestResult(
            $row['active_fi'],
            (int) $row['pass'],
            (float) ($row['max_points'] ?? 0.0),
            (float) ($row['reached_points'] ?? 0.0),
            $row['mark_short'] ?? '',
            $row['mark_official'] ?? '',
            (bool) ($row['passed'] ?? false),
            (bool) ($row['failed'] ?? true),
            (int) ($row['tstamp'] ?? -1),
            (int) ($row['hint_count'] ?? 0),
            (float) ($row['hint_points'] ?? 0.0),
            (bool) ($row['passed_once'] ?? false),
        );
    }

    private function toTestPassResult(?array $row): ?TestPassResult
    {
        if ($row === null) {
            return null;
        }

        return new TestPassResult(
            $row['active_fi'],
            (int) $row['pass'],
            (float) ($row['maxpoints'] ?? 0.0),
            (float) ($row['points'] ?? 0.0),
            (int) ($row['questioncount'] ?? 0),
            (int) ($row['answeredquestions'] ?? 0),
            (int) ($row['workingtime'] ?? 0),
            (int) ($row['tstamp'] ?? -1),
            (int) ($row['hint_count'] ?? 0),
            (float) ($row['hint_points'] ?? 0.0),
            $row['exam_id'] ?? '',
            $row['finalized_by'] ?? '',
        );
    }


    public function invalidateStatusCache(array $active_ids, int $test_obj_id): void
    {
        $condition = $this->db->in('active_id', $active_ids, false, \ilDBConstants::T_INTEGER);
        $result = $this->db->fetchAll($this->db->query("SELECT user_fi FROM tst_active WHERE $condition"));

        foreach ($result as $row) {
            $this->cache->delete($row['user_fi'] . ":" . $test_obj_id);
        }
    }

    /**
     * @param array{'passed': bool, 'failed': bool, 'finished': bool} $status
     */
    private function updateStatusCache(int $user_id, int $test_obj_id, array $status): void
    {
        $this->cache->set("$user_id:$test_obj_id", $status);
    }

    /**
     * @return array{'passed': bool, 'failed': bool, 'finished': bool}|null
     */
    private function readOrQueryStatus(int $user_id, int $test_obj_id): ?array
    {
        $cached_status = $this->cache->get("$user_id:$test_obj_id", $this->refinery->identity());
        if ($cached_status !== null) {
            return $cached_status;
        }

        $status = $this->db->fetchAssoc($this->db->queryF(
            "SELECT tst_result_cache.passed, tst_result_cache.failed, (tst_active.last_finished_pass IS NOT NULL) AS finished  
                    FROM tst_result_cache 
                    INNER JOIN tst_active ON tst_active.active_id = tst_result_cache.active_fi
                    INNER JOIN tst_tests ON tst_tests.test_id = tst_active.test_fi
                    WHERE tst_active.user_fi = %s AND tst_tests.obj_fi = %s",
            [\ilDBConstants::T_INTEGER, \ilDBConstants::T_INTEGER],
            [$user_id, $test_obj_id]
        ));
        if ($status === null) {
            return null;
        }

        $this->updateStatusCache($user_id, $test_obj_id, $status);
        return $status;
    }
}
