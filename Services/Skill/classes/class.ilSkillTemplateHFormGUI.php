<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilHierarchyFormGUI.php");

/**
 * This class allows quick editing of the skill templates
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup	ServicesSkill
 */
class ilSkillTemplateHFormGUI extends ilHierarchyFormGUI
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
		$this->setExpandVariable("sktexpand");
		$this->setTypeWhiteList(array("skrt", "sktp", "sctp"));
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
			if ($a_node["type"] == "sktp" || $a_node["type"] == "sctp")
			{
				$cmds[] = array("text" => $lng->txt("skmg_insert_basic_skill_template"), "cmd" => "insertBasicSkillTemplate", "multi" => 10);
				$cmds[] = array("text" => $lng->txt("skmg_insert_skill_template_category"), "cmd" => "insertSkillTemplateCategory", "multi" => 10);
				if ($ilUser->clipboardHasObjectsOfType("sktp"))
				{
					$cmds[] = array("text" => $lng->txt("skmg_insert_basic_skill_template_from_clip"),
						"cmd" => "insertBasicSkillTemplateClip", "as_subitem" => false);
				}
				if ($ilUser->clipboardHasObjectsOfType("sctp"))
				{
					$cmds[] = array("text" => $lng->txt("skmg_insert_skill_template_category_from_clip"),
						"cmd" => "insertSkillTemplateCategoryClip", "as_subitem" => false);
				}
			}

			if ($a_node["type"] == "sctp" || $a_node["type"] == "skrt")
			{
				$cmds[] = array("text" => $lng->txt("skmg_insert_basic_skill_template_child"), "cmd" => "insertBasicSkillTemplate", "multi" => 10,
					"as_subitem" => true);
				$cmds[] = array("text" => $lng->txt("skmg_insert_skill_template_category_child"), "cmd" => "insertSkillTemplateCategory", "multi" => 10,
					"as_subitem" => true);
				if ($ilUser->clipboardHasObjectsOfType("sktp"))
				{
					$cmds[] = array("text" => $lng->txt("skmg_insert_basic_skill_template_from_clip_child"),
						"cmd" => "insertBasicSkillTemplateClip", "as_subitem" => true);
				}
				if ($ilUser->clipboardHasObjectsOfType("sctp"))
				{
					$cmds[] = array("text" => $lng->txt("skmg_insert_skill_template_category_from_clip_child"),
						"cmd" => "insertSkillTemplateCategoryClip", "as_subitem" => true);
				}
			}
		}
		else  // drop area before first child of node
		{
			if (($a_node["type"] == "skrt" && $a_node["node_id"] == 1)
					|| $a_node["type"] == "sctp")
			{
				$cmds[] = array("text" => $lng->txt("skmg_insert_basic_skill_template"), "cmd" => "insertBasicSkillTemplate", "multi" => 10);
				$cmds[] = array("text" => $lng->txt("skmg_insert_skill_template_category"), "cmd" => "insertSkillTemplateCategory", "multi" => 10);
				if ($ilUser->clipboardHasObjectsOfType("sktp"))
				{
					$cmds[] = array("text" => $lng->txt("skmg_insert_basic_skill_template_from_clip"),
						"cmd" => "insertBasicSkillTemplateClip", "as_subitem" => false);
				}
				if ($ilUser->clipboardHasObjectsOfType("sctp"))
				{
					$cmds[] = array("text" => $lng->txt("skmg_insert_skill_template_category_from_clip"),
						"cmd" => "insertSkillTemplateCategoryClip", "as_subitem" => false);
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
		if ($a_node["type"] == "skll" || $a_node["type"] == "sktp")
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
			if ($a_node["type"] == "sktp" || $a_node["type"] == "sctp")
			{
				$this->makeDragTarget($a_node["node_id"], "grp_sktp", $a_first_child_drop_area,
					false, $lng->txt("skmg_insert_on_same_level"));
				$this->makeDragTarget($a_node["node_id"], "grp_sctp", $a_first_child_drop_area,
					false, $lng->txt("skmg_insert_on_same_level"));
			}
			if ($a_node["type"] == "sctp" || $a_node["type"] == "skrt")
			{
				$this->makeDragTarget($a_node["node_id"], "grp_sktp", $a_first_child_drop_area,
					true, $lng->txt("skmg_insert_as_subitem"));
				$this->makeDragTarget($a_node["node_id"], "grp_sctp", $a_first_child_drop_area,
					true, $lng->txt("skmg_insert_as_subitem"));
			}
		}
		else
		{
			if (($a_node["type"] == "skrt" && $a_node["node_id"] == 1)
					|| $a_node["type"] == "sctp")
			{
				$this->makeDragTarget($a_node["node_id"], "grp_sktp", $a_first_child_drop_area,
					true, "");
				$this->makeDragTarget($a_node["node_id"], "grp_sctp", $a_first_child_drop_area,
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
			case "sctp":
/*				$ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "obj_id",
					$a_item["node_id"]);
				$commands[] = array("text" => $lng->txt("edit"),
					"link" => $ilCtrl->getLinkTargetByClass(array("ilobjskillmanagementgui",
						"ilskilltemplatecategorygui"), "edit"));*/
				break;

			case "sktp":
				$ilCtrl->setParameterByClass("ilbasicskilltemplategui", "obj_id",
					$a_item["node_id"]);
				$commands[] = array("text" => $lng->txt("edit"),
					"link" => $ilCtrl->getLinkTargetByClass(array("ilobjskillmanagementgui",
						"ilbasicskilltemplategui"), "edit"));
				break;

		}
		
		return $commands;
	}

}
