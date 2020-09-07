<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skills of a container
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilContainerSkills
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
    protected $id;

    /**
     * Constrictor
     *
     * @param int $a_obj_id
     */
    public function __construct($a_obj_id)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->setId($a_obj_id);
        if ($a_obj_id > 0) {
            $this->read();
        }
    }

    /**
     * Set id
     *
     * @param int $a_val object id
     */
    public function setId($a_val)
    {
        $this->id = $a_val;
    }

    /**
     * Get id
     *
     * @return int object id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Reset skills
     */
    public function resetSkills()
    {
        $this->skills = array();
    }

    /**
     * Add skill
     *
     * @param int $a_skill_id skill id
     * @param int $a_val tref id
     */
    public function addSkill($a_skill_id, $a_tref_id)
    {
        $this->skills[$a_skill_id . "-" . $a_tref_id] = array(
            "skill_id" => $a_skill_id,
            "tref_id" => $a_tref_id
        );
    }

    /**
     * Remove skill
     *
     * @param int $a_skill_id skill id
     * @param int $a_val tref id
     */
    public function removeSkill($a_skill_id, $a_tref_id)
    {
        unset($this->skills[$a_skill_id . "-" . $a_tref_id]);
    }


    /**
     * Get skills
     *
     * @return
     */
    public function getSkills()
    {
        return $this->skills;
    }

    /**
     * Get odered skills
     *
     * @param
     * @return
     */
    public function getOrderedSkills()
    {
        include_once("./Services/Skill/classes/class.ilVirtualSkillTree.php");
        $vtree = new ilVirtualSkillTree();
        return $vtree->getOrderedNodeset($this->getSkills(), "skill_id", "tref_id");
    }


    /**
     * Read
     */
    public function read()
    {
        $db = $this->db;

        $this->skills = array();
        $set = $db->query("SELECT * FROM cont_skills " .
            " WHERE id  = " . $db->quote($this->getId(), "integer"));
        while ($rec = $db->fetchAssoc($set)) {
            $this->skills[$rec["skill_id"] . "-" . $rec["tref_id"]] = $rec;
        }
    }

    /**
     * Delete
     */
    public function delete()
    {
        $db = $this->db;

        $db->manipulate("DELETE FROM cont_skills WHERE " .
            " id = " . $db->quote($this->getId(), "integer"));
    }

    /**
     * Save
     */
    public function save()
    {
        $db = $this->db;

        $this->delete();
        foreach ($this->skills as $s) {
            $db->manipulate("INSERT INTO cont_skills " .
                "(id, skill_id, tref_id) VALUES (" .
                $db->quote($this->getId(), "integer") . "," .
                $db->quote($s["skill_id"], "integer") . "," .
                $db->quote($s["tref_id"], "integer") . ")");
        }
    }
}
