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
 * Survey text evaluation
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class SurveyTextQuestionEvaluation extends SurveyQuestionEvaluation
{
    //
    // DETAILS
    //

    /**
     * @param array|ilSurveyEvaluationResults $a_results
     */
    public function getGrid(
        $a_results,
        bool $a_abs = true,
        bool $a_perc = true
    ) : array {
        return [];
    }

    /**
     * @param array|ilSurveyEvaluationResults $a_results
     */
    public function getChart($a_results) : ?array
    {
        return null;
    }


    //
    // EXPORT
    //

    /**
     * @param array|ilSurveyEvaluationResults $a_results
     */
    public function getExportGrid($a_results) : array
    {
        return [];
    }
    
    public function addUserSpecificResults(
        array &$a_row,
        int $a_user_id,
        $a_results
    ) : void {
        $answer = $a_results->getUserResults($a_user_id);
        if (count($answer) === 0) {
            $a_row[] = $this->getSkippedValue();
        } else {
            $a_row[] = $answer[0][1];
        }
    }
}
