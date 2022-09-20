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
 * Survey sc evaluation
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class SurveySingleChoiceQuestionEvaluation extends SurveyQuestionEvaluation
{
    //
    // EXPORT
    //

    public function getUserSpecificVariableTitles(
        array &$a_title_row,
        array &$a_title_row2,
        bool $a_do_title,
        bool $a_do_label
    ): void {
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

    /**
     * @param array|ilSurveyEvaluationResults $a_results
     */
    public function addUserSpecificResults(
        array &$a_row,
        int $a_user_id,
        $a_results
    ): void {
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
        if (count($answer) === 0) {
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

    protected function supportsSumScore(): bool
    {
        return true;
    }
}
