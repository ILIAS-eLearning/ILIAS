<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/SurveyQuestionPool/classes/class.SurveyQuestionEvaluation.php";

/**
 * Survey metric  evaluation
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesSurveyQuestionPool
 */
class SurveyMetricQuestionEvaluation extends SurveyQuestionEvaluation
{
    //
    // RESULTS
    //
    
    protected function parseResults(ilSurveyEvaluationResults $a_results, array $a_answers, SurveyCategories $a_categories = null)
    {
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
            $a_results->setMean($sum/$total);
        }
    }
    
    
    //
    // DETAILS
    //
    
    public function getGrid($a_results, $a_abs = true, $a_perc = true)
    {
        $lng = $this->lng;
        
        if ((bool) $a_abs && (bool) $a_perc) {
            $cols = array(
                $lng->txt("category_nr_selected"),
                $lng->txt("svy_fraction_of_selections")
            );
        } elseif ((bool) $a_abs) {
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
        if (is_array($answ)) {
            $total = sizeof($a_results->getAnswers());
            if ($total > 0) {
                $cumulated = array();
                foreach ($a_results->getAnswers() as $answer) {
                    $cumulated[$answer->value]++;
                }
                foreach ($cumulated as $value => $count) {
                    $perc = sprintf("%.2f", $count / $total * 100) . "%";
                    if ((bool) $a_abs && (bool) $a_perc) {
                        $res["rows"][] = array(
                            $value,
                            $count,
                            $perc
                        );
                    } elseif ((bool) $a_abs) {
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
        }
        
        return $res;
    }
    
    public function getChart($a_results)
    {
        $lng = $this->lng;
        
        include_once "Services/Chart/classes/class.ilChart.php";
        $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, $a_results->getQuestion()->getId());
        $chart->setYAxisToInteger(true);
        
        $colors = $this->getChartColors();
        $chart->setColors($colors);

        // :TODO:
        $chart->setsize($this->chart_width, $this->chart_height);
                        
        $data = $chart->getDataInstance(ilChartGrid::DATA_BARS);
        $data->setLabel($lng->txt("category_nr_selected"));
        $data->setBarOptions(0.5, "center");
        $data->setFill(1);
        
        $total = sizeof($a_results->getAnswers());
        if ($total > 0) {
            $cumulated = array();
            foreach ($a_results->getAnswers() as $answer) {
                $cumulated[$answer->value]++;
            }
            
            $labels = array();
            foreach ($cumulated as $value => $count) {
                $data->addPoint($value, $count);
                $labels[$value] = $value;
            }
            $chart->addData($data);

            $chart->setTicks($labels, false, true);
        
            return $chart->getHTML();
        }
    }

    
    //
    // EXPORT
    //
    
    /**
     * Get grid data
     *
     * @param ilSurveyEvaluationResults|array $a_results
     * @return array
     */
    public function getExportGrid($a_results)
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
        $total = sizeof($a_results->getAnswers());
        if ($total > 0) {
            $cumulated = array();
            foreach ($a_results->getAnswers() as $answer) {
                $cumulated[$answer->value]++;
            }
            foreach ($cumulated as $value => $count) {
                $res["rows"][] = array(
                    $value,
                    $count,
                    sprintf("%.2f", $count/$total*100) . "%"
                );
            }
        }
                
        return $res;
    }
    
    public function addUserSpecificResults(array &$a_row, $a_user_id, $a_results)
    {
        $answer = $a_results->getUserResults($a_user_id);
        if ($answer === null) {
            $a_row[] = $this->getSkippedValue();
        } else {
            $a_row[] = $answer[0][0];
        }
    }
}
