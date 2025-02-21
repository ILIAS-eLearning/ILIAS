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

use ILIAS\Test\Results\Data\StatusOfAttempt;

/**
 * @deprecated 11; Result/EvaluationData will be refined.
 */
class ilTestEvaluationFactory
{
    public function __construct(
        protected ilDBInterface $db,
        protected ilObjTest $test_obj
    ) {
    }

    /**
     * @return list<int>
     */
    private function getAccessFilteredActiveIds(): array
    {
        if (($participants_list = $this->test_obj->getAccessFilteredParticipantList()) !== null) {
            return $participants_list->getAllActiveIds();
        }
        return array_keys($this->test_obj->getTestParticipants());
    }

    /**
     * @param list<int> $active_ids
     */
    private function retrieveEvaluationData(array $active_ids): \Generator
    {
        $query = '
        SELECT      tst_test_result.question_fi,
                    tst_test_result.points result_points,
                    tst_test_result.answered,
                    tst_test_result.manual,

                    qpl_questions.original_id,
                    qpl_questions.title questiontitle,
                    qpl_questions.points qpl_maxpoints,

                    tst_active.active_id,
                    tst_active.submitted,
                    tst_active.last_finished_pass,
                    tst_pass_result.*,

                    usr_data.usr_id,
                    usr_data.firstname,
                    usr_data.lastname,
                    usr_data.title,
                    usr_data.login

        FROM        tst_active

        LEFT JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
        LEFT JOIN tst_test_result ON tst_active.active_id = tst_test_result.active_fi AND tst_test_result.pass = tst_pass_result.pass
        LEFT JOIN qpl_questions ON qpl_questions.question_id = tst_test_result.question_fi
        LEFT JOIN usr_data ON tst_active.user_fi = usr_data.usr_id

        WHERE       tst_active.test_fi = %s
        AND         %s

        ORDER BY    tst_active.active_id ASC, tst_pass_result.pass ASC, tst_test_result.tstamp DESC
        ';

        $ret = [];
        $result = $this->db->query(
            sprintf(
                $query,
                $this->db->quote($this->test_obj->getTestId(), 'integer'),
                $this->db->in('tst_active.active_id', $active_ids, false, 'integer'),
            )
        );
        while ($row = $this->db->fetchAssoc($result)) {
            yield $row;
        }
    }

    public function getEvaluationData(): ilTestEvaluationData
    {
        $eval_data_rows = $this->retrieveEvaluationData($this->getAccessFilteredActiveIds());
        $scoring_settings = $this->test_obj->getPassScoring();
        $participants = [];
        $current_user = null;
        $current_attempt = null;

        foreach ($eval_data_rows as $row) {
            if ($current_user !== $row['active_id']) {
                $current_user = $row['active_id'];
                $current_attempt = null;

                $user_eval_data = new ilTestEvaluationUserData($scoring_settings);

                $user_eval_data->setName(
                    $this->test_obj->buildName($row['usr_id'], $row['firstname'], $row['lastname'])
                );

                if ($row['login'] !== null) {
                    $user_eval_data->setLogin($row['login']);
                }
                if ($row['usr_id'] !== null) {
                    $user_eval_data->setUserID($row['usr_id']);
                }
                $user_eval_data->setSubmitted((bool) $row['submitted']);
                $user_eval_data->setLastFinishedPass($row['last_finished_pass']);


                $visiting_time = $this->test_obj->getVisitingTimeOfParticipant($row['active_id']);
                $user_eval_data->setFirstVisit($visiting_time["first_access"]);
                $user_eval_data->setLastVisit($visiting_time["last_access"]);
            }

            if ($row['pass'] !== null && $current_attempt !== $row['pass']) {
                $current_attempt = $row['pass'];
                $attempt = new \ilTestEvaluationPassData();
                $attempt->setPass($row['pass']);
                $attempt->setReachedPoints($row['points']);

                if ($row['questioncount'] == 0) {
                    list($count, $points) = array_values(
                        $this->test_obj->getQuestionCountAndPointsForPassOfParticipant($row['active_id'], $row['pass'])
                    );
                    $attempt->setMaxPoints($points);
                    $attempt->setQuestionCount($count);
                } else {
                    $attempt->setMaxPoints($row['maxpoints']);
                    $attempt->setQuestionCount($row['questioncount']);
                }

                $attempt->setNrOfAnsweredQuestions($row['answeredquestions']);
                $attempt->setWorkingTime($row['workingtime']);
                $start_time = $this->getFirstVisitForActiveIdAndAttempt($row['active_id'], $row['pass']);
                if ($start_time !== null) {
                    $attempt->setStartTime($start_time);
                }
                $attempt->setExamId((string) $row['exam_id']);
                $attempt->setRequestedHintsCount($row['hint_count']);
                $attempt->setDeductedHintPoints($row['hint_points']);
                $attempt->setStatusOfAttempt(
                    $this->buildFinalizedBy($current_attempt, $row['last_finished_pass'], $row['finalized_by'])
                );
            }

            if ($row['question_fi'] !== null) {
                $attempt->addAnsweredQuestion(
                    $row["question_fi"],
                    $row["qpl_maxpoints"],
                    $row["result_points"],
                    (bool) $row['answered'],
                    null,
                    $row['manual']
                );
            }

            if (isset($attempt)) {
                $user_eval_data->addPass($row['pass'], $attempt);
            }

            $participants[$row['active_id']] = $user_eval_data;
        }

        $evaluation_data = $this->addQuestionsToParticipantPasses(new ilTestEvaluationData($participants));
        return $this->addMarksToParticipants($evaluation_data);
    }


