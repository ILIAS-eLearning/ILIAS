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

class Repository
{
    private Container $cache;

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly Refinery $refinery,
        private readonly MarksRepository $marks_repository,
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
            'SELECT tst_result_cache.active_fi AS active_id, tst_active.user_fi AS user_id FROM tst_result_cache' . PHP_EOL
            . 'INNER JOIN tst_active ON tst_active.active_id = tst_result_cache.active_fi' . PHP_EOL
            . 'INNER JOIN tst_tests ON tst_tests.test_id = tst_active.test_fi' . PHP_EOL
            . 'WHERE tst_tests.obj_fi = %s AND tst_result_cache.passed_once = 1' . PHP_EOL,
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

    public function reachedPercentage(
        int $a_usr_id,
        int $a_trigger_obj_id,
        float $min_threshold,
        float $max_threshold
    ): bool {
        return ($status = $this->readOrQueryStatus($a_usr_id, $a_trigger_obj_id)) !== null
            && $status['percentage'] >= $min_threshold
            && $status['percentage'] <= $max_threshold;
    }

    public function getTestResult(int $active_id): ?ParticipantResult
    {
        $result = $this->db->queryF(
            'SELECT tst_result_cache.*, tst_active.test_fi AS test_id FROM tst_result_cache' . PHP_EOL
            . 'JOIN tst_active ON tst_result_cache.active_fi = tst_active.active_id' . PHP_EOL
            . 'WHERE active_fi = %s',
            [\ilDBConstants::T_INTEGER],
            [$active_id]
        );

        return $this->toParticipantResult($this->db->fetchAssoc($result));
    }

    public function updateTestResultCache(int $active_id, ?\ilAssQuestionProcessLocker $process_locker = null): ?ParticipantResult
    {
        $attempt = $this->lookupAttempt($active_id);
        $attempt_result = $this->fetchTestAttemptResult($active_id, $attempt);
        if (!$attempt_result) {
            return null;
        }

        // Prevent unfinished passes from being entered in the table so that no inconsistencies occur during an attempt
        $status = StatusOfAttempt::build(
            $attempt,
            $attempt_result['last_finished_pass'],
            $attempt_result['finalized_by'],
        );

        $result = $this->buildTestResultObject($attempt_result);
        $callback = function () use ($result) {
            $values = [
                'active_fi' => [\ilDBConstants::T_INTEGER, $result->getActiveId()],
                'pass' => [\ilDBConstants::T_INTEGER, $result->getAttempt()],
                'max_points' => [\ilDBConstants::T_FLOAT, $result->getMaxPoints()],
                'reached_points' => [\ilDBConstants::T_FLOAT, $result->getReachedPoints()],
                'mark_short' => [\ilDBConstants::T_TEXT, $result->getMarkShort()],
                'mark_official' => [\ilDBConstants::T_TEXT, $result->getMarkOfficial()],
                'passed_once' => [\ilDBConstants::T_INTEGER, $result->isPassedOnce()],
                'passed' => [\ilDBConstants::T_INTEGER, (int) $result->isPassed()],
                'failed' => [\ilDBConstants::T_INTEGER, (int) $result->isFailed()],
                'tstamp' => [\ilDBConstants::T_INTEGER, time()],
            ];
            $this->db->replace(
                'tst_result_cache',
                ['active_fi' => $result->getActiveId()],
                $values
            );
        };

        if (is_object($process_locker)) {
            $process_locker->executeUserTestResultUpdateLockOperation($callback);
        } else {
            $callback();
        }

        $this->updateStatusCache(
            $attempt_result['user_id'],
            $attempt_result['test_obj_id'],
            [
                'passed' => $result->isPassed(),
                'failed' => $result->isFailed(),
                'finished' => $status->isFinished(),
                'percentage' => $result->getPercentage(),
            ]
        );

        return $result;
    }

    public function getTestAttemptResult(int $active_id): ?AttemptResult
    {
        $result = $this->db->queryF(
            "SELECT * FROM tst_pass_result WHERE active_fi = %s",
            [\ilDBConstants::T_INTEGER],
            [$active_id]
        );
        return $this->toTestAttemptResult($this->db->fetchAssoc($result));
    }

