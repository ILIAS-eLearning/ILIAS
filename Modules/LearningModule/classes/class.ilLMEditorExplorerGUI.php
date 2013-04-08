<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");
include_once("./Modules/LearningModule/classes/class.ilStructureObject.php");
include_once("./Modules/LearningModule/classes/class.ilLMObject.php");

/**
 * LM editor explorer GUI class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ModulesLearningModule
 */
class ilLMEditorExplorerGUI extends ilTreeExplorerGUI
{
	/**
	 * Constructor
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_lm)
	{
		global $ilUser;
		
		$this->lm = $a_lm;
		
		$tree = new ilTree($this->lm->getId());
		$tree->setTableNames('lm_tree','lm_data');
		$tree->setTreeTablePK("lm_id");

		parent::__construct("lm_ed_exp", $a_parent_obj, $a_parent_cmd, $tree);
		
//		$this->setTypeWhiteList(array("dummy", "fold"));
		$this->setSkipRootNode(false);
		$this->setAjax(false);
	}

	/**
	 * Get node content
	 *
	 * @param array 
	 * @return
	 */
	function getNodeContent($a_node)
	{
		global $lng;

		if ($a_node["child"] == $this->getNodeId($this->getRootNode()))
		{
			return $this->lm->getTitle();
		}
		
		if ($a_node["type"] == "st")
		{
			return ilStructureObject::_getPresentationTitle($a_node["child"],
				$this->lm->isActiveNumbering());
		}
				
		return $a_node["title"];
	}
	
	/**
	 * Get node icon
	 *
	 * @param array 
	 * @return
	 */
	function getNodeIcon($a_node)
	{
		if ($a_node["child"] == $this->getNodeId($this->getRootNode()))
		{
			$icon = ilUtil::getImagePath("icon_lm_s.png");
		}
		else
		{
			$a_name = "icon_".$a_node["type"]."_s.png";
			if ($a_node["type"] == "pg")
			{
				include_once("./Services/COPage/classes/class.ilPageObject.php");
				$lm_set = new ilSetting("lm");
				$active = ilPageObject::_lookupActive($a_node["child"], $this->lm->getType(),
					$lm_set->get("time_scheduled_page_activation"));
				
				// is page scheduled?
				$img_sc = ($lm_set->get("time_scheduled_page_activation") &&
					ilPageObject::_isScheduledActivation($a_id, $this->lm->getType()))
					? "_sc"
					: "";
					
				$a_name = "icon_pg".$img_sc."_s.png";
	
				if (!$active)
				{
					$a_name = "icon_pg_d".$img_sc."_s.png";
				}
				else
				{
					include_once("./Services/COPage/classes/class.ilPageObject.php");
					$contains_dis = ilPageObject::_lookupContainsDeactivatedElements($a_node["child"],
						$this->lm->getType());
					if ($contains_dis)
					{
						$a_name = "icon_pg_del".$img_sc."_s.png";
					}
				}
			}
			$icon = ilUtil::getImagePath($a_name);
		}
		
		return $icon;
	}

	/**
	 * Get node icon alt text
	 *
	 * @param array node array
	 * @return string alt text
	 */
	function getNodeIconAlt($a_node)
	{
		global $lng;
		
		include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
		
		if ($a_node["type"] == "pg")
		{
			include_once("./Services/COPage/classes/class.ilPageObject.php");
			$lm_set = new ilSetting("lm");
			$active = ilPageObject::_lookupActive($a_node["child"], $this->lm->getType(),
				$lm_set->get("time_scheduled_page_activation"));

			if (!$active)
			{
				return $lng->txt("cont_page_deactivated");
			}
			else
			{
				include_once("./Services/COPage/classes/class.ilPageObject.php");
				$contains_dis = ilPageObject::_lookupContainsDeactivatedElements($a_node["child"],
					$this->lm->getType());
				if ($contains_dis)
				{
					return $lng->txt("cont_page_deactivated_elements");
				}
			}
		}
		return parent::getNodeIconAlt($a_node);
	}
	
	/**
	 * Is node highlighted?
	 *
	 * @param mixed $a_node node object/array
	 * @return boolean node visible true/false
	 */
	function isNodeHighlighted($a_node)
	{
		if ($a_node["child"] == $_GET["obj_id"] ||
			($_GET["obj_id"] == "" && $a_node["child"] == $this->getNodeId($this->getRootNode())))
		{
			return true;
		}
		return false;
	}	
	
	/**
	 * Get href for node
	 *
	 * @param mixed $a_node node object/array
	 * @return string href attribute
	 */
	function getNodeHref($a_node)
	{
		global $ilCtrl;
		
		switch($a_node["type"])
		{
			case "du":
				$ilCtrl->setParameterByClass("ilobjlearningmodulegui", "obj_id", $a_node["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilobjlearningmodulegui", "chapters");
				$ilCtrl->setParameterByClass("ilobjlearningmodulegui", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;

			case "pg":
				$ilCtrl->setParameterByClass("illmpageobjectgui", "obj_id", $a_node["child"]);
				$ret = $ilCtrl->getLinkTargetByClass(array("ilobjlearningmodulegui", "illmpageobjectgui"), "edit");
				$ilCtrl->setParameterByClass("illmpageobjectgui", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;

			case "st":
				$ilCtrl->setParameterByClass("ilstructureobjectgui", "obj_id", $a_node["child"]);
				$ret = $ilCtrl->getLinkTargetByClass(array("ilobjlearningmodulegui", "ilstructureobjectgui"), "view");
				$ilCtrl->setParameterByClass("ilstructureobjectgui", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;
		}
	}

}

?>
