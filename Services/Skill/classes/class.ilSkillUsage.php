<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skill usage
 *
 * With this class a general skill use by an object (identified by its obj_id)
 * is registered or unregistered.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesSkill
 */
class ilSkillUsage
{
	const TYPE_GENERAL = 1;
	
	/**
	 * Set usage
	 *
	 * @param int $a_obj_id object id
	 * @param int $a_skill_id skill id
	 * @param int $a_tref_id tref id
	 */
	static function setUsage($a_obj_id, $a_skill_id, $a_tref_id, $a_use = true)
	{
		global $ilDB;
		
		if ($a_use)
		{
			$ilDB->replace("skl_usage",
				array(
					"obj_id" => array("integer", $obj_id),
					"skill_id" => array("integer", $a_skill_id),
					"tref_id" => array("integer", $a_tref_id)
					),
				array()
				);
		}
		else
		{
			$ilDB->manipulate("DELETE FROM skl_usage WHERE ".
				" obj_id = ".$ilDB->quote($a_obj_id, "integer").
				" AND skill_id = ".$ilDB->quote($a_skill_id, "integer").
				" AND tref_id = ".$ilDB->quote($a_tref_id, "integer")
				);
			
		}
	}
	
	/**
	 * Get usages
	 *
	 * @param int $a_skill_id skill id
	 * @param int $a_tref_id tref id
	 * @return array of int object ids
	 */
	function getUsages($a_skill_id, $a_tref_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT obj_id FROM skl_usage ".
			" WHERE skill_id = ".$ilDB->quote($a_skill_id, "integer").
			" AND tref_id = ".$ilDB->quote($a_tref_id, "integer")
			);
		$obj_ids = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$obj_ids[] = $rec["obj_id"];
		}
		
		return $obj_ids;
	}
	
	
}

?>