    public function updateTestAttemptResult(
        int $active_id,
        int $attempt,
        ?\ilAssQuestionProcessLocker $process_locker = null,
        ?int $test_obj_id = null,
        bool $update_result_cache_table = true
    ): ?AttemptResult {
        $test_result = $this->fetchTestResult($active_id, $attempt);
        if (!$test_result) {
            return null;
        }

        $result_object = $this->buildTestAttemptResultObject(
            $active_id,
            $test_result,
            $test_obj_id
        );

        $callback = function () use ($result_object, $attempt) {
            $this->db->replace(
                'tst_pass_result',
                [
                    'active_fi' => [\ilDBConstants::T_INTEGER, $result_object->getActiveId()],
                    'pass' => [\ilDBConstants::T_INTEGER, $attempt]
                ],
                [
                    'points' => [\ilDBConstants::T_FLOAT, $result_object->getReachedPoints()],
                    'maxpoints' => [\ilDBConstants::T_FLOAT, $result_object->getMaxPoints()],
                    'questioncount' => [\ilDBConstants::T_INTEGER, $result_object->getQuestionCount()],
                    'answeredquestions' => [\ilDBConstants::T_INTEGER, $result_object->getAnsweredQuestions()],
                    'workingtime' => [\ilDBConstants::T_INTEGER, $result_object->getWorkingTime()],
                    'tstamp' => [\ilDBConstants::T_INTEGER, time()],
                    'exam_id' => [\ilDBConstants::T_TEXT, $result_object->getExamId()],
                    'finalized_by' => [\ilDBConstants::T_TEXT, $result_object->getFinalizedBy()]
                ]
            );
        };

        if (is_object($process_locker)) {
            $process_locker->executeUserPassResultUpdateLockOperation($callback);
        } else {
            $callback();
        }

        if ($update_result_cache_table) {
            $this->updateTestResultCache($active_id, $process_locker);
        }

        return $result_object;
    }

    public function finalizeTestAttemptResult(int $active_id, int $attempt, StatusOfAttempt $status_of_attempt): void
    {
        if (!$status_of_attempt->isFinished()) {
            throw new \RuntimeException('Status of attempt must be finished to finalize test attempt result');
        }

        $this->db->manipulateF(
            'UPDATE tst_pass_result SET tstamp = %s, finalized_by = %s WHERE active_fi = %s AND pass = %s',
            ['integer', 'text', 'integer', 'integer'],
            [time(), $status_of_attempt->value, $active_id, $attempt]
        );
    }

    private function fetchTestAttemptResult(int $active_id, int $attempt): ?array
    {
        return $this->db->fetchAssoc($this->db->queryF(
            "SELECT tst_pass_result.*, tst_active.last_finished_pass, tst_active.user_fi AS user_id, tst_tests.test_id,
                    tst_tests.obj_fi AS test_obj_id, tst_pass_result.maxpoints AS max_points, points AS reached_points,
                    tst_result_cache.passed_once AS passed_once_before
                    FROM tst_pass_result
                    INNER JOIN tst_active ON tst_pass_result.active_fi = tst_active.active_id
                    INNER JOIN tst_tests ON tst_tests.test_id = tst_active.test_fi
                    LEFT JOIN tst_result_cache ON tst_result_cache.active_fi = tst_active.active_id
                    WHERE tst_pass_result.active_fi = %s AND tst_pass_result.pass = %s",
            [\ilDBConstants::T_INTEGER,\ilDBConstants::T_INTEGER],
            [$active_id, $attempt]
        ));
    }

    private function buildTestResultObject(array $test_attempt_result_array): ParticipantResult
    {
        $test_attempt_result = $this->toParticipantResult($test_attempt_result_array);

        $is_passed = $test_attempt_result->getAttempt() <= $test_attempt_result_array['last_finished_pass'] && $test_attempt_result->isPassed();
        $passed_once_before = (bool) ($test_attempt_result_array['passed_once_before'] ?? false);
        return $test_attempt_result->withPassedOnce($is_passed || $passed_once_before);
    }

    private function fetchTestResult(int $active_id, int $attempt): ?array
    {
        return $this->db->fetchAssoc($this->db->queryF(
            'SELECT r.pass,' . PHP_EOL
            . 'SUM(r.points) AS points,' . PHP_EOL
            . 'COUNT(DISTINCT(r.question_fi)) answeredquestions,' . PHP_EOL
            . 'pr.exam_id,' . PHP_EOL
            . 'pr.finalized_by' . PHP_EOL
            . 'FROM tst_test_result r' . PHP_EOL
            . 'INNER JOIN  tst_pass_result pr' . PHP_EOL
            . 'ON r.active_fi = pr.active_fi AND r.pass = pr.pass' . PHP_EOL
            . 'WHERE r.active_fi = %s AND r.pass = %s',
            [\ilDBConstants::T_INTEGER,\ilDBConstants::T_INTEGER],
            [$active_id, $attempt]
        ));
    }

    private function buildTestAttemptResultObject(int $active_id, array $test_result, ?int $test_obj_id): AttemptResult
    {
        $test_result['active_fi'] = $active_id;
        $test_attempt_result = $this->toTestAttemptResult($test_result);
        $additional_data = $this->fetchAdditionalTestData($test_attempt_result->getActiveId(), $test_attempt_result->getAttempt());

        return $test_attempt_result->withMaxPoints($additional_data['max_points'])
            ->withQuestionCount($additional_data['question_count'])
            ->withWorkingTime(
                $this->fetchWorkingTime($test_attempt_result->getActiveId(), $test_attempt_result->getAttempt())
            )
            ->withExamId(
                \ilObjTest::buildExamId(
                    $test_attempt_result->getActiveId(),
                    $test_attempt_result->getAttempt(),
                    $test_obj_id
                )
            )
            ->withTimestamp();
    }

