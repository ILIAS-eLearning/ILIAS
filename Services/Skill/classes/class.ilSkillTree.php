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

}

?>
