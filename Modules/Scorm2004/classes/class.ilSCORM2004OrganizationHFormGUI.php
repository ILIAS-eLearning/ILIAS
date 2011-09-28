<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilHierarchyFormGUI.php");

/**
* This class allows quick editing of a chapter/sco/page hierarchy
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilSCORM2004OrganizationHFormGUI extends ilHierarchyFormGUI
{
	/**
	* Constructor
	*
	* @param
	*/
	function __construct()
	{
		global $lng;
		
		parent::__construct();
		$this->setCheckboxName("id");
		$lng->loadLanguageModule("sahs");
		$this->setExpandVariable("scexpand");
	}
	
	/**
	* Get menu items
	*/
	function getMenuItems($a_node, $a_depth, $a_first_child = false, $a_next_sibling = null, $a_childs = null)
	{
		global $lng, $ilUser;

		include_once("./Services/Style/classes/class.ilPageLayout.php");
		$page_layouts = (count(ilPageLayout::activeLayouts()) > 0);
		$special_pages = (count(ilPageLayout::activeLayouts(true)) > 0);
		
		$cmds = array();
//echo "+".$a_depth."-";
		if (!$a_first_child)		// drop area of node
		{
			// page inserts
			if ($a_node["type"] == "page" || ($a_node["type"] == "sco" && count($a_childs) == 0) ||
				($a_node["type"] == "ass" && count($a_childs) == 0))
			{
				if ($a_node["type"] == "sco" || $a_node["type"] == "ass")
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_page"), "cmd" => "insertPage", "multi" => 10,
						"as_subitem" => true);
					if ($page_layouts > 0)
					{
						$cmds[] = array("text" => $lng->txt("sahs_insert_pagelayout"), "cmd" => "insertTemplateGUI", "multi" => 10,
							"as_subitem" => true);
					}
					if ($special_pages > 0)
					{
						$cmds[] = array("text" => $lng->txt("sahs_insert_special_page"), "cmd" => "insertSpecialPage", "multi" => 10,
							"as_subitem" => true);
					}
					if ($ilUser->clipboardHasObjectsOfType("page"))
					{
						$cmds[] = array("text" => $lng->txt("sahs_insert_page_from_clip"),
							"cmd" => "insertPageClip", "as_subitem" => true);
					}
				}
				else
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_page"), "cmd" => "insertPage", "multi" => 10);
					if ($page_layouts > 0)
					{
						$cmds[] = array("text" => $lng->txt("sahs_insert_pagelayout"), "cmd" => "insertTemplateGUI", "multi" => 10);
					}
					if ($special_pages > 0)
					{
						$cmds[] = array("text" => $lng->txt("sahs_insert_special_page"), "cmd" => "insertSpecialPage", "multi" => 10);
					}

					if ($ilUser->clipboardHasObjectsOfType("page"))
					{
						$cmds[] = array("text" => $lng->txt("sahs_insert_page_from_clip"),
							"cmd" => "insertPageClip");
					}
				}
			}

			// sco/asset inserts... in/after chapters
			if ($a_node["type"] == "chap" || $a_node["type"] == "seqc")
			{
				$cmds[] = array("text" => $lng->txt("sahs_insert_ass"), "cmd" => "insertAsset", "multi" => 10);
				if (count($a_childs) == 0)
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_ass_inside_chap"), "cmd" => "insertAsset", "multi" => 10,
						"as_subitem" => true);
				}
				$cmds[] = array("text" => $lng->txt("sahs_insert_sco"), "cmd" => "insertSco", "multi" => 10);
				if (count($a_childs) == 0)
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_sco_inside_chap"), "cmd" => "insertSco", "multi" => 10,
						"as_subitem" => true);
				}
				if ($ilUser->clipboardHasObjectsOfType("sco"))
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_sco_from_clip"),
						"cmd" => "insertScoClip", "as_subitem" => false);
					if (count($a_childs) == 0)
					{
						$cmds[] = array("text" => $lng->txt("sahs_insert_sco_from_clip_inside_chap"),
							"cmd" => "insertScoClip", "as_subitem" => true);
					}
				}
				if ($ilUser->clipboardHasObjectsOfType("ass"))
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_ass_from_clip"),
						"cmd" => "insertAssetClip", "as_subitem" => false);
					if (count($a_childs) == 0)
					{
						$cmds[] = array("text" => $lng->txt("sahs_insert_ass_from_clip_inside_chap"),
							"cmd" => "insertAssetClip", "as_subitem" => true);
					}
				}
			}
			
			// sco/asset inserts... after sco/assets
			if ($a_node["type"] == "sco" || $a_node["type"] == "ass")
			{
				// scos
				$cmds[] = array("text" => $lng->txt("sahs_insert_sco"), "cmd" => "insertSco", "multi" => 10);
				if ($ilUser->clipboardHasObjectsOfType("sco"))
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_sco_from_clip"),
						"cmd" => "insertScoClip");
				}

				// assets
				$cmds[] = array("text" => $lng->txt("sahs_insert_ass"), "cmd" => "insertAsset", "multi" => 10);
				if ($ilUser->clipboardHasObjectsOfType("ass"))
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_ass_from_clip"),
						"cmd" => "insertAssetClip");
				}

				// chapters
				if ($a_node["depth"] == 2)
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_chapter"), "cmd" => "insertChapter", "multi" => 10);
					if ($ilUser->clipboardHasObjectsOfType("chap"))
					{
						$cmds[] = array("text" => $lng->txt("sahs_insert_chap_from_clip"),
							"cmd" => "insertChapterClip");
					}
				}
			}
			//if ($a_node["type"] == "chap")
			//{
			//	$cmds[] = array("text" => $lng->txt("sahs_insert_sub_chapter"), "cmd" => "insertSubchapter", "multi" => 10);
			//}
			
			// chapter inserts
			if ($a_node["type"] == "chap" || $a_node["type"] == "seqc")
			{
				$cmds[] = array("text" => $lng->txt("sahs_insert_chapter"), "cmd" => "insertChapter", "multi" => 10);
				if ($ilUser->clipboardHasObjectsOfType("chap"))
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_chap_from_clip"),
						"cmd" => "insertChapterClip");
				}
				
				//check if parent chaper has sequencing scenario
			//	$cmds[] = array("text" => $lng->txt("sahs_insert_scenario"), "cmd" => "insertScenarioGUI", "multi" => 0);
				
			}
		}
		else  // drop area before first child of node
		{
			if ($a_node["type"] == "" && $a_node["node_id"] == 1)	// top node
			{
				// scos
				$cmds[] = array("text" => $lng->txt("sahs_insert_sco"), "cmd" => "insertSco", "multi" => 10);
				if ($ilUser->clipboardHasObjectsOfType("sco"))
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_sco_from_clip"),
						"cmd" => "insertScoClip");
				}

				// assets
				$cmds[] = array("text" => $lng->txt("sahs_insert_ass"), "cmd" => "insertAsset", "multi" => 10);
				if ($ilUser->clipboardHasObjectsOfType("ass"))
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_ass_from_clip"),
						"cmd" => "insertAssetClip");
				}

				// chapters
				$cmds[] = array("text" => $lng->txt("sahs_insert_chapter"), "cmd" => "insertChapter", "multi" => 10);
				if ($ilUser->clipboardHasObjectsOfType("chap"))
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_chap_from_clip"),
						"cmd" => "insertChapterClip");
				}
			//	$cmds[] = array("text" => $lng->txt("sahs_insert_scenario"), "cmd" => "insertScenarioGUI", "multi" => 0);
			}
			if ($a_node["type"] == "chap" || $a_node["type"] == "seqc")
			{
				$cmds[] = array("text" => $lng->txt("sahs_insert_sco"), "cmd" => "insertSco", "multi" => 10);
				$cmds[] = array("text" => $lng->txt("sahs_insert_ass"), "cmd" => "insertAsset", "multi" => 10);
				if ($ilUser->clipboardHasObjectsOfType("sco"))
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_sco_from_clip"),
						"cmd" => "insertScoClip");
				}
				if ($ilUser->clipboardHasObjectsOfType("ass"))
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_ass_from_clip"),
						"cmd" => "insertAssetClip");
				}
			}
			if ($a_node["type"] == "sco" || $a_node["type"] == "ass")
			{
				$cmds[] = array("text" => $lng->txt("sahs_insert_page"), "cmd" => "insertPage", "multi" => 10);
				if ($page_layouts > 0)
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_pagelayout"), "cmd" => "insertTemplateGUI", "multi" => 10);
				}
				if ($special_pages > 0)
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_special_page"), "cmd" => "insertSpecialPage", "multi" => 10);
				}

				if ($ilUser->clipboardHasObjectsOfType("page"))
				{
					$cmds[] = array("text" => $lng->txt("sahs_insert_page_from_clip"),
						"cmd" => "insertPageClip");
				}
			}

