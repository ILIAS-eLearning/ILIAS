<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilHierarchyFormGUI.php");

/**
 * This class allows quick editing of the skill hierarchy
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup	ServicesSkill
 */
class ilSkillHFormGUI extends ilHierarchyFormGUI
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		global $lng;
		
		parent::__construct();
		$this->setCheckboxName("id");
		$lng->loadLanguageModule("skmg");
		$this->setExpandVariable("skexpand");
		$this->setTypeWhiteList(array("skrt", "skll", "scat", "sktr"));
	}
	
	/**
	 * Get menu items
	 */
	function getMenuItems($a_node, $a_depth, $a_first_child = false, $a_next_sibling = null, $a_childs)
	{
		global $lng, $ilUser;
		
		$cmds = array();
		
		if (!$a_first_child)		// drop area of node
		{
			// page inserts
			if ($a_node["type"] == "skll" || $a_node["type"] == "scat")
			{
				$cmds[] = array("text" => $lng->txt("skmg_insert_basic_skill"), "cmd" => "insertBasicSkill", "multi" => 10);
				$cmds[] = array("text" => $lng->txt("skmg_insert_skill_template_reference"), "cmd" => "insertSkillTemplateReference");
				$cmds[] = array("text" => $lng->txt("skmg_insert_skill_category"), "cmd" => "insertSkillCategory", "multi" => 10);
				if ($ilUser->clipboardHasObjectsOfType("skll"))
				{
					$cmds[] = array("text" => $lng->txt("skmg_insert_basic_skill_from_clip"),
						"cmd" => "insertBasicSkillClip", "as_subitem" => false);
				}
				if ($ilUser->clipboardHasObjectsOfType("sktr"))
				{
					$cmds[] = array("text" => $lng->txt("skmg_insert_skill_template_reference_from_clip"),
						"cmd" => "insertSkillTemplateReferenceClip", "as_subitem" => false);
				}
				if ($ilUser->clipboardHasObjectsOfType("scat"))
				{
					$cmds[] = array("text" => $lng->txt("skmg_insert_skill_category_from_clip"),
						"cmd" => "insertSkillCategoryClip", "as_subitem" => false);
				}
			}

			if ($a_node["type"] == "scat" || $a_node["type"] == "skrt")
			{
				$cmds[] = array("text" => $lng->txt("skmg_insert_basic_skill_child"), "cmd" => "insertBasicSkill", "multi" => 10,
					"as_subitem" => true);
				$cmds[] = array("text" => $lng->txt("skmg_insert_skill_template_reference_child"), "cmd" => "insertSkillTemplateReference",
					"as_subitem" => true);
				$cmds[] = array("text" => $lng->txt("skmg_insert_skill_category_child"), "cmd" => "insertSkillCategory", "multi" => 10,
					"as_subitem" => true);
				if ($ilUser->clipboardHasObjectsOfType("skll"))
				{
					$cmds[] = array("text" => $lng->txt("skmg_insert_basic_skill_from_clip_child"),
						"cmd" => "insertBasicSkillClip", "as_subitem" => true);
				}
				if ($ilUser->clipboardHasObjectsOfType("sktr"))
				{
					$cmds[] = array("text" => $lng->txt("skmg_insert_skill_template_reference_from_clip_child"),
						"cmd" => "insertSkillTemplateReferenceClip", "as_subitem" => true);
				}
				if ($ilUser->clipboardHasObjectsOfType("scat"))
				{
					$cmds[] = array("text" => $lng->txt("skmg_insert_skill_category_from_clip_child"),
						"cmd" => "insertSkillCategoryClip", "as_subitem" => true);
				}
			}
		}
		else  // drop area before first child of node
		{
			if (($a_node["type"] == "skrt" && $a_node["node_id"] == 1)
					|| $a_node["type"] == "scat")
			{
				$cmds[] = array("text" => $lng->txt("skmg_insert_basic_skill"), "cmd" => "insertBasicSkill", "multi" => 10);
				$cmds[] = array("text" => $lng->txt("skmg_insert_skill_template_reference"), "cmd" => "insertSkillTemplateReference");
				$cmds[] = array("text" => $lng->txt("skmg_insert_skill_category"), "cmd" => "insertSkillCategory", "multi" => 10);
				if ($ilUser->clipboardHasObjectsOfType("skll"))
				{
					$cmds[] = array("text" => $lng->txt("skmg_insert_basic_skill_from_clip"),
						"cmd" => "insertBasicSkillClip", "as_subitem" => false);
				}
				if ($ilUser->clipboardHasObjectsOfType("sktr"))
				{
					$cmds[] = array("text" => $lng->txt("skmg_insert_skill_template_reference_from_clip"),
						"cmd" => "insertSkillTemplateReferenceClip", "as_subitem" => false);
				}
				if ($ilUser->clipboardHasObjectsOfType("scat"))
				{
					$cmds[] = array("text" => $lng->txt("skmg_insert_skill_category_from_clip"),
						"cmd" => "insertSkillCategoryClip", "as_subitem" => false);
				}
			}
		}

		return $cmds;
	}

	/**
	* Which nodes allow child nodes?
	*/
	function nodeAllowsChilds($a_node)
	{
		if ($a_node["type"] == "skll")
		{
			return false;
		}
		return true;
	}

	/**
	* Makes nodes drag and drop content and targets.
	*
	* @param	object	$a_node		node array
	*/
	function manageDragAndDrop($a_node, $a_depth, $a_first_child_drop_area = false, $a_next_sibling = null, $a_childs = null)
	{
		global $lng;

		$this->makeDragContent($a_node["node_id"], "grp_".$a_node["type"]);
		
		if (!$a_first_child_drop_area)
		{
			if ($a_node["type"] == "skll" || $a_node["type"] == "scat")
			{
				$this->makeDragTarget($a_node["node_id"], "grp_skll", $a_first_child_drop_area,
					false, $lng->txt("skmg_insert_on_same_level"));
				$this->makeDragTarget($a_node["node_id"], "grp_scat", $a_first_child_drop_area,
					false, $lng->txt("skmg_insert_on_same_level"));
			}
			if ($a_node["type"] == "scat" || $a_node["type"] == "skrt")
			{
				$this->makeDragTarget($a_node["node_id"], "grp_skll", $a_first_child_drop_area,
					true, $lng->txt("skmg_insert_as_subitem"));
				$this->makeDragTarget($a_node["node_id"], "grp_scat", $a_first_child_drop_area,
					true, $lng->txt("skmg_insert_as_subitem"));
			}
		}
		else
		{
			if (($a_node["type"] == "skrt" && $a_node["node_id"] == 1)
					|| $a_node["type"] == "scat")
			{
				$this->makeDragTarget($a_node["node_id"], "grp_skll", $a_first_child_drop_area,
					true, "");
				$this->makeDragTarget($a_node["node_id"], "grp_scat", $a_first_child_drop_area,
					true, "");
			}
		}
	}

	/**
	* Get item commands
	*
	* @param	array		item array
	* @return	array		array of arrays("text", "link")
	*/
	function getChildCommands($a_item)
	{
		global $lng, $ilCtrl;
		
		$commands = array();
//echo "-".$a_item["type"]."-";
		switch ($a_item["type"])
		{
			case "scat":
				$ilCtrl->setParameterByClass("ilskillcategorygui", "obj_id",
					$a_item["node_id"]);
				$commands[] = array("text" => $lng->txt("edit"),
					"link" => $ilCtrl->getLinkTargetByClass(array("ilobjskillmanagementgui",
						"ilskillcategorygui"), "edit"));
				break;

			case "skll":
				$ilCtrl->setParameterByClass("ilbasicskillgui", "obj_id",
					$a_item["node_id"]);
				$commands[] = array("text" => $lng->txt("edit"),
					"link" => $ilCtrl->getLinkTargetByClass(array("ilobjskillmanagementgui",
						"ilbasicskillgui"), "edit"));
				break;

			case "sktr":
				$ilCtrl->setParameterByClass("ilskilltemplatereferencegui", "obj_id",
					$a_item["node_id"]);
				$commands[] = array("text" => $lng->txt("edit"),
					"link" => $ilCtrl->getLinkTargetByClass(array("ilobjskillmanagementgui",
						"ilskilltemplatereferencegui"), "edit"));
				break;

		}
		
		return $commands;
	}

}
