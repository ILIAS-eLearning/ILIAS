<?php

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
        if ($result->numRows() == 1) {
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

        if ($result->numRows() == 1) {
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

        if ($result->numRows() == 1) {
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
        if ($result->numRows() == 0) {
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
        if ($this->database->numRows($result) == 1) {
            $row = $this->database->fetchAssoc($result);
            $points = (float) $row["points"];
        }
        return $points;
    }
}
