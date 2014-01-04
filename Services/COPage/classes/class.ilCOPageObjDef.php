<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * COPage page object definition handler 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesCOPage
 */
class ilCOPageObjDef
{
	static $page_obj_def = null;
	
	/**
	 * Init
	 *
	 * @param
	 * @return
	 */
	static function init()
	{
		global $ilDB;
		
		if (self::$page_obj_def == null)
		{
			$set = $ilDB->query("SELECT * FROM copg_pobj_def ");
			while ($rec = $ilDB->fetchAssoc($set))
			{
				self::$page_obj_def[$rec["parent_type"]] = $rec;
			}
		}
	}
	
	/**
	 * Get definitions
	 *
	 * @param
	 * @return
	 */
	function getDefinitions()
	{
		self::init();
		return self::$page_obj_def;
	}
	
	/**
	 * Get definition by parent type
	 *
	 * @param string $a_parent_type parent type
	 * @return array definition
	 */
	static function getDefinitionByParentType($a_parent_type)
	{
		self::init();
		return self::$page_obj_def[$a_parent_type];
	}
	
}

?>
