<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/SurveyQuestionPool/classes/class.SurveyQuestionEvaluation.php";

/**
 * Survey sc evaluation
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesSurveyQuestionPool
 */
class SurveySingleChoiceQuestionEvaluation extends SurveyQuestionEvaluation
{
    //
    // EXPORT
    //
    
    public function getUserSpecificVariableTitles(array &$a_title_row, array &$a_title_row2, $a_do_title, $a_do_label)
    {
        $lng = $this->lng;

        // this is for the separation of title and scale, see #20646
        $a_title_row[] = $a_title_row[count($a_title_row) - 1];
        $a_title_row2[] = $a_title_row2[count($a_title_row2) - 1];

        $categories = $this->question->getCategories();
        for ($i = 0; $i < $categories->getCategoryCount(); $i++) {
            $cat = $categories->getCategory($i);
            if ($cat->other) {
                $a_title_row[] = $cat->title . " [" . $cat->scale . "]";
                $a_title_row2[] = $lng->txt('other');
            }
        }
    }
    
    public function addUserSpecificResults(array &$a_row, $a_user_id, $a_results)
    {
        // check if text answer column is needed
        $other = array();
        $categories = $this->question->getCategories();
        for ($i = 0; $i < $categories->getCategoryCount(); $i++) {
            $cat = $categories->getCategory($i);
            if ($cat->other) {
                $other[] = $cat->scale;
                // outcommented due to #0021525
//				break;
            }
        }
        
        $answer = $a_results->getUserResults($a_user_id);
        if ($answer === null) {
            $a_row[] = $this->getSkippedValue();
            $a_row[] = "";	// see #20646
            foreach ($other as $dummy) {
                $a_row[] = "";
            }
        } else {
            //$a_row[] = $answer[0][0];	// see #20646
            $a_row[] = $answer[0][3];	// see #20646
            $a_row[] = $answer[0][2];	// see #20646

            foreach ($other as $scale) {
                if ($scale == $answer[0][2]) {
                    $a_row[] = $answer[0][1];
                } else {
                    $a_row[] = "";
                }
            }
        }
    }
}
