<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Virtual skill tree
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ServicesSkill
 */
class ilVirtualSkillTree
{
	protected $include_drafts = false;
	protected $drafts = array();
	protected $include_outdated = false;
	protected $outdated = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$this->tree = new ilSkillTree();
	}
	
	/**
	 * Get root node
	 *
	 * @return array root node array
	 */
	function getRootNode()
	{
		$root_id = $this->tree->readRootId();
		$root_node = $this->tree->getNodeData($root_id);
		unset($root_node["child"]);
		$root_node["id"] = $root_id.":0";
		$root_node["cskill_id"] = $root_id.":0";

		return $root_node;
	}
	
	/**
	 * Set include drafts
	 *
	 * @param bool $a_val include drafts	
	 */
	function setIncludeDrafts($a_val)
	{
		$this->include_drafts = $a_val;
	}

	/**
	 * Get include drafts
	 *
	 * @return bool include drafts
	 */
	function getIncludeDrafts()
	{
		return $this->include_drafts;
	}

	/**
	 * Set include outdated
	 *
	 * @param bool $a_val include outdated
	 */
	function setIncludeOutdated($a_val)
	{
		$this->include_outdated = $a_val;
	}

	/**
	 * Get include outdated
	 *
	 * @return bool include outdated
	 */
	function getIncludeOutdated()
	{
		return $this->include_outdated;
	}
	
	/**
	 * Get node
	 *
	 * @param string $a_id vtree id
	 * @return array node array
	 */
	function getNode($a_id)
	{
		$id_parts = explode(":", $a_id);
		$skl_tree_id = $id_parts[0];
		$skl_template_tree_id = $id_parts[1];
	
		if ($skl_template_tree_id == 0 || (ilSkillTemplateReference::_lookupTemplateId($skl_tree_id)
					== $skl_template_tree_id))
		{
			$node_data = $this->tree->getNodeData($skl_tree_id);
			$node_data["parent"] = $node_data["parent"].":0";
		}
		else
		{
			$node_data = $this->tree->getNodeData($skl_template_tree_id);
			$node_data["parent"] = $skl_tree_id.":".$node_data["parent"];
		}

		unset($node_data["child"]);
		unset($node_data["skl_tree_id"]);
		unset($node_data["lft"]);
		unset($node_data["rgt"]);
		unset($node_data["depth"]);

		$node_data["id"] = $a_id;
		$cid = $this->getCSkillIdForVTreeId($a_id);
		$cid_parts = explode(":", $cid);
		$node_data["skill_id"] = $cid_parts[0];
		$node_data["tref_id"] = $cid_parts[1];
		$node_data["cskill_id"] = $cid;


		return $node_data;
	}

	
	/**
	 * Get childs of node
	 *
	 * @param string $a_parent_id parent id
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
		$outdated = array();
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
//echo "-".$child_id."-";
			$cid = $this->getCSkillIdForVTreeId($child_id);
//echo "-".$cid."-";
			$cid_parts = explode(":", $cid);
			$childs[$k]["skill_id"] = $cid_parts[0];
			$childs[$k]["tref_id"] = $cid_parts[1];
			$childs[$k]["cskill_id"] = $cid;
			$childs[$k]["parent"] = $a_parent_id;
			
			$this->parent[$c["id"]] = $a_parent_id;
			
			// @todo: prepare this for tref id?
			if (ilSkillTreeNode::_lookupStatus($c["child"]) == ilSkillTreeNode::STATUS_DRAFT ||
				in_array($a_parent_id, $this->drafts))
			{
				$this->drafts[] = $child_id;
				$drafts[] = $k;
			}
			if (ilSkillTreeNode::_lookupStatus($c["child"]) == ilSkillTreeNode::STATUS_OUTDATED ||
				in_array($a_parent_id, $this->outdated))
			{
				$this->outdated[] = $child_id;
				$outdated[] = $k;
			}
		}
		if (!$this->getIncludeDrafts())
		{
			foreach ($drafts as $d)
			{
				unset($childs[$d]);
			}
		}
		if (!$this->getIncludeOutdated())
		{
			foreach ($outdated as $d)
			{
				unset($childs[$d]);
			}
		}

		return $childs;
	}

	/**
	 * Get childs of node for cskill id
	 *
	 * @param string $a_cskill_id common skill id <skill_id>:<tref_id>
	 * @return array array of childs
	 */
	function getChildsOfNodeForCSkillId($a_cskill_id)
	{
		$id_parts = explode(":", $a_cskill_id);
		if ($id_parts[1] == 0)
		{
			$id = $id_parts[0].":0";
		}
		else
		{
			$id = $id_parts[1].":".$id_parts[0];
		}
		return $this->getChildsOfNode($id);
	}

	/**
	 * Get common skill id for tree id
	 *
	 * @param string $a_vtree_id vtree id
	 * @return string cskill id
	 */
	function getCSkillIdForVTreeId($a_vtree_id)
	{
		$id_parts = explode(":", $a_vtree_id);
		if ($id_parts[1] == 0)
		{
			// skill in main tree
			$skill_id = $id_parts[0];
			$tref_id = 0;
		}
		else
		{
			// skill in template
			$tref_id = $id_parts[0];
			$skill_id = $id_parts[1];
		}
		return $skill_id.":".$tref_id;
	}

	
	/**
	 * Get node content
	 *
	 * @param array $a_node node data
	 * @return string title
	 */
	function getNodeTitle($a_node)
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
	 * Get sub tree
	 *
	 * @param string $a_cskill_id cskill id
	 * @param bool $a_only_basic return only basic skills (and basic skill templates)
	 * @return array node array
	 */
	function getSubTreeForCSkillId($a_cskill_id, $a_only_basic = false)
	{
		$id_parts = explode(":", $a_cskill_id);
		if ($id_parts[1] == 0)
		{
			$id = $id_parts[0].":0";
		}
		else
		{
			$id = $id_parts[1].":".$id_parts[0];
		}
		
		$result = array();

		$node = $this->getNode($id);
		if (!$a_only_basic || in_array($node["type"], array("skll", "sktp")) ||
			($node["type"] == "sktr" && ilSkillTreeNode::_lookupType($node["skill_id"]) == "sktp"))
		{
			$result[] = $node;
		}
		$this->__getSubTreeRec($id, $result, $a_only_basic);
				
		return $result;
	}

	/**
	 * Get subtree, internal
	 *
	 * @param string $id vtree id
	 * @param array $result node array (called by reference)
	 * @param bool $a_only_basic return only basic skills (and basic skill templates)
	 */
	private function __getSubTreeRec($id, &$result, $a_only_basic)
	{
		$childs = $this->getChildsOfNode($id);
		foreach ($childs as $c)
		{
			if (!$a_only_basic || in_array($c["type"], array("skll", "sktp")) ||
				($c["type"] == "sktr" && ilSkillTreeNode::_lookupType($c["skill_id"]) == "sktp"))
			{
				$result[] = $c;
			}
			$this->__getSubTreeRec($c["id"], $result, $a_only_basic);
		}
	}

	/**
	 * Is draft
	 *
	 * @param int $a_node_id node id
	 * @return bool is draft true/false
	 */
	function isDraft($a_node_id)
	{
		return in_array($a_node_id, $this->drafts);
	}

	/**
	 * Is outdated
	 *
	 * @param int $a_node_id node id
	 * @return bool is outdated true/false
	 */
	function isOutdated($a_node_id)
	{
		return in_array($a_node_id, $this->outdated);
	}

}

?>
