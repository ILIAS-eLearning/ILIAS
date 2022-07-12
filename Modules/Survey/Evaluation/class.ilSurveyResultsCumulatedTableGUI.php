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
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyResultsCumulatedTableGUI extends ilTable2GUI
{
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        array $a_results
    ) {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->setId("svy_cum");
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        $this->addColumn($this->lng->txt("title"));
        foreach ($this->getSelectedColumns() as $c) {
            if (strcmp($c, 'question') === 0) {
                $this->addColumn($this->lng->txt("question"));
            }
            if (strcmp($c, 'question_type') === 0) {
                $this->addColumn($this->lng->txt("question_type"));
            }
            if (strcmp($c, 'users_answered') === 0) {
                $this->addColumn($this->lng->txt("users_answered"));
            }
            if (strcmp($c, 'users_skipped') === 0) {
                $this->addColumn($this->lng->txt("users_skipped"));
            }
            if (strcmp($c, 'mode') === 0) {
                $this->addColumn($this->lng->txt("mode"));
            }
            if (strcmp($c, 'mode_nr_of_selections') === 0) {
                $this->addColumn($this->lng->txt("mode_nr_of_selections"));
            }
            if (strcmp($c, 'median') === 0) {
                $this->addColumn($this->lng->txt("median"));
            }
            if (strcmp($c, 'arithmetic_mean') === 0) {
                $this->addColumn($this->lng->txt("arithmetic_mean"));
            }
        }
    
        $this->setRowTemplate(
            "tpl.il_svy_svy_results_cumulated_row.html",
            "Modules/Survey/Evaluation"
        );
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setShowRowsSelector(false);

        $this->getItems($a_results);
    }

    public function getSelectableColumns() : array
    {
        $lng = $this->lng;
        $cols["question"] = array(
            "txt" => $lng->txt("question"),
            "default" => true
        );
        $cols["question_type"] = array(
            "txt" => $lng->txt("question_type"),
            "default" => true
        );
        $cols["users_answered"] = array(
            "txt" => $lng->txt("users_answered"),
            "default" => true
        );
        $cols["users_skipped"] = array(
            "txt" => $lng->txt("users_skipped"),
            "default" => true
        );
        $cols["mode"] = array(
            "txt" => $lng->txt("mode"),
            "default" => false
        );
        $cols["mode_nr_of_selections"] = array(
            "txt" => $lng->txt("mode_nr_of_selections"),
            "default" => false
        );
        $cols["median"] = array(
            "txt" => $lng->txt("median"),
            "default" => true
        );
        $cols["arithmetic_mean"] = array(
            "txt" => $lng->txt("arithmetic_mean"),
            "default" => true
        );
        return $cols;
    }
    
    protected function getItems(
        array $a_results
    ) : void {
        $data = array();
            
        foreach ($a_results as $question_res) {
            if (!is_array($question_res)) {
                $question = $question_res->getQuestion();
                
                $data[] = array(
                    "title" => $question->getTitle(),
                    "question" => strip_tags($question->getQuestiontext()),
                    "question_type" => SurveyQuestion::_getQuestionTypeName($question->getQuestionType()),
                    "users_answered" => $question_res->getUsersAnswered(),
                    "users_skipped" => $question_res->getUsersSkipped(),
                    "mode" => $question_res->getModeValueAsText(),
                    "mode_nr_of_selections" => $question_res->getModeNrOfSelections(),
                    "median" => $question_res->getMedianAsText(),
                    "arithmetic_mean" => $question_res->getMean()
                );
            }
            // matrix
            else {
                // :TODO: $question->getQuestiontext() ?
                // :TODO: should there be overall figures?
                
                foreach ($question_res as $idx => $item) {
                    $row_title = $item[0];
                    $row_res = $item[1];
                    $question = $row_res->getQuestion();
                    
                    $data[] = array(
                        "title" => $question->getTitle(),
                        "question" => $row_title,
                        "question_type" => SurveyQuestion::_getQuestionTypeName($question->getQuestionType()),
                        "users_answered" => $row_res->getUsersAnswered(),
                        "users_skipped" => $row_res->getUsersSkipped(),
                        "mode" => $row_res->getModeValueAsText(),
                        "mode_nr_of_selections" => $row_res->getModeNrOfSelections(),
                        "median" => $row_res->getMedianAsText(),
                        "arithmetic_mean" => $row_res->getMean()
                    );
                }
            }
        }
        
        $this->setData($data);
    }
    
    public function numericOrdering(string $a_field) : bool
    {
        return !in_array($a_field, array("question", "question_type"));
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("TITLE", $a_set['title']);
    
        foreach ($this->getSelectedColumns() as $c) {
            if (strcmp($c, 'question') === 0) {
                $this->tpl->setCurrentBlock('question');
                $this->tpl->setVariable("QUESTION", $a_set['question']);
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'question_type') === 0) {
                $this->tpl->setCurrentBlock('question_type');
                $this->tpl->setVariable("QUESTION_TYPE", trim($a_set['question_type']));
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'users_answered') === 0) {
                $this->tpl->setCurrentBlock('users_answered');
                $this->tpl->setVariable("USERS_ANSWERED", trim($a_set['users_answered']));
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'users_skipped') === 0) {
                $this->tpl->setCurrentBlock('users_skipped');
                $this->tpl->setVariable("USERS_SKIPPED", trim($a_set['users_skipped']));
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'mode') === 0) {
                $this->tpl->setCurrentBlock('mode');
                $this->tpl->setVariable("MODE", trim($a_set['mode']));
                // : $this->lng->txt("survey_not_available")
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'mode_nr_of_selections') === 0) {
                $this->tpl->setCurrentBlock('mode_nr_of_selections');
                $this->tpl->setVariable("MODE_NR_OF_SELECTIONS", trim($a_set['mode_nr_of_selections']));
                // : $this->lng->txt("survey_not_available")
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'median') === 0) {
                $this->tpl->setCurrentBlock('median');
                $this->tpl->setVariable("MEDIAN", trim($a_set['median']));
                // : $this->lng->txt("survey_not_available")
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'arithmetic_mean') === 0) {
                $this->tpl->setCurrentBlock('arithmetic_mean');
                $this->tpl->setVariable("ARITHMETIC_MEAN", trim($a_set['arithmetic_mean']));
                // : $this->lng->txt("survey_not_available");
                $this->tpl->parseCurrentBlock();
            }
        }
    }
}
