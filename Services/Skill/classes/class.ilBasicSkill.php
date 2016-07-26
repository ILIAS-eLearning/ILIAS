<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
include_once("./Services/Skill/interfaces/interface.ilSkillUsageInfo.php");

/**
 * Basic Skill
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
class ilBasicSkill extends ilSkillTreeNode implements ilSkillUsageInfo
{
	const ACHIEVED = 1;
	const NOT_ACHIEVED = 0;

	const EVAL_BY_OTHERS_= 0;
	const EVAL_BY_SELF = 1;
	const EVAL_BY_ALL = 2;

	var $id;

	/**
	 * Constructor
	 * @access	public
	 */
	function __construct($a_id = 0)
	{
		parent::__construct($a_id);
		$this->setType("skll");
	}

	/**
	 * Read data from database
	 */
	function read()
	{
		parent::read();
	}

	/**
	 * Create skill
	 *
	 */
	function create()
	{
		parent::create();
	}

	/**
	 * Delete skill
	 */
	function delete()
	{
		global $ilDB;

		$ilDB->manipulate("DELETE FROM skl_level WHERE "
			." skill_id = ".$ilDB->quote($this->getId(), "integer")
			);

		$ilDB->manipulate("DELETE FROM skl_user_has_level WHERE "
			." skill_id = ".$ilDB->quote($this->getId(), "integer")
			);

		parent::delete();
	}

	/**
	 * Copy basic skill
	 */
	function copy()
	{
		$skill = new ilBasicSkill();
		$skill->setTitle($this->getTitle());
		$skill->setType($this->getType());
		$skill->setSelfEvaluation($this->getSelfEvaluation());
		$skill->setOrderNr($this->getOrderNr());
		$skill->create();

		$levels = $this->getLevelData();
		if (sizeof($levels))
		{
			foreach($levels as $item)
			{
				$skill->addLevel($item["title"], $item["description"]);
			}
		}
		$skill->update();
		
		return $skill;
	}

	//
	//
	// Skill level related methods
	//
	//

	/**
	 * Add new level
	 *
	 * @param	string	title
	 * @param	string	description
	 */
	function addLevel($a_title, $a_description, $a_import_id = "")
	{
		global $ilDB;

		$nr = $this->getMaxLevelNr();
		$nid = $ilDB->nextId("skl_level");
		$ilDB->insert("skl_level", array(
				"id" => array("integer", $nid),
				"skill_id" => array("integer", $this->getId()),
				"nr" => array("integer", $nr+1),
				"title" => array("text", $a_title),
				"description" => array("clob", $a_description),
				"import_id" => array("text", $a_import_id),
				"creation_date" => array("timestamp", ilUtil::now())
			));

	}

	/**
	 * Get maximum level nr
	 *
	 * @return	int		maximum level nr of skill
	 */
	function getMaxLevelNr()
	{
		global $ilDB;

		$set = $ilDB->query("SELECT MAX(nr) mnr FROM skl_level WHERE ".
			" skill_id = ".$ilDB->quote($this->getId(), "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		return (int) $rec["mnr"];
	}

	/**
	 * Get level data
	 *
	 * @return	array	level data
	 */
	function getLevelData($a_id = 0)
	{
		global $ilDB;

		if ($a_id > 0)
		{
			$and = " AND id = ".$ilDB->quote($a_id, "integer");
		}

		$set = $ilDB->query("SELECT * FROM skl_level WHERE ".
			" skill_id = ".$ilDB->quote($this->getId(), "integer").
			$and.
			" ORDER BY nr"
			);
		$levels = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			if ($a_id > 0)
			{
				return $rec;
			}
			$levels[] = $rec;
		}
		return $levels;
	}

	/**
	 * Lookup level property
	 *
	 * @param	id		level id
	 * @return	mixed	property value
	 */
	protected static function lookupLevelProperty($a_id, $a_prop)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT $a_prop FROM skl_level WHERE ".
			" id = ".$ilDB->quote($a_id, "integer")
		);
		$rec = $ilDB->fetchAssoc($set);
		return $rec[$a_prop];
	}

	/**
	 * Lookup level title
	 *
	 * @param	int		level id
	 * @return	string	level title
	 */
	static function lookupLevelTitle($a_id)
	{
		return ilBasicSkill::lookupLevelProperty($a_id, "title");
	}

	/**
	 * Lookup level description
	 *
	 * @param	int		level id
	 * @return	string	level description
	 */
	static function lookupLevelDescription($a_id)
	{
		return ilBasicSkill::lookupLevelProperty($a_id, "description");
	}

	/**
	 * Lookup level skill id
	 *
	 * @param	int		level id
	 * @return	string	skill id
	 */
	static function lookupLevelSkillId($a_id)
	{
		return ilBasicSkill::lookupLevelProperty($a_id, "skill_id");
	}

	/**
	 * Write level property
	 *
	 * @param
	 * @return
	 */
	static protected function writeLevelProperty($a_id, $a_prop, $a_value, $a_type)
	{
		global $ilDB;

		$ilDB->update("skl_level", array(
			$a_prop => array($a_type, $a_value),
			), array(
			"id" => array("integer", $a_id),
		));
	}

	/**
	 * Write level title
	 *
	 * @param	int		level id
	 * @param	text	level title
	 */
	static function writeLevelTitle($a_id, $a_title)
	{
		ilBasicSkill::writeLevelProperty($a_id, "title", $a_title, "text");
	}

	/**
	 * Write level description
	 *
	 * @param	int		level id
	 * @param	text	level description
	 */
	static function writeLevelDescription($a_id, $a_description)
	{
		ilBasicSkill::writeLevelProperty($a_id, "description", $a_description, "clob");
	}

	/**
	 * Update level order
	 *
	 * @param
	 * @return
	 */
	function updateLevelOrder($order)
	{
		global $ilDB;

		asort($order);

		$cnt = 1;
		foreach ($order as $id => $o)
		{
			$ilDB->manipulate("UPDATE skl_level SET ".
				" nr = ".$ilDB->quote($cnt, "integer").
				" WHERE id = ".$ilDB->quote($id, "integer")
				);
			$cnt++;
		}
	}

	/**
	 * Delete level
	 *
	 * @param
	 * @return
	 */
	function deleteLevel($a_id)
	{
		global $ilDB;

		$ilDB->manipulate("DELETE FROM skl_level WHERE "
			." id = ".$ilDB->quote($a_id, "integer")
			);

	}

	/**
	 * Fix level numbering
	 *
	 * @param
	 * @return
	 */
	function fixLevelNumbering()
	{
		global $ilDB;

		$set = $ilDB->query("SELECT id, nr FROM skl_level WHERE ".
			" skill_id = ".$ilDB->quote($this->getId(), "integer").
			" ORDER BY nr ASC"
		);
		$cnt = 1;
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$ilDB->manipulate("UPDATE skl_level SET ".
				" nr = ".$ilDB->quote($cnt, "integer").
				" WHERE id = ".$ilDB->quote($rec["id"], "integer")
				);
			$cnt++;
		}
	}

	/**
	 * Get skill for level id
	 *
	 * @param
	 * @return
	 */
	function getSkillForLevelId($a_level_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM skl_level WHERE ".
			" id = ".$ilDB->quote($a_level_id, "integer")
			);
		$skill = null;
		if ($rec = $ilDB->fetchAssoc($set))
		{
			if (ilSkillTreeNode::isInTree($rec["skill_id"]))
			{
				$skill = new ilBasicSkill($rec["skill_id"]);
			}
		}
		return $skill;
	}

	//
	//
	// User skill (level) related methods
	//
	//


	/**
	 * Write skill level status
	 *
	 * @param	int		skill level id
	 * @param	int		user id
	 * @param	int		status
	 * @param	string	any unique identifier set from the outside, if records for
	 *                  skill_id-tref_id-user_id-trigger_ref_id-self_eval-unique_identifier already exist
	 *                  the are removed from the history and the new entry is added
	 * 					The unique identifier is "unique per trigger object" not globally.
	 */
	static function writeUserSkillLevelStatus($a_level_id, $a_user_id,
		$a_trigger_ref_id, $a_tref_id = 0, $a_status = ilBasicSkill::ACHIEVED, $a_force = false,
		$a_self_eval = 0, $a_unique_identifier = "")
	{
		global $ilDB;

		$skill_id = ilBasicSkill::lookupLevelSkillId($a_level_id);
		$trigger_ref_id = $a_trigger_ref_id;
		$trigger_obj_id = ilObject::_lookupObjId($trigger_ref_id);
		$trigger_title = ilObject::_lookupTitle($trigger_obj_id);
		$trigger_type = ilObject::_lookupType($trigger_obj_id);

		$update = false;

			// check whether current skill user level is identical
			// to the one that should be set (-> no change required)
/*			$ilDB->setLimit(1);
			$set = $ilDB->query("SELECT status, valid FROM skl_user_skill_level WHERE ".
				"level_id = ".$ilDB->quote($a_level_id, "integer")." AND ".
				"user_id = ".$ilDB->quote($a_user_id, "integer")." AND ".
				"tref_id = ".$ilDB->quote((int) $a_tref_id, "integer")." AND ".
				"trigger_obj_id = ".$ilDB->quote($trigger_obj_id, "integer")." AND ".
				"self_eval = ".$ilDB->quote($a_self_eval, "integer").
				" ORDER BY status_date DESC"
			);
			$rec = $ilDB->fetchAssoc($set);
			if (!$rec["valid"] || $rec["status"] != $a_status)
			{
				$save = true;
			}*/

		if ($a_self_eval)
		{
			$ilDB->setLimit(1);
			$set = $ilDB->query("SELECT * FROM skl_user_skill_level WHERE ".
				"skill_id = ".$ilDB->quote($skill_id, "integer")." AND ".
				"user_id = ".$ilDB->quote($a_user_id, "integer")." AND ".
				"tref_id = ".$ilDB->quote((int) $a_tref_id, "integer")." AND ".
				"trigger_obj_id = ".$ilDB->quote($trigger_obj_id, "integer")." AND ".
				"self_eval = ".$ilDB->quote($a_self_eval, "integer").
				" ORDER BY status_date DESC"
			);
			$rec = $ilDB->fetchAssoc($set);
			$status_day = substr($rec["status_date"], 0, 10);
			$today = substr(ilUtil::now(), 0, 10);
			if ($rec["valid"] && $rec["status"] == $a_status && $status_day == $today)
			{
				$update = true;
			}
		}

		if ($update)
		{
			$now = ilUtil::now();
			$ilDB->manipulate("UPDATE skl_user_skill_level SET ".
				" level_id = ".$ilDB->quote($a_level_id, "integer").",".
				" status_date = ".$ilDB->quote($now, "timestamp").
				" WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
				" AND status_date = ".$ilDB->quote($rec["status_date"], "timestamp").
				" AND skill_id = ".$ilDB->quote($rec["skill_id"], "integer").
				" AND status = ".$ilDB->quote($a_status, "integer").
				" AND trigger_obj_id = ".$ilDB->quote($trigger_obj_id, "integer").
				" AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer").
				" AND self_eval = ".$ilDB->quote($a_self_eval, "integer")
				);
		}
		else
		{
			if ($a_unique_identifier != "")
			{
				$ilDB->manipulate("DELETE FROM skl_user_skill_level WHERE ".
					" user_id = ".$ilDB->quote($a_user_id, "integer").
					" AND tref_id = ".$ilDB->quote($a_tref_id, "integer").
					" AND skill_id = ".$ilDB->quote($skill_id, "integer").
					" AND trigger_ref_id = ".$ilDB->quote($trigger_ref_id, "integer").
					" AND trigger_obj_id = ".$ilDB->quote($trigger_obj_id, "integer").
					" AND self_eval = ".$ilDB->quote($a_self_eval, "integer").
					" AND unique_identifier = ".$ilDB->quote($a_unique_identifier, "text")
				);
			}

			$now = ilUtil::now();
			$ilDB->manipulate("INSERT INTO skl_user_skill_level ".
				"(level_id, user_id, tref_id, status_date, skill_id, status, valid, trigger_ref_id,".
				"trigger_obj_id, trigger_obj_type, trigger_title, self_eval, unique_identifier) VALUES (".
				$ilDB->quote($a_level_id, "integer").",".
				$ilDB->quote($a_user_id, "integer").",".
				$ilDB->quote((int) $a_tref_id, "integer").",".
				$ilDB->quote($now, "timestamp").",".
				$ilDB->quote($skill_id, "integer").",".
				$ilDB->quote($a_status, "integer").",".
				$ilDB->quote(1, "integer").",".
				$ilDB->quote($trigger_ref_id, "integer").",".
				$ilDB->quote($trigger_obj_id, "integer").",".
				$ilDB->quote($trigger_type, "text").",".
				$ilDB->quote($trigger_title, "text").",".
				$ilDB->quote($a_self_eval, "integer").",".
				$ilDB->quote($a_unique_identifier, "text").
				")");
		}

		// fix (removed level_id and added skill id, since table should hold only
		// one entry per skill)
		$ilDB->manipulate("DELETE FROM skl_user_has_level WHERE "
			." user_id = ".$ilDB->quote($a_user_id, "integer")
			." AND skill_id = ".$ilDB->quote($skill_id, "integer")
			." AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer")
			." AND trigger_obj_id = ".$ilDB->quote($trigger_obj_id, "integer")
			." AND self_eval = ".$ilDB->quote($a_self_eval, "integer")
		);

		if ($a_status == ilBasicSkill::ACHIEVED)
		{
			$ilDB->manipulate("INSERT INTO skl_user_has_level ".
			"(level_id, user_id, tref_id, status_date, skill_id, trigger_ref_id, trigger_obj_id, trigger_obj_type, trigger_title, self_eval) VALUES (".
			$ilDB->quote($a_level_id, "integer").",".
			$ilDB->quote($a_user_id, "integer").",".
			$ilDB->quote($a_tref_id, "integer").",".
			$ilDB->quote($now, "timestamp").",".
			$ilDB->quote($skill_id, "integer").",".
			$ilDB->quote($trigger_ref_id, "integer").",".
			$ilDB->quote($trigger_obj_id, "integer").",".
			$ilDB->quote($trigger_type, "text").",".
			$ilDB->quote($trigger_title, "text").",".
			$ilDB->quote($a_self_eval, "integer").
			")");
		}
	}

	/**
	 * Get max levels per type
	 *
	 * @param
	 * @return
	 */
	function getMaxLevelPerType($a_tref_id, $a_type, $a_user_id = 0, $a_self_eval = 0)
	{
		global $ilDB, $ilUser;
		
		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}
		
		$set = $ilDB->query($q = "SELECT level_id FROM skl_user_has_level ".
			" WHERE trigger_obj_type = ".$ilDB->quote($a_type, "text").
			" AND skill_id = ".$ilDB->quote($this->getId(), "integer").
			" AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND self_eval = ".$ilDB->quote($a_self_eval, "integer")
			);

		$has_level = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$has_level[$rec["level_id"]] = true;
		}
		$max_level = 0;
		foreach ($this->getLevelData() as $l)
		{
			if (isset($has_level[$l["id"]]))
			{
				$max_level = $l["id"];
			}
		}
		return $max_level;
	}

	/**
	 * Get all level entries
	 *
	 * @param
	 * @return
	 */
	function getAllLevelEntriesOfUser($a_tref_id, $a_user_id = 0, $a_self_eval = 0)
	{
		global $ilDB, $ilUser;
		
		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}
		
		$set = $ilDB->query($q = "SELECT * FROM skl_user_has_level ".
			" WHERE skill_id = ".$ilDB->quote($this->getId(), "integer").
			" AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND self_eval = ".$ilDB->quote($a_self_eval, "integer").
			" ORDER BY status_date DESC"
			);

		$levels = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$levels[] = $rec;
		}
		return $levels;
	}

	/**
	 * Get all historic level entries
	 *
	 * @param
	 * @return
	 */
	function getAllHistoricLevelEntriesOfUser($a_tref_id, $a_user_id = 0, $a_eval_by = 0)
	{
		global $ilDB, $ilUser;

		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}

		$by = ($a_eval_by != self::EVAL_BY_ALL)
			? " AND self_eval = ".$ilDB->quote($a_self_eval, "integer")
			: "";

		$set = $ilDB->query($q = "SELECT * FROM skl_user_skill_level ".
				" WHERE skill_id = ".$ilDB->quote($this->getId(), "integer").
				" AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer").
				" AND user_id = ".$ilDB->quote($a_user_id, "integer").
				$by.
				" ORDER BY status_date DESC"
		);
		$levels = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$levels[] = $rec;
		}
		return $levels;
	}


	/**
	 * Get max levels per object
	 *
	 * @param
	 * @return
	 */
	function getMaxLevelPerObject($a_tref_id, $a_object_id, $a_user_id = 0, $a_self_eval = 0)
	{
		global $ilDB, $ilUser;

		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}

		$set = $ilDB->query($q = "SELECT level_id FROM skl_user_has_level ".
				" WHERE trigger_obj_id = ".$ilDB->quote($a_object_id, "integer").
				" AND skill_id = ".$ilDB->quote($this->getId(), "integer").
				" AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer").
				" AND user_id = ".$ilDB->quote($a_user_id, "integer").
				" AND self_eval = ".$ilDB->quote($a_self_eval, "integer")
		);

		$has_level = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$has_level[$rec["level_id"]] = true;
		}
		$max_level = 0;
		foreach ($this->getLevelData() as $l)
		{
			if (isset($has_level[$l["id"]]))
			{
				$max_level = $l["id"];
			}
		}
		return $max_level;
	}

	/**
	 * Get last level set per object
	 *
	 * @param
	 * @return
	 */
	function getLastLevelPerObject($a_tref_id, $a_object_id, $a_user_id = 0, $a_self_eval = 0)
	{
		global $ilDB, $ilUser;

		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}

		$ilDB->setLimit(1);
		$set = $ilDB->query($q = "SELECT level_id FROM skl_user_has_level ".
				" WHERE trigger_obj_id = ".$ilDB->quote($a_object_id, "integer").
				" AND skill_id = ".$ilDB->quote($this->getId(), "integer").
				" AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer").
				" AND user_id = ".$ilDB->quote($a_user_id, "integer").
				" AND self_eval = ".$ilDB->quote($a_self_eval, "integer").
				" ORDER BY status_date DESC"
		);

		$rec = $ilDB->fetchAssoc($set);

		return $rec["level_id"];
	}

	/**
	 * Get last update per object
	 *
	 * @param
	 * @return
	 */
	function getLastUpdatePerObject($a_tref_id, $a_object_id, $a_user_id = 0, $a_self_eval = 0)
	{
		global $ilDB, $ilUser;

		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}

		$ilDB->setLimit(1);
		$set = $ilDB->query($q = "SELECT status_date FROM skl_user_has_level ".
				" WHERE trigger_obj_id = ".$ilDB->quote($a_object_id, "integer").
				" AND skill_id = ".$ilDB->quote($this->getId(), "integer").
				" AND tref_id = ".$ilDB->quote((int) $a_tref_id, "integer").
				" AND user_id = ".$ilDB->quote($a_user_id, "integer").
				" AND self_eval = ".$ilDB->quote($a_self_eval, "integer").
				" ORDER BY status_date DESC"
		);

		$rec = $ilDB->fetchAssoc($set);

		return $rec["status_date"];
	}

	//
	//
	// Certificate related methods
	//
	//

	/**
	 * Get title for certificate
	 *
	 * @param
	 * @return
	 */
	function getTitleForCertificate()
	{
		return $this->getTitle();
	}

	/**
	 * Get short title for certificate
	 *
	 * @param
	 * @return
	 */
	function getShortTitleForCertificate()
	{
		return "Skill";
	}

	/**
	 * Checks whether a skill level has a certificate or not
	 * @param int	skill id
	 * @param int	skill level id
	 * @return true/false
	 */
	public static function _lookupCertificate($a_skill_id, $a_skill_level_id)
	{
		$certificatefile = CLIENT_WEB_DIR."/certificates/skill/".
			((int)$a_skill_id)."/".((int) $a_skill_level_id)."/certificate.xml";
		if (@file_exists($certificatefile))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get usage info
	 *
	 * @param
	 * @return
	 */
	static public function getUsageInfo($a_cskill_ids, &$a_usages)
	{
		global $ilDB;
		
		include_once("./Services/Skill/classes/class.ilSkillUsage.php");
		ilSkillUsage::getUsageInfoGeneric($a_cskill_ids, $a_usages, ilSkillUsage::USER_ASSIGNED,
				"skl_user_skill_level", "user_id");
	}

	/**
	 * Get common skill ids for import IDs (newest first)
	 *
	 * @param int $a_source_inst_id source installation id, must be <>0
	 * @param int $a_skill_import_id source skill id (type basic skill ("skll") or basic skill template ("sktp"))
	 * @param int $a_tref_import_id source template reference id (if > 0 skill_import_id will be of type "sktp")
	 * @return array array of common skill ids, keys are "skill_id", "tref_id", "creation_date"
	 */
	static function getCommonSkillIdForImportId($a_source_inst_id, $a_skill_import_id, $a_tref_import_id = 0)
	{
		global $ilDB;

		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
		$tree = new ilSkillTree();

		if ($a_source_inst_id == 0)
		{
			return array();
		}

		$template_ids = array();
		if ($a_tref_import_id > 0)
		{
			$skill_node_type = "sktp";

			// get all matching tref nodes
			$set = $ilDB->query("SELECT * FROM skl_tree_node n JOIN skl_tree t ON (n.obj_id = t.child) ".
					" WHERE n.import_id = ".$ilDB->quote("il_".((int)$a_source_inst_id)."_sktr_".$a_tref_import_id, "text").
					" ORDER BY n.creation_date DESC ");
			while ($rec = $ilDB->fetchAssoc($set))
			{
				if (($t = ilSkillTemplateReference::_lookupTemplateId($rec["obj_id"])) > 0)
				{
					$template_ids[$t] = $rec["obj_id"];
				}
			}
		}
		else
		{
			$skill_node_type = "skll";
		}
		$set = $ilDB->query("SELECT * FROM skl_tree_node n JOIN skl_tree t ON (n.obj_id = t.child) ".
			" WHERE n.import_id = ".$ilDB->quote("il_".((int)$a_source_inst_id)."_".$skill_node_type."_".$a_skill_import_id, "text").
			" ORDER BY n.creation_date DESC ");
		$results = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$matching_trefs = array();
			if ($a_tref_import_id > 0)
			{
				$skill_template_id = $tree->getTopParentNodeId($rec["obj_id"]);

				// check of skill is in template
				foreach ($template_ids as $templ => $tref)
				{
					if ($skill_template_id == $templ)
					{
						$matching_trefs[] = $tref;
					}
				}
			}
			else
			{
				$matching_trefs = array(0);
			}

			foreach ($matching_trefs as $t)
			{
				$results[] = array("skill_id" => $rec["obj_id"], "tref_id" => $t, "creation_date" => $rec["creation_date"]);
			}
		}
		return $results;
	}

	/**
	 * Get level ids for import IDs (newest first)
	 *
	 * @param int $a_source_inst_id source installation id, must be <>0
	 * @param int $a_skill_import_id source skill id (type basic skill ("skll") or basic skill template ("sktp"))
	 * @return array array of common skill ids, keys are "level_id", "creation_date"
	 */
	static function getLevelIdForImportId($a_source_inst_id, $a_level_import_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM skl_level l JOIN skl_tree t ON (l.skill_id = t.child) " .
				" WHERE l.import_id = " . $ilDB->quote("il_" . ((int)$a_source_inst_id) . "_sklv_" . $a_level_import_id, "text") .
				" ORDER BY l.creation_date DESC ");
		$results = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$results[] = array("level_id" => $rec["id"], "creation_date" => $rec["creation_date"]);
		}
		return $results;
	}

	/**
	 * Get level ids for import Ids matching common skills
	 *
	 * @param
	 * @return
	 */
	static function getLevelIdForImportIdMatchSkill($a_source_inst_id, $a_level_import_id, $a_skill_import_id, $a_tref_import_id = 0)
	{
		$level_id_data = self::getLevelIdForImportId($a_source_inst_id, $a_level_import_id);
		$skill_data = self::getCommonSkillIdForImportId($a_source_inst_id, $a_skill_import_id, $a_tref_import_id);
		$matches = array();
		foreach($level_id_data as $l)
		{
			reset($skill_data);
			foreach ($skill_data as $s)
			{
				if (ilBasicSkill::lookupLevelSkillId($l["level_id"]) == $s["skill_id"])
				{
					$matches[] = array(
							"level_id" => $l["level_id"],
							"creation_date" => $l["creation_date"],
							"skill_id" => $s["skill_id"],
							"tref_id" => $s["tref_id"]
					);
				}
			}
		}
		return $matches;
	}

}
?>
