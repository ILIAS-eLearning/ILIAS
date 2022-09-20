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
 * Survey metric  evaluation
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class SurveyMetricQuestionEvaluation extends SurveyQuestionEvaluation
{
    //
    // RESULTS
    //

    protected function parseResults(
        ilSurveyEvaluationResults $a_results,
        array $a_answers,
        SurveyCategories $a_categories = null
    ): void {
        parent::parseResults($a_results, $a_answers);

        // add arithmetic mean
        $total = $sum = 0;
        foreach ($a_answers as $answers) {
            foreach ($answers as $answer) {
                $total++;
                $sum += $answer["value"];
            }
        }
        if ($total > 0) {
            $a_results->setMean($sum / $total);
        }
    }


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
    ): array {
        $lng = $this->lng;

        if ($a_abs && $a_perc) {
            $cols = array(
                $lng->txt("category_nr_selected"),
                $lng->txt("svy_fraction_of_selections")
            );
        } elseif ($a_abs) {
            $cols = array(
                $lng->txt("category_nr_selected")
            );
        } else {
            $cols = array(
                $lng->txt("svy_fraction_of_selections")
            );
        }

        $res = array(
            "cols" => $cols,
            "rows" => array()
        );

        // as we have no variables build rows from answers directly
        $answ = $a_results->getAnswers();
        $total = count($answ);

        if ($total > 0) {
            $cumulated = array();
            foreach ($a_results->getAnswers() as $answer) {
                $cumulated[$answer->value] = ($cumulated[$answer->value] ?? 0) + 1;
            }
            foreach ($cumulated as $value => $count) {
                $perc = sprintf("%.2f", $count / $total * 100) . "%";
                if ($a_abs && $a_perc) {
                    $res["rows"][] = array(
                        $value,
                        $count,
                        $perc
                    );
                } elseif ($a_abs) {
                    $res["rows"][] = array(
                        $value,
                        $count
                    );
                } else {
                    $res["rows"][] = array(
                        $value,
                        $perc
                    );
                }
            }
        }

        return $res;
    }

    /**
     * @param array|ilSurveyEvaluationResults $a_results
     */
    public function getChart($a_results): ?array
    {
        $lng = $this->lng;

        $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, $a_results->getQuestion()->getId());
        $chart->setYAxisToInteger(true);

        $colors = $this->getChartColors();
        $chart->setColors($colors);

        // :TODO:
        $chart->setSize((string) $this->chart_width, (string) $this->chart_height);

        $data = $chart->getDataInstance(ilChartGrid::DATA_BARS);
        $data->setLabel($lng->txt("category_nr_selected"));
        $data->setBarOptions(0.5, "center");
        $data->setFill(1);

        $total = count($a_results->getAnswers());
        if ($total > 0) {
            $cumulated = array();
            foreach ($a_results->getAnswers() as $answer) {
                $cumulated[$answer->value] = ($cumulated[$answer->value] ?? 0) + 1;
            }

            $labels = array();
            foreach ($cumulated as $value => $count) {
                $data->addPoint($value, $count);
                $labels[$value] = $value;
            }
            $chart->addData($data);

            $chart->setTicks($labels, false, true);

            return array(
                $chart->getHTML(),
                null
            );
        }
        return null;
    }


    //
    // EXPORT
    //

    /**
     * Get grid data
     * @param ilSurveyEvaluationResults|array $a_results
     */
    public function getExportGrid($a_results): array
    {
        $lng = $this->lng;

        $res = array(
            "cols" => array(
                $lng->txt("value"),
                $lng->txt("category_nr_selected"),
                $lng->txt("svy_fraction_of_selections")
            ),
            "rows" => array()
        );

        // as we have no variables build rows from answers directly
        $total = count($a_results->getAnswers());
        if ($total > 0) {
            $cumulated = array();
            foreach ($a_results->getAnswers() as $answer) {
                $cumulated[$answer->value]++;
            }
            foreach ($cumulated as $value => $count) {
                $res["rows"][] = array(
                    $value,
                    $count,
                    sprintf("%.2f", $count / $total * 100) . "%"
                );
            }
        }

        return $res;
    }

    /**
     * @param array|ilSurveyEvaluationResults $a_results
     */
    public function addUserSpecificResults(
        array &$a_row,
        int $a_user_id,
        $a_results
    ): void {
        $answer = $a_results->getUserResults($a_user_id);
        if (count($answer) === 0) {
            $a_row[] = $this->getSkippedValue();
        } else {
            $a_row[] = $answer[0][0];
        }
    }
}