/*			if ($a_childs["type"] == "")
			{
				$cmds[] = array("text" => "insert Chapter", "cmd" => "insertChapter", "multi" => 10);
			}*/
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
		
		if ($a_node["type"] == "ass")
		{
			$this->makeDragContent($a_node["node_id"], "grp_sco");
		}
		else
		{
			$this->makeDragContent($a_node["node_id"], "grp_".$a_node["type"]);
		}
		
		if (!$a_first_child_drop_area)
		{
			// page targets
			if ($a_node["type"] == "page" || ($a_node["type"] == "sco" && count($a_childs) == 0)
				|| ($a_node["type"] == "ass" && count($a_childs) == 0))
			{
				if ($a_node["type"] == "sco" || $a_node["type"] == "ass")
				{
					$this->makeDragTarget($a_node["node_id"], "grp_page", $a_first_child_drop_area,
						true, "");
				}
				else
				{
					$this->makeDragTarget($a_node["node_id"], "grp_page", $a_first_child_drop_area,
						false, "");
				}
			}

			if ($a_node["type"] == "chap" && count($a_childs) == 0)
			{
				$this->makeDragTarget($a_node["node_id"], "grp_sco", $a_first_child_drop_area,
					true, $lng->txt("cont_insert_into_chap"));
			}
			
			// sco targets
			if ($a_node["type"] == "sco" || $a_node["type"] == "ass" || $a_node["type"] == "chap")
			{
				$this->makeDragTarget($a_node["node_id"], "grp_sco", $a_first_child_drop_area,
					false, $lng->txt("cont_insert_after_".$a_node["type"]));
			}

			//if ($a_node["type"] != "pg")
			//{
			//	$this->makeDragTarget($a_node["node_id"], "grp_st", $a_first_child_drop_area,
			//		true, $lng->txt("cont_insert_as_subchapter"));
			//}
			
			// chapter targets				// chapters
			if ($a_node["depth"] == 2)
			{
				$this->makeDragTarget($a_node["node_id"], "grp_chap", $a_first_child_drop_area,
					false, $lng->txt("sahs_insert_as_chapter"));
//				$this->makeDragTarget($a_node["node_id"], "grp_sco", $a_first_child_drop_area,
//					false, $lng->txt("cont_insert_after_chap"));
			}
		}
		else
		{
			if ($a_node["type"] == "" && $a_node["node_id"] == 1)	// top node
			{
				$this->makeDragTarget($a_node["node_id"], "grp_chap", $a_first_child_drop_area,
					true);
				$this->makeDragTarget($a_node["node_id"], "grp_sco", $a_first_child_drop_area,
					true);
			}
			if ($a_node["type"] == "chap")
			{
				$this->makeDragTarget($a_node["node_id"], "grp_sco", $a_first_child_drop_area,
					true);
			}
			if ($a_node["type"] == "sco" || $a_node["type"] == "ass")
			{
				$this->makeDragTarget($a_node["node_id"], "grp_page", $a_first_child_drop_area,
					true);
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
			case "sco":
				$ilCtrl->setParameterByClass("ilscorm2004scogui", "obj_id",
					$a_item["node_id"]);
				$commands[] = array("text" => $lng->txt("edit"),
					"link" => $ilCtrl->getLinkTargetByClass(array("ilobjscorm2004learningmodulegui",
						"ilscorm2004scogui"), "showOrganization"));
				break;

			case "ass":
				$ilCtrl->setParameterByClass("ilscorm2004assetgui", "obj_id",
					$a_item["node_id"]);
				$commands[] = array("text" => $lng->txt("edit"),
					"link" => $ilCtrl->getLinkTargetByClass(array("ilobjscorm2004learningmodulegui",
						"ilscorm2004assetgui"), "showOrganization"));
				break;

			case "chap":
				$ilCtrl->setParameterByClass("ilscorm2004chaptergui", "obj_id",
					$a_item["node_id"]);
				$commands[] = array("text" => $lng->txt("edit"),
					"link" => $ilCtrl->getLinkTargetByClass(array("ilobjscorm2004learningmodulegui",
						"ilscorm2004chaptergui"), "showOrganization"));
				break;

			case "seqc":
				$ilCtrl->setParameterByClass("ilscorm2004seqchaptergui", "obj_id",
					$a_item["node_id"]);
				$commands[] = array("text" => $lng->txt("edit"),
					"link" => $ilCtrl->getLinkTargetByClass(array("ilobjscorm2004learningmodulegui",
						"ilscorm2004seqchaptergui"), "showOrganization"));
				break;

			case "page":
				$ilCtrl->setParameterByClass("ilscorm2004pagenodegui", "obj_id",
					$a_item["node_id"]);
				$commands[] = array("text" => $lng->txt("edit"),
					"link" => $ilCtrl->getLinkTargetByClass(array("ilobjscorm2004learningmodulegui",
						"ilscorm2004pagenodegui"), "edit"));
				break;
		}
		
		return $commands;
	}

}
