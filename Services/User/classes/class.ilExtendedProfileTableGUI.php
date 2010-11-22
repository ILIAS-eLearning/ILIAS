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

		include_once("./Services/User/classes/class.ilExtPublicProfilePage.php");
		$this->setData(ilExtPublicProfilePage::getPagesOfUser($ilUser->getId()));
		
		$this->setTitle($lng->txt("pages"));

		$this->addColumn($this->lng->txt(""), "", "1");
		$this->addColumn($this->lng->txt("user_order"));
		$this->addColumn($this->lng->txt("title"));
		$this->addColumn($this->lng->txt("actions"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.ext_user_profile_row.html", "Services/User");

		$this->addMultiCommand("confirmProfilePageDeletion", $lng->txt("delete"));
		$this->addCommandButton("saveExtProfilePagesOrdering",
			$lng->txt("user_save_ordering_and_titles"));
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $lng, $ilCtrl;

		$this->tpl->setVariable("VAL_TITLE", ilUtil::prepareFormOutput($a_set["title"]));
		$this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
		$ilCtrl->setParameterByClass("ilextpublicprofilepagegui",
			"user_page", $a_set["id"]);
		$this->tpl->setVariable("CMD_EDIT",
			$ilCtrl->getLinkTargetByClass("ilextpublicprofilepagegui", "edit"));
		$this->tpl->setVariable("ID", $a_set["id"]);
		$this->tpl->setVariable("VAL_ORDER_NR", $a_set["order_nr"]);
	}
	
}?>
