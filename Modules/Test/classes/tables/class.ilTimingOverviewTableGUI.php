<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');

class ilTimingOverviewTableGUI extends ilTable2GUI
{
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $lng, $ilCtrl;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;

		$this->setTitle($this->lng->txt('timing'));
		$this->setRowTemplate("tpl.il_as_tst_timing_overview_row.html", "Modules/Test");
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
	
		$this->addColumn($this->lng->txt("login"),'login', '');
		$this->addColumn($this->lng->txt("name"),'name', '');
		$this->addColumn($this->lng->txt("tst_started"),'started', '');
		$this->addColumn($this->lng->txt("timing"),'extratime', '');
		
		$this->addCommandButton('showTimingForm', $this->lng->txt('timing'));
	}

	public function fillRow($data)
	{
		$this->tpl->setVariable("LOGIN", $data['login']);
		$this->tpl->setVariable("NAME", $data['name']);
		$this->tpl->setVariable("STARTED", $data['started']);
		$this->tpl->setVariable("EXTRATIME", $data['extratime']);		
	}
}
