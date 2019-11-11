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

    /**
     * @param $active_id
     * @param $pass
     *
     * @return TestResultAr[]
     */
    public function getTestResults($active_id, $pass):array {

        $arr_test_result_ar = [];
        foreach(TestResultAr::where(['active_fi' => $active_id, 'pass' => $pass])->orderBy('order_by')->get() as $test_result_ar) {
            /**
             * @var TestResultAr $test_result_ar
             */
            $arr_test_result_ar[] = $test_result_ar;
        }

        return $arr_test_result_ar;
    }

    public function persistQuestionResult($active_id, $pass, $question_int_id, string $revision_key, int $manual, int $order, AnswerScoreDto $answer_score_dto)
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
            $revision_key,
            $answer_score_dto->getReachedPoints(),
            $answer_score_dto->getMaxPoints(),
            $pass,
            $manual,
            time(),
            $answer_score_dto->getRequestedHints(),
            0, //todo
            1, //always 1!
            0,
            $answer_score_dto->getPercentSolved(),
            $order
            ); //always 0;

        $update_test_result_ar->update();
    }


    public function calculateTestPassResult(
        int $active_id,
        int $pass,
        int $working_time,
        string $exam_id
    ) : TestPassResult {
        global $DIC;
        $db = $DIC->database();

        $sql = "SELECT 
                count(question_fi) as total_questions,
                SUM(points) as points, 
                SUM(max_points) as max_points, 
                SUM(hint_count) as hint_count, 
                SUM(hint_points) as hint_points, 
                SUM(answered) as answered
                FROM tst_test_result 
                 WHERE active_fi = " . $db->quote($active_id, 'integer') . "
                and pass = " . $db->quote($pass, 'integer');

        $result = $db->query($sql);
        while ($row = $db->fetchAssoc($result)) {
            return TestPassResult::createNew(
                $active_id,
                $pass,
                $row['points'],
                $row['max_points'],
                $row['total_questions'],
                $row['answered'],
                $working_time,
                time(),
                $row['hint_count'],
                $row['hint_points'],
                1, //TODO
                $exam_id
            );
        }
    }
}