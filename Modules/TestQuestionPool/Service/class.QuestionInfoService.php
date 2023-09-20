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
}
