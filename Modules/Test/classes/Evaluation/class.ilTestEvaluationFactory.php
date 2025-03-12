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

class ilTestEvaluationFactory
{
    public function __construct(
        protected ilDBInterface $db,
        protected ilObjTest $test_obj
    ) {
    }

    protected function getPassScoringSettings(): int
    {
        return $this->test_obj->getPassScoring();
    }

    protected function isRandomTest(): bool
    {
        return $this->test_obj->isRandomTest();
    }

    protected function getTestQuestionCount(): int
    {
        return $this->test_obj->getQuestionCountWithoutReloading();
    }

    protected function getTesttMarkSchema(): ASS_MarkSchema
    {
        return $this->test_obj->getMarkSchema();
    }

    protected function getVisitTimeOfParticipant(int $active_id): array
    {
        return $this->test_obj->getVisitTimeOfParticipant($active_id);
    }

    protected function getQuestionCountAndPointsForPassOfParticipant(
        int $active_id,
        int $pass
    ): array {
        return ilObjTest::_getQuestionCountAndPointsForPassOfParticipant($active_id, $pass);
    }

    /**
     * @return int[]
     */
    protected function getAccessFilteredActiveIds(): array
    {
        if (($participants_list = $this->test_obj->getAccessFilteredParticipantList()) !== null) {
            return $participants_list->getAllActiveIds();
        }
        $participants = $this->test_obj->getTestParticipants();
        return array_keys($participants);
    }

    /**
     * @param int[] $active_ids
     */
    protected function queryEvaluationData(array $active_ids): array
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

