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
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilObjUser
     */
    protected $user;

    const ACHIEVED = 1;
    const NOT_ACHIEVED = 0;

    const EVAL_BY_OTHERS_ = 0;
    const EVAL_BY_SELF = 1;
    const EVAL_BY_ALL = 2;

    public $id;

    /**
     * Constructor
     * @access	public
     */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        parent::__construct($a_id);
        $this->setType("skll");
    }

    /**
     * Read data from database
     */
    public function read()
    {
        parent::read();
    }

    /**
     * Create skill
     *
     */
    public function create()
    {
        parent::create();
    }

    /**
     * Delete skill
     */
    public function delete()
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_level WHERE "
            . " skill_id = " . $ilDB->quote($this->getId(), "integer")
        );

        $ilDB->manipulate(
            "DELETE FROM skl_user_has_level WHERE "
            . " skill_id = " . $ilDB->quote($this->getId(), "integer")
        );

        parent::delete();
    }

    /**
     * Copy basic skill
     */
    public function copy()
    {
        $skill = new ilBasicSkill();
        $skill->setTitle($this->getTitle());
        $skill->setType($this->getType());
        $skill->setSelfEvaluation($this->getSelfEvaluation());
        $skill->setOrderNr($this->getOrderNr());
        $skill->create();

        $levels = $this->getLevelData();
        if (sizeof($levels)) {
            foreach ($levels as $item) {
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
    public function addLevel($a_title, $a_description, $a_import_id = "")
    {
        $ilDB = $this->db;

        $nr = $this->getMaxLevelNr();
        $nid = $ilDB->nextId("skl_level");
        $ilDB->insert("skl_level", array(
                "id" => array("integer", $nid),
                "skill_id" => array("integer", $this->getId()),
                "nr" => array("integer", $nr + 1),
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
    public function getMaxLevelNr()
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT MAX(nr) mnr FROM skl_level WHERE " .
            " skill_id = " . $ilDB->quote($this->getId(), "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["mnr"];
    }

    /**
     * Get level data
     *
     * @return	array	level data
     */
    public function getLevelData($a_id = 0)
    {
        $ilDB = $this->db;

        if ($a_id > 0) {
            $and = " AND id = " . $ilDB->quote($a_id, "integer");
        }

        $set = $ilDB->query(
            "SELECT * FROM skl_level WHERE " .
            " skill_id = " . $ilDB->quote($this->getId(), "integer") .
            $and .
            " ORDER BY nr"
        );
        $levels = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($a_id > 0) {
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
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT $a_prop FROM skl_level WHERE " .
            " id = " . $ilDB->quote($a_id, "integer")
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
    public static function lookupLevelTitle($a_id)
    {
        return ilBasicSkill::lookupLevelProperty($a_id, "title");
    }

    /**
     * Lookup level description
     *
     * @param	int		level id
     * @return	string	level description
     */
    public static function lookupLevelDescription($a_id)
    {
        return ilBasicSkill::lookupLevelProperty($a_id, "description");
    }

    /**
     * Lookup level skill id
     *
     * @param	int		level id
     * @return	string	skill id
     */
    public static function lookupLevelSkillId($a_id)
    {
        return ilBasicSkill::lookupLevelProperty($a_id, "skill_id");
    }

    /**
     * Write level property
     *
     * @param
     * @return
     */
    protected static function writeLevelProperty($a_id, $a_prop, $a_value, $a_type)
    {
        global $DIC;

        $ilDB = $DIC->database();

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
    public static function writeLevelTitle($a_id, $a_title)
    {
        ilBasicSkill::writeLevelProperty($a_id, "title", $a_title, "text");
    }

    /**
     * Write level description
     *
     * @param	int		level id
     * @param	text	level description
     */
    public static function writeLevelDescription($a_id, $a_description)
    {
        ilBasicSkill::writeLevelProperty($a_id, "description", $a_description, "clob");
    }

    /**
     * Update level order
     *
     * @param
     * @return
     */
    public function updateLevelOrder($order)
    {
        $ilDB = $this->db;

        asort($order);

        $cnt = 1;
        foreach ($order as $id => $o) {
            $ilDB->manipulate(
                "UPDATE skl_level SET " .
                " nr = " . $ilDB->quote($cnt, "integer") .
                " WHERE id = " . $ilDB->quote($id, "integer")
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
    public function deleteLevel($a_id)
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_level WHERE "
            . " id = " . $ilDB->quote($a_id, "integer")
        );
    }

    /**
     * Fix level numbering
     *
     * @param
     * @return
     */
    public function fixLevelNumbering()
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT id, nr FROM skl_level WHERE " .
            " skill_id = " . $ilDB->quote($this->getId(), "integer") .
            " ORDER BY nr ASC"
        );
        $cnt = 1;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ilDB->manipulate(
                "UPDATE skl_level SET " .
                " nr = " . $ilDB->quote($cnt, "integer") .
                " WHERE id = " . $ilDB->quote($rec["id"], "integer")
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
    public function getSkillForLevelId($a_level_id)
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_level WHERE " .
            " id = " . $ilDB->quote($a_level_id, "integer")
        );
        $skill = null;
        if ($rec = $ilDB->fetchAssoc($set)) {
            if (ilSkillTreeNode::isInTree($rec["skill_id"])) {
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
     * Reset skill level status. This is currently only used for self evaluations with a "no competence" level.
     * It has to be discussed, how this should be provided for non-self-evaluations.
     *
     * @param int $a_user_id user id
     * @param int $a_skill_id skill id
     * @param int $a_tref_id skill tref id
     * @param int $a_trigger_ref_id triggering repository object ref id
     * @param bool $a_self_eval currently needs to be set to true
     *
     * @throws ilSkillException
     */
    public static function resetUserSkillLevelStatus($a_user_id, $a_skill_id, $a_tref_id = 0, $a_trigger_ref_id = 0, $a_self_eval = false)
    {
        global $DIC;

        $db = $DIC->database();

        if (!$a_self_eval) {
            include_once("./Services/Skill/exceptions/class.ilSkillException.php");
            throw new ilSkillException("resetUserSkillLevelStatus currently only provided for self evaluations.");
        }

        $trigger_obj_id = ($a_trigger_ref_id > 0)
            ? ilObject::_lookupObjId($a_trigger_ref_id)
            : 0;

        $update = false;
        $status_date = self::hasRecentSelfEvaluation($a_user_id, $a_skill_id, $a_tref_id, $a_trigger_ref_id);
        if ($status_date != "") {
            $update = true;
        }

        if ($update) {
            // this will only be set in self eval case, means this will always have a $rec
            $now = ilUtil::now();
            $db->manipulate(
                "UPDATE skl_user_skill_level SET " .
                " level_id = " . $db->quote(0, "integer") . "," .
                " status_date = " . $db->quote($now, "timestamp") .
                " WHERE user_id = " . $db->quote($a_user_id, "integer") .
                " AND status_date = " . $db->quote($status_date, "timestamp") .
                " AND skill_id = " . $db->quote($a_skill_id, "integer") .
                " AND status = " . $db->quote(self::ACHIEVED, "integer") .
                " AND trigger_obj_id = " . $db->quote($trigger_obj_id, "integer") .
                " AND tref_id = " . $db->quote((int) $a_tref_id, "integer") .
                " AND self_eval = " . $db->quote($a_self_eval, "integer")
            );
        } else {
            $now = ilUtil::now();
            $db->manipulate("INSERT INTO skl_user_skill_level " .
                "(level_id, user_id, tref_id, status_date, skill_id, status, valid, trigger_ref_id," .
                "trigger_obj_id, trigger_obj_type, trigger_title, self_eval, unique_identifier) VALUES (" .
                $db->quote(0, "integer") . "," .
                $db->quote($a_user_id, "integer") . "," .
                $db->quote((int) $a_tref_id, "integer") . "," .
                $db->quote($now, "timestamp") . "," .
                $db->quote($a_skill_id, "integer") . "," .
                $db->quote(self::ACHIEVED, "integer") . "," .
                $db->quote(1, "integer") . "," .
                $db->quote($a_trigger_ref_id, "integer") . "," .
                $db->quote($trigger_obj_id, "integer") . "," .
                $db->quote("", "text") . "," .
                $db->quote("", "text") . "," .
                $db->quote($a_self_eval, "integer") . "," .
                $db->quote("", "text") .
                ")");
        }

        $db->manipulate(
            "DELETE FROM skl_user_has_level WHERE "
            . " user_id = " . $db->quote($a_user_id, "integer")
            . " AND skill_id = " . $db->quote($a_skill_id, "integer")
            . " AND tref_id = " . $db->quote((int) $a_tref_id, "integer")
            . " AND trigger_obj_id = " . $db->quote($trigger_obj_id, "integer")
            . " AND self_eval = " . $db->quote($a_self_eval, "integer")
        );
    }

    /**
     * Has recent self evaluation. Check if self evaluation for user/object has been done on the same day
     * already
     *
     * @param
     * @return
     */
    protected static function hasRecentSelfEvaluation($a_user_id, $a_skill_id, $a_tref_id = 0, $a_trigger_ref_id = 0)
    {
        global $DIC;

        $db = $DIC->database();

        $trigger_obj_id = ($a_trigger_ref_id > 0)
            ? ilObject::_lookupObjId($a_trigger_ref_id)
            : 0;

        $recent = "";

        $db->setLimit(1);
        $set = $db->query(
            "SELECT * FROM skl_user_skill_level WHERE " .
            "skill_id = " . $db->quote($a_skill_id, "integer") . " AND " .
            "user_id = " . $db->quote($a_user_id, "integer") . " AND " .
            "tref_id = " . $db->quote((int) $a_tref_id, "integer") . " AND " .
            "trigger_obj_id = " . $db->quote($trigger_obj_id, "integer") . " AND " .
            "self_eval = " . $db->quote(1, "integer") .
            " ORDER BY status_date DESC"
        );
        $rec = $db->fetchAssoc($set);
        $status_day = substr($rec["status_date"], 0, 10);
        $today = substr(ilUtil::now(), 0, 10);
        if ($rec["valid"] && $rec["status"] == ilBasicSkill::ACHIEVED && $status_day == $today) {
            $recent = $rec["status_date"];
        }

        return $recent;
    }

    /**
     * Get new achievements
     *
     * @param string $a_timestamp
     * @return array
     */
    public static function getNewAchievementsPerUser($a_timestamp, $a_timestamp_to = null, $a_user_id = 0, $a_self_eval = 0)
    {
        global $DIC;

        $db = $DIC->database();

        $to = (!is_null($a_timestamp_to))
            ? " AND status_date <= " . $db->quote($a_timestamp_to, "timestamp")
            : "";

        $user = ($a_user_id > 0)
            ? " AND user_id = " . $db->quote($a_user_id, "integer")
            : "";

        $set = $db->query("SELECT * FROM skl_user_skill_level " .
            " WHERE status_date >= " . $db->quote($a_timestamp, "timestamp") .
            " AND valid = " . $db->quote(1, "integer") .
            " AND status = " . $db->quote(ilBasicSkill::ACHIEVED, "integer") .
            " AND self_eval = " . $db->quote($a_self_eval, "integer") .
            $to .
            $user .
            " ORDER BY user_id, status_date ASC ");
        $achievments = array();
        while ($rec = $db->fetchAssoc($set)) {
            $achievments[$rec["user_id"]][] = $rec;
        }

        return $achievments;
    }


    /**
     * Write skill level status
     *
     * @param int $a_level_id skill level id
     * @param int $a_user_id user id
     * @param int $a_trigger_ref_id trigger repository object ref id
     * @param int $a_tref_id skill tref id
     * @param int $a_status DEPRECATED, always use ilBasicSkill::ACHIEVED
     * @param bool $a_force DEPRECATED
     * @param bool $a_self_eval self evaluation
     * @param string $a_unique_identifier a  unique identifier (should be used with trigger_ref_id > 0)
     */
    public static function writeUserSkillLevelStatus(
        $a_level_id,
        $a_user_id,
        $a_trigger_ref_id,
        $a_tref_id = 0,
        $a_status = ilBasicSkill::ACHIEVED,
        $a_force = false,
        $a_self_eval = false,
        $a_unique_identifier = ""
    ) {
        global $DIC;

        $ilDB = $DIC->database();

        $skill_id = ilBasicSkill::lookupLevelSkillId($a_level_id);
        $trigger_ref_id = $a_trigger_ref_id;
        $trigger_obj_id = ilObject::_lookupObjId($trigger_ref_id);
        $trigger_title = ilObject::_lookupTitle($trigger_obj_id);
        $trigger_type = ilObject::_lookupType($trigger_obj_id);

        $update = false;

        // self evaluations will update, if the last self evaluation is on the same day
        if ($a_self_eval && self::hasRecentSelfEvaluation($a_user_id, $skill_id, $a_tref_id, $trigger_ref_id)) {
            $status_date = self::hasRecentSelfEvaluation($a_user_id, $skill_id, $a_tref_id, $trigger_ref_id);
            if ($status_date != "") {
                $update = true;
            }
        }

        if ($update) {
            // this will only be set in self eval case, means this will always have a $rec
            $now = ilUtil::now();
            $ilDB->manipulate(
                "UPDATE skl_user_skill_level SET " .
                " level_id = " . $ilDB->quote($a_level_id, "integer") . "," .
                " status_date = " . $ilDB->quote($now, "timestamp") .
                " WHERE user_id = " . $ilDB->quote($a_user_id, "integer") .
                " AND status_date = " . $ilDB->quote($status_date, "timestamp") .
                " AND skill_id = " . $ilDB->quote($skill_id, "integer") .
                " AND status = " . $ilDB->quote($a_status, "integer") .
                " AND trigger_obj_id = " . $ilDB->quote($trigger_obj_id, "integer") .
                " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
                " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
            );
        } else {
            if ($a_unique_identifier != "") {
                $ilDB->manipulate(
                    "DELETE FROM skl_user_skill_level WHERE " .
                    " user_id = " . $ilDB->quote($a_user_id, "integer") .
                    " AND tref_id = " . $ilDB->quote($a_tref_id, "integer") .
                    " AND skill_id = " . $ilDB->quote($skill_id, "integer") .
                    " AND trigger_ref_id = " . $ilDB->quote($trigger_ref_id, "integer") .
                    " AND trigger_obj_id = " . $ilDB->quote($trigger_obj_id, "integer") .
                    " AND self_eval = " . $ilDB->quote($a_self_eval, "integer") .
                    " AND unique_identifier = " . $ilDB->quote($a_unique_identifier, "text")
                );
            }

            $now = ilUtil::now();
            $ilDB->manipulate("INSERT INTO skl_user_skill_level " .
                "(level_id, user_id, tref_id, status_date, skill_id, status, valid, trigger_ref_id," .
                "trigger_obj_id, trigger_obj_type, trigger_title, self_eval, unique_identifier) VALUES (" .
                $ilDB->quote($a_level_id, "integer") . "," .
                $ilDB->quote($a_user_id, "integer") . "," .
                $ilDB->quote((int) $a_tref_id, "integer") . "," .
                $ilDB->quote($now, "timestamp") . "," .
                $ilDB->quote($skill_id, "integer") . "," .
                $ilDB->quote($a_status, "integer") . "," .
                $ilDB->quote(1, "integer") . "," .
                $ilDB->quote($trigger_ref_id, "integer") . "," .
                $ilDB->quote($trigger_obj_id, "integer") . "," .
                $ilDB->quote($trigger_type, "text") . "," .
                $ilDB->quote($trigger_title, "text") . "," .
                $ilDB->quote($a_self_eval, "integer") . "," .
                $ilDB->quote($a_unique_identifier, "text") .
                ")");
        }

        // fix (removed level_id and added skill id, since table should hold only
        // one entry per skill)
        $ilDB->manipulate(
            "DELETE FROM skl_user_has_level WHERE "
            . " user_id = " . $ilDB->quote($a_user_id, "integer")
            . " AND skill_id = " . $ilDB->quote($skill_id, "integer")
            . " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer")
            . " AND trigger_obj_id = " . $ilDB->quote($trigger_obj_id, "integer")
            . " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
        );

        if ($a_status == ilBasicSkill::ACHIEVED) {
            $ilDB->manipulate("INSERT INTO skl_user_has_level " .
            "(level_id, user_id, tref_id, status_date, skill_id, trigger_ref_id, trigger_obj_id, trigger_obj_type, trigger_title, self_eval) VALUES (" .
            $ilDB->quote($a_level_id, "integer") . "," .
            $ilDB->quote($a_user_id, "integer") . "," .
            $ilDB->quote($a_tref_id, "integer") . "," .
            $ilDB->quote($now, "timestamp") . "," .
            $ilDB->quote($skill_id, "integer") . "," .
            $ilDB->quote($trigger_ref_id, "integer") . "," .
            $ilDB->quote($trigger_obj_id, "integer") . "," .
            $ilDB->quote($trigger_type, "text") . "," .
            $ilDB->quote($trigger_title, "text") . "," .
            $ilDB->quote($a_self_eval, "integer") .
            ")");
        }
    }

    /**
     * Remove a user skill completely
     *
     * @param int $a_user_id user id
     * @param int $a_trigger_obj_id triggering repository object obj id
     * @param bool $a_self_eval currently needs to be set to true
     * @param string $a_unique_identifier unique identifier string
     * @return bool true, if entries have been deleted, otherwise false
     */
    public static function removeAllUserSkillLevelStatusOfObject($a_user_id, $a_trigger_obj_id, $a_self_eval = false, $a_unique_identifier = "")
    {
        global $DIC;

        $db = $DIC->database();

        if ($a_trigger_obj_id == 0) {
            return false;
        }

        $changed = false;

        $aff_rows = $db->manipulate(
            "DELETE FROM skl_user_skill_level WHERE "
            . " user_id = " . $db->quote($a_user_id, "integer")
            . " AND trigger_obj_id = " . $db->quote($a_trigger_obj_id, "integer")
            . " AND self_eval = " . $db->quote($a_self_eval, "integer")
            . " AND unique_identifier = " . $db->quote($a_unique_identifier, "text")
        );
        if ($aff_rows > 0) {
            $changed = true;
        }

        $aff_rows = $db->manipulate(
            "DELETE FROM skl_user_has_level WHERE "
            . " user_id = " . $db->quote($a_user_id, "integer")
            . " AND trigger_obj_id = " . $db->quote($a_trigger_obj_id, "integer")
            . " AND self_eval = " . $db->quote($a_self_eval, "integer")
        );
        if ($aff_rows > 0) {
            $changed = true;
        }
        return $changed;
    }

    /**
     * Remove all data of a user
     *
     * @param int $a_user_id
     */
    public static function removeAllUserData($a_user_id)
    {
        global $DIC;

        $db = $DIC->database();

        $db->manipulate(
            "DELETE FROM skl_user_skill_level WHERE "
            . " user_id = " . $db->quote($a_user_id, "integer")
        );
        $db->manipulate(
            "DELETE FROM skl_user_has_level WHERE "
            . " user_id = " . $db->quote($a_user_id, "integer")
        );
    }


    /**
     * Get max levels per type
     *
     * @param
     * @return
     */
    public function getMaxLevelPerType($a_tref_id, $a_type, $a_user_id = 0, $a_self_eval = 0)
    {
        $ilDB = $this->db;
        $ilUser = $this->user;
        
        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }
        
        $set = $ilDB->query(
            $q = "SELECT level_id FROM skl_user_has_level " .
            " WHERE trigger_obj_type = " . $ilDB->quote($a_type, "text") .
            " AND skill_id = " . $ilDB->quote($this->getId(), "integer") .
            " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
        );

        $has_level = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $has_level[$rec["level_id"]] = true;
        }
        $max_level = 0;
        foreach ($this->getLevelData() as $l) {
            if (isset($has_level[$l["id"]])) {
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
    public function getAllLevelEntriesOfUser($a_tref_id, $a_user_id = 0, $a_self_eval = 0)
    {
        $ilDB = $this->db;
        $ilUser = $this->user;
        
        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }
        
        $set = $ilDB->query(
            $q = "SELECT * FROM skl_user_has_level " .
            " WHERE skill_id = " . $ilDB->quote($this->getId(), "integer") .
            " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND self_eval = " . $ilDB->quote($a_self_eval, "integer") .
            " ORDER BY status_date DESC"
        );

        $levels = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
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
    public function getAllHistoricLevelEntriesOfUser($a_tref_id, $a_user_id = 0, $a_eval_by = 0)
    {
        $ilDB = $this->db;
        $ilUser = $this->user;

        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }

        $by = ($a_eval_by != self::EVAL_BY_ALL)
            ? " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
            : "";

        $set = $ilDB->query(
            $q = "SELECT * FROM skl_user_skill_level " .
                " WHERE skill_id = " . $ilDB->quote($this->getId(), "integer") .
                " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
                " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
                $by .
                " ORDER BY status_date DESC"
        );
        $levels = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
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
    public function getMaxLevelPerObject($a_tref_id, $a_object_id, $a_user_id = 0, $a_self_eval = 0)
    {
        $ilDB = $this->db;
        $ilUser = $this->user;

        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }

        $set = $ilDB->query(
            $q = "SELECT level_id FROM skl_user_has_level " .
                " WHERE trigger_obj_id = " . $ilDB->quote($a_object_id, "integer") .
                " AND skill_id = " . $ilDB->quote($this->getId(), "integer") .
                " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
                " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
                " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
        );

        $has_level = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $has_level[$rec["level_id"]] = true;
        }
        $max_level = 0;
        foreach ($this->getLevelData() as $l) {
            if (isset($has_level[$l["id"]])) {
                $max_level = $l["id"];
            }
        }
        return $max_level;
    }

    /**
     * Get max levels per object
     *
     * @param
     * @return
     */
    public function getMaxLevel($a_tref_id, $a_user_id = 0, $a_self_eval = 0)
    {
        $ilDB = $this->db;
        $ilUser = $this->user;

        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }

        $set = $ilDB->query(
            $q = "SELECT level_id FROM skl_user_has_level " .
            " WHERE skill_id = " . $ilDB->quote($this->getId(), "integer") .
            " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
        );

        $has_level = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $has_level[$rec["level_id"]] = true;
        }
        $max_level = 0;
        foreach ($this->getLevelData() as $l) {
            if (isset($has_level[$l["id"]])) {
                $max_level = $l["id"];
            }
        }
        return $max_level;
    }


    /**
     * Has use self evaluated a skill?
     *
     * @param int $a_user_id
     * @param int $a_skill_id
     * @param int $a_tref_id
     * @return bool
     */
    public static function hasSelfEvaluated($a_user_id, $a_skill_id, $a_tref_id)
    {
        global $DIC;

        $db = $DIC->database();

        $set = $db->query(
            $q = "SELECT level_id FROM skl_user_has_level " .
            " WHERE skill_id = " . $db->quote((int) $a_skill_id, "integer") .
            " AND tref_id = " . $db->quote((int) $a_tref_id, "integer") .
            " AND user_id = " . $db->quote($a_user_id, "integer") .
            " AND self_eval = " . $db->quote(1, "integer")
        );
        
        if ($rec = $db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * Get last level set per object
     *
     * @param
     * @return
     */
    public function getLastLevelPerObject($a_tref_id, $a_object_id, $a_user_id = 0, $a_self_eval = 0)
    {
        $ilDB = $this->db;
        $ilUser = $this->user;

        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }

        $ilDB->setLimit(1);
        $set = $ilDB->query(
            $q = "SELECT level_id FROM skl_user_has_level " .
                " WHERE trigger_obj_id = " . $ilDB->quote($a_object_id, "integer") .
                " AND skill_id = " . $ilDB->quote($this->getId(), "integer") .
                " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
                " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
                " AND self_eval = " . $ilDB->quote($a_self_eval, "integer") .
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
    public function getLastUpdatePerObject($a_tref_id, $a_object_id, $a_user_id = 0, $a_self_eval = 0)
    {
        $ilDB = $this->db;
        $ilUser = $this->user;

        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }

        $ilDB->setLimit(1);
        $set = $ilDB->query(
            $q = "SELECT status_date FROM skl_user_has_level " .
                " WHERE trigger_obj_id = " . $ilDB->quote($a_object_id, "integer") .
                " AND skill_id = " . $ilDB->quote($this->getId(), "integer") .
                " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
                " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
                " AND self_eval = " . $ilDB->quote($a_self_eval, "integer") .
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
    public function getTitleForCertificate()
    {
        return $this->getTitle();
    }

    /**
     * Get short title for certificate
     *
     * @param
     * @return
     */
    public function getShortTitleForCertificate()
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
        $certificatefile = CLIENT_WEB_DIR . "/certificates/skill/" .
            ((int) $a_skill_id) . "/" . ((int) $a_skill_level_id) . "/certificate.xml";
        if (@file_exists($certificatefile)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get usage info
     *
     * @param
     * @return
     */
    public static function getUsageInfo($a_cskill_ids, &$a_usages)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        include_once("./Services/Skill/classes/class.ilSkillUsage.php");
        ilSkillUsage::getUsageInfoGeneric(
            $a_cskill_ids,
            $a_usages,
            ilSkillUsage::USER_ASSIGNED,
            "skl_user_skill_level",
            "user_id"
        );
    }

    /**
     * Get common skill ids for import IDs (newest first)
     *
     * @param int $a_source_inst_id source installation id, must be <>0
     * @param int $a_skill_import_id source skill id (type basic skill ("skll") or basic skill template ("sktp"))
     * @param int $a_tref_import_id source template reference id (if > 0 skill_import_id will be of type "sktp")
     * @return array array of common skill ids, keys are "skill_id", "tref_id", "creation_date"
     */
    public static function getCommonSkillIdForImportId($a_source_inst_id, $a_skill_import_id, $a_tref_import_id = 0)
    {
        global $DIC;

        $ilDB = $DIC->database();

        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
        $tree = new ilSkillTree();

        if ($a_source_inst_id == 0) {
            return array();
        }

        $template_ids = array();
        if ($a_tref_import_id > 0) {
            $skill_node_type = "sktp";

            // get all matching tref nodes
            $set = $ilDB->query("SELECT * FROM skl_tree_node n JOIN skl_tree t ON (n.obj_id = t.child) " .
                    " WHERE n.import_id = " . $ilDB->quote("il_" . ((int) $a_source_inst_id) . "_sktr_" . $a_tref_import_id, "text") .
                    " ORDER BY n.creation_date DESC ");
            while ($rec = $ilDB->fetchAssoc($set)) {
                if (($t = ilSkillTemplateReference::_lookupTemplateId($rec["obj_id"])) > 0) {
                    $template_ids[$t] = $rec["obj_id"];
                }
            }
        } else {
            $skill_node_type = "skll";
        }
        $set = $ilDB->query("SELECT * FROM skl_tree_node n JOIN skl_tree t ON (n.obj_id = t.child) " .
            " WHERE n.import_id = " . $ilDB->quote("il_" . ((int) $a_source_inst_id) . "_" . $skill_node_type . "_" . $a_skill_import_id, "text") .
            " ORDER BY n.creation_date DESC ");
        $results = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $matching_trefs = array();
            if ($a_tref_import_id > 0) {
                $skill_template_id = $tree->getTopParentNodeId($rec["obj_id"]);

                // check of skill is in template
                foreach ($template_ids as $templ => $tref) {
                    if ($skill_template_id == $templ) {
                        $matching_trefs[] = $tref;
                    }
                }
            } else {
                $matching_trefs = array(0);
            }

            foreach ($matching_trefs as $t) {
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
    public static function getLevelIdForImportId($a_source_inst_id, $a_level_import_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT * FROM skl_level l JOIN skl_tree t ON (l.skill_id = t.child) " .
                " WHERE l.import_id = " . $ilDB->quote("il_" . ((int) $a_source_inst_id) . "_sklv_" . $a_level_import_id, "text") .
                " ORDER BY l.creation_date DESC ");
        $results = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
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
    public static function getLevelIdForImportIdMatchSkill($a_source_inst_id, $a_level_import_id, $a_skill_import_id, $a_tref_import_id = 0)
    {
        $level_id_data = self::getLevelIdForImportId($a_source_inst_id, $a_level_import_id);
        $skill_data = self::getCommonSkillIdForImportId($a_source_inst_id, $a_skill_import_id, $a_tref_import_id);
        $matches = array();
        foreach ($level_id_data as $l) {
            reset($skill_data);
            foreach ($skill_data as $s) {
                if (ilBasicSkill::lookupLevelSkillId($l["level_id"]) == $s["skill_id"]) {
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