    private function addQuestionsToParticipantPasses(ilTestEvaluationData $evaluation_data): ilTestEvaluationData
    {
        foreach ($evaluation_data->getParticipantIds() as $active_id) {
            $user_eval_data = $evaluation_data->getParticipant($active_id);
            $add_user_questions = $this->test_obj->isRandomTest() ?
                $this->retrieveQuestionsForParticipantPassesForRandomTests(
                    $active_id,
                    $user_eval_data,
                    $this->test_obj->getQuestionCountWithoutReloading()
                ) :
                $this->retrieveQuestionsForParticipantPassesForSequencedTests($active_id);

            foreach ($add_user_questions as $q) {
                $user_eval_data->addQuestion(
                    $q['original_id'],
                    $q['question_id'],
                    $q['max_points'],
                    $q['sequence'],
                    $q['pass']
                );
                $evaluation_data->addQuestionTitle($q['question_id'], $q['title']);
            }
        }
        return $evaluation_data;
    }

    private function retrieveQuestionsForParticipantPassesForRandomTests(
        int $active_id,
        ilTestEvaluationUserData $user_eval_data,
        int $question_count
    ): array {
        $ret = [];
        for ($testpass = 0; $testpass <= $user_eval_data->getLastPass(); $testpass++) {
            $this->db->setLimit($question_count, 0);
            $query = '
                SELECT tst_test_rnd_qst.sequence, tst_test_rnd_qst.question_fi, qpl_questions.original_id,
                tst_test_rnd_qst.pass, qpl_questions.points, qpl_questions.title
                FROM tst_test_rnd_qst, qpl_questions
                WHERE tst_test_rnd_qst.question_fi = qpl_questions.question_id
                AND tst_test_rnd_qst.pass = %s
                AND tst_test_rnd_qst.active_fi = %s ORDER BY tst_test_rnd_qst.sequence
            ';

            $result = $this->db->queryF(
                $query,
                ['integer','integer'],
                [$testpass, $active_id]
            );

            if ($result->numRows()) {
                while ($row = $this->db->fetchAssoc($result)) {
                    $tpass = array_key_exists('pass', $row) ? $row['pass'] : 0;

                    if (
                        !isset($row['question_fi'], $row['points'], $row['sequence']) ||
                        !is_numeric($row['question_fi']) || !is_numeric($row['points']) || !is_numeric($row['sequence'])
                    ) {
                        continue;
                    }

                    $ret[] = [
                        'original_id' => (int) $row['original_id'],
                        'question_id' => (int) $row['question_fi'],
                        'max_points' => (float) $row['points'],
                        'sequence' => (int) $row['sequence'],
                        'pass' => $tpass,
                        'title' => $row['title']
                    ];
                }
            }
        }
        return $ret;
    }

