<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilExplorerBaseGUI.php");

/**
 * Virtual skill tree explorer 
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ServicesSkill
 */
class ilVirtualSkillTreeExplorerGUI extends ilExplorerBaseGUI
{
	protected $show_draft_nodes = false;
	protected $drafts = array();
	
	/**
	 * Constructor
	 */
	public function __construct($a_id, $a_parent_obj, $a_parent_cmd)
	{
		parent::__construct($a_id, $a_parent_obj, $a_parent_cmd);
		
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$this->tree = new ilSkillTree();
		$this->setSkipRootNode(false);
		$this->setAjax(false);
	}
	
	/**
	 * Set show draft nodes
	 *
	 * @param boolean $a_val show draft nodes	
	 */
	function setShowDraftNodes($a_val)
	{
		$this->show_draft_nodes = $a_val;
	}

	/**
	 * Get show draft nodes
	 *
	 * @return boolean show draft nodes
	 */
	function getShowDraftNodes()
	{
		return $this->show_draft_nodes;
	}
	
	/**
	 * Get root node
	 *
	 * @param
	 * @return
	 */
	function getRootNode()
	{
		$root_id = $this->tree->readRootId();
		$root_node = $this->tree->getNodeData($root_id);
		unset($root_node["child"]);
		$root_node["id"] = $root_id.":0";

		return $root_node;
	}
	
	/**
	 * Get node id
	 *
	 * @param
	 * @return
	 */
	function getNodeId($a_node)
	{
		return $a_node["id"];
	}
	
	/**
	 * Get childs of node
	 *
	 * @param int $a_parent_id parent id
	 * @return array childs
	 */
	function getChildsOfNode($a_parent_id)
	{
		$a_parent_id_parts = explode(":", $a_parent_id);
		$a_parent_skl_tree_id = $a_parent_id_parts[0];
		$a_parent_skl_template_tree_id = $a_parent_id_parts[1];

		if ($a_parent_skl_template_tree_id == 0)
		{
			$childs = $this->tree->getChildsByTypeFilter($a_parent_skl_tree_id, array("scat", "skll", "sktr"), "order_nr");
		}
		else
		{
			$childs = $this->tree->getChildsByTypeFilter($a_parent_skl_template_tree_id, array("sktp", "sctp"), "order_nr");
		}
		
		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		$drafts = array();
		foreach ($childs as $k => $c)
		{
			if ($a_parent_skl_template_tree_id > 0)
			{
				// we are in template tree only
				$child_id = $a_parent_skl_tree_id.":".$c["child"]; 
			}
			else if (!in_array($c["type"], array("sktr", "sctr")))
			{
				// we are in main tree only
				$child_id = $c["child"].":0";
			}
			else
			{
				// get template id for references
				include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
				$child_id = $c["child"].":".ilSkillTemplateReference::_lookupTemplateId($c["child"]);
			}
			unset($childs[$k]["child"]);
			unset($childs[$k]["skl_tree_id"]);
			unset($childs[$k]["lft"]);
			unset($childs[$k]["rgt"]);
			unset($childs[$k]["depth"]);
			$childs[$k]["id"] = $child_id;
			$childs[$k]["parent"] = $a_parent_id;
			
			$this->parent[$c["id"]] = $a_parent_id;
			
			// @todo: prepare this for tref id?
			if (ilSkillTreeNode::_lookupDraft($c["child"]))
			{
				$this->drafts[] = $child_id;
				$drafts[] = $k;
			}
		}
		if (!$this->getShowDraftNodes())
		{
			foreach ($drafts as $d)
			{
				unset($childs[$d]);
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

		$a_parent_id_parts = explode(":", $a_node["id"]);
		$a_parent_skl_tree_id = $a_parent_id_parts[0];
		$a_parent_skl_template_tree_id = $a_parent_id_parts[1];
		
		// title
		$title = $a_node["title"];
		
		// root?
		if ($a_node["type"] == "skrt")
		{
			$lng->txt("skmg_skills");
		}
		else
		{
			if ($a_node["type"] == "sktr")
			{
//				include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
//				$title.= " (".ilSkillTreeNode::_lookupTitle($a_parent_skl_template_tree_id).")";
			}
		}
		
		return $title;
	}
	
	/**
	 * Get node icon
	 *
	 * @param array 
	 * @return
	 */
	function getNodeIcon($a_node)
	{
		$a_id_parts = explode(":", $a_node["id"]);
		$a_skl_tree_id = $a_parent_id_parts[0];
		$a_skl_template_tree_id = $a_id_parts[1];

		// root?
		if ($a_node["type"] == "skrt")
		{
			$icon = ilUtil::getImagePath("icon_scat_s.png");
		}
		else
		{
			$type = $a_node["type"];
			if ($type == "sktr") 
			{
				include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
				$type = ilSkillTreeNode::_lookupType($a_skl_template_tree_id);
			}
			if ($type == "sktp")
			{
				$type = "skll";
			}
			if ($type == "sctp")
			{
				$type = "scat";
			}
			$icon = ilUtil::getImagePath("icon_".$type."_s.png");
		}
		
		return $icon;
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
		
		// we have a tree id like <skl_tree_id>:<skl_template_tree_id> here
		// use this, if you want a "common" skill id in format <skill_id>:<tref_id>
		$id_parts = explode(":", $a_node["id"]);
		if ($id_parts[1] == 0)
		{
			// skill in main tree
			$skill_id = $a_node["id"];
		}
		else
		{
			// skill in template
			$skill_id = $id_parts[1].":".$id_parts[0];
		}
		
		return "";
	}

	/**
	 * Is clickable
	 *
	 * @param
	 * @return
	 */
	function isNodeClickable($a_node)
	{
		return false;
	}
	
}

?>
