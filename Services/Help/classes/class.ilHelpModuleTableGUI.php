<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for help modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesHelp
 */
class ilHelpModuleTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->setId("help_mods");
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->getHelpModules();
		$this->setTitle($lng->txt("help_modules"));
		
		$this->addColumn("", "", "1");
		$this->addColumn($this->lng->txt("title"));
		$this->addColumn($this->lng->txt("help_imported_on"));
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.help_module_row.html", "Services/Help");

		$this->addMultiCommand("confirmHelpModulesDeletion", $lng->txt("delete"));
		//$this->addCommandButton("", $lng->txt(""));
	}
	
	/**
	 * Get help modules
	 */
	function getHelpModules()
	{
		$this->setData($this->parent_obj->object->getHelpModules());
	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilSetting, $ilCtrl;

		$ilCtrl->setParameter($this->parent_obj, "hm_id", $a_set["id"]);
		if ($a_set["id"] == $ilSetting->get("help_module"))
		{
			$this->tpl->setCurrentBlock("cmd");
			$this->tpl->setVariable("HREF_CMD",
				$ilCtrl->getLinkTarget($this->parent_obj, "deactivateModule"));
			$this->tpl->setVariable("TXT_CMD", $lng->txt("deactivate"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("cmd");
			$this->tpl->setVariable("HREF_CMD",
				$ilCtrl->getLinkTarget($this->parent_obj, "activateModule"));
			$this->tpl->setVariable("TXT_CMD", $lng->txt("activate"));
			$this->tpl->parseCurrentBlock();
		}
		$ilCtrl->setParameter($this->parent_obj, "hm_id", "");
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("CREATION_DATE",
			ilDatePresentation::formatDate(new ilDateTime($a_set["create_date"],IL_CAL_DATETIME)));
		$this->tpl->setVariable("ID", $a_set["id"]);
		
	}

}
?>
