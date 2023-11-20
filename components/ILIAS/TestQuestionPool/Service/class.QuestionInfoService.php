<?php

declare(strict_types=1);

namespace ILIAS\TestQuestionPool;

class QuestionInfoService
{
    public function __construct(
        private \ilDBInterface $database,
        private \ilComponentFactory $component_factory,
        private \ilLanguage $lng
    ) {
    }

    public function getQuestionTitle(int $question_id): string
    {
        if ($question_id < 1) {
            return "";
        }

        $result = $this->database->queryF(
            "SELECT title FROM qpl_questions WHERE qpl_questions.question_id = %s",
            ['integer'],
            [$question_id]
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
            ['integer'],
            [$question_id]
        );

        if ($result->numRows() === 1) {
            $data = $this->database->fetchAssoc($result);
            return $data["type_tag"];
        }

        return "";
    }

    public function getQuestionTypeName(int $question_id): string
    {
        $question_type = $this->getQuestionType($question_id);

        if ($question_type === '') {
            return '';
        }

        if (file_exists("./components/ILIAS/TestQuestionPool/classes/class." . $question_type . ".php")) {
            return $this->lng->txt($question_type);
        }

        foreach ($this->component_factory->getActivePluginsInSlot('qst') as $pl) {
            if ($pl->getQuestionType() === $question_type) {
                return $pl->getQuestionTypeTranslation();
            }
        }
        return "";
    }

    public function getQuestionText(int $a_q_id): string
    {
        $result = $this->database->queryF(
            "SELECT question_text FROM qpl_questions WHERE question_id = %s",
            ['integer'],
            [$a_q_id]
        );

        if ($result->numRows() === 1) {
            $row = $this->database->fetchAssoc($result);
            return $row["question_text"];
        }

        return "";
    }

    public function getFractionOfReachedToReachablePointsTotal(int $a_q_id): float
    {
        $result = $this->database->queryF(
            "SELECT question_id FROM qpl_questions WHERE original_id = %s OR question_id = %s",
            ['integer','integer'],
            [$a_q_id, $a_q_id]
        );
        if ($result->numRows() === 0) {
            return 0.0;
        }

        $found_id = [];
        while ($row = $this->database->fetchAssoc($result)) {
            $found_id[] = $row["question_id"];
        }

        $result = $this->database->query("SELECT question_fi, points FROM tst_test_result WHERE " . $this->database->in('question_fi', $found_id, false, 'integer'));
        $answers = [];
        while ($row = $this->database->fetchAssoc($result)) {
            $reached = $row["points"];
            $max = $this->getMaximumPoints($row["question_fi"]);
            $answers[] = ["reached" => $reached, "max" => $max];
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
            ['integer'],
            [$question_id]
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
            ['integer'],
            [$question_id]
        );

        if ($this->database->numRows($result)) {
            return $this->database->fetchAssoc($result);
        }
        return [];
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
            ['integer'],
            [$a_user_id]
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

        $row = $this->database->fetchAssoc($this->database->queryF($query, ['integer', 'integer', 'integer'], [$activeId, $questionId, $pass]));

        return $row['cnt'] > 0;
    }

