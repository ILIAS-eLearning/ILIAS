<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for skill usages
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilSkillUsageTableGUI extends ilTable2GUI
{

	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_usage)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		$data = array();
		foreach ($a_usage as $k => $v)
		{
			$data[] = array("type" => $k, "usages" => $v);
		}

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setData($data);
		$this->setTitle($lng->txt("skmg_usage").": ");
		$this->addColumn($this->lng->txt("skmg_type"));
		$this->addColumn($this->lng->txt("skmg_number"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.skill_usage_row.html", "Services/Skill");

//		$this->addMultiCommand("", $lng->txt(""));
//		$this->addCommandButton("", $lng->txt(""));
	}


	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;
//var_dump($a_set);
		$this->tpl->setVariable("TYPE_INFO", ilSkillUsage::getTypeInfoString($a_set["type"]));
		$this->tpl->setVariable("NUMBER", count($a_set["usages"]));
		$this->tpl->setVariable("OBJ_TYPE", ilSkillUsage::getObjTypeString($a_set["type"]));
	}

}
?>