<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilSkillTree.php");

/**
 * A node in the skill tree
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
class ilSkillTreeNode
{
	const STATUS_PUBLISH = 0;
	const STATUS_DRAFT = 1;
	const STATUS_OUTDATED = 2;
	var $type;
	var $id;
	var $title;

	/**
	* @param	int		node id
	*/
	function ilSkillTreeNode($a_id = 0)
	{
		$this->id = $a_id;
		
		$this->skill_tree = new ilSkillTree();

		if($a_id != 0)
		{
			$this->read();
		}
	}

	/**
	 * Set title
	 *
	 * @param	string		$a_title	title
	 */
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	 * Get title
	 *
	 * @return	string		title
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set type
	 *
	 * @param	string		Type
	 */
	function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	 * Get type
	 *
	 * @return	string		Type
	 */
	function getType()
	{
		return $this->type;
	}

	/**
	 * Set Node ID
	 *
	 * @param	int		Node ID
	 */
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	 * Get Node ID
	 *
	 * @param	int		Node ID
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 * Set self evaluation
	 *
	 * @param	boolean	self evaluation
	 */
	function setSelfEvaluation($a_val)
	{
		$this->self_eval = $a_val;
	}

	/**
	 * Get self evaluation
	 *
	 * @return	boolean	self evaluation
	 */
	function getSelfEvaluation()
	{
		return $this->self_eval;
	}
	
	/**
	 * Set order nr
	 *
	 * @param int $a_val order nr	
	 */
	function setOrderNr($a_val)
	{
		$this->order_nr = $a_val;
	}
	
	/**
	 * Get order nr
	 *
	 * @return int order nr
	 */
	function getOrderNr()
	{
		return $this->order_nr;
	}

	/**
	 * Get all status
	 *
	 * @return array array of status, key is value, value is lang text
	 */
	static function getAllStatus()
	{
		global $lng;

		return array(
			self::STATUS_DRAFT => $lng->txt("skmg_status_draft"),
			self::STATUS_PUBLISH => $lng->txt("skmg_status_publish"),
			self::STATUS_OUTDATED => $lng->txt("skmg_status_outdated")
		);
	}

	/**
	 * Get status info
	 *
	 * @param int $a_status status
	 * @return string info text
	 */
	static function getStatusInfo($a_status)
	{
		global $lng;

		switch($a_status)
		{
			case self::STATUS_PUBLISH: return $lng->txt("skmg_status_publish_info");
			case self::STATUS_DRAFT: return $lng->txt("skmg_status_draft_info");
			case self::STATUS_OUTDATED: return $lng->txt("skmg_status_outdated_info");
		}
		return "";
	}

	/**
	* Read Data of Node
	*/
	function read()
	{
		global $ilBench, $ilDB;

		if(!isset($this->data_record))
		{
			$query = "SELECT * FROM skl_tree_node WHERE obj_id = ".
				$ilDB->quote($this->id, "integer");
			$obj_set = $ilDB->query($query);
			$this->data_record = $ilDB->fetchAssoc($obj_set);
		}
		$this->setType($this->data_record["type"]);
		$this->setTitle($this->data_record["title"]);
		$this->setOrderNr($this->data_record["order_nr"]);
		$this->setSelfEvaluation($this->data_record["self_eval"]);
		$this->setStatus($this->data_record["status"]);
	}

	/**
	* this method should only be called by class ilSCORM2004NodeFactory
	*/
	function setDataRecord($a_record)
	{
		$this->data_record = $a_record;
	}

	/**
	 * Lookup Title
	 *
	 * @param	int			Node ID
	 * @return	string		Title
	 */
	protected static function _lookup($a_obj_id, $a_field)
	{
		global $ilDB;

		$query = "SELECT $a_field FROM skl_tree_node WHERE obj_id = ".
			$ilDB->quote($a_obj_id, "integer");
		$obj_set = $ilDB->query($query);
		$obj_rec = $ilDB->fetchAssoc($obj_set);

		return $obj_rec[$a_field];
	}

	/**
	 * Lookup Title
	 *
	 * @param	int			node ID
	 * @return	string		title
	 */
	static function _lookupTitle($a_obj_id, $a_tref_id = 0)
	{
		global $ilDB;

		include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
		if ($a_tref_id > 0 && ilSkillTemplateReference::_lookupTemplateId($a_tref_id) == $a_obj_id)
		{
			return self::_lookup($a_tref_id, "title");
		}
		return self::_lookup($a_obj_id, "title");
	}
	
	/**
	 * Lookup self evaluation
	 *
	 * @param	int			node ID
	 * @return	boolean		selectable? (self evaluation=
	 */
	static function _lookupSelfEvaluation($a_obj_id)
	{
		global $ilDB;

		return self::_lookup($a_obj_id, "self_eval");
	}
	
	/**
	 * Lookup Status
	 *
	 * @param int $a_obj_id node ID
	 * @return int status
	 */
	static function _lookupStatus($a_obj_id)
	{
		global $ilDB;

		return self::_lookup($a_obj_id, "status");
	}
	
	/**
	* Lookup Type
	*
	* @param	int			Node ID
	* @return	string		Type
	*/
	static function _lookupType($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM skl_tree_node WHERE obj_id = ".
			$ilDB->quote($a_obj_id, "integer");
		$obj_set = $ilDB->query($query);
		$obj_rec = $ilDB->fetchAssoc($obj_set);

		return $obj_rec["type"];
	}

	/**
	 * Set status
	 *
	 * @param boolean $a_val status
	 */
	function setStatus($a_val)
	{
		$this->status = $a_val;
	}
	
	/**
	 * Get status
	 *
	 * @return int status
	 */
	function getStatus()
	{
		return $this->status;
	}
	
	/**
	 * Write Title
	 *
	 * @param	int			Node ID
	 * @param	string		Title
	 */
	static function _writeTitle($a_obj_id, $a_title)
	{
		global $ilDB;

		$query = "UPDATE skl_tree_node SET ".
			" title = ".$ilDB->quote($a_title, "text").
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer");

		$ilDB->manipulate($query);
	}

	/**
	 * Write Order Nr
	 *
	 * @param	int			Node ID
	 * @param	string		Order Nr
	 */
	static function _writeOrderNr($a_obj_id, $a_nr)
	{
		global $ilDB;

		$query = "UPDATE skl_tree_node SET ".
			" order_nr = ".$ilDB->quote($a_nr, "integer").
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer");
		$ilDB->manipulate($query);
	}
	
	/**
	* Create Node
	*
	* @param	boolean		Upload Mode
	*/
	function create()
	{
		global $ilDB;

		// insert object data
		$id = $ilDB->nextId("skl_tree_node");
		$query = "INSERT INTO skl_tree_node (obj_id, title, type, create_date, self_eval, order_nr, status) ".
			"VALUES (".
			$ilDB->quote($id, "integer").",".
			$ilDB->quote($this->getTitle(), "text").",".
			$ilDB->quote($this->getType(), "text").", ".
			$ilDB->now().", ".
			$ilDB->quote((int) $this->getSelfEvaluation(), "integer").", ".
			$ilDB->quote((int) $this->getOrderNr(), "integer").", ".
			$ilDB->quote((int) $this->getStatus(), "integer").
			")";
		$ilDB->manipulate($query);
		$this->setId($id);
	}

	/**
	* Update Node
	*/
	function update()
	{
		global $ilDB;

		$query = "UPDATE skl_tree_node SET ".
			" title = ".$ilDB->quote($this->getTitle(), "text").
			" ,self_eval = ".$ilDB->quote((int) $this->getSelfEvaluation(), "integer").
			" ,order_nr = ".$ilDB->quote((int) $this->getOrderNr(), "integer").
			" ,status = ".$ilDB->quote((int) $this->getStatus(), "integer").
			" WHERE obj_id = ".$ilDB->quote($this->getId(), "integer");

		$ilDB->manipulate($query);
	}

	/**
	* Delete Node
	*/
	function delete()
	{
		global $ilDB;
		
		$query = "DELETE FROM skl_tree_node WHERE obj_id= ".
			$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);
	}

	/**
	 * Put this object into the skill tree
	 */
	static function putInTree($a_obj, $a_parent_id = "", $a_target_node_id = "")
	{
		$skill_tree = new ilSkillTree();

		// determine parent
		$parent_id = ($a_parent_id != "")
			? $a_parent_id
			: $skill_tree->getRootId();

		// make a check, whether the type of object is allowed under
		// the parent
		$allowed = array(
			"skrt" => array("skll", "scat", "sktr", "sktp", "sctp"),
			"scat" => array("skll", "scat", "sktr"),
			"sctp" => array("sktp", "sctp"));
		$par_type = self::_lookupType($parent_id);
		if (!is_array($allowed[$par_type]) ||
			!in_array($a_obj->getType(), $allowed[$par_type]))
		{
			return;
		}
		
		// determine target
		if ($a_target_node_id != "")
		{
			$target = $a_target_node_id;
		}
		else
		{
			// determine last child that serves as predecessor
			$childs = $skill_tree->getChilds($parent_id);

			if (count($childs) == 0)
			{
				$target = IL_FIRST_NODE;
			}
			else
			{
				$target = $childs[count($childs) - 1]["obj_id"];
			}
		}

		if ($skill_tree->isInTree($parent_id) && !$skill_tree->isInTree($a_obj->getId()))
		{
			$skill_tree->insertNode($a_obj->getId(), $parent_id, $target);
		}
	}
	
	/**
	* Get scorm module editing tree
	*
	* @param	int		scorm module object id
	*
	* @return	object		tree object
	*/
	static function getTree($a_slm_obj_id)
	{
		$tree = new ilSkillTree();
		
		return $tree;
	}

	/**
	 * Check for unique types
	 */
	static function uniqueTypesCheck($a_items)
	{
		$types = array();
		if (is_array($a_items))
		{
			foreach($a_items as $item)
			{
				$type = ilSkillTreeNode::_lookupType($item);
				$types[$type] = $type;
			}
		}

		if (count($types) > 1)
		{
			return false;
		}
		return true;
	}

	/**
	 * Cut and copy a set of skills/skill categories into the clipboard
	 */
	function clipboardCut($a_tree_id, $a_ids)
	{
		self::clearClipboard();
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$tree = new ilSkillTree();

		if (!is_array($a_ids))
		{
			return false;
		}
		else
		{
			// get all "top" ids, i.e. remove ids, that have a selected parent
			foreach($a_ids as $id)
			{
				$path = $tree->getPathId($id);
				$take = true;
				foreach($path as $path_id)
				{
					if ($path_id != $id && in_array($path_id, $a_ids))
					{
						$take = false;
					}
				}
				if ($take)
				{
					$cut_ids[] = $id;
				}
			}
		}

		ilSkillTreeNode::clipboardCopy($a_tree_id, $cut_ids);

		// remove the objects from the tree
		// note: we are getting skills/categories which are *not* in the tree
		// we do not delete any pages/chapters here
		foreach ($cut_ids as $id)
		{
			$curnode = $tree->getNodeData($id);
			if ($tree->isInTree($id))
			{
				$tree->deleteTree($curnode);
			}
		}

	}


	/**
	 * Copy a set of skills/skill categories into the clipboard
	 */
	static function clipboardCopy($a_tree_id, $a_ids)
	{
		global $ilUser;
		
		self::clearClipboard();
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$tree = new ilSkillTree();
		
		// put them into the clipboard
		$time = date("Y-m-d H:i:s", time());
		foreach ($a_ids as $id)
		{
			$curnode = "";
			if ($tree->isInTree($id))
			{
				$curnode = $tree->getNodeData($id);
				$subnodes = $tree->getSubTree($curnode);
				foreach($subnodes as $subnode)
				{
					if ($subnode["child"] != $id)
					{
						$ilUser->addObjectToClipboard($subnode["child"],
							$subnode["type"], $subnode["title"],
							$subnode["parent"], $time, $subnode["lft"]);
					}
				}
			}
			$order = ($curnode["lft"] > 0)
				? $curnode["lft"]
				: (int) ($order + 1);
			$ilUser->addObjectToClipboard($id,
				ilSkillTreeNode::_lookupType($id), ilSkillTreeNode::_lookupTitle($id), 0, $time,
				$order);
		}
	}


	/**
	 * Insert basic skills from clipboard
	 */
	static function insertItemsFromClip($a_type, $a_obj_id)
	{
		global $ilCtrl, $ilUser;
		
		// @todo: move this to a service since it can be used here, too
		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");

		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$tree = new ilSkillTree();
		
		$parent_id = $a_obj_id;
		$target = IL_LAST_NODE;

		// cut and paste
		$skills = $ilUser->getClipboardObjects($a_type);  // this will get all skills _regardless_ of level
		$copied_nodes = array();
		foreach ($skills as $skill)
		{
			// if skill was already copied as part of tree - do not copy it again
			if(!in_array($skill["id"], array_keys($copied_nodes)))
			{
				$cid = ilSkillTreeNode::pasteTree($skill["id"], $parent_id, $target,
					$skill["insert_time"], $copied_nodes,
					(ilEditClipboard::getAction() == "copy"), true);
//				$target = $cid;
			}
		}

//		if (ilEditClipboard::getAction() == "cut")
//		{
			self::clearClipboard();
//		}

		ilSkillTreeNode::saveChildsOrder($a_obj_id, array(),
			in_array($a_type, array("sktp", "sctp")));

		return $copied_nodes;
	}

	/**
	 * Remove all skill items from clipboard
	 *
	 * @param
	 * @return
	 */
	static function clearClipboard()
	{
		global $ilUser;
		
		$ilUser->clipboardDeleteObjectsOfType("skll");
		$ilUser->clipboardDeleteObjectsOfType("scat");
		$ilUser->clipboardDeleteObjectsOfType("sktr");
		$ilUser->clipboardDeleteObjectsOfType("sktp");
		$ilUser->clipboardDeleteObjectsOfType("sctp");
		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
		ilEditClipboard::clear();
	}
	
	
	/**
	 * Paste item (tree) from clipboard to skill tree
	 */
	static function pasteTree($a_item_id, $a_parent_id, $a_target, $a_insert_time,
		&$a_copied_nodes, $a_as_copy = false, $a_add_suffix = false)
	{
		global $ilUser, $ilias, $ilLog, $lng;

		$item_type = ilSkillTreeNode::_lookupType($a_item_id);

		if ($item_type == "scat")
		{
			include_once("./Services/Skill/classes/class.ilSkillCategory.php");
			$item = new ilSkillCategory($a_item_id);
		}
		else if ($item_type == "skll")
		{
			include_once("./Services/Skill/classes/class.ilBasicSkill.php");
			$item = new ilBasicSkill($a_item_id);
		}
		else if ($item_type == "sktr")
		{
			include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
			$item = new ilSkillTemplateReference($a_item_id);
		}
		else if ($item_type == "sktp")
		{
			include_once("./Services/Skill/classes/class.ilBasicSkillTemplate.php");
			$item = new ilBasicSkillTemplate($a_item_id);
		}
		else if ($item_type == "sctp")
		{
			include_once("./Services/Skill/classes/class.ilSkillTemplateCategory.php");
			$item = new ilSkillTemplateCategory($a_item_id);
		}

		$ilLog->write("Getting from clipboard type ".$item_type.", ".
			"Item ID: ".$a_item_id);

		if ($a_as_copy)
		{
			$target_item = $item->copy();
			if($a_add_suffix)
			{
				$target_item->setTitle($target_item->getTitle()." ".$lng->txt("copy_of_suffix"));
				$target_item->update();
			}
			$a_copied_nodes[$item->getId()] = $target_item->getId();
		}
		else
		{
			$target_item = $item;
		}
		
		$ilLog->write("Putting into skill tree type ".$target_item->getType().
			"Item ID: ".$target_item->getId().", Parent: ".$a_parent_id.", ".
			"Target: ".$a_target);
		
		ilSkillTreeNode::putInTree($target_item, $a_parent_id, $a_target);
		
		$childs = $ilUser->getClipboardChilds($item->getId(), $a_insert_time);

		foreach($childs as $child)
		{
			ilSkillTreeNode::pasteTree($child["id"], $target_item->getId(),
				IL_LAST_NODE, $a_insert_time, $a_copied_nodes, $a_as_copy);
		}
		
		return $target_item->getId();
	}

	/**
	 * Is id in tree?
	 *
	 * @param
	 * @return
	 */
	static function isInTree($a_id)
	{
		$skill_tree = new ilSkillTree();
		if ($skill_tree->isInTree($a_id))
		{
			return true;
		}
		return false;
	}

	/**
	 * Get all self evaluation nodes
	 *
	 * @param
	 * @return
	 */
	static function getAllSelfEvaluationNodes()
	{
		global $ilDB;

		$set = $ilDB->query("SELECT obj_id, title FROM skl_tree_node WHERE ".
			" self_eval = ".$ilDB->quote(true, "integer")." ORDER BY TITLE "
			);
		$nodes = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$nodes[$rec["obj_id"]] = $rec["title"];
		}
		return $nodes;
	}

	/**
	 * Get top skill templates and template categories
	 *
	 * @param
	 * @return
	 */
	static function getTopTemplates()
	{
		$tr = new ilSkillTree();
		$childs = $tr->getChildsByTypeFilter($tr->getRootId(), array("sktp", "sctp"));
		
		return $childs;
	}
	
	/**
	 * Get selectable skills
	 *
	 * @param
	 * @return
	 */
	static function getSelectableSkills()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM skl_tree_node ".
			" WHERE self_eval = ".$ilDB->quote(1, "integer")
			);
		
		$sel_skills = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$sel_skills[] = $rec;
		}
		
		return $sel_skills;
	}

	/**
	 * Save childs order
	 *
	 * @param
	 * @return
	 */
	static function saveChildsOrder($a_par_id, $a_childs_order, $a_templates = false)
	{
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$skill_tree = new ilSkillTree();
		
		if ($a_par_id != $skill_tree->readRootId())
		{
			$childs = $skill_tree->getChilds($a_par_id);
		}
		else
		{
			if ($a_templates)
			{
				$childs = $skill_tree->getChildsByTypeFilter($a_par_id,
					array("skrt", "sktp", "sctp"));
			}
			else
			{
				$childs = $skill_tree->getChildsByTypeFilter($a_par_id,
					array("skrt", "skll", "scat", "sktr"));
			}
		}
		
		foreach ($childs as $k => $c)
		{
			if (isset($a_childs_order[$c["child"]]))
			{
				$childs[$k]["order_nr"] = (int) $a_childs_order[$c["child"]];
			}
		}
		
		$childs = ilUtil::sortArray($childs, "order_nr", "asc", true);

		$cnt = 10;
		foreach ($childs as $c)
		{
			ilSkillTreeNode::_writeOrderNr($c["child"], $cnt);
			$cnt += 10;
		}
	}
	
	/**
	 * Get icon path
	 *
	 * @param int $a_obj_id node id
	 * @param string $a_type node type
	 * @param string $a_size size
	 * @param int $a_status status
	 * @return string icon path
	 */
	static function getIconPath($a_obj_id, $a_type, $a_size = "", $a_status = 0)
	{
		if ($a_status == self::STATUS_DRAFT && $a_type == "sctp")
		{
			$a_type = "scat";
		}
		if ($a_status == self::STATUS_DRAFT && $a_type == "sktp")
		{
			$a_type = "skll";
		}
		
		$off = ($a_status == self::STATUS_DRAFT)
			? "_off"
			: "";
			
		$a_name = "icon_".$a_type.$a_size.$off.".svg";
		if ($a_type == "sktr")
		{
			include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
			$tid = ilSkillTemplateReference::_lookupTemplateId($a_obj_id);
			$type = ilSkillTreeNode::_lookupType($tid);
			if ($type == "sctp")
			{
				$a_name = "icon_sctr".$a_size.$off.".svg";
			}
		}
		$vers = "vers=".str_replace(array(".", " "), "-", ILIAS_VERSION);
		return ilUtil::getImagePath($a_name)."?".$vers;
	}
	
}
?>