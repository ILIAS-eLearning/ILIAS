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
		$this->addColumn($this->lng->txt("create_date"));
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
		global $lng;

		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("ID", $a_set["id"]);
	}

}
?>
