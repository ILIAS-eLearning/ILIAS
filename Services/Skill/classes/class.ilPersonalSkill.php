<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/interfaces/interface.ilSkillUsageInfo.php");

/**
 * Personal skill
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilPersonalSkill implements ilSkillUsageInfo
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
		
		include_once "Services/Skill/classes/class.ilSkillTreeNode.php";
		
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$stree = new ilSkillTree();

		$set = $ilDB->query("SELECT * FROM skl_personal_skill ".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer")
			);
		$pskills = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			if ($stree->isInTree($rec["skill_node_id"]))
			{
				$pskills[$rec["skill_node_id"]] = array("skill_node_id" => $rec["skill_node_id"],
					"title" => ilSkillTreeNode::_lookupTitle($rec["skill_node_id"]));
			}
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
	 * @param int $a_user_id user id
	 * @param int $a_skill_node_id the "selectable" top skill
	 */
	function removeSkill($a_user_id, $a_skill_node_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM skl_personal_skill WHERE ".
			" user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND skill_node_id = ".$ilDB->quote($a_skill_node_id, "integer")
			);
		
	}
	
	
	//
	// Assigned materials
	//
	
	/**
	 * Assign material to skill level
	 *
	 * @param int $a_user_id user id
	 * @param int $a_top_skill the "selectable" top skill
	 * @param int $a_tref_id template reference id
	 * @param int $a_basic_skill the basic skill the level belongs to
	 * @param int $a_level level id
	 * @param int $a_wsp_id workspace object
	 */
	static function assignMaterial($a_user_id, $a_top_skill, $a_tref_id, $a_basic_skill, $a_level, $a_wsp_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM skl_assigned_material ".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND top_skill_id = ".$ilDB->quote($a_top_skill, "integer").
			" AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer").
			" AND skill_id = ".$ilDB->quote($a_basic_skill, "integer").
			" AND level_id = ".$ilDB->quote($a_level, "integer").
			" AND wsp_id = ".$ilDB->quote($a_wsp_id, "integer")
			);
		if (!$ilDB->fetchAssoc($set))
		{
			$ilDB->manipulate("INSERT INTO skl_assigned_material ".
				"(user_id, top_skill_id, tref_id, skill_id, level_id, wsp_id) VALUES (".
				$ilDB->quote($a_user_id, "integer").",".
				$ilDB->quote($a_top_skill, "integer").",".
				$ilDB->quote((int) $a_tref_id, "integer").",".
				$ilDB->quote($a_basic_skill, "integer").",".
				$ilDB->quote($a_level, "integer").",".
				$ilDB->quote($a_wsp_id, "integer").
				")");
		}
	}
	
	/**
	 * Get assigned material (for a skill level and user)
	 *
	 * @param int $a_user_id user id
	 * @param int $a_tref_id template reference id
	 * @param int $a_level level id
	 */
	static function getAssignedMaterial($a_user_id, $a_tref_id, $a_level)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM skl_assigned_material ".
			" WHERE level_id = ".$ilDB->quote($a_level, "integer").
			" AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer")
			);
		$mat = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$mat[] = $rec;
		}
		return $mat;
	}
	
	/**
	 * Get assigned material (for a skill level and user)
	 *
	 * @param int $a_user_id user id
	 * @param int $a_tref_id template reference id
	 * @param int $a_level level id
	 */
	static function countAssignedMaterial($a_user_id, $a_tref_id, $a_level)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT count(*) as cnt FROM skl_assigned_material ".
			" WHERE level_id = ".$ilDB->quote($a_level, "integer").
			" AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		return $rec["cnt"];
	}
	
	/**
	 * Remove material
	 *
	 * @param
	 * @return
	 */
	static function removeMaterial($a_user_id, $a_tref_id, $a_level_id, $a_wsp_id)
	{
		global $ilDB;
		
		$t = "DELETE FROM skl_assigned_material WHERE ".
			" user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer").
			" AND level_id = ".$ilDB->quote($a_level_id, "integer").
			" AND wsp_id = ".$ilDB->quote($a_wsp_id, "integer");

		$ilDB->manipulate($t);
	}
	
	//
	// Self evaluation
	//
	
	/**
	 * Save self evaluation
	 *
	 * @param int $a_user_id user id
	 * @param int $a_top_skill the "selectable" top skill
	 * @param int $a_tref_id template reference id
	 * @param int $a_basic_skill the basic skill the level belongs to
	 * @param int $a_level level id
	 */
	static function saveSelfEvaluation($a_user_id, $a_top_skill, $a_tref_id, $a_basic_skill, $a_level)
	{
		global $ilDB;

		include_once("./Services/Skill/classes/class.ilBasicSkill.php");
		ilBasicSkill::writeUserSkillLevelStatus($a_level, $a_user_id,
			0, $a_tref_id, ilBasicSkill::ACHIEVED, false,
			1);

		return;
		
		$set = $ilDB->query("SELECT * FROM skl_self_eval_level ".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND top_skill_id = ".$ilDB->quote($a_top_skill, "integer").
			" AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer").
			" AND skill_id = ".$ilDB->quote($a_basic_skill, "integer"));
		if (!$ilDB->fetchAssoc($set))
		{
			$ilDB->manipulate("INSERT INTO skl_self_eval_level ".
				"(user_id, top_skill_id, tref_id, skill_id, level_id, last_update) VALUES (".
				$ilDB->quote($a_user_id, "integer").",".
				$ilDB->quote($a_top_skill, "integer").",".
				$ilDB->quote((int) $a_tref_id, "integer").",".
				$ilDB->quote($a_basic_skill, "integer").",".
				$ilDB->quote($a_level, "integer").",".
				$ilDB->quote(ilUtil::now(), "timestamp").
				")");
		}
		else
		{
			$ilDB->manipulate("UPDATE skl_self_eval_level SET ".
				" level_id = ".$ilDB->quote($a_level, "integer").", ".
				" last_update = ".$ilDB->quote(ilUtil::now(), "timestamp").
				" WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
				" AND top_skill_id = ".$ilDB->quote($a_top_skill, "integer").
				" AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer").
				" AND skill_id = ".$ilDB->quote($a_basic_skill, "integer"));
		}
	}

	/**
	 * Get self evaluation
	 *
	 * @param int $a_user_id user id
	 * @param int $a_top_skill the "selectable" top skill
	 * @param int $a_tref_id template reference id
	 * @param int $a_basic_skill the basic skill the level belongs to
	 * @return int level id
	 */
	static function getSelfEvaluation($a_user_id, $a_top_skill, $a_tref_id, $a_basic_skill)
	{
		global $ilDB;

		include_once("./Services/Skill/classes/class.ilBasicSkill.php");
		$bs = new ilBasicSkill($a_basic_skill);
		return $bs->getLastLevelPerObject($a_tref_id, 0, $a_user_id, 1);
		
		$set = $ilDB->query("SELECT level_id FROM skl_self_eval_level ".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND top_skill_id = ".$ilDB->quote($a_top_skill, "integer").
			" AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer").
			" AND skill_id = ".$ilDB->quote($a_basic_skill, "integer")
			);
		$rec  = $ilDB->fetchAssoc($set);
		
		return (int) $rec["level_id"];
	}

	/**
	 * Get self evaluation
	 *
	 * @param int $a_user_id user id
	 * @param int $a_top_skill the "selectable" top skill
	 * @param int $a_tref_id template reference id
	 * @param int $a_basic_skill the basic skill the level belongs to
	 * @return int level id
	 */
	static function getSelfEvaluationDate($a_user_id, $a_top_skill, $a_tref_id, $a_basic_skill)
	{
		global $ilDB;

		include_once("./Services/Skill/classes/class.ilBasicSkill.php");
		$bs = new ilBasicSkill($a_basic_skill);
		return $bs->getLastUpdatePerObject($a_tref_id, 0, $a_user_id, 1);

		$set = $ilDB->query("SELECT last_update FROM skl_self_eval_level ".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND top_skill_id = ".$ilDB->quote($a_top_skill, "integer").
			" AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer").
			" AND skill_id = ".$ilDB->quote($a_basic_skill, "integer")
			);
		$rec  = $ilDB->fetchAssoc($set);
		
		return $rec["last_update"];
	}

	/**
	 * Get usage info
	 *
	 * @param array $a_cskill_ids skill ids
	 * @param array $a_usages usages array
	 */
	static public function getUsageInfo($a_cskill_ids, &$a_usages)
	{
		global $ilDB;

		// material
		include_once("./Services/Skill/classes/class.ilSkillUsage.php");
		ilSkillUsage::getUsageInfoGeneric($a_cskill_ids, $a_usages, ilSkillUsage::USER_MATERIAL,
				"skl_assigned_material", "user_id");

		// self evaluations
		ilSkillUsage::getUsageInfoGeneric($a_cskill_ids, $a_usages, ilSkillUsage::SELF_EVAL,
				"skl_self_eval_level", "user_id");

		// users that use the skills as personal skills
		$pskill_ids = array();
		$tref_ids = array();
		foreach ($a_cskill_ids as $cs)
		{
			if ($cs["tref_id"] > 0)
			{
				include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
				if (ilSkillTemplateReference::_lookupTemplateId($cs["tref_id"]) == $cs["skill_id"])
				{
					$pskill_ids[$cs["tref_id"]] = $cs["tref_id"];
					$tref_ids[(int) $cs["tref_id"]] = $cs["skill_id"];
				}
			}
			else
			{
				$pskill_ids[$cs["skill_id"]] = $cs["skill_id"];
			}
		}
		$set = $ilDB->query("SELECT skill_node_id, user_id FROM skl_personal_skill ".
			" WHERE ".$ilDB->in("skill_node_id", $pskill_ids, false, "integer").
			" GROUP BY skill_node_id, user_id"
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			if (isset($tref_ids[(int) $rec["skill_node_id"]]))
			{
				$a_usages[$tref_ids[$rec["skill_node_id"]].":".$rec["skill_node_id"]][ilSkillUsage::PERSONAL_SKILL][] =
					array("key" => $rec["user_id"]);
			}
			else
			{
				$a_usages[$rec["skill_node_id"].":0"][ilSkillUsage::PERSONAL_SKILL][] =
					array("key" => $rec["user_id"]);
			}
		}
	}

}

?>
