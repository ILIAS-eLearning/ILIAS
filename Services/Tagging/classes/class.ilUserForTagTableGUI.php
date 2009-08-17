<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Services/Tagging/classes/class.ilTagging.php");

/**
* Show all users for a tag
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesTagging
*/
class ilUserForTagTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_tag)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setData(ilTagging::getUsersForTag($a_tag));
		$this->setTitle($lng->txt("tagging_users_using_tag"));
		
		$this->addColumn($this->lng->txt("user"), "");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.user_for_tag_row.html", "Services/Tagging");
		//$this->disable("footer");
		$this->setEnableTitle(true);

		//$this->addMultiCommand("", $lng->txt(""));
		//$this->addCommandButton("", $lng->txt(""));
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $lng;

		include_once("./Services/User/classes/class.ilUserUtil.php");
		$this->tpl->setVariable("USER",
			ilUserUtil::getNamePresentation($a_set["id"], true, false, "", true));
	}

}
?>