        $result = $this->db->query(
            sprintf(
                $query,
                $this->db->quote($this->test_obj->getTestId(), 'integer'),
                $this->db->in('tst_active.active_id', $active_ids, false, 'integer'),
            )
        );
        $ret = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $ret[] = $row;
        }
        return $ret;
    }

    public function getCorrectionsEvaluationData(): ilTestEvaluationData
    {
        $eval_data_rows = $this->queryEvaluationData($this->getAccessFilteredActiveIds());
        $participants = [];
        $current_user = null;
        $current_attempt = null;

        foreach ($eval_data_rows as $row) {
            if ($current_user !== $row['active_id']) {
                $current_user = $row['active_id'];
                $current_attempt = null;
                $user_eval_data = $this->buildBasicUserEvaluationDataFromDB($row);
            }

            if ($current_attempt !== $row['pass']) {
                $current_attempt = $row['pass'];
                $attempt = $this->buildBasicAttemptEvaluationDataFromDB($row);
            }

            $attempt = $this->addQuestionToAttempt($attempt, $row);
            $user_eval_data->addPass($row['pass'], $attempt);
            $participants[$row['active_id']] = $user_eval_data;
        }
        return new ilTestEvaluationData($participants);
    }

    public function getEvaluationData(): ilTestEvaluationData
    {
        $eval_data_rows = $this->queryEvaluationData($this->getAccessFilteredActiveIds());
        $participants = [];
        $current_user = null;
        $current_attempt = null;

        foreach ($eval_data_rows as $row) {
            if ($current_user !== $row['active_id']) {
                $current_user = $row['active_id'];
                $current_attempt = null;
                $user_eval_data = $this->addVisitingTimeToUserEvalData(
                    $this->buildBasicUserEvaluationDataFromDB($row),
                    $row['active_id']
                );
            }

            if ($current_attempt !== $row['pass']) {
                $current_attempt = $row['pass'];
                $attempt = $this->addPointsAndQuestionCountToAttempt(
                    $this->buildBasicAttemptEvaluationDataFromDB($row),
                    $row
                );
            }

            $attempt = $this->addQuestionToAttempt($attempt, $row);
            $user_eval_data->addPass($row['pass'], $attempt);
            $participants[$row['active_id']] = $user_eval_data;
        }

        $evaluation_data = $this->addQuestionsToParticipantPasses(
            new ilTestEvaluationData($participants)
        );
        return $this->addMarksToParticipants($evaluation_data);
    }

    private function buildBasicUserEvaluationDataFromDB(array $row): ilTestEvaluationUserData
    {
        $user_data = new ilTestEvaluationUserData($this->getPassScoringSettings());

        $user_data->setName(
            $this->test_obj->buildName($row['usr_id'], $row['firstname'], $row['lastname'])
        );

        if ($row['login'] !== null) {
            $user_data->setLogin($row['login']);
        }
        if ($row['usr_id'] !== null) {
            $user_data->setUserID($row['usr_id']);
        }
        $user_data->setSubmitted((bool) $row['submitted']);
        $user_data->setLastFinishedPass($row['last_finished_pass']);
        return $user_data;
    }

    private function buildBasicAttemptEvaluationDataFromDB(array $row): ilTestEvaluationPassData
    {
        $attempt = new \ilTestEvaluationPassData();
        $attempt->setPass($row['pass']);
        $attempt->setReachedPoints($row['points']);
        $attempt->setObligationsAnswered((bool) $row['obligations_answered']);
        $attempt->setNrOfAnsweredQuestions($row['answeredquestions']);
        $attempt->setWorkingTime($row['workingtime']);
        $attempt->setExamId((string) $row['exam_id']);
        $attempt->setRequestedHintsCount($row['hint_count']);
        $attempt->setDeductedHintPoints($row['hint_points']);
        return $attempt;
    }

    private function addVisitingTimeToUserEvalData(
        ilTestEvaluationUserData $user_data,
        int $active_id
    ): ilTestEvaluationUserData {
        $visitingTime = $this->getVisitTimeOfParticipant($active_id);
        $user_data->setFirstVisit($visitingTime['firstvisit']);
        $user_data->setLastVisit($visitingTime['lastvisit']);
        return $user_data;
    }

    private function addPointsAndQuestionCountToAttempt(
        ilTestEvaluationPassData $attempt,
        array $row
    ): ilTestEvaluationPassData {
        if ($row['questioncount'] !== 0) {
            $attempt->setMaxPoints($row['maxpoints']);
            $attempt->setQuestionCount($row['questioncount']);
            return $attempt;
        }

        list($count, $points) = array_values(
            $this->getQuestionCountAndPointsForPassOfParticipant($row['active_id'], $row['pass'])
        );
        $attempt->setMaxPoints($points);
        $attempt->setQuestionCount($count);
        return $attempt;
    }

    private function addQuestionToAttempt(
        ilTestEvaluationPassData $attempt,
        array $row
    ): ilTestEvaluationPassData {
        if ($row['question_fi'] === null) {
            return $attempt;
        }

        $attempt->addAnsweredQuestion(
            $row["question_fi"],
            $row["qpl_maxpoints"],
            $row["result_points"],
            (bool) $row['answered'],
            null,
            $row['manual']
        );
        return $attempt;
    }

    protected function addQuestionsToParticipantPasses(ilTestEvaluationData $evaluation_data): ilTestEvaluationData
    {
        $is_random_test = $this->isRandomTest();

        foreach ($evaluation_data->getParticipantIds() as $active_id) {
            $user_eval_data = $evaluation_data->getParticipant($active_id);

            $add_user_questions = $this->isRandomTest() ?
                $this->getQuestionsForParticipantPassesForRandomTests($active_id, $user_eval_data, $this->getTestQuestionCount()) :
                $this->getQuestionsForParticipantPassesForSequencedTests($active_id);

            foreach ($add_user_questions as $q) {

                $original_id = $q['original_id'];
                $question_id = $q['question_id'];
                $max_points = $q['max_points'];
                $sequence = $q['sequence'];
                $pass = $q['pass'];
                $title = $q['title'];

                $user_eval_data->addQuestion(
                    $original_id,
                    $question_id,
                    $max_points,
                    $sequence,
                    $pass
                );

                $evaluation_data->addQuestionTitle($question_id, $title);
            }
        }

        return $evaluation_data;
    }

    protected function getQuestionsForParticipantPassesForRandomTests(
        int $active_id,
        ilTestEvaluationUserData $user_eval_data,
        int $question_count
    ): array {
        $ret = [];
        for ($testpass = 0; $testpass <= $user_eval_data->getLastPass(); $testpass++) {
            $this->db->setLimit($question_count, 0);
            $query = "
                SELECT tst_test_rnd_qst.sequence, tst_test_rnd_qst.question_fi, qpl_questions.original_id,
                tst_test_rnd_qst.pass, qpl_questions.points, qpl_questions.title
                FROM tst_test_rnd_qst, qpl_questions
                WHERE tst_test_rnd_qst.question_fi = qpl_questions.question_id
                AND tst_test_rnd_qst.pass = %s
                AND tst_test_rnd_qst.active_fi = %s ORDER BY tst_test_rnd_qst.sequence
            ";

            $result = $this->db->queryF(
                $query,
                ['integer','integer'],
                [$testpass, $active_id]
            );

            if ($result->numRows()) {
                while ($row = $this->db->fetchAssoc($result)) {
                    $tpass = array_key_exists("pass", $row) ? $row["pass"] : 0;

                    if (
                        !isset($row["question_fi"], $row["points"], $row["sequence"]) ||
                        !is_numeric($row["question_fi"]) || !is_numeric($row["points"]) || !is_numeric($row["sequence"])
                    ) {
                        continue;
                    }

                    $ret[] = [
                        'original_id' => (int) $row["original_id"],
                        'question_id' => (int) $row["question_fi"],
                        'max_points' => (float) $row["points"],
                        'sequence' => (int) $row["sequence"],
                        'pass' => $tpass,
                        'title' => $row["title"]
                    ];
                }
            }
        }
        return $ret;
    }

    protected function getQuestionsForParticipantPassesForSequencedTests(
        int $active_id
    ): array {
        $ret = [];

        $query = "
            SELECT tst_test_question.sequence, tst_test_question.question_fi,
            qpl_questions.points, qpl_questions.title, qpl_questions.original_id
            FROM tst_test_question, tst_active, qpl_questions
            WHERE tst_test_question.question_fi = qpl_questions.question_id
            AND tst_active.active_id = %s
            AND tst_active.test_fi = tst_test_question.test_fi
            ORDER BY tst_test_question.sequence
        ";

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
                "SELECT * FROM tst_sequence WHERE active_fi = %s",
                ['integer'],
                [$active_id]
            );

            while ($seqrow = $this->db->fetchAssoc($seqresult)) {
                $questionsequence = unserialize($seqrow['sequence']);
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

    protected function addMarksToParticipants(ilTestEvaluationData $evaluation_data): ilTestEvaluationData
    {
        $mark_schema = $this->getTesttMarkSchema();

        foreach ($evaluation_data->getParticipantIds() as $active_id) {
            $user_eval_data = $evaluation_data->getParticipant($active_id);

            $percentage = $user_eval_data->getReachedPointsInPercent();
            $mark = $mark_schema->getMatchingMark($percentage);

            if (is_object($mark)) {
                $user_eval_data->setMark($mark->getShortName());
                $user_eval_data->setMarkOfficial($mark->getOfficialName());

                $user_eval_data->setPassed(
                    $mark->getPassed() && $user_eval_data->areObligationsAnswered()
                );
            }
        }

        return $evaluation_data;
    }

}
