<?php

namespace ILIAS\Modules\Test\Result;

use ILIAS\AssessmentQuestion\DomainModel\AnswerScoreDto;

/**
 * Class TestPassResultRepository
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class TestPassResultRepository
{

    public function persistPassResult(
        TestPassResult $test_pass_result_ar
    ) : void {
        global $DIC;

        $db = $DIC->database();

        //Prevent Raise Conditions
        $query = "INSERT INTO " . TestPassResult::STORAGE_NAME . " 
        (active_fi, question_fi)
        SELECT * FROM (
            SELECT " . $db->quote($test_pass_result_ar->getActiveFi(), 'integer') . " as active_fi," . $db->quote($test_pass_result_ar->getPass(),
                'integer') . " as pass
                ) AS tmp
    WHERE NOT EXISTS (
            SELECT active_fi FROM " . TestPassResult::STORAGE_NAME . " 
            WHERE active_fi = " . $db->quote($test_pass_result_ar->getActiveFi(), 'integer') . " 
            and pass = " . $db->quote($test_pass_result_ar->getPass(), 'integer') . "
    ) LIMIT 1";
        $db->query($query);

        /**
         * @var TestResultAr $test_result_ar
         */
        $sql = "UPDATE " . TestPassResult::STORAGE_NAME . " 
                SET active_fi " . $test_pass_result_ar->getActiveFi() . ",
                pass " . $test_pass_result_ar->getPass() . ",
                points " . $test_pass_result_ar->getPoints() . ", 
                maxpoints " . $test_pass_result_ar->getMaxPoints() . ",
                questioncount " . $test_pass_result_ar->getQuestioncount() . ",
                answeredquestions " . $test_pass_result_ar->getAnsweredquestions() . ",
                workingtime " . $test_pass_result_ar->getWorkingtime() . ",
                tstamp " . $test_pass_result_ar->getTstamp() . ",
                hint_count " . $test_pass_result_ar->getHintCount() . ",
                hint_points " . $test_pass_result_ar->getHintPoints() . ",
                obligations_answered " . $test_pass_result_ar->getObligationsAnswered() . ",
                exam_id " . $test_pass_result_ar->getExamId() . ";
        WHERE active_fi = " . $db->quote($test_pass_result_ar->getActiveFi(), 'integer') . " 
            and pass = " . $db->quote($test_pass_result_ar->getPass(), 'integer');
        $db->query($query);
    }
}