<?php

namespace ILIAS\Modules\Test\Result;

use ILIAS\AssessmentQuestion\DomainModel\AnswerScoreDto;

/**
 * Class TestResultRepository
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class TestResultRepository
{

    public function storeQuestionResult($active_id, $pass, $question_int_id, int $manual, AnswerScoreDto $answer_score_dto)
    {
        global $DIC;

        $db = $DIC->database();

        $next_id = $db->nextId(TestResultAr::STORAGE_NAME);

        //Prevent Raise Conditions
        $query = "INSERT INTO " . TestResultAr::STORAGE_NAME . " 
        (test_result_id, active_fi, question_fi, pass)
        SELECT * FROM (
            SELECT " . $next_id . " as test_result_id," . $db->quote($active_id, 'integer') . " as active_fi," . $db->quote($question_int_id, 'integer') . " as question_fi," . $db->quote($pass,
                'integer') . " as pass
                ) AS tmp
    WHERE NOT EXISTS (
            SELECT test_result_id FROM " . TestResultAr::STORAGE_NAME . " 
            WHERE active_fi = " . $db->quote($active_id, 'integer') . " 
            and question_fi = " . $db->quote($question_int_id, 'integer')
            . " 
            and pass = " . $db->quote($pass, 'integer') . "
    ) LIMIT 1";
        $db->query($query);

        /**
         * @var TestResultAr $test_result_ar
         */
        $test_result_ar = TestResultAr::where(['active_fi' => $db->quote($active_id, 'integer'), 'question_fi' => $db->quote($question_int_id, 'integer'), 'pass' => $db->quote($pass, 'integer')])
            ->first();

        $update_test_result_ar = TestResultAr::createNew(
            $test_result_ar->getTestResultId(),
            $active_id,
            $question_int_id,
            $answer_score_dto->getReachedPoints(),
            $pass,
            $manual,
            time(),
            $answer_score_dto->getRequestedHints(),
            0, //todo
            1, //always 1!
            0); //always 0;

        $update_test_result_ar->update();
    }
}