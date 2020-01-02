<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Survey/classes/class.ilSurveyEvaluationResults.php";

/**
 * Survey question evaluation
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesSurveyQuestionPool
 */
abstract class SurveyQuestionEvaluation
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilDB
     */
    protected $db;

    protected $question; // [SurveyQuestion]
    protected $finished_ids; // [array]
    protected $chart_width = 400;
    protected $chart_height = 300;
    
    /**
     * Constructor
     *
     * @param SurveyQuestion $a_question
     * @param array $a_finished_ids
     * @return self
     */
    public function __construct(SurveyQuestion $a_question, array $a_finished_ids = null)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->db = $DIC->database();
        $this->question = $a_question;
        $this->finished_ids = $a_finished_ids;
    }
    
    
    //
    // RESULTS
    //
    
    /**
     * Get results
     *
     * @return ilSurveyEvaluationResults|array
     */
    public function getResults()
    {
        $results = new ilSurveyEvaluationResults($this->question);
        $answers = $this->getAnswerData();
                                        
        $this->parseResults(
            $results,
            (array) $answers[0],
            method_exists($this->question, "getCategories")
                ? $this->question->getCategories()
                : null
        );
        
        return $results;
    }
        
    /**
     * Parse answer data into results instance
     *
     * @param ilSurveyEvaluationResults $a_results
     * @param array $a_answers
     * @param SurveyCategories $a_categories
     */
    protected function parseResults(ilSurveyEvaluationResults $a_results, array $a_answers, SurveyCategories $a_categories = null)
    {
        $num_users_answered = sizeof($a_answers);

        $a_results->setUsersAnswered($num_users_answered);
        $a_results->setUsersSkipped($this->getNrOfParticipants()-$num_users_answered);
        
        // parse answers
        $has_multi = false;
        $selections = array();
        foreach ($a_answers as $active_id => $answers) {
            // :TODO:
            if (sizeof($answers) > 1) {
                $has_multi = true;
            }
            foreach ($answers as $answer) {
                // map selection value to scale/category
                if ($a_categories &&
                    $answer["value"] != "") {
                    $scale = $a_categories->getCategoryForScale($answer["value"]+1);
                    if ($scale instanceof ilSurveyCategory) {
                        $answer["value"] = $scale->scale;
                    }
                }
                
                $parsed = new ilSurveyEvaluationResultsAnswer(
                    $active_id,
                    $answer["value"],
                    $answer["text"]
                );
                $a_results->addAnswer($parsed);

                if ($answer["value"] != "") {
                    $selections[$answer["value"]]++;
                }
            }
        }
        
        $total = array_sum($selections);

        if ($total) {
            // mode
            $mode_nr = max($selections);
            $tmp_mode = $selections;
            asort($tmp_mode, SORT_NUMERIC);
            $mode = array_keys($tmp_mode, $mode_nr);
            $a_results->setMode($mode, $mode_nr);
            
            if (!$has_multi) {
                // median
                ksort($selections, SORT_NUMERIC);
                $median = array();
                foreach ($selections as $value => $count) {
                    for ($i = 0; $i < $count; $i++) {
                        $median[] = $value;
                    }
                }
                if ($total % 2 == 0) {
                    $lower = $median[($total/2)-1];
                    $upper = $median[($total/2)];
                    $median_value = 0.5 * ($lower + $upper);
                    if ($a_categories &&
                        round($median_value) != $median_value) {
                        // mapping calculated value to scale values
                        $median_value = array($lower, $upper);
                    }
                } else {
                    $median_value = $median[(($total+1)/2)-1];
                }
                $a_results->setMedian($median_value);
            }
        }
        
        if ($a_categories) {
            // selections by category
            for ($c = 0; $c < $a_categories->getCategoryCount(); $c++) {
                $cat = $a_categories->getCategory($c);
                $scale = $cat->scale;

                $var = new ilSurveyEvaluationResultsVariable(
                    $cat,
                    $selections[$scale],
                    $total
                            ? $selections[$scale]/$total
                            : null
                );
                $a_results->addVariable($var);
            }
        }
    }
            
    public function parseUserSpecificResults($a_qres, $a_user_id)
    {
        $parsed_results = array();
        
        if (is_array($a_qres)) {
            foreach ($a_qres as $row_idx => $row_results) {
                $row_title = $row_results[0];
                $user_results = $row_results[1]->getUserResults($a_user_id);
                if ($user_results) {
                    foreach ($user_results as $item) {
                        // :TODO: layout
                        $tmp = $row_title . ": ";
                        if ($item[0] !== "") {
                            $tmp .= $item[0];
                        }
                        if ($item[1] && $item[0]) {
                            $tmp .= ", \"" . nl2br($item[1]) . "\"";
                        } elseif ($item[1]) {
                            $tmp .= "\"" . nl2br($item[1]) . "\"";
                        }
                        $parsed_results[$row_idx . "-" . $item[2]] = $tmp;
                    }
                }
            }
        } else {
            $user_results = $a_qres->getUserResults($a_user_id);
            if ($user_results) {
                foreach ($user_results as $item) {
                    // :TODO: layout
                    if ($item[0] !== "") {
                        $tmp = $item[0];
                    }
                    if ($item[1] && $item[0]) {
                        $tmp .= ", \"" . nl2br($item[1]) . "\"";
                    } elseif ($item[1]) {
                        $tmp = "\"" . nl2br($item[1]) . "\"";
                    }
                    $parsed_results[$item[2]] = $tmp;
                }
            }
        }
        
        return $parsed_results;
    }
    
    
    //
    // DETAILS
    //
    
    /**
     * Get grid data
     *
     * @param ilSurveyEvaluationResults|array $a_results
     * @param bool $a_abs
     * @param bool $a_perc
     * @return array
     */
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
        
        $vars = $a_results->getVariables();
        if ($vars) {
            foreach ($vars as $var) {
                $perc = $var->perc
                    ? sprintf("%.2f", $var->perc*100) . "%"
                    : "0%";
                
                if ((bool) $a_abs && (bool) $a_perc) {
                    $res["rows"][] = array(
                        $var->cat->title,
                        $var->abs,
                        $perc
                    );
                } elseif ((bool) $a_abs) {
                    $res["rows"][] = array(
                        $var->cat->title,
                        $var->abs
                    );
                } else {
                    $res["rows"][] = array(
                        $var->cat->title,
                        $perc
                    );
                }
            }
        }
        
        return $res;
    }
    
    /**
     * Get text answers
     *
     * @param ilSurveyEvaluationResults|array $a_results
     * @return array
     */
    public function getTextAnswers($a_results)
    {
        return $a_results->getMappedTextAnswers();
    }
    
    protected function getChartColors()
    {
        return array(
            // flot "default" theme
            "#edc240", "#afd8f8", "#cb4b4b", "#4da74d", "#9440ed",
            // http://godsnotwheregodsnot.blogspot.de/2012/09/color-distribution-methodology.html
            "#1CE6FF", "#FF34FF", "#FF4A46", "#008941", "#006FA6", "#A30059",
            "#FFDBE5", "#7A4900", "#0000A6", "#63FFAC", "#B79762", "#004D43", "#8FB0FF", "#997D87",
            "#5A0007", "#809693", "#FEFFE6", "#1B4400", "#4FC601", "#3B5DFF", "#4A3B53", "#FF2F80",
            "#61615A", "#BA0900", "#6B7900", "#00C2A0", "#FFAA92", "#FF90C9", "#B903AA", "#D16100",
            "#DDEFFF", "#000035", "#7B4F4B", "#A1C299", "#300018", "#0AA6D8", "#013349", "#00846F",
            "#372101", "#FFB500", "#C2FFED", "#A079BF", "#CC0744", "#C0B9B2", "#C2FF99", "#001E09",
            "#00489C", "#6F0062", "#0CBD66", "#EEC3FF", "#456D75", "#B77B68", "#7A87A1", "#788D66",
            "#885578", "#FAD09F", "#FF8A9A", "#D157A0", "#BEC459", "#456648", "#0086ED", "#886F4C",
            "#34362D", "#B4A8BD", "#00A6AA", "#452C2C", "#636375", "#A3C8C9", "#FF913F", "#938A81",
            "#575329", "#00FECF", "#B05B6F", "#8CD0FF", "#3B9700", "#04F757", "#C8A1A1", "#1E6E00",
            "#7900D7", "#A77500", "#6367A9", "#A05837", "#6B002C", "#772600", "#D790FF", "#9B9700",
            "#549E79", "#FFF69F", "#201625", "#72418F", "#BC23FF", "#99ADC0", "#3A2465", "#922329",
            "#5B4534", "#FDE8DC", "#404E55", "#0089A3", "#CB7E98", "#A4E804", "#324E72", "#6A3A4C"
        );
    }
    
    /**
     * Get chart
     *
     * @param ilSurveyEvaluationResults|array $a_results
     * @return array
     */
    public function getChart($a_results)
    {
        include_once "Services/Chart/classes/class.ilChart.php";
        $chart = ilChart::getInstanceByType(ilChart::TYPE_GRID, $a_results->getQuestion()->getId());
        $chart->setYAxisToInteger(true);

        $colors = $this->getChartColors();
        $chart->setColors($colors);
            
        // :TODO:
        $chart->setsize($this->chart_width, $this->chart_height);
                    
        $vars = $a_results->getVariables();
        
        $legend = $labels = array();
        foreach ($vars as $idx => $var) {
            $data = $chart->getDataInstance(ilChartGrid::DATA_BARS);
            $data->setBarOptions(0.5, "center");
            $data->setFill(1);
            $chart->addData($data);
                    
            // labels
            $labels[$idx] = "";
            $legend[] = array(
                $var->cat->title,
                $colors[$idx]
            );
            $data->setLabel($var->cat->title);
            
            $data->addPoint($idx, $var->abs);
        }
        
        $chart->setTicks($labels, false, true);

        return array(
            $chart->getHTML(),
            $legend
        );
    }
    
    
    //
    // USER-SPECIFIC
    //
    
    /**
     * Get caption for skipped value
     *
     * @return string
     */
    public function getSkippedValue()
    {
        include_once "Modules/Survey/classes/class.ilObjSurvey.php";
        return ilObjSurvey::getSurveySkippedValue();
    }
    
            
    //
    // HELPER
    //
    
    protected function getSurveyId()
    {
        $ilDB = $this->db;
        
        // #18968
        $set = $ilDB->query("SELECT survey_fi" .
            " FROM svy_svy_qst" .
            " WHERE question_fi = " . $ilDB->quote($this->question->getId(), "integer"));
        $row = $ilDB->fetchAssoc($set);
        return $row["survey_fi"];
    }
    
    
    /**
    * Returns the number of participants for a survey
    *
    * @return integer The number of participants
    */
    protected function getNrOfParticipants()
    {
        $ilDB = $this->db;
        
        if (is_array($this->finished_ids)) {
            return sizeof($this->finished_ids);
        }
        
        $set = $ilDB->query("SELECT finished_id FROM svy_finished" .
            " WHERE survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer"));
        return $set->numRows();
    }
    
    protected function getAnswerData()
    {
        $ilDB = $this->db;
        
        $res = array();
        
        $sql = "SELECT svy_answer.* FROM svy_answer" .
            " JOIN svy_finished ON (svy_finished.finished_id = svy_answer.active_fi)" .
            " WHERE svy_answer.question_fi = " . $ilDB->quote($this->question->getId(), "integer") .
            " AND svy_finished.survey_fi = " . $ilDB->quote($this->getSurveyId(), "integer");
        if (is_array($this->finished_ids)) {
            $sql .= " AND " . $ilDB->in("svy_finished.finished_id", $this->finished_ids, "", "integer");
        }
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[(int) $row["rowvalue"]][(int) $row["active_fi"]][] = array(
                "value" => $row["value"],
                "text" => $row["textanswer"]
            );
        }
        
        return $res;
    }
    
    
    //
    // EXPORT
    //
    
    public function exportResults($a_results, $a_do_title, $a_do_label)
    {
        $question = $a_results->getQuestion();
        
        $res = array();
        
        if ($a_do_title) {
            $res[] = $question->getTitle();
        }
        if ($a_do_label) {
            $res[] = $question->label;
        }
        
        $res[] = $question->getQuestiontext();
        $res[] = SurveyQuestion::_getQuestionTypeName($question->getQuestionType());
        
        $res[] = (int) $a_results->getUsersAnswered();
        $res[] = (int) $a_results->getUsersSkipped();
        
        // :TODO:
        $res[] = is_array($a_results->getModeValue())
            ? implode(", ", $a_results->getModeValue())
            : $a_results->getModeValue();
        
        $res[] = $a_results->getModeValueAsText();
        $res[] = (int) $a_results->getModeNrOfSelections();
        
        // :TODO:
        $res[] = $a_results->getMedianAsText();
        
        $res[] = $a_results->getMean();
                
        return array($res);
    }
        
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
                $lng->txt("title"),
                $lng->txt("value"),
                $lng->txt("category_nr_selected"),
                $lng->txt("svy_fraction_of_selections")
            ),
            "rows" => array()
        );
        
        $vars = $a_results->getVariables();
        if ($vars) {
            foreach ($vars as $var) {
                $res["rows"][] = array(
                    $var->cat->title,
                    $var->cat->scale,
                    $var->abs,
                    $var->perc
                        ? sprintf("%.2f", $var->perc*100) . "%"
                        : "0%"
                );
            }
        }
        
        return $res;
    }
    
    /**
     * Get title columns for user-specific export
     *
     * @param array $a_title_row
     * @param array $a_title_row2
     * @param bool $a_do_title
     * @param bool $a_do_label
     */
    public function getUserSpecificVariableTitles(array &$a_title_row, array &$a_title_row2, $a_do_title, $a_do_label)
    {
        // type-specific
    }
    
    /**
     *
     *
     * @param array $a_row
     * @param int $a_user_id
     * @param ilSurveyEvaluationResults|array $a_results
     */
    abstract public function addUserSpecificResults(array &$a_row, $a_user_id, $a_results);
}
