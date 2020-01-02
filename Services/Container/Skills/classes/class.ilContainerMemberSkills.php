<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skills of a container
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilContainerMemberSkills
{

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var array
     */
    protected $skills = array();

    /**
     * @var int object id
     */
    protected $obj_id;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var array
     */
    protected $skill_levels = array();

    /**
     * @var bool
     */
    protected $published = false;

    /**
     * Constrictor
     *
     * @param int $a_obj_id
     */
    public function __construct($a_obj_id, $a_user_id)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->setObjId($a_obj_id);
        $this->setUserId($a_user_id);
        if ($a_obj_id > 0 && $a_user_id > 0) {
            $this->read();
        }
    }

    /**
     * Set object id
     *
     * @param int $a_val object id
     */
    public function setObjId($a_val)
    {
        $this->obj_id = $a_val;
    }

    /**
     * Get object id
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * Set user id
     *
     * @param int $a_val user id
     */
    public function setUserId($a_val)
    {
        $this->user_id = $a_val;
    }

    /**
     * Get user id
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Read
     */
    public function read()
    {
        $db = $this->db;

        $set = $db->query(
            "SELECT * FROM cont_member_skills " .
            " WHERE obj_id = " . $db->quote($this->getObjId(), "integer") .
            " AND user_id = " . $db->quote($this->getUserId(), "integer")
        );
        $this->skill_levels = array();
        while ($rec = $this->db->fetchAssoc($set)) {
            $this->skill_levels[$rec["skill_id"] . ":" . $rec["tref_id"]] = $rec["level_id"];
            $this->published = $rec["published"];	// this is a little weak, but the value should be the same for all save skills
        }
    }

    /**
     * Get Skill levels
     *
     * @return array (key is skill_id:tref_id, value is level id)
     */
    public function getSkillLevels()
    {
        return $this->skill_levels;
    }

    /**
     * Get ordered skill levels
     *
     * @return array[] each item comes with keys "level_id", "skill_id", "tref_id"
     */
    public function getOrderedSkillLevels()
    {
        $skill_levels = array_map(function ($a, $k) {
            $s = explode(":", $k);
            return array("level_id" => $a, "skill_id" => $s[0], "tref_id" => $s[1]);
        }, $this->getSkillLevels(), array_keys($this->getSkillLevels()));

        include_once("./Services/Skill/classes/class.ilVirtualSkillTree.php");
        $vtree = new ilVirtualSkillTree();
        return $vtree->getOrderedNodeset($skill_levels, "skill_id", "tref_id");
    }


    /**
     * Get published
     *
     * @return bool
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Save levels for skills
     *
     * @param array $a_level_data (key is skill_id:tref_id, value is level id)
     */
    public function saveLevelForSkills($a_level_data)
    {
        $db = $this->db;

        $this->delete();
        foreach ($a_level_data as $k => $v) {
            $sk = explode(":", $k);
            $db->manipulate("INSERT INTO cont_member_skills " .
                "(obj_id, user_id, skill_id, tref_id, level_id, published) VALUES (" .
                $db->quote($this->getObjId(), "integer") . "," .
                $db->quote($this->getUserId(), "integer") . "," .
                $db->quote($sk[0], "integer") . "," .
                $db->quote($sk[1], "integer") . "," .
                $db->quote($v, "integer") . "," .
                $db->quote(0, "integer") .
                ")");
        }

        $this->skill_levels = $a_level_data;
    }

    /**
     * Delete all level data for current user
     *
     * @param
     */
    public function delete()
    {
        $db = $this->db;

        $db->manipulate("DELETE FROM cont_member_skills WHERE " .
            " obj_id = " . $db->quote($this->getObjId(), "integer") .
            " AND user_id = " . $db->quote($this->getUserId(), "integer"));
    }

    /**
     * Publish
     */
    public function publish($a_ref_id)
    {
        $db = $this->db;

        $changed = ilBasicSkill::removeAllUserSkillLevelStatusOfObject(
            $this->getUserId(),
            $this->getObjId(),
            false,
            $this->getObjId()
        );

        foreach ($this->skill_levels as $sk => $l) {
            $changed = true;
            $sk = explode(":", $sk);
            ilBasicSkill::writeUserSkillLevelStatus(
                $l,
                $this->user_id,
                $a_ref_id,
                $sk[1],
                ilBasicSkill::ACHIEVED,
                false,
                false,
                $this->obj_id
            );
        }

        $db->manipulate("UPDATE cont_member_skills SET " .
            " published = " . $db->quote(1, "integer") .
            " WHERE obj_id = " . $db->quote($this->getObjId(), "integer") .
            " AND user_id = " . $db->quote($this->getUserId(), "integer"));

        return $changed;
    }


    /**
     * Remove all skill levels
     */
    public function removeAllSkillLevels()
    {
        ilBasicSkill::removeAllUserSkillLevelStatusOfObject(
            $this->getUserId(),
            $this->getObjId(),
            false,
            $this->getObjId()
        );

        $this->delete();
    }
}
