<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI class for found users in survey administration
 *
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilFoundUsersTableGUI extends ilTable2GUI
{

	function __construct($a_parent_obj, $a_parent_cmd = "")
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$ilCtrl = $DIC->ctrl();
		$lng = $DIC->language();
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn("", "f", "1");
		$this->addColumn($lng->txt("login"), "", "33%");
		$this->addColumn($lng->txt("firstname"), "", "33%");
		$this->addColumn($lng->txt("lastname"), "", "33%");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.table_found_users_row.html", "Modules/Survey");
		$this->setDefaultOrderField("lastname");
		$this->setDefaultOrderDirection("asc");
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		$ilCtrl = $this->ctrl;
		$ilCtrl->setParameterByClass("ilObjSurveyAdministrationGUI", "item_id", $a_set["usr_id"]);
		$this->tpl->setVariable("USER_ID", $a_set["usr_id"]);
		$this->tpl->setVariable("LOGIN", $a_set["login"]);
		$this->tpl->setVariable("FIRSTNAME", $a_set["firstname"]);
		$this->tpl->setVariable("LASTNAME", $a_set["lastname"]);
	}

}
?>
