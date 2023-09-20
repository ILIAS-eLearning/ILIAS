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


}
