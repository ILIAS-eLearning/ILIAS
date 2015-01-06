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
class ilSkillTemplateTreeExplorerGUI extends ilTreeExplorerGUI
{
	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent command
	 */
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$tree = new ilSkillTree();
		parent::__construct("skill_exp", $a_parent_obj, $a_parent_cmd, $tree);

		$this->setTypeWhiteList(array("skrt", "sktp", "sctp"));
		
		$this->setSkipRootNode(false);
		$this->setAjax(true);
		$this->setOrderField("order_nr");
	}
	
	/**
	 * Get root node
	 *
	 * @return array node data
	 */
	function getRootNode()
	{
		$path = $this->getTree()->getPathId($_GET["obj_id"]);
		return $this->getTree()->getNodeData($path[1]);
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
				$this->draft[$c["child"]] = (ilSkillTreeNode::_lookupStatus($c["child"]) == ilSkillTreeNode::STATUS_DRAFT);
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
			$title = $lng->txt("skmg_skill_templates");
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
			$icon = ilUtil::getImagePath("icon_sctp.svg");
		}
		else
		{
			if (in_array($a_node["type"], array("skll", "scat", "sctr", "sktr")))
			{
				$icon = ilSkillTreeNode::getIconPath($a_node["child"], $a_node["type"], "",
					$this->draft[$a_node["child"]]);
			}
			else
			{
				$icon = ilUtil::getImagePath("icon_".$a_node["type"].".svg");
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
				$ret = $ilCtrl->getLinkTargetByClass("ilskillrootgui", "listTemplates");
				$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id", $_GET["obj_id"]);
				return $ret;
				break;

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
