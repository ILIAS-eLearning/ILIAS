<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for skill profile user assignment
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilSkillProfileUserTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_profile)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->profile = $a_profile;
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setData($this->profile->getAssignedUsers());
		$this->setTitle($lng->txt("skmg_assigned_users"));
		
		$this->addColumn("", "", "1px", true);
		$this->addColumn($this->lng->txt("lastname"), "lastname");
		$this->addColumn($this->lng->txt("firstname"), "firstname");
		$this->addColumn($this->lng->txt("login"), "login");
//		$this->addColumn($this->lng->txt("actions"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.profile_user_row.html", "Services/Skill");

		$this->addMultiCommand("confirmUserRemoval", $lng->txt("remove"));
		//$this->addCommandButton("", $lng->txt(""));
	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("LASTNAME", $a_set["lastname"]);
		$this->tpl->setVariable("FIRSTNAME", $a_set["firstname"]);
		$this->tpl->setVariable("LOGIN", $a_set["login"]);
		$this->tpl->setVariable("ID", $a_set["id"]);
	}

}
?>