    /**
     * Checks whether the question is a clone of another question or not
     */
    public function isClone(int $question_id): bool
    {
        $result = $this->database->queryF(
            "SELECT COUNT(original_id) cnt FROM qpl_questions WHERE question_id = %s",
            ['integer'],
            [$question_id]
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
        $result = $this->database->queryF(
            "SELECT COUNT(qpl_questions.question_id) question_count FROM qpl_questions, tst_test_question WHERE qpl_questions.original_id = %s AND qpl_questions.question_id = tst_test_question.question_fi",
            ['integer'],
            [$question_id]
        );
        $row = $this->database->fetchAssoc($result);
        $count = (int) $row["question_count"];

        $result = $this->database->queryF(
            "
			SELECT tst_active.test_fi
			FROM qpl_questions
			INNER JOIN tst_test_rnd_qst ON tst_test_rnd_qst.question_fi = qpl_questions.question_id
			INNER JOIN tst_active ON tst_active.active_id = tst_test_rnd_qst.active_fi
			WHERE qpl_questions.original_id = %s
			GROUP BY tst_active.test_fi",
            ['integer'],
            [$question_id]
        );
        $count += $this->database->numRows($result);

        return $count;
    }

    public function questionExists(int $question_id): bool
    {
        if ($question_id < 1) {
            return false;
        }

        $result = $this->database->queryF(
            "SELECT question_id FROM qpl_questions WHERE question_id = %s",
            ['integer'],
            [$question_id]
        );
        return $result->numRows() === 1;
    }

    public function questionExistsInPool(int $question_id): bool
    {
        if ($question_id < 1) {
            return false;
        }

        $result = $this->database->queryF(
            "SELECT question_id FROM qpl_questions INNER JOIN object_data ON obj_fi = obj_id WHERE question_id = %s AND type = 'qpl'",
            ['integer'],
            [$question_id]
        );
        return $this->database->numRows($result) === 1;
    }

    public function isUsedInRandomTest(int $question_id): bool
    {
        $result = $this->database->queryF(
            "SELECT test_random_question_id FROM tst_test_rnd_qst WHERE question_fi = %s",
            ['integer'],
            [$question_id]
        );
        return $this->database->numRows($result) > 0;
    }

    public function originalQuestionExists(int $questionId): bool
    {
        $query = "
			SELECT COUNT(dupl.question_id) cnt
			FROM qpl_questions dupl
			INNER JOIN qpl_questions orig
			ON orig.question_id = dupl.original_id
			WHERE dupl.question_id = %s
		";

        $res = $this->database->queryF($query, ['integer'], [$questionId]);
        $row = $this->database->fetchAssoc($res);

        return $row['cnt'] > 0;
    }

    public function getOriginalId(int $question_id): int
    {
        $result = $this->database->queryF(
            "SELECT * FROM qpl_questions WHERE question_id = %s",
            ['integer'],
            [$question_id]
        );
        if ($this->database->numRows($result) > 0) {
            $row = $this->database->fetchAssoc($result);
            if ($row["original_id"] > 0) {
                return $row["original_id"];
            }

            return (int) $row["question_id"];
        }

        return -1;
    }

    public function getQuestionsMissingResultRecord(int $activeId, int $pass, array $questionIds): array
    {
        $IN_questionIds = $this->database->in('question_fi', $questionIds, false, 'integer');

        $query = "
			SELECT question_fi
			FROM tst_test_result
			WHERE active_fi = %s
			AND pass = %s
			AND $IN_questionIds
		";

        $res = $this->database->queryF(
            $query,
            ['integer', 'integer'],
            [$activeId, $pass]
        );

        $questionsHavingResultRecord = [];

        while ($row = $this->database->fetchAssoc($res)) {
            $questionsHavingResultRecord[] = $row['question_fi'];
        }

        $questionsMissingResultRecordt = array_diff(
            $questionIds,
            $questionsHavingResultRecord
        );

        return $questionsMissingResultRecordt;
    }

    public function missingResultRecordExists(int $activeId, int $pass, array $questionIds): bool
    {
        $IN_questionIds = $this->database->in('question_fi', $questionIds, false, 'integer');

        $query = "
			SELECT COUNT(*) cnt
			FROM tst_test_result
			WHERE active_fi = %s
			AND pass = %s
			AND $IN_questionIds
		";

        $row = $this->database->fetchAssoc($this->database->queryF(
            $query,
            ['integer', 'integer'],
            [$activeId, $pass]
        ));

        return $row['cnt'] < count($questionIds);
    }

    public function isInActiveTest(int $obj_id): bool
    {
        $query = 'SELECT user_fi FROM tst_active ' . PHP_EOL
            . 'JOIN tst_test_question ON tst_test_question.test_fi = tst_active.test_fi ' . PHP_EOL
            . 'JOIN qpl_questions ON qpl_questions.question_id = tst_test_question.question_fi ' . PHP_EOL
            . 'WHERE qpl_questions.obj_fi = ' . $this->database->quote($obj_id, 'integer');

        $res = $this->database->query($query);
        return $res->numRows() > 0;
    }

    public function questionTitleExistsInPool(int $questionpool_id, string $title): bool
    {
        $result = $this->database->queryF(
            "SELECT * FROM qpl_questions WHERE obj_fi = %s AND title = %s",
            ['integer','text'],
            [$questionpool_id, $title]
        );
        return $result->numRows() > 0;
    }
}
