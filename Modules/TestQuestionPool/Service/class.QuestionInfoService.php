<?php declare(strict_types=1);

namespace ILIAS\TestQuestionPool;

class QuestionInfoService
{
    private \ilDBInterface $database;

    public function __construct(\ilDBInterface $db)
    {
        $this->database = $db;
    }

    public function getQuestionTitle(int $question_id): string
    {
        if ($question_id < 1) {
            return "";
        }

        $result = $this->database->queryF(
            "SELECT title FROM qpl_questions WHERE qpl_questions.question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() === 1) {
            $data = $this->database->fetchAssoc($result);
            return $data["title"];
        }
        return "";
    }

    public function getQuestionType(int $question_id): string
    {
        if ($question_id < 1) {
            return "";
        }

        $result = $this->database->queryF(
            "SELECT type_tag FROM qpl_questions, qpl_qst_type WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id",
            array('integer'),
            array($question_id)
        );

        if ($result->numRows() === 1) {
            $data = $this->database->fetchAssoc($result);
            return $data["type_tag"];
        }

        return "";
    }

    public function getQuestionText(int $a_q_id): string
    {
        $result = $this->database->queryF(
            "SELECT question_text FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($a_q_id)
        );

        if ($result->numRows() === 1) {
            $row = $this->database->fetchAssoc($result);
            return $row["question_text"];
        }

        return "";
    }

    public function getTotalRightAnswers(int $a_q_id): int
    {
        $result = $this->database->queryF(
            "SELECT question_id FROM qpl_questions WHERE original_id = %s OR question_id = %s",
            array('integer','integer'),
            array($a_q_id, $a_q_id)
        );
        if ($result->numRows() === 0) {
            return 0;
        }

        $found_id = array();
        while ($row = $this->database->fetchAssoc($result)) {
            $found_id[] = $row["question_id"];
        }

        $result = $this->database->query("SELECT * FROM tst_test_result WHERE " . $this->database->in('question_fi', $found_id, false, 'integer'));
        $answers = array();
        while ($row = $this->database->fetchAssoc($result)) {
            $reached = $row["points"];
            $max = $this->getMaximumPoints($row["question_fi"]);
            $answers[] = array("reached" => $reached, "max" => $max);
        }

        $max = 0.0;
        $reached = 0.0;
        foreach ($answers as $key => $value) {
            $max += $value["max"];
            $reached += $value["reached"];
        }
        if ($max > 0) {
            return $reached / $max;
        }
        return 0;
    }

    public function getMaximumPoints(int $question_id): float
    {
        $points = 0.0;
        $result = $this->database->queryF(
            "SELECT points FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($this->database->numRows($result) === 1) {
            $row = $this->database->fetchAssoc($result);
            $points = (float) $row["points"];
        }
        return $points;
    }

    public function getQuestionInfo(int $question_id): array
    {
        $result = $this->database->queryF(
            "SELECT qpl_questions.*, qpl_qst_type.type_tag FROM qpl_qst_type, qpl_questions WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_qst_type.question_type_id",
            array('integer'),
            array($question_id)
        );

        if ($this->database->numRows($result)) {
            return $this->database->fetchAssoc($result);
        }
        return array();
    }

    /**
     * Checks if an array of question ids is answered by a user or not
     *
     * @param int user_id
     * @param int[] $question_ids user id array
     */
    public function areQuestionsAnsweredByUser(int $a_user_id, array $a_question_ids): bool
    {
        $res = $this->database->queryF(
            "SELECT DISTINCT(question_fi) FROM tst_test_result JOIN tst_active " .
            "ON (active_id = active_fi) " .
            "WHERE " . $this->database->in('question_fi', $a_question_ids, false, 'integer') .
            " AND user_fi = %s",
            array('integer'),
            array($a_user_id)
        );
        return $res->numRows() === count($a_question_ids);
    }

    public function lookupResultRecordExist(int $activeId, int $questionId, int $pass): bool
    {
        $query = "
			SELECT COUNT(*) cnt
			FROM tst_test_result
			WHERE active_fi = %s
			AND question_fi = %s
			AND pass = %s
		";

        $row = $this->database->fetchAssoc($this->database->queryF($query, array('integer', 'integer', 'integer'), array($activeId, $questionId, $pass)));

        return $row['cnt'] > 0;
    }

    /**
     * Checks whether the question is a clone of another question or not
     */
    public function isClone(int $question_id): bool
    {
        $result = $this->database->queryF(
            "SELECT COUNT(original_id) cnt FROM qpl_questions WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        $row = $this->database->fetchAssoc($result);
        return ((int) $row["cnt"]) > 0;
    }

    /**
     * Checks whether the question is in use or not in pools or tests
     */
    public function isInUse(int $question_id = 0): bool
    {
        return $this->usageNumber($question_id) > 0;
    }

    /**
     * Returns the number of place the question is in use in pools or tests
     */
    public function usageNumber(int $question_id = 0): int
    {
        if ($question_id < 1) {
            $question_id = $this->getId();
        }

        $result = $this->db->queryF(
            "SELECT COUNT(qpl_questions.question_id) question_count FROM qpl_questions, tst_test_question WHERE qpl_questions.original_id = %s AND qpl_questions.question_id = tst_test_question.question_fi",
            array('integer'),
            array($question_id)
        );
        $row = $this->db->fetchAssoc($result);
        $count = (int) $row["question_count"];

        $result = $this->db->queryF(
            "
			SELECT tst_active.test_fi
			FROM qpl_questions
			INNER JOIN tst_test_rnd_qst ON tst_test_rnd_qst.question_fi = qpl_questions.question_id
			INNER JOIN tst_active ON tst_active.active_id = tst_test_rnd_qst.active_fi
			WHERE qpl_questions.original_id = %s
			GROUP BY tst_active.test_fi",
            array('integer'),
            array($question_id)
        );
        $count += (int) $this->db->numRows($result);

        return $count;
    }
}
