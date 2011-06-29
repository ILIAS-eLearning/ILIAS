<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Personal skill
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilPersonalSkill
{
	/**
	 * Get personal selected user skills
	 *
	 * @param int $a_user_id user id
	 * @return array 
	 */
	static function getSelectedUserSkills($a_user_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM skl_personal_skill ".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer")
			);
		$pskills = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$pskills[$rec["skill_node_id"]] = array("skill_node_id" => $rec["skill_node_id"],
				"title" => ilSkillTreeNode::_lookupTitle($rec["skill_node_id"]));
		}
		return $pskills;
	}
	
	/**
	 * Add personal skill
	 *
	 * @param int $a_user_id
	 * @param int $a_skill_node_id
	 */
	function addPersonalSkill($a_user_id, $a_skill_node_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM skl_personal_skill ".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND skill_node_id = ".$ilDB->quote($a_skill_node_id, "integer")
			);
		if (!$ilDB->fetchAssoc($set))
		{
			$ilDB->manipulate("INSERT INTO skl_personal_skill ".
				"(user_id, skill_node_id) VALUES (".
				$ilDB->quote($a_user_id, "integer").",".
				$ilDB->quote($a_skill_node_id, "integer").
				")");
		}
	}
	
	/**
	 * Remove personal skill
	 *
	 * @param
	 * @return
	 */
	function removeSkill($a_user_id, $a_skill_node_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM skl_personal_skill WHERE ".
			" user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND skill_node_id = ".$ilDB->quote($a_skill_node_id, "integer")
			);
		
	}
	
	
}

?>
