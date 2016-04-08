<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id\$
 * @ingroup 
 */
class ilLMTree extends ilTree
{
	static $instances = array();

	/**
	 * Constructor
	 *
	 * @param integer $a_tree_id tree id
	 */
	function __construct($a_tree_id)
	{
		parent::__construct($a_tree_id);
		$this->setTableNames('lm_tree','lm_data');
		$this->setTreeTablePK("lm_id");
		$this->useCache(true);
	}

	/**
	 * Get Instance
	 *
	 * @param
	 * @return
	 */
	static function getInstance($a_tree_id)
	{
		if (isset(self::$instances[$a_tree_id]))
		{
			return self::$instances[$a_tree_id];
		}
		$tree = new ilLMTree($a_tree_id);
		self::$instances[$a_tree_id] = $tree;

		return $tree;
	}


	/**
	 * Check if cache is active
	 * @return bool
	 */
	public function isCacheUsed()
	{
		return $this->use_cache;
	}

	
	public function getLastActivePage()
	{
		global $ilDB;
		
		$ilDB->setLimit(1);
		
		$sql = "SELECT lm_data.obj_id".
			" FROM lm_data".
			" JOIN lm_tree ON (lm_tree.child = lm_data.obj_id)".
			" JOIN page_object ON (page_object.page_id = lm_data.obj_id AND page_object.parent_type = ".$ilDB->quote("lm", "text").")".
			" WHERE lm_tree.lm_id = ".$ilDB->quote($this->tree_id, "integer").
			" AND lm_data.type = ".$ilDB->quote("pg", "text").
			" AND page_object.active = ".$ilDB->quote(1, "integer").
			" ORDER BY lm_tree.rgt DESC";
		$set = $ilDB->query($sql);
		$row = $ilDB->fetchAssoc($set);		
		return (int)$row["obj_id"];
	}

}

?>