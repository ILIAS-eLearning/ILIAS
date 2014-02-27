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
	private function __construct($a_tree_id)
	{
		parent::ilTree($a_tree_id);
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


}

?>