<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyResultsUserTableGUI extends ilTable2GUI
{
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;

        $this->setId("svy_usr");
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->counter = 1;
        
        $this->setFormName('invitegroups');
        $this->setStyle('table', 'fullwidth');

        $this->addColumn($this->lng->txt("username"), 'username', '');
        $this->addColumn($this->lng->txt("question"), '', '');
        $this->addColumn($this->lng->txt("results"), '', '');
        $this->addColumn($this->lng->txt("workingtime"), 'workingtime', '');
        $this->addColumn($this->lng->txt("survey_results_finished"), 'finished', '');
    
        $this->setRowTemplate(
            "tpl.il_svy_svy_results_user_row.html",
            "Modules/Survey/Evaluation"
        );

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        
        $this->setDefaultOrderField('username');
        
        $this->setShowRowsSelector(true);

        $this->enable('header');
        $this->disable('select_all');
    }
    
    protected function formatTime(?int $timeinseconds) : string
    {
        if (is_null($timeinseconds)) {
            return " ";
        } elseif ($timeinseconds == 0) {
            return $this->lng->txt('not_available');
        } else {
            return sprintf("%02d:%02d:%02d", ($timeinseconds / 3600), ($timeinseconds / 60) % 60, $timeinseconds % 60);
        }
    }

    public function fillRow($data)
    {
        $this->tpl->setVariable("USERNAME", $data['username']);
        $this->tpl->setVariable("QUESTION", $data['question']);
        $results = array_map(function ($i) {
            return htmlentities($i);
        }, $data["results"]);
        $this->tpl->setVariable("RESULTS", $results
            ? implode("<br />", $results)
            : ilObjSurvey::getSurveySkippedValue());
        $this->tpl->setVariable("WORKINGTIME", $this->formatTime($data['workingtime']));
        $finished = "";
        if ($data["finished"] !== null) {
            if ($data["finished"] !== false) {
                $finished .= ilDatePresentation::formatDate(new ilDateTime($data["finished"], IL_CAL_UNIX));
            } else {
                $finished = "-";
            }
            $this->tpl->setVariable("FINISHED", $finished);
        } else {
            $this->tpl->setVariable("FINISHED", "&nbsp;");
        }
        
        if ($data["subitems"]) {
            $this->tpl->setCurrentBlock("tbl_content");
            $this->tpl->parseCurrentBlock();
            
            foreach ($data["subitems"] as $subitem) {
                $this->fillRow($subitem);
                
                $this->tpl->setCurrentBlock("tbl_content");
                $this->css_row = ($this->css_row != "tblrow1")
                    ? "tblrow1"
                    : "tblrow2";
                $this->tpl->setVariable("CSS_ROW", $this->css_row);
                $this->tpl->parseCurrentBlock();
            }
        }
    }
}
