<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilSkillCatTableGUI extends ilTable2GUI
{
	const MODE_SCAT = 0;
	const MODE_SCTP = 1;
	
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_obj_id,
		$a_mode = self::MODE_SCAT)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->mode = $a_mode;
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$this->skill_tree = new ilSkillTree();
		$this->obj_id = $a_obj_id;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		if ($this->mode == self::MODE_SCAT)
		{
			$this->setData($this->skill_tree->getChildsByTypeFilter($a_obj_id,
				array("skrt", "skll", "scat", "sktr")));
//			$this->setTitle($lng->txt("skmg_skills"));
		}
		else if ($this->mode == self::MODE_SCTP)
		{
			$this->setData($this->skill_tree->getChildsByTypeFilter($a_obj_id,
				array("skrt", "sktp", "sctp")));
//			$this->setTitle($lng->txt("skmg_skill_templates"));
		}
		
		if ($this->obj_id != $this->skill_tree->readRootId())
		{
//			$this->setTitle(ilSkillTreeNode::_lookupTitle($this->obj_id));
		}
		$this->setTitle($lng->txt("skmg_items"));
		
		$this->addColumn($this->lng->txt(""), "", "1px", true);
		$this->addColumn($this->lng->txt("type"), "", "1px");
		$this->addColumn($this->lng->txt("skmg_order"), "", "1px");
		$this->addColumn($this->lng->txt("title"));
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.skill_cat_row.html", "Services/Skill");

		$this->addMultiCommand("deleteNodes", $lng->txt("delete"));
		$this->addMultiCommand("cutItems", $lng->txt("cut"));
		$this->addMultiCommand("copyItems", $lng->txt("copy"));
		$this->addCommandButton("saveOrder", $lng->txt("skmg_save_order"));

	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("OBJ_ID", $a_set["child"]);
		$this->tpl->setVariable("ORDER_NR", $a_set["order_nr"]);
		$this->tpl->setVariable("ICON",
			ilUtil::img(ilUtil::getImagePath("icon_".$a_set["type"].".gif"),
				""));
	}

}
?>
