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

/**
 * Personal skill
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilPersonalSkill implements ilSkillUsageInfo
{
    /**
     * @return array<int, array{skill_node_id: int, title: string}>
     */
    public static function getSelectedUserSkills(int $a_user_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $repo = $DIC->skills()->internal()->repo()->getTreeRepo();

        $set = $ilDB->query(
            "SELECT * FROM skl_personal_skill " .
            " WHERE user_id = " . $ilDB->quote($a_user_id, "integer")
        );
        $pskills = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($repo->isInAnyTree($rec["skill_node_id"])) {
                $pskills[(int) $rec["skill_node_id"]] = array(
                    "skill_node_id" => (int) $rec["skill_node_id"],
                    "title" => ilSkillTreeNode::_lookupTitle($rec["skill_node_id"])
                );
            }
        }
        return $pskills;
    }

    public static function addPersonalSkill(int $a_user_id, int $a_skill_node_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT * FROM skl_personal_skill " .
            " WHERE user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND skill_node_id = " . $ilDB->quote($a_skill_node_id, "integer")
        );
        if (!$ilDB->fetchAssoc($set)) {
            $ilDB->manipulate("INSERT INTO skl_personal_skill " .
                "(user_id, skill_node_id) VALUES (" .
                $ilDB->quote($a_user_id, "integer") . "," .
                $ilDB->quote($a_skill_node_id, "integer") .
                ")");
        }
    }

    public static function removeSkill(int $a_user_id, int $a_skill_node_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->manipulate(
            "DELETE FROM skl_personal_skill WHERE " .
            " user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND skill_node_id = " . $ilDB->quote($a_skill_node_id, "integer")
        );
    }

    public static function removeSkills(int $a_user_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "DELETE FROM skl_personal_skill WHERE " .
            " user_id = " . $ilDB->quote($a_user_id, "integer")
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
    public static function assignMaterial(
        int $a_user_id,
        int $a_top_skill,
        int $a_tref_id,
        int $a_basic_skill,
        int $a_level,
        int $a_wsp_id
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT * FROM skl_assigned_material " .
            " WHERE user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND top_skill_id = " . $ilDB->quote($a_top_skill, "integer") .
            " AND tref_id = " . $ilDB->quote($a_tref_id, "integer") .
            " AND skill_id = " . $ilDB->quote($a_basic_skill, "integer") .
            " AND level_id = " . $ilDB->quote($a_level, "integer") .
            " AND wsp_id = " . $ilDB->quote($a_wsp_id, "integer")
        );
        if (!$ilDB->fetchAssoc($set)) {
            $ilDB->manipulate("INSERT INTO skl_assigned_material " .
                "(user_id, top_skill_id, tref_id, skill_id, level_id, wsp_id) VALUES (" .
                $ilDB->quote($a_user_id, "integer") . "," .
                $ilDB->quote($a_top_skill, "integer") . "," .
                $ilDB->quote($a_tref_id, "integer") . "," .
                $ilDB->quote($a_basic_skill, "integer") . "," .
                $ilDB->quote($a_level, "integer") . "," .
                $ilDB->quote($a_wsp_id, "integer") .
                ")");
        }
    }
    
    /**
     * Get assigned material (for a skill level and user)
     * @return array{user_id: int, top_skill_id: int, skill_id: int, level_id: int, wsp_id: int, tref_id: int}[]
     */
    public static function getAssignedMaterial(int $a_user_id, int $a_tref_id, int $a_level) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT * FROM skl_assigned_material " .
            " WHERE level_id = " . $ilDB->quote($a_level, "integer") .
            " AND tref_id = " . $ilDB->quote($a_tref_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer")
        );
        $mat = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec['user_id'] = (int) $rec['user_id'];
            $rec['top_skill_id'] = (int) $rec['top_skill_id'];
            $rec['skill_id'] = (int) $rec['skill_id'];
            $rec['level_id'] = (int) $rec['level_id'];
            $rec['wsp_id'] = (int) $rec['wsp_id'];
            $rec['tref_id'] = (int) $rec['tref_id'];
            $mat[] = $rec;
        }
        return $mat;
    }
    
    /**
     * Count assigned material (for a skill level and user)
     */
    public static function countAssignedMaterial(int $a_user_id, int $a_tref_id, int $a_level) : int
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT count(*) as cnt FROM skl_assigned_material " .
            " WHERE level_id = " . $ilDB->quote($a_level, "integer") .
            " AND tref_id = " . $ilDB->quote($a_tref_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["cnt"];
    }

    public static function removeMaterial(int $a_user_id, int $a_tref_id, int $a_level_id, int $a_wsp_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $t = "DELETE FROM skl_assigned_material WHERE " .
            " user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND tref_id = " . $ilDB->quote($a_tref_id, "integer") .
            " AND level_id = " . $ilDB->quote($a_level_id, "integer") .
            " AND wsp_id = " . $ilDB->quote($a_wsp_id, "integer");

        $ilDB->manipulate($t);
    }

    public static function removeMaterials(int $a_user_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $t = "DELETE FROM skl_assigned_material WHERE " .
            " user_id = " . $ilDB->quote($a_user_id, "integer");
        $ilDB->manipulate($t);
    }

    //
    // Self evaluation
    //
    
    /**
     * @param int $a_user_id user id
     * @param int $a_top_skill the "selectable" top skill
     * @param int $a_tref_id template reference id
     * @param int $a_basic_skill the basic skill the level belongs to
     * @param int $a_level level id
     */
    public static function saveSelfEvaluation(
        int $a_user_id,
        int $a_top_skill,
        int $a_tref_id,
        int $a_basic_skill,
        int $a_level
    ) : void {
        if ($a_level > 0) {
            ilBasicSkill::writeUserSkillLevelStatus(
                $a_level,
                $a_user_id,
                0,
                $a_tref_id,
                ilBasicSkill::ACHIEVED,
                false,
                1
            );
        } else {
            ilBasicSkill::resetUserSkillLevelStatus($a_user_id, $a_basic_skill, $a_tref_id, 0, true);
        }
    }

    /**
     * @param int $a_user_id user id
     * @param int $a_top_skill the "selectable" top skill
     * @param int $a_tref_id template reference id
     * @param int $a_basic_skill the basic skill the level belongs to
     * @return int|null level id
     */
    public static function getSelfEvaluation(
        int $a_user_id,
        int $a_top_skill,
        int $a_tref_id,
        int $a_basic_skill
    ) : ?int {
        $bs = new ilBasicSkill($a_basic_skill);
        return $bs->getLastLevelPerObject($a_tref_id, 0, $a_user_id, 1);
    }

    /**
     * @param int $a_user_id user id
     * @param int $a_top_skill the "selectable" top skill
     * @param int $a_tref_id template reference id
     * @param int $a_basic_skill the basic skill the level belongs to
     * @return string|null status date
     */
    public static function getSelfEvaluationDate(
        int $a_user_id,
        int $a_top_skill,
        int $a_tref_id,
        int $a_basic_skill
    ) : ?string {
        $bs = new ilBasicSkill($a_basic_skill);
        return $bs->getLastUpdatePerObject($a_tref_id, 0, $a_user_id, 1);
    }

    /**
     * @param array{skill_id: int, tref_id: int}[] $a_cskill_ids
     *
     * @return array<string, array<string, array{key: string}[]>>
     */
    public static function getUsageInfo(array $a_cskill_ids) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        // material
        $a_usages = ilSkillUsage::getUsageInfoGeneric(
            $a_cskill_ids,
            ilSkillUsage::USER_MATERIAL,
            "skl_assigned_material",
            "user_id"
        );

        // users that use the skills as personal skills
        $pskill_ids = [];
        $tref_ids = [];
        foreach ($a_cskill_ids as $cs) {
            if ($cs["tref_id"] > 0) {
                if (ilSkillTemplateReference::_lookupTemplateId($cs["tref_id"]) == $cs["skill_id"]) {
                    $pskill_ids[$cs["tref_id"]] = $cs["tref_id"];
                    $tref_ids[(int) $cs["tref_id"]] = $cs["skill_id"];
                }
            } else {
                $pskill_ids[$cs["skill_id"]] = $cs["skill_id"];
            }
        }
        $set = $ilDB->query(
            "SELECT skill_node_id, user_id FROM skl_personal_skill " .
            " WHERE " . $ilDB->in("skill_node_id", $pskill_ids, false, "integer") .
            " GROUP BY skill_node_id, user_id"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (isset($tref_ids[(int) $rec["skill_node_id"]])) {
                $a_usages[$tref_ids[$rec["skill_node_id"]] . ":" . $rec["skill_node_id"]][ilSkillUsage::PERSONAL_SKILL][] =
                    array("key" => $rec["user_id"]);
            } else {
                $a_usages[$rec["skill_node_id"] . ":0"][ilSkillUsage::PERSONAL_SKILL][] =
                    array("key" => $rec["user_id"]);
            }
        }

        return $a_usages;
    }
}