    private function retrieveQuestionsForParticipantPassesForSequencedTests(
        int $active_id
    ): array {
        $ret = [];

        $query = '
            SELECT tst_test_question.sequence, tst_test_question.question_fi,
            qpl_questions.points, qpl_questions.title, qpl_questions.original_id
            FROM tst_test_question, tst_active, qpl_questions
            WHERE tst_test_question.question_fi = qpl_questions.question_id
            AND tst_active.active_id = %s
            AND tst_active.test_fi = tst_test_question.test_fi
            ORDER BY tst_test_question.sequence
        ';

        $result = $this->db->queryF(
            $query,
            ['integer'],
            [$active_id]
        );

        if ($result->numRows()) {
            $questionsbysequence = [];
            while ($row = $this->db->fetchAssoc($result)) {
                $questionsbysequence[$row['sequence']] = $row;
            }

            $seqresult = $this->db->queryF(
                'SELECT * FROM tst_sequence WHERE active_fi = %s',
                ['integer'],
                [$active_id]
            );

            while ($seqrow = $this->db->fetchAssoc($seqresult)) {
                $questionsequence = unserialize($seqrow["sequence"]);
                foreach ($questionsequence as $sidx => $seq) {
                    if (!isset($questionsbysequence[$seq])) {
                        continue;
                    }
                    $ret[] = [
                        'original_id' => $questionsbysequence[$seq]['original_id'] ?? 0,
                        'question_id' => $questionsbysequence[$seq]['question_fi'],
                        'max_points' => $questionsbysequence[$seq]['points'],
                        'sequence' => $sidx + 1,
                        'pass' => $seqrow['pass'],
                        'title' => $questionsbysequence[$seq]["title"]
                    ];
                }
            }
        }
        return $ret;
    }

    private function addMarksToParticipants(ilTestEvaluationData $evaluation_data): ilTestEvaluationData
    {
        $mark_schema = $this->test_obj->getMarkSchema();

        foreach ($evaluation_data->getParticipantIds() as $active_id) {
            $user_eval_data = $evaluation_data->getParticipant($active_id);

            $mark = $mark_schema->getMatchingMark(
                $user_eval_data->getReachedPointsInPercent()
            );

            if ($mark === null) {
                continue;
            }

            $user_eval_data->setMark($mark);
            for ($i = 0; $i < $user_eval_data->getPassCount(); $i++) {
                $pass_data = $user_eval_data->getPass($i);
                if ($pass_data === null) {
                    continue;
                }
                $mark = $mark_schema->getMatchingMark(
                    $pass_data->getReachedPointsInPercent()
                );
                if ($mark !== null) {
                    $pass_data->setMark($mark);
                }
            }
        }

        return $evaluation_data;
    }

    public function getAllActivesPasses(): array
    {
        $query = '
            SELECT active_fi, pass
            FROM tst_active actives
            INNER JOIN tst_pass_result passes
            ON active_fi = active_id
            WHERE test_fi = %s
        ';

        $res = $this->db->queryF($query, ['integer'], [$this->test_obj->getTestId()]);

        $passes = [];
        while ($row = $this->db->fetchAssoc($res)) {
            if (!isset($passes[$row['active_fi']])) {
                $passes[$row['active_fi']] = [];
            }

            $passes[$row['active_fi']][] = $row['pass'];
        }

        return $passes;
    }

    public function getFirstVisitForActiveIdAndAttempt(int $active_id, int $attempt): ?string
    {
        $times = $this->db->fetchAssoc(
            $this->db->queryF(
                'SELECT MIN(started) AS first_access '
                    . 'FROM tst_times WHERE active_fi = %s AND pass = %s',
                ['integer', 'integer'],
                [$active_id, $attempt]
            )
        );

        return $times['first_access'];
    }

    private function buildFinalizedBy(int $current_attempt, ?int $last_finished_attempt, ?string $finalized_by): StatusOfAttempt
    {
        if ($last_finished_attempt === null || $current_attempt > $last_finished_attempt) {
            return StatusOfAttempt::RUNNING;
        }

        if ($finalized_by === null) {
            return StatusOfAttempt::FINISHED_BY_UNKNOWN;
        }

        return StatusOfAttempt::tryFrom($finalized_by);
    }
}
