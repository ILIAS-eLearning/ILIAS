<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/SurveyQuestionPool/classes/class.SurveyQuestionEvaluation.php";

/**
 * Survey text evaluation
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesSurveyQuestionPool
 */
class SurveyTextQuestionEvaluation extends SurveyQuestionEvaluation
{
    //
    // DETAILS
    //
    
    public function getGrid($a_results, $a_abs = true, $a_perc = true)
    {
    }
    
    public function getChart($a_results)
    {
    }
    

    //
    // EXPORT
    //
    
    public function getExportGrid($a_results)
    {
    }
    
    public function addUserSpecificResults(array &$a_row, $a_user_id, $a_results)
    {
        $answer = $a_results->getUserResults($a_user_id);
        if ($answer === null) {
            $a_row[] = $this->getSkippedValue();
        } else {
            $a_row[] = $answer[0][1];
        }
    }
}
