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

use ILIAS\Test\Scoring\Marks\MarksRepository;

class TestPassResultManager
{
    public function __construct(
        protected readonly \ilDBInterface $db,
        protected readonly MarksRepository $marks_repository
    ) {
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
            ['integer'],
            [$test_obj_id]
        );
        return $this->db->fetchAll($result);
    }

    public function readTestResultCache(int $active_id): ?TestResult
    {
        $result = $this->db->queryF(
            "SELECT * FROM tst_result_cache WHERE active_fi = %s",
            ['integer'],
            [$active_id]
        );
        return self::toTestResult($this->db->fetchAssoc($result));
    }

    public function readTestResultCacheByParticipant(int $test_id, int $user_id): ?TestResult
    {
        $result = $this->db->queryF(
            "SELECT tst_result_cache.*  FROM tst_result_cache
                    INNER JOIN tst_active ON tst_active.active_id = tst_result_cache.active_fi
                    WHERE tst_active.user_fi = %s AND tst_active.test_fi = %s",
            ['integer', 'integer'],
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
                'active_fi' => ['integer', $result->getActiveId()],
                'pass' => ['integer', $result->getPass()],
                'max_points' => ['float', $result->getMaxPoints()],
                'reached_points' => ['float', $result->getReachedPoints()],
                'mark_short' => ['text', $result->getMarkShort()],
                'mark_official' => ['text', $result->getMarkOfficial()],
                'passed_once' => ['integer', $result->isPassedOnce()],
                'passed' => ['integer', (int) $result->isPassed()],
                'failed' => ['integer', (int) $result->isFailed()],
                'tstamp' => ['integer', time()],
                'hint_count' => ['integer', $result->getHintCount()],
                'hint_points' => ['float', $result->getHintPoints()]
            ];
            $this->db->replace('tst_result_cache', ['active_fi' => $result->getActiveId()], $values);
        };

        if (is_object($process_locker)) {
            $process_locker->executeUserTestResultUpdateLockOperation($callback);
        } else {
            $callback();
        }

        return $result;
    }

    public function getTestPassResults(int $active_id): ?TestPassResult
    {
        $result = $this->db->queryF(
            "SELECT * FROM tst_pass_result WHERE active_fi = %s",
            ['integer'],
            [$active_id]
        );
        return self::toTestPassResult($this->db->fetchAssoc($result));
    }

    public function updateTestPassResults(
        int $active_id,
        int $pass,
        ?\ilAssQuestionProcessLocker $process_locker = null,
        ?int $test_obj_id = null
    ): ?TestPassResult {
        $test_result = $this->fetchTestResult($active_id, $pass);
        if (!$test_result) {
            return null;
        }

        $object = $this->buildTestPassResultObject($active_id, $test_result,$test_obj_id);
        $callback = function () use ($object, $pass) {
            $this->db->replace(
                'tst_pass_result',
                [
                    'active_fi' => ['integer', $object->getActiveId()],
                    'pass' => ['integer', $pass]
                ],
                [
                    'points' => ['float', $object->getReachedPoints()],
                    'maxpoints' => ['float', $object->getMaxPoints()],
                    'questioncount' => ['integer', $object->getQuestionCount()],
                    'answeredquestions' => ['integer', $object->getAnsweredQuestions()],
                    'workingtime' => ['integer', $object->getWorkingTime()],
                    'tstamp' => ['integer', time()],
                    'hint_count' => ['integer', $object->getHintCount()],
                    'hint_points' => ['float', $object->getHintPoints()],
                    'exam_id' => ['text', $object->getExamId()],
                ]
            );
        };

        if (is_object($process_locker)) {
            $process_locker->executeUserPassResultUpdateLockOperation($callback);
        } else {
            $callback();
        }

        $this->updateTestResultCache($active_id, $process_locker);

        return $object;
    }

    private function fetchTestPassResult(int $active_id, int $pass): ?array
    {
        return $this->db->fetchAssoc($this->db->queryF(
            "SELECT tst_pass_result.*, tst_active.last_finished_pass, 
                    tst_pass_result.maxpoints AS max_points, tst_active.test_fi AS test_id, points AS reached_points
                    FROM tst_pass_result
                    INNER JOIN tst_active ON tst_pass_result.active_fi = tst_active.active_id
                    WHERE active_fi = %s AND pass = %s",
            ['integer','integer'],
            [$active_id, $pass]
        ));
    }

    private function fetchTestResult(int $active_id, int $pass): ?array
    {
        return $this->db->fetchAssoc($this->db->queryF(
            "SELECT pass, SUM(points) AS points, SUM(hint_count) AS hint_count, 
                    SUM(hint_points) AS hint_points, COUNT(DISTINCT(question_fi)) answeredquestions
                    FROM tst_test_result
                    WHERE active_fi = %s AND pass = %s",
            ['integer','integer'],
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
        $passed_once_before = $this->db->queryF(
            "SELECT passed_once FROM tst_result_cache WHERE active_fi = %s",
            ['integer'],
            [$test_pass_result['active_fi']]
        )->fetchAssoc()['passed_once'] ?? false;
        return $object->withPassedOnce($is_passed || $passed_once_before);
    }

    private function buildTestPassResultObject(int $active_id, array $test_result, ?int $test_obj_id): TestPassResult
    {
        $test_result['active_fi'] = $active_id;
        $object = $this->toTestPassResult($test_result);
        $additional_data = $this->fetchAdditionalTestData($object->getActiveId(), $object->getPass());

        return $object->withMaxPoints($additional_data['max_points'])
            ->withQuestionCount($additional_data['question_count'])
            ->withWorkingTime($this->fetchWorkingTime($object->getActiveId(), $object->getPass()))
            ->withExamId(\ilObjTest::buildExamId($object->getActiveId(), $object->getPass(), $test_obj_id));
    }

    private function fetchWorkingTime(int $active_id, int $pass): int
    {
        $result = $this->db->queryF(
            "SELECT started, finished FROM tst_times WHERE active_fi = %s AND pass = %s ORDER BY started",
            ['integer','integer'],
            [$active_id, $pass]
        );

        $time = 0;
        while ($row = $this->db->fetchAssoc($result)) {
            $time += (strtotime($row['finished']) - strtotime($row['started']));
        }
        return $time;
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
            ['integer'],
            [$active_id]
        );
        $question_set_type = $result->numRows() > 0 ? $result->fetchAssoc()['question_set_type'] : '';

        $result = match ($question_set_type) {
            \ilObjTest::QUESTION_SET_TYPE_RANDOM => $this->db->queryF(
                "SELECT tst_test_rnd_qst.pass, COUNT(tst_test_rnd_qst.question_fi) qcount, SUM(qpl_questions.points) qsum
						FROM tst_test_rnd_qst, qpl_questions
						WHERE tst_test_rnd_qst.question_fi = qpl_questions.question_id 
						    AND tst_test_rnd_qst.active_fi = %s AND	pass = %s
						GROUP BY tst_test_rnd_qst.active_fi, tst_test_rnd_qst.pass",
                ['integer', 'integer'],
                [$active_id, $pass]
            ),
            \ilObjTest::QUESTION_SET_TYPE_FIXED => $this->db->queryF(
                "SELECT COUNT(tst_test_question.question_fi) qcount, SUM(qpl_questions.points) qsum
						FROM tst_test_question, qpl_questions, tst_active
						WHERE tst_test_question.question_fi = qpl_questions.question_id
						    AND	tst_test_question.test_fi = tst_active.test_fi AND tst_active.active_id = %s
						GROUP BY	tst_test_question.test_fi",
                ['integer'],
                [$active_id]
            ),
            default => throw new \ilTestException("not supported question set type: $question_set_type"),
        };

        $row = $this->db->fetchAssoc($result);
        return is_array($row)
            ? ['question_count' => (int) $row['qcount'], 'max_points' => (float) $row['qsum']]
            : ['question_count' => 0, 'max_points' => 0.0];
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
            (int) ($row['timestamp'] ?? -1),
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
}
