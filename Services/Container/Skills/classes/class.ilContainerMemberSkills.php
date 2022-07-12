<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

use ILIAS\Skill\Service\SkillTreeService;
use ILIAS\Skill\Service\SkillProfileService;

/**
 * Skills of a container
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilContainerMemberSkills
{
    protected ilDBInterface $db;
    protected SkillTreeService $tree_service;
    protected SkillProfileService $profile_service;
    protected array $skills = [];
    protected int $obj_id = 0;
    protected int $user_id = 0;
    protected array $skill_levels = [];
    protected bool $published = false;

    public function __construct(int $a_obj_id, int $a_user_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->tree_service = $DIC->skills()->tree();
        $this->profile_service = $DIC->skills()->profile();

        $this->setObjId($a_obj_id);
        $this->setUserId($a_user_id);
        if ($a_obj_id > 0 && $a_user_id > 0) {
            $this->read();
        }
    }

    public function setObjId(int $a_val) : void
    {
        $this->obj_id = $a_val;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function setUserId(int $a_val) : void
    {
        $this->user_id = $a_val;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function read() : void
    {
        $db = $this->db;

        $set = $db->query(
            "SELECT * FROM cont_member_skills " .
            " WHERE obj_id = " . $db->quote($this->getObjId(), "integer") .
            " AND user_id = " . $db->quote($this->getUserId(), "integer")
        );
        $this->skill_levels = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $this->skill_levels[$rec["skill_id"] . ":" . $rec["tref_id"]] = $rec["level_id"];
            $this->published = $rec["published"];	// this is a little weak, but the value should be the same for all save skills
        }
    }

    /**
     * @return array (key is skill_id:tref_id, value is level id)
     */
    public function getSkillLevels() : array
    {
        return $this->skill_levels;
    }

    /**
     * @return array[] each item comes with keys "level_id", "skill_id", "tref_id"
     */
    public function getOrderedSkillLevels() : array
    {
        $skill_levels = array_map(static function ($a, $k) : array {
            $s = explode(":", $k);
            return ["level_id" => $a, "skill_id" => $s[0], "tref_id" => $s[1]];
        }, $this->getSkillLevels(), array_keys($this->getSkillLevels()));

        $vtree = $this->tree_service->getGlobalVirtualSkillTree();
        return $vtree->getOrderedNodeset($skill_levels, "skill_id", "tref_id");
    }

    public function getPublished() : bool
    {
        return $this->published;
    }

    /**
     * @param array $a_level_data (key is skill_id:tref_id, value is level id)
     */
    public function saveLevelForSkills(array $a_level_data) : void
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
     */
    public function delete() : void
    {
        $db = $this->db;

        $db->manipulate("DELETE FROM cont_member_skills WHERE " .
            " obj_id = " . $db->quote($this->getObjId(), "integer") .
            " AND user_id = " . $db->quote($this->getUserId(), "integer"));
    }

    public function publish(int $a_ref_id) : bool
    {
        $db = $this->db;

        $changed = ilBasicSkill::removeAllUserSkillLevelStatusOfObject(
            $this->getUserId(),
            $this->getObjId(),
            false,
            (string) $this->getObjId()
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

            if ($sk[1] > 0) {
                ilPersonalSkill::addPersonalSkill($this->getUserId(), $sk[1]);
            } else {
                ilPersonalSkill::addPersonalSkill($this->getUserId(), $sk[0]);
            }
        }

        //write profile completion entries if fulfilment status has changed
        $this->profile_service->writeCompletionEntryForAllProfiles($this->getUserId());

        $db->manipulate("UPDATE cont_member_skills SET " .
            " published = " . $db->quote(1, "integer") .
            " WHERE obj_id = " . $db->quote($this->getObjId(), "integer") .
            " AND user_id = " . $db->quote($this->getUserId(), "integer"));

        return $changed;
    }

    public function removeAllSkillLevels() : void
    {
        ilBasicSkill::removeAllUserSkillLevelStatusOfObject(
            $this->getUserId(),
            $this->getObjId(),
            false,
            (string) $this->getObjId()
        );

        $this->delete();
    }
}
