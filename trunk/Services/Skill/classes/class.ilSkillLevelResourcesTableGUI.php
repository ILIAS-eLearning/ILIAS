<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for skill level resources
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilSkillLevelResourcesTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_skill_id, $a_tref_id,
		$a_level_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->level_id = $a_level_id;
		
		include_once("./Services/Skill/classes/class.ilSkillResources.php");
		$this->resources = new ilSkillResources($a_skill_id, $a_tref_id);
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setData($this->resources->getResourcesOfLevel($a_level_id));
		$this->setTitle($lng->txt("resources"));
		
		$this->addColumn("", "", "1px", true);
		$this->addColumn($this->lng->txt("type"), "", "1px");
		$this->addColumn($this->lng->txt("title"), "");
		$this->addColumn($this->lng->txt("path"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.level_resources_row.html", "Services/Skill");

		$this->addMultiCommand("confirmLevelResourcesRemoval", $lng->txt("remove"));
		//$this->addCommandButton("", $lng->txt(""));
	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $tree;

		$ref_id = $a_set["rep_ref_id"];
		$obj_id = ilObject::_lookupObjId($ref_id);
		
		$this->tpl->setVariable("TITLE", ilObject::_lookupTitle($obj_id));
		$this->tpl->setVariable("IMG", ilUtil::img(ilObject::_getIcon($obj_id, "tiny")));
		$this->tpl->setVariable("ID", $ref_id);
		
		$path = $tree->getPathFull($ref_id);
		$path_items = array();
		foreach ($path as $p)
		{
			if ($p["type"] != "root" && $p["child"] != $ref_id)
			{
				$path_items[] = $p["title"];
			}
		}
		$this->tpl->setVariable("PATH", implode($path_items, " > "));
	}

}
?>
