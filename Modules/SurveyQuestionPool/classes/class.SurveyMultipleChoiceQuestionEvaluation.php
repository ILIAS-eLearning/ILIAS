<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/SurveyQuestionPool/classes/class.SurveyQuestionEvaluation.php";

/**
 * Survey mc evaluation
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesSurveyQuestionPool
 */
class SurveyMultipleChoiceQuestionEvaluation extends SurveyQuestionEvaluation
{
    
    //
    // EXPORT
    //
    
    public function getUserSpecificVariableTitles(array &$a_title_row, array &$a_title_row2, $a_do_title, $a_do_label)
    {
        $lng = $this->lng;
        
        $categories = $this->question->getCategories();
        for ($i = 0; $i < $categories->getCategoryCount(); $i++) {
            $cat = $categories->getCategory($i);
            
            $a_title_row[] = $cat->title . " [" . $cat->scale . "]";
            $a_title_row2[] = "";
            
            if ($cat->other) {
                $a_title_row[] = $cat->title . " [" . $cat->scale . "]";
                $a_title_row2[] = $lng->txt('other');
            }
        }
    }
    
    public function addUserSpecificResults(array &$a_row, $a_user_id, $a_results)
    {
        $categories = $this->question->getCategories();
                
        $answers = $a_results->getUserResults($a_user_id);
        if ($answers === null) {
            $a_row[] = $this->getSkippedValue();
            
            for ($i = 0; $i < $categories->getCategoryCount(); $i++) {
                $cat = $categories->getCategory($i);
                $a_row[] = "";
                
                if ($cat->other) {
                    $a_row[] = "";
                }
            }
        } else {
            $a_row[] = "";
            
            for ($i = 0; $i < $categories->getCategoryCount(); $i++) {
                $cat = $categories->getCategory($i);
                $found = false;
                foreach ($answers as $answer) {
                    if ($answer[2] == $cat->scale) {
                        $a_row[] = $answer[2];
                        if ($cat->other) {
                            $a_row[] = $answer[1];
                        }
                        $found = true;
                    }
                }
                if (!$found) {
                    $a_row[] = ""; // "0" ?!
                    if ($cat->other) {
                        $a_row[] = "";
                    }
                }
            }
        }
    }
}
