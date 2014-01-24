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
		
		
		$style_arr = explode(":", $_GET["style_id"]);
		$this->skin_id = $style_arr[0];
		$this->style_id = $style_arr[1];
		
		$this->getStyleCatAssignments();
		$this->setTitle($lng->txt("sty_cat_assignments").", ".
			$this->skin_id."/".$this->style_id);
		
		$this->addColumn("", "", "1");
		$this->addColumn($this->lng->txt("sty_substyle"));
		$this->addColumn($this->lng->txt("obj_cat"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.sty_cat_ass_row.html", "Services/Style");

		$this->addMultiCommand("deleteSysStyleCatAssignments", $lng->txt("delete"));
	}
	
	/**
	 * Get style assignments
	 *
	 * @param
	 * @return
	 */
	function getStyleCatAssignments()
	{
		$this->setData(ilStyleDefinition::getSystemStyleCategoryAssignments($this->skin_id, $this->style_id));
	}
	
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("REF_ID", $a_set["ref_id"]);
		$this->tpl->setVariable("SUBSTYLE", $a_set["substyle"]);
		$this->tpl->setVariable("CATEGORY",
			ilObject::_lookupTitle(ilObject::_lookupObjId($a_set["ref_id"])));
	}

}
?>
