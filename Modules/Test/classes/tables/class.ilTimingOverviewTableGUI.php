<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * Class ilTimingOverviewTableGUI
 */
class ilTimingOverviewTableGUI extends ilTable2GUI
{
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($this->lng->txt('timing'));
        $this->setRowTemplate("tpl.il_as_tst_timing_overview_row.html", "Modules/Test");
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
    
        $this->addColumn($this->lng->txt("login"), 'login', '');
        $this->addColumn($this->lng->txt("name"), 'name', '');
        $this->addColumn($this->lng->txt("tst_started"), 'started', '');
        $this->addColumn($this->lng->txt("timing"), 'extratime', '');
    }

    public function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("LOGIN", $a_set['login']);
        $this->tpl->setVariable("NAME", $a_set['name']);
        $this->tpl->setVariable("STARTED", $a_set['started']);
        $this->tpl->setVariable("EXTRATIME", ilDatePresentation::secondsToString($a_set['extratime'] * 60));
    }
}
