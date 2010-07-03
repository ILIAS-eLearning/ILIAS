<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Extended user profile table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilExtendedProfileTableGUI extends ilTable2GUI
{

	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $ilUser;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		include_once("./Services/User/classes/class.ilExtendedPublicProfile.php");
		$this->setData(ilExtendedPublicProfile::getTabsOfUser($ilUser->getId()));
		
		$this->setTitle($lng->txt("tabs"));

		$this->addColumn($this->lng->txt(""), "", "1");
		$this->addColumn($this->lng->txt("order"));
		$this->addColumn($this->lng->txt("title"));
		$this->addColumn($this->lng->txt("last_change"));
		$this->addColumn($this->lng->txt("actions"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.ext_user_profile_row.html", "Services/User");

		$this->addMultiCommand("deleteExtProfileTab", $lng->txt("delete"));
		//$this->addCommandButton("", $lng->txt(""));
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

//		$this->tpl->setVariable("", );
	}
	
}?>
