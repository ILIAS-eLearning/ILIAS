<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");

/**
 * Explorer class that works on tree objects (Services/Tree)
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ServicesUIComponent
 */
class ilSkillTreeExplorerGUI extends ilTreeExplorerGUI
{
	/**
	 * Constructor
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_templates = false)
	{
		$this->templates = $a_templates;
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$tree = new ilSkillTree();
		parent::__construct("skill_exp", $a_parent_obj, $a_parent_cmd, $tree);

		if ($this->templates)
		{
			$this->setTypeWhiteList(array("skrt", "sktp", "sctp"));
		}
		else
		{
			$this->setTypeWhiteList(array("skrt", "skll", "scat", "sktr"));
		}
		
		$this->setSkipRootNode(false);
		$this->setAjax(true);
		$this->setOrderField("order_nr");
	}
	
	/**
	 * Get childs of node
	 *
	 * @param int $a_parent_id parent id
	 * @return array childs
	 */
	function getChildsOfNode($a_parent_id)
	{
		$childs = parent::getChildsOfNode($a_parent_id);
		
		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		foreach ($childs as $c)
		{
			$this->parent[$c["child"]] = $c["parent"];
			if ($this->draft[$c["parent"]])
			{
				$this->draft[$c["child"]] = true;
			}
			else
			{
				$this->draft[$c["child"]] = ilSkillTreeNode::_lookupDraft($c["child"]);
			}
		}
		return $childs;
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
		
		// title
		$title = $a_node["title"];
		
		// root?
		if ($a_node["type"] == "skrt")
		{
			$title = ($this->templates)
				? $lng->txt("skmg_skill_templates")
				: $lng->txt("skmg_skills");
		}
		else
		{
			if ($a_node["type"] == "sktr")
			{
				include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
				$tid = ilSkillTemplateReference::_lookupTemplateId($a_node["child"]);
				$title.= " (".ilSkillTreeNode::_lookupTitle($tid).")";
			}
			if (ilSkillTreeNode::_lookupSelfEvaluation($a_node["child"]))
			{
				$title = "<u>".$title."</u>";
			}
		}
		
		return $title;
	}
	
	/**
	 * Get node content
	 *
	 * @param array 
	 * @return
	 */
	function getNodeIcon($a_node)
	{
		// root?
		if ($a_node["type"] == "skrt")
		{
			$icon = ($this->templates)
				? ilUtil::getImagePath("icon_sctp_s.png")
				: ilUtil::getImagePath("icon_scat_s.png");
		}
		else
		{
			if (in_array($a_node["type"], array("skll", "scat", "sctr", "sktr")))
			{
				$icon = ilSkillTreeNode::getIconPath($a_node["child"], $a_node["type"], "_s",
					$this->draft[$a_node["child"]]);
			}
			else
			{
				$icon = ilUtil::getImagePath("icon_".$a_node["type"]."_s.png");
			}
		}
		
		return $icon;
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
			($_GET["obj_id"] == "" && $a_node["type"] == "skrt"))
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
			// root
			case "skrt":
				$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id", $a_node["child"]);
				$ret = ($this->templates)
					? $ilCtrl->getLinkTargetByClass("ilskillrootgui", "listTemplates")
					: $ilCtrl->getLinkTargetByClass("ilskillrootgui", "listSkills");
				$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;
				
			// category
			case "scat":
				$ilCtrl->setParameterByClass("ilskillcategorygui", "obj_id", $a_node["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilskillcategorygui", "listItems");
				$ilCtrl->setParameterByClass("ilskillcategorygui", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;
				
			// skill template reference
			case "sktr":
				$ilCtrl->setParameterByClass("ilskilltemplatereferencegui", "obj_id", $a_node["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilskilltemplatereferencegui", "listItems");
				$ilCtrl->setParameterByClass("ilskilltemplatereferencegui", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;
				
			// skill
			case "skll":
				$ilCtrl->setParameterByClass("ilbasicskillgui", "obj_id", $a_node["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilbasicskillgui", "edit");
				$ilCtrl->setParameterByClass("ilbasicskillgui", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;
				
			// --------
				
			// template
			case "sktp":
				$ilCtrl->setParameterByClass("ilbasicskilltemplategui", "obj_id", $a_node["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilbasicskilltemplategui", "edit");
				$ilCtrl->setParameterByClass("ilbasicskilltemplategui", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;

			// template category
			case "sctp":
				$ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "obj_id", $a_node["child"]);
				$ret = $ilCtrl->getLinkTargetByClass("ilskilltemplatecategorygui", "listItems");
				$ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;
		}
	}

}

?>
