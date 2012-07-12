<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for system style to category assignments
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilSysStyleCatAssignmentTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->getStyleCatAssignments();
		$this->setTitle($lng->txt(""));
		
		$style_arr = explode(":", $_GET["style_id"]);
		$this->skin_id = $style_arr[0];
		$this->style_id = $style_arr[1];
		
		$this->addColumn($this->lng->txt(""), "", "1");
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl..html", "");

		$this->addMultiCommand("", $lng->txt(""));
		$this->addCommandButton("", $lng->txt(""));
	}
	
	/**
	 * Get style assignments
	 *
	 * @param
	 * @return
	 */
	function getStyleCatAssignments()
	{
		$stdef = new ilStyleDefinition($this->skin_id);
//var_dump($stdef);

//		$this->setData();
	}
	
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		//$this->tpl->setVariable("", );
	}

}
?>