    /**
     * @return array{max_points: float, question_count: int}
     */
    private function fetchAdditionalTestData(int $active_id, int $attempt): array
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
                [$active_id, $attempt]
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
            default => throw new \ilTestException('not supported question set type: ' . $question_set_type),
        };

        $row = $this->db->fetchAssoc($result);
        return is_array($row)
            ? ['question_count' => (int) $row['qcount'], 'max_points' => (float) $row['qsum']]
            : ['question_count' => 0, 'max_points' => 0.0];
    }

    public function fetchWorkingTime(int $active_id, int $attempt): int
    {
        $result = $this->db->queryF(
            "SELECT started, finished FROM tst_times WHERE active_fi = %s AND pass = %s ORDER BY started",
            [\ilDBConstants::T_INTEGER, \ilDBConstants::T_INTEGER],
            [$active_id, $attempt]
        );

        $time = 0;
        while ($row = $this->db->fetchAssoc($result)) {
            $time += (strtotime($row['finished']) - strtotime($row['started']));
        }
        return $time;
    }

    public function removeTestResults(array $active_ids, int $test_obj_id): void
    {
        $condition = $this->db->in('active_fi', $active_ids, false, \ilDBConstants::T_INTEGER);

        $this->db->manipulate("DELETE FROM tst_test_result WHERE {$condition}");
        $this->db->manipulate("DELETE FROM tst_pass_result WHERE {$condition}");

        $user_ids = $this->db->fetchAll(
            $this->db->query(
                'SELECT user_fi FROM tst_active WHERE' . PHP_EOL
                . $this->db->in('active_id', $active_ids, false, \ilDBConstants::T_INTEGER)
            )
        );
        foreach ($user_ids as $row) {
            $this->cache->delete($row['user_fi'] . ':' . $test_obj_id);
        }
    }

    protected function lookupAttempt(int $active_id): ?int
    {
        return \ilObjTest::_getResultPass($active_id);
    }


    private function toParticipantResult(?array $row): ?ParticipantResult
    {
        if ($row === null) {
            return null;
        }

        $mark = $this->marks_repository
            ->getMarkSchemaFor($row['test_id'])
            ->getMatchingMark($this->calculatePercentage($row) * 100);

        return new ParticipantResult(
            $row['active_fi'],
            (int) $row['pass'],
            $this->ensurePositive($row['max_points'] ?? 0.0),
            $this->ensurePositive($row['reached_points'] ?? 0.0),
            $mark,
            (int) ($row['tstamp'] ?? -1),
            (bool) ($row['passed_once'] ?? false),
        );
    }

    private function toTestAttemptResult(?array $row): ?AttemptResult
    {
        if ($row === null) {
            return null;
        }

        return new AttemptResult(
            $row['active_fi'],
            (int) $row['pass'],
            $this->ensurePositive($row['maxpoints'] ?? 0.0),
            $this->ensurePositive($row['points'] ?? 0.0),
            (int) ($row['questioncount'] ?? 0),
            (int) ($row['answeredquestions'] ?? 0),
            (int) ($row['workingtime'] ?? 0),
            (int) ($row['tstamp'] ?? -1),
            $row['exam_id'] ?? '',
            $row['finalized_by'] ?? '',
        );
    }

    private function ensurePositive(mixed $value): float
    {
        return max(0.0, (float) $value);
    }

    /**
     * @param array{'passed': bool, 'failed': bool, 'finished': bool, 'percentage': float} $status
     */
    private function updateStatusCache(int $user_id, int $test_obj_id, array $status): void
    {
        $this->cache->set($user_id . ':' . $test_obj_id, $status);
    }

    /**
     * @return array{'passed': bool, 'failed': bool, 'finished': bool, 'percentage': float}|null
     */
    private function readOrQueryStatus(int $user_id, int $test_obj_id): ?array
    {
        $cached_status = $this->cache->get($user_id . ':' . $test_obj_id, $this->refinery->identity());
        if ($cached_status !== null) {
            return $cached_status;
        }

        $status = $this->db->fetchAssoc($this->db->queryF(
            "SELECT tst_result_cache.passed, tst_result_cache.failed, (tst_active.last_finished_pass IS NOT NULL) AS finished,
                    tst_result_cache.reached_points, tst_result_cache.max_points
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

        $status['percentage'] = $this->calculatePercentage($status);
        unset($status['reached_points'], $status['max_points']);

        $this->updateStatusCache($user_id, $test_obj_id, $status);
        return $status;
    }

    /**
     * @param array{max_points: float, reached_points: float} $row
     *
     * @return float
     */
    private function calculatePercentage(array $row): float
    {
        $max_points = $this->ensurePositive($row['max_points'] ?? 0.0);
        $reached_points = $this->ensurePositive($row['reached_points'] ?? 0.0);

        return $max_points > 0 ? $reached_points / $max_points : 0.0;
    }
}
