<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Survey mc evaluation
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class SurveyMultipleChoiceQuestionEvaluation extends SurveyQuestionEvaluation
{
    
    //
    // EXPORT
    //
    
    public function getUserSpecificVariableTitles(
        array &$a_title_row,
        array &$a_title_row2,
        bool $a_do_title,
        bool $a_do_label
    ) : void {
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

    /**
     * @param array|ilSurveyEvaluationResults $a_results
     */
    public function addUserSpecificResults(
        array &$a_row,
        int $a_user_id,
        $a_results
    ) : void {
        $categories = $this->question->getCategories();
                
        $answers = $a_results->getUserResults($a_user_id);
        if (count($answers) === 0) {
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

    protected function supportsSumScore() : bool
    {
        return true;
    }
}
