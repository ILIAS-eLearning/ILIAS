<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesTest
*/

class ilAssessmentFolderLogTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        parent::__construct($a_parent_obj, $a_parent_cmd);

        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->counter = 1;
        
        $this->setFormName('showlog');
        $this->setStyle('table', 'fullwidth');

        $this->addColumn($this->lng->txt("assessment_log_datetime"), 'date', '10%');
        $this->addColumn($this->lng->txt("user"), 'user', '20%');
        $this->addColumn($this->lng->txt("assessment_log_text"), 'message', '50%');
        $this->addColumn($this->lng->txt("ass_location"), '', '20%');
    
        $this->setRowTemplate("tpl.il_as_tst_assessment_log_row.html", "Modules/Test");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->setDefaultOrderField("date");
        $this->setDefaultOrderDirection("asc");
        
        $this->enable('header');
        $this->enable('sort');
        $this->disable('select_all');
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
        $this->tpl->setVariable("DATE", ilDatePresentation::formatDate(new ilDateTime($data['tstamp'], IL_CAL_UNIX)));
        $user = ilObjUser::_lookupName($data["user_fi"]);
        $this->tpl->setVariable("USER", ilUtil::prepareFormOutput(trim($user["title"] . " " . $user["firstname"] . " " . $user["lastname"])));
        $title = "";
        if ($data["question_fi"] || $data["original_fi"]) {
            include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
            $title = assQuestion::_getQuestionTitle($data["question_fi"]);
            if (strlen($title) == 0) {
                $title = assQuestion::_getQuestionTitle($data["original_fi"]);
            }
            $title = $this->lng->txt("assessment_log_question") . ": " . $title;
        }
        $this->tpl->setVariable("MESSAGE", ilUtil::prepareFormOutput($data['logtext']) . ((strlen($title)) ?  " (" . $title . ")" : ''));

        if (strlen($data['location_href']) > 0 && strlen($data['location_txt']) > 0) {
            $this->tpl->setVariable("LOCATION_HREF", $data['location_href']);
            $this->tpl->setVariable("LOCATION_TXT", $data['location_txt']);
        }
    }
}
