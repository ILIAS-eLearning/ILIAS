<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tree/classes/class.ilTree.php");

/**
 * Skill tree
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesSkill
 */
class ilSkillTree extends ilTree
{
	function __construct()
	{
		parent::__construct(1);	// only one skill tree, with ID 1
		$this->setTreeTablePK("skl_tree_id");
		$this->setTableNames('skl_tree', 'skl_tree_node');
	}

	/**
	 * Get skill tree path
	 *
	 * @param int $a_base_skill_id base skill id
	 * @param int $a_tref_id template reference id
	 * @return array path
	 */
	function getSkillTreePath($a_base_skill_id, $a_tref_id = 0)
	{
		if ($a_tref_id > 0)
		{
			include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
			$path = $this->getPathFull($a_tref_id);
			$sub_path = $this->getPathFull($a_base_skill_id);
			foreach ($path as $k => $v)
			{
				if ($v["child"] != $a_tref_id)
				{
					$path[$k]["skill_id"] = $v["child"];
					$path[$k]["tref_id"] = 0;
				}
				else
				{
					$path[$k]["skill_id"] = ilSkillTemplateReference::_lookupTemplateId($a_tref_id);
					$path[$k]["tref_id"] = $a_tref_id;
				}
			}
			$found = false;
			foreach ($sub_path as $s)
			{
				if ($found)
				{
					$s["skill_id"] = $s["child"];
					$s["tref_id"] = $a_tref_id;
					$path[] = $s;
				}
				if ($s["child"] == ilSkillTemplateReference::_lookupTemplateId($a_tref_id))
				{
					$found = true;
				}
			}
				
		}
		else
		{
			$path = $this->getPathFull($a_base_skill_id);
			foreach ($path as $k => $v)
			{
				$path[$k]["skill_id"] = $v["child"];
				$path[$k]["tref_id"] = 0;
			}
		}
		
		return $path;
	}

	/**
	 * Get skill tree path as string
	 *
	 * @param int $a_base_skill_id base skill id
	 * @param int $a_tref_id template reference id
	 * @return string path
	 */
	function getSkillTreePathAsString($a_base_skill_id, $a_tref_id = 0)
	{
		$path = $this->getSkillTreePath($a_base_skill_id, $a_tref_id);
		$str = "";
		$sep = "";
		foreach ($path as $p)
		{
			if ($p["type"] != "skrt" && $p["child"] != $a_base_skill_id)
			{
				$str.= $sep.$p["title"];
				$sep = " > ";
			}
		}
		return $str;
	}

	/**
	 * Get top parent node id for a node
	 *
	 * @param int $a_node_id
	 * @return int top parent node id
	 */
	function getTopParentNodeId($a_node_id)
	{
		$path = $this->getPathId($a_node_id);
		return (int) $path[1];
	}


}

?>
