<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");

/**
 * SCORM editor explorer GUI class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ModulesScormAicc
 */
class ilSCORM2004EditorExplorerGUI extends ilTreeExplorerGUI
{
	/**
	 * Constructor
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_slm)
	{
		global $ilUser;
		
		$this->slm = $a_slm;
		
		$tree = new ilTree($this->slm->getId());
		$tree->setTableNames('sahs_sc13_tree','sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");

		parent::__construct("scorm_ed_exp", $a_parent_obj, $a_parent_cmd, $tree);
		
		//$this->setTypeWhiteList(array("du", "chap", "page"));
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
			return $this->slm->getTitle();
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
			$icon = ilUtil::getImagePath("icon_sahs.svg");
		}
		else
		{
			$icon = ilUtil::getImagePath("icon_".$a_node["type"].".svg");
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

		if ($a_node["child"] == $this->getNodeId($this->getRootNode()))
		{
			return $this->slm->getTitle();
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

		if ($a_node["child"] == $this->getNodeId($this->getRootNode()))
		{
			$ilCtrl->setParameterByClass("ilobjscorm2004learningmodulegui", "obj_id", $a_node["child"]);
			$ret = $ilCtrl->getLinkTargetByClass("ilobjscorm2004learningmodulegui", "showOrganization");
			$ilCtrl->setParameterByClass("ilobjscorm2004learningmodulegui", "obj_id", $_GET["obj_id"]);
			return $ret;
		}
		
		switch($a_node["type"])
		{
			case "page":
				$ilCtrl->setParameterByClass("ilScorm2004PageNodeGUI", "obj_id", $a_node["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilScorm2004PageNodeGUI", "edit");
				$ilCtrl->setParameterByClass("ilScorm2004PageNodeGUI", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;

			case "chap":
				$ilCtrl->setParameterByClass("ilScorm2004ChapterGUI", "obj_id", $a_node["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilScorm2004ChapterGUI", "showOrganization");
				$ilCtrl->setParameterByClass("ilScorm2004ChapterGUI", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;

			case "sco":
				$ilCtrl->setParameterByClass("ilScorm2004ScoGUI", "obj_id", $a_node["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilScorm2004ScoGUI", "showOrganization");
				$ilCtrl->setParameterByClass("ilScorm2004ScoGUI", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;

			case "ass":
				$ilCtrl->setParameterByClass("ilScorm2004AssetGUI", "obj_id", $a_node["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilScorm2004AssetGUI", "showOrganization");
				$ilCtrl->setParameterByClass("ilScorm2004AssetGUI", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;
		}
	}

}

?>
