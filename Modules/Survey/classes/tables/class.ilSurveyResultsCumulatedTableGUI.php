<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesSurvey
*/

class ilSurveyResultsCumulatedTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd, array $a_results)
    {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->setId("svy_cum");
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        $this->addColumn($this->lng->txt("title"));
        foreach ($this->getSelectedColumns() as $c) {
            if (strcmp($c, 'question') == 0) {
                $this->addColumn($this->lng->txt("question"));
            }
            if (strcmp($c, 'question_type') == 0) {
                $this->addColumn($this->lng->txt("question_type"));
            }
            if (strcmp($c, 'users_answered') == 0) {
                $this->addColumn($this->lng->txt("users_answered"));
            }
            if (strcmp($c, 'users_skipped') == 0) {
                $this->addColumn($this->lng->txt("users_skipped"));
            }
            if (strcmp($c, 'mode') == 0) {
                $this->addColumn($this->lng->txt("mode"));
            }
            if (strcmp($c, 'mode_nr_of_selections') == 0) {
                $this->addColumn($this->lng->txt("mode_nr_of_selections"));
            }
            if (strcmp($c, 'median') == 0) {
                $this->addColumn($this->lng->txt("median"));
            }
            if (strcmp($c, 'arithmetic_mean') == 0) {
                $this->addColumn($this->lng->txt("arithmetic_mean"));
            }
        }
    
        $this->setRowTemplate("tpl.il_svy_svy_results_cumulated_row.html", "Modules/Survey");
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setShowRowsSelector(false);

        $this->getItems($a_results);
    }

    public function getSelectableColumns()
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
    
    protected function getItems(array $a_results)
    {
        $data = array();
            
        foreach ($a_results as $question_res) {
            /* :TODO:
            $maxlen = 75;
            include_once "./Services/Utilities/classes/class.ilStr.php";
            if (ilStr::strlen($questiontext) > $maxlen + 3)
            {
                $questiontext = ilStr::substr($questiontext, 0, $maxlen) . "...";
            }
            */
            
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
    
    public function numericOrdering($a_field)
    {
        return !in_array($a_field, array("question", "question_type"));
    }

    /**
     * fill row
     *
     * @access public
     * @param
     * @return
     */
    public function fillRow($data)
    {
        $this->tpl->setVariable("TITLE", $data['title']);
    
        foreach ($this->getSelectedColumns() as $c) {
            if (strcmp($c, 'question') == 0) {
                $this->tpl->setCurrentBlock('question');
                $this->tpl->setVariable("QUESTION", $data['question']);
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'question_type') == 0) {
                $this->tpl->setCurrentBlock('question_type');
                $this->tpl->setVariable("QUESTION_TYPE", trim($data['question_type']));
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'users_answered') == 0) {
                $this->tpl->setCurrentBlock('users_answered');
                $this->tpl->setVariable("USERS_ANSWERED", trim($data['users_answered']));
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'users_skipped') == 0) {
                $this->tpl->setCurrentBlock('users_skipped');
                $this->tpl->setVariable("USERS_SKIPPED", trim($data['users_skipped']));
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'mode') == 0) {
                $this->tpl->setCurrentBlock('mode');
                $this->tpl->setVariable("MODE", trim($data['mode']));
                // : $this->lng->txt("survey_not_available")
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'mode_nr_of_selections') == 0) {
                $this->tpl->setCurrentBlock('mode_nr_of_selections');
                $this->tpl->setVariable("MODE_NR_OF_SELECTIONS", trim($data['mode_nr_of_selections']));
                // : $this->lng->txt("survey_not_available")
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'median') == 0) {
                $this->tpl->setCurrentBlock('median');
                $this->tpl->setVariable("MEDIAN", trim($data['median']));
                // : $this->lng->txt("survey_not_available")
                $this->tpl->parseCurrentBlock();
            }
            if (strcmp($c, 'arithmetic_mean') == 0) {
                $this->tpl->setCurrentBlock('arithmetic_mean');
                $this->tpl->setVariable("ARITHMETIC_MEAN", trim($data['arithmetic_mean']));
                // : $this->lng->txt("survey_not_available");
                $this->tpl->parseCurrentBlock();
            }
        }
        
        /*
        if($data["subitems"])
        {
            $this->tpl->setCurrentBlock("tbl_content");
            $this->tpl->parseCurrentBlock();

            foreach($data["subitems"] as $subitem)
            {
                $this->fillRow($subitem);

                $this->tpl->setCurrentBlock("tbl_content");
                $this->css_row = ($this->css_row != "tblrow1")
                    ? "tblrow1"
                    : "tblrow2";
                $this->tpl->setVariable("CSS_ROW", $this->css_row);
                $this->tpl->parseCurrentBlock();
            }
        }
        */
    }
}
