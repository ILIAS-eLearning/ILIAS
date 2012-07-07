<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilHierarchyFormGUI.php");

/**
* This class represents a hierarchical form. These forms are used for
* quick editing, where each node is represented by it's title.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilChapterHierarchyFormGUI extends ilHierarchyFormGUI
{
	/**
	* Constructor
	*
	* @param
	*/
	function __construct($a_lm_type)
	{
		$this->lm_type = $a_lm_type;

		parent::__construct();
		$this->setCheckboxName("id");
	}
	
	/**
	* Get menu items
	*/
	function getMenuItems($a_node, $a_depth, $a_first_child = false, $a_next_sibling = null, $a_childs = null)
	{
		global $lng, $ilUser;
		
		$cmds = array();
		
		if (!$a_first_child)		// drop area of node
		{
			if ($a_node["type"] == "pg" || ($a_node["type"] == "st" && count($a_childs) == 0 && $this->getMaxDepth() != 0))
			{
				if ($a_node["type"] == "st")
				{
					$cmds[] = array("text" => $lng->txt("cont_insert_page"), "cmd" => "insertPage", "multi" => 10,
						"as_subitem" => true);
					if ($ilUser->clipboardHasObjectsOfType("pg"))
					{
						$cmds[] = array("text" => $lng->txt("cont_insert_page_from_clip"),
							"cmd" => "insertPageClip", "as_subitem" => true);
					}
				}
				else
				{
					$cmds[] = array("text" => $lng->txt("cont_insert_page"), "cmd" => "insertPage", "multi" => 10);
					if ($ilUser->clipboardHasObjectsOfType("pg"))
					{
						$cmds[] = array("text" => $lng->txt("cont_insert_page_from_clip"),
							"cmd" => "insertPageClip");
					}
				}
			}
			if ($a_node["type"] != "pg" && $this->getMaxDepth() != 0)
			{
				$cmds[] = array("text" => $lng->txt("cont_insert_subchapter"),
					"cmd" => "insertSubchapter", "multi" => 10);
				if ($ilUser->clipboardHasObjectsOfType("st"))
				{
					$cmds[] = array("text" => $lng->txt("cont_insert_subchapter_from_clip"),
						"cmd" => "insertSubchapterClip");
				}
			}
			
			if (($a_next_sibling["type"] != "pg" && ($a_depth == 0 || $a_next_sibling["type"] == "st"))
				|| $a_node["type"] == "st")
			{
				$cmds[] = array("text" => $lng->txt("cont_insert_chapter"),
					"cmd" => "insertChapter", "multi" => 10);
				if ($ilUser->clipboardHasObjectsOfType("st"))
				{
					$cmds[] = array("text" => $lng->txt("cont_insert_chapter_from_clip"),
						"cmd" => "insertChapterClip");
				}
			}
		}
		else						// drop area before first child of node
		{
			if ($a_node["type"] == "st" && $this->getMaxDepth() != 0)
			{
				$cmds[] = array("text" => $lng->txt("cont_insert_page"),
					"cmd" => "insertPage", "multi" => 10);
				if ($ilUser->clipboardHasObjectsOfType("pg"))
				{
					$cmds[] = array("text" => $lng->txt("cont_insert_page_from_clip"),
						"cmd" => "insertPageClip");
				}
			}
			if ($a_childs[0]["type"] != "pg")
			{
				$cmds[] = array("text" => $lng->txt("cont_insert_chapter"),
					"cmd" => "insertChapter", "multi" => 10);
				if ($ilUser->clipboardHasObjectsOfType("st"))
				{
					$cmds[] = array("text" => $lng->txt("cont_insert_chapter_from_clip"),
						"cmd" => "insertChapterClip");
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
		if ($a_node["type"] == "pg")
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
			if ($a_node["type"] == "pg" || ($a_node["type"] == "st" && count($a_childs) == 0 && $this->getMaxDepth() != 0))
			{
				if ($a_node["type"] == "st")
				{
					$this->makeDragTarget($a_node["node_id"], "grp_pg", $a_first_child_drop_area,
						true, "");
				}
				else
				{
					$this->makeDragTarget($a_node["node_id"], "grp_pg", $a_first_child_drop_area,
						false, "");
				}
			}
			
			if ($a_node["type"] != "pg" && $this->getMaxDepth() != 0)
			{
				$this->makeDragTarget($a_node["node_id"], "grp_st", $a_first_child_drop_area,
					true, $lng->txt("cont_insert_as_subchapter"));
			}
			
			if (($a_next_sibling["type"] != "pg" && ($a_depth == 0 || $a_next_sibling["type"] == "st"))
				|| $a_node["type"] == "st")
			{
				$this->makeDragTarget($a_node["node_id"], "grp_st", $a_first_child_drop_area,
					false, $lng->txt("cont_insert_as_chapter"));
			}
		}
		else
		{
			if ($a_node["type"] == "st" && $this->getMaxDepth() != 0)
			{
				$this->makeDragTarget($a_node["node_id"], "grp_pg", $a_first_child_drop_area,
					true);
			}
			if ($a_childs[0]["type"] != "pg")
			{
				$this->makeDragTarget($a_node["node_id"], "grp_st", $a_first_child_drop_area,
					true);
			}
		}
	}

	/**
	* Get icon path for an item.
	*
	* @param	array		itema array
	* @return	string		icon path
	*/
	function getChildIcon($a_item)
	{
		include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
		
		$img = "icon_".$a_item["type"].".png";
		
		if ($a_item["type"] == "pg")
		{
			include_once("./Services/COPage/classes/class.ilPageObject.php");
			$lm_set = new ilSetting("lm");
			$active = ilPageObject::_lookupActive($a_item["node_id"], $this->lm_type,
				$lm_set->get("time_scheduled_page_activation"));
				
			// is page scheduled?
			$img_sc = ($lm_set->get("time_scheduled_page_activation") &&
				ilPageObject::_isScheduledActivation($a_item["node_id"], $this->lm_type))
				? "_sc"
				: "";
				
			$img = "icon_pg".$img_sc.".png";

			if (!$active)
			{
				$img = "icon_pg_d".$img_sc.".png";
			}
			else
			{
				include_once("./Services/COPage/classes/class.ilPageObject.php");
				$contains_dis = ilPageObject::_lookupContainsDeactivatedElements($a_item["node_id"],
					$this->lm_type);
				if ($contains_dis)
				{
					$img = "icon_pg_del".$img_sc.".png";
				}
			}
		}
		return ilUtil::getImagePath($img);
	}

	/**
	* Get icon alt text
	*
	* @param	array		itema array
	* @return	string		icon alt text
	*/
	function getChildIconAlt($a_item)
	{
		global $lng;
		
		include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
		
		if ($a_item["type"] == "pg")
		{
			include_once("./Services/COPage/classes/class.ilPageObject.php");
			$active = ilPageObject::_lookupActive($a_item["node_id"], $this->lm_type);

			if (!$active)
			{
				return $lng->txt("cont_page_deactivated");
			}
			else
			{
				include_once("./Services/COPage/classes/class.ilPageObject.php");
				$contains_dis = ilPageObject::_lookupContainsDeactivatedElements($a_item["node_id"],
					$this->lm_type);
				if ($contains_dis)
				{
					return $lng->txt("cont_page_deactivated_elements");
				}
			}
		}
		return ilUtil::getImagePath("icon_".$a_item["type"].".png");
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
		
		$lm_class = ($this->lm_type == "lm")
			? "ilobjlearningmodulegui"
			: "ilobjdlbookgui";
		
		$commands = array();
		switch ($a_item["type"])
		{
			case "pg":
				$ilCtrl->setParameterByClass("illmpageobjectgui", "obj_id",
					$a_item["node_id"]);
				$commands[] = array("text" => $lng->txt("edit"),
					"link" => $ilCtrl->getLinkTargetByClass(array($lm_class, "illmpageobjectgui"), "edit"));
				break;

			case "st":
				$ilCtrl->setParameterByClass("ilstructureobjectgui", "obj_id",
					$a_item["node_id"]);
				$commands[] = array("text" => $lng->txt("edit"),
					"link" => $ilCtrl->getLinkTargetByClass(array($lm_class, "ilstructureobjectgui"), "view"));
				break;
		}
		
		return $commands;
	}

}
