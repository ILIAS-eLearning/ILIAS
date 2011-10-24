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
	 */
	function getSkillTreePath($a_base_skill_id, $a_tref_id = 0)
	{
		if ($a_tref_id > 0)
		{
			$path = $this->getPathFull($a_tref_id);
			$sub_path = $this->getPathFull($a_base_skill_id);
			$found = false;
			foreach ($sub_path as $s)
			{
				include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
				if ($found)
				{
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
		}
		
		return $path;
	}
	
}

?>
