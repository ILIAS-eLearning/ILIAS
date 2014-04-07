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
	protected $tref_id = 0;
	
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_obj_id,
		$a_mode = self::MODE_SCAT, $a_tref_id = 0)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->tref_id = $a_tref_id;
		$ilCtrl->setParameter($a_parent_obj, "tmpmode", $a_mode);
		
		$this->mode = $a_mode;
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$this->skill_tree = new ilSkillTree();
		$this->obj_id = $a_obj_id;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		if ($this->mode == self::MODE_SCAT)
		{
			$childs = $this->skill_tree->getChildsByTypeFilter($a_obj_id,
				array("skrt", "skll", "scat", "sktr"));
			$childs = ilUtil::sortArray($childs, "order_nr", "asc", true);
			$this->setData($childs);
		}
		else if ($this->mode == self::MODE_SCTP)
		{
			$childs = $this->skill_tree->getChildsByTypeFilter($a_obj_id,
				array("skrt", "sktp", "sctp"));
			$childs = ilUtil::sortArray($childs, "order_nr", "asc", true);
			$this->setData($childs);
		}
		
		if ($this->obj_id != $this->skill_tree->readRootId())
		{
//			$this->setTitle(ilSkillTreeNode::_lookupTitle($this->obj_id));
		}
		$this->setTitle($lng->txt("skmg_items"));
		
		if ($this->tref_id == 0)
		{
			$this->addColumn($this->lng->txt(""), "", "1px", true);
		}
		$this->addColumn($this->lng->txt("type"), "", "1px");
		if ($this->tref_id == 0)
		{
			$this->addColumn($this->lng->txt("skmg_order"), "", "1px");
		}
		$this->addColumn($this->lng->txt("title"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.skill_cat_row.html", "Services/Skill");

		if ($this->tref_id == 0)
		{
			$this->addMultiCommand("cutItems", $lng->txt("cut"));
			$this->addMultiCommand("copyItems", $lng->txt("copy"));
			$this->addMultiCommand("deleteNodes", $lng->txt("delete"));
			$this->addCommandButton("saveOrder", $lng->txt("skmg_save_order"));
		}
	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		switch($a_set["type"])
		{
			// category
			case "scat":
				$ilCtrl->setParameterByClass("ilskillcategorygui", "obj_id", $a_set["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilskillcategorygui", "listItems");
				$ilCtrl->setParameterByClass("ilskillcategorygui", "obj_id", $_GET["obj_id"]);
				break;
				
			// skill template reference
			case "sktr":
				$tid = ilSkillTemplateReference::_lookupTemplateId($a_set["child"]);
				$ilCtrl->setParameterByClass("ilskilltemplatereferencegui", "tref_id", $a_set["child"]);
				$ilCtrl->setParameterByClass("ilskilltemplatereferencegui", "obj_id", $tid);
				$ret = $ilCtrl->getLinkTargetByClass("ilskilltemplatereferencegui", "listItems");
				$ilCtrl->setParameterByClass("ilskilltemplatereferencegui", "obj_id", $_GET["obj_id"]);
				$ilCtrl->setParameterByClass("ilskilltemplatereferencegui", "tref_id", $_GET["tref_id"]);
				break;
				
			// skill
			case "skll":
				$ilCtrl->setParameterByClass("ilbasicskillgui", "obj_id", $a_set["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilbasicskillgui", "edit");
				$ilCtrl->setParameterByClass("ilbasicskillgui", "obj_id", $_GET["obj_id"]);
				break;
				
			// --------
				
			// template
			case "sktp":
				$ilCtrl->setParameterByClass("ilbasicskilltemplategui", "obj_id", $a_set["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilbasicskilltemplategui", "edit");
				$ilCtrl->setParameterByClass("ilbasicskilltemplategui", "obj_id", $_GET["obj_id"]);
				break;

			// template category
			case "sctp":
				$ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "obj_id", $a_set["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilskilltemplatecategorygui", "listItems");
				$ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "obj_id", $_GET["obj_id"]);
				break;
		}

		if ($this->tref_id == 0)
		{
			$this->tpl->setCurrentBlock("cb");
			$this->tpl->setVariable("CB_ID", $a_set["child"]);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("nr");
			$this->tpl->setVariable("OBJ_ID", $a_set["child"]);
			$this->tpl->setVariable("ORDER_NR", $a_set["order_nr"]);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable("HREF_TITLE", $ret);
		
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$icon = ilSkillTreeNode::getIconPath($a_set["child"],
			$a_set["type"], "", ilSkillTreeNode::_lookupStatus($a_set["child"]));
		$this->tpl->setVariable("ICON", ilUtil::img($icon, ""));
	}

}
?>
