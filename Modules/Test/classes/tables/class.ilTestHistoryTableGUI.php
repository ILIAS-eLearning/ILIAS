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

class ilTestHistoryTableGUI extends ilTable2GUI
{
    protected $tstObject;
    
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
    
        $this->setFormName('questionbrowser');
        $this->setStyle('table', 'fullwidth');

        $this->addColumn($this->lng->txt("assessment_log_datetime"), 'datetime', '25%');
        $this->addColumn($this->lng->txt("user"), 'user', '25%');
        $this->addColumn($this->lng->txt("assessment_log_text"), 'log', '50%');

        $this->setRowTemplate("tpl.il_as_tst_history_row.html", "Modules/Test");

        $this->setDefaultOrderField("datetime");
        $this->setDefaultOrderDirection("asc");
        
        $this->enable('header');
    }

    public function setTestObject($obj)
    {
        $this->tstObject = $obj;
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
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];

        $username = $this->tstObject->userLookupFullName($data["user_fi"], true);
        $this->tpl->setVariable("DATETIME", ilDatePresentation::formatDate(new ilDateTime($data["tstamp"], IL_CAL_UNIX)));
        $this->tpl->setVariable("USER", $username);
        $this->tpl->setVariable("LOG", trim(ilUtil::prepareFormOutput($data["logtext"])));
    }
}
