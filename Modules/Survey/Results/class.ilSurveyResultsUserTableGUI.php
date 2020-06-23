<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyResultsUserTableGUI extends ilTable2GUI
{
    private $is_anonymized;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $is_anonymized)
    {
        global $DIC;

        $this->setId("svy_usr");
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->is_anonymized = $is_anonymized;
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
    
        $this->setRowTemplate("tpl.il_svy_svy_results_user_row.html", "Modules/Survey");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        
        $this->setDefaultOrderField('username');
        
        $this->setShowRowsSelector(true);

        $this->enable('header');
        $this->disable('select_all');
    }
    
    protected function formatTime($timeinseconds)
    {
        if (is_null($timeinseconds)) {
            return " ";
        } elseif ($timeinseconds == 0) {
            return $this->lng->txt('not_available');
        } else {
            return sprintf("%02d:%02d:%02d", ($timeinseconds / 3600), ($timeinseconds / 60) % 60, $timeinseconds % 60);
        }
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
        $this->tpl->setVariable("USERNAME", $data['username']);
        $this->tpl->setVariable("QUESTION", $data['question']);
        $results = array_map(function ($i) {
            return htmlentities($i);
        }, $data["results"]);
        $this->tpl->setVariable("RESULTS", $results
            ? implode("<br />", $results)
            : ilObjSurvey::getSurveySkippedValue());
        $this->tpl->setVariable("WORKINGTIME", $this->formatTime($data['workingtime']));
        
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
