<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/interfaces/interface.ilSkillUsageInfo.php");

/**
 * Skill usage
 *
 * With this class a general skill use by an object (identified by its obj_id)
 * is registered or unregistered.
 *
 * The class maintains skill usages of the following types
 * - GENERAL: General use submitted by an object, saved in table "skl_usage"
 * - USER_ASSIGNED: Skill level is assigned to a user (tables skl_user_skill_level and skl_user_has_level)
 * - PERSONAL_SKILL: table skl_personal_skill (do we need that?)
 * - USER_MATERIAL: User has assigned material to the skill
 * - SELF_EVAL: User has self evaluated (may be USER_ASSIGNED in the future)
 * - PROFILE: Skill is used in skill profile (table "skl_profile_level")
 * - RESOURCE: A resource is assigned to a skill level (table "skl_skill_resource")
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesSkill
 */
class ilSkillUsage implements ilSkillUsageInfo
{
    const TYPE_GENERAL = "gen";
    const USER_ASSIGNED = "user";
    const PERSONAL_SKILL = "pers";
    const USER_MATERIAL = "mat";
    const SELF_EVAL = "seval";
    const PROFILE = "prof";
    const RESOURCE = "res";
    
    // these classes implement the ilSkillUsageInfo interface
    // currently this array is ok, we do not need any subscription model here
    /*protected $classes = array("ilBasicSkill", "ilPersonalSkill",
        "ilSkillSelfEvaluation", "ilSkillProfile", "ilSkillResources", "ilSkillUsage");*/
    protected $classes = array("ilBasicSkill", "ilPersonalSkill", "ilSkillProfile",  "ilSkillResources", "ilSkillUsage");
    
    /**
     * Set usage
     *
     * @param int $a_obj_id object id
     * @param int $a_skill_id skill id
     * @param int $a_tref_id tref id
     * @param bool $a_use in use true/false
     */
    public static function setUsage($a_obj_id, $a_skill_id, $a_tref_id, $a_use = true)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        if ($a_use) {
            $ilDB->replace(
                "skl_usage",
                array(
                    "obj_id" => array("integer", $a_obj_id),
                    "skill_id" => array("integer", $a_skill_id),
                    "tref_id" => array("integer", $a_tref_id)
                    ),
                array()
            );
        } else {
            $ilDB->manipulate(
                $q = "DELETE FROM skl_usage WHERE " .
                " obj_id = " . $ilDB->quote($a_obj_id, "integer") .
                " AND skill_id = " . $ilDB->quote($a_skill_id, "integer") .
                " AND tref_id = " . $ilDB->quote($a_tref_id, "integer")
            );
            //echo $q; exit;
        }
    }
    
    /**
     * Get usages
     *
     * @param int $a_skill_id skill id
     * @param int $a_tref_id tref id
     * @return array of int object ids
     */
    public static function getUsages($a_skill_id, $a_tref_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT obj_id FROM skl_usage " .
            " WHERE skill_id = " . $ilDB->quote($a_skill_id, "integer") .
            " AND tref_id = " . $ilDB->quote($a_tref_id, "integer")
        );
        $obj_ids = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $obj_ids[] = $rec["obj_id"];
        }
        
        return $obj_ids;
    }
    
    /**
     * Get usage info
     *
     * @param array $a_cskill_ids skill ids
     * @param array $a_usages usages array
     */
    public static function getUsageInfo($a_cskill_ids, &$a_usages)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        self::getUsageInfoGeneric(
            $a_cskill_ids,
            $a_usages,
            ilSkillUsage::TYPE_GENERAL,
            "skl_usage",
            "obj_id"
        );
    }
    
    /**
     * Get standard usage query
     *
     * @param array $a_cskill_ids skill ids
     * @param array $a_usages usages array
     */
    public static function getUsageInfoGeneric(
        $a_cskill_ids,
        &$a_usages,
        $a_usage_type,
        $a_table,
        $a_key_field,
        $a_skill_field = "skill_id",
        $a_tref_field = "tref_id"
    ) {
        global $DIC;

        $ilDB = $DIC->database();

        if (count($a_cskill_ids) == 0) {
            return;
        }

        $w = "WHERE";
        $q = "SELECT " . $a_key_field . ", " . $a_skill_field . ", " . $a_tref_field . " FROM " . $a_table . " ";
        foreach ($a_cskill_ids as $sk) {
            $q .= $w . " (" . $a_skill_field . " = " . $ilDB->quote($sk["skill_id"], "integer") .
            " AND " . $a_tref_field . " = " . $ilDB->quote($sk["tref_id"], "integer") . ") ";
            $w = "OR";
        }
        $q .= " GROUP BY " . $a_key_field . ", " . $a_skill_field . ", " . $a_tref_field;

        $set = $ilDB->query($q);
        while ($rec = $ilDB->fetchAssoc($set)) {
            $a_usages[$rec[$a_skill_field] . ":" . $rec[$a_tref_field]][$a_usage_type][] =
                    array("key" => $rec[$a_key_field]);
        }
    }

    
    /**
     * Get all usages info
     *
     * @param array of common skill ids ("skill_id" => skill_id, "tref_id" => tref_id)
     * @return array usages
     */
    public function getAllUsagesInfo($a_cskill_ids)
    {
        $classes = $this->classes;
        
        $usages = array();
        foreach ($classes as $class) {
            // make static call
            include_once("./Services/Skill/classes/class." . $class . ".php");
            //call_user_func($class.'::getUsageInfo', $a_cskill_ids, $usages);
            $class::getUsageInfo($a_cskill_ids, $usages);
        }
        return $usages;
    }

    /**
     * Get all usages info of subtree
     *
     * @param int $a_skill_id skill node id
     * @param int $a_tref_id tref id
     * @return array usages
     */
    public function getAllUsagesInfoOfSubtree($a_skill_id, $a_tref_id = 0)
    {
        // get nodes
        include_once("./Services/Skill/classes/class.ilVirtualSkillTree.php");
        $vtree = new ilVirtualSkillTree();
        $nodes = $vtree->getSubTreeForCSkillId($a_skill_id . ":" . $a_tref_id);

        return $this->getAllUsagesInfo($nodes);
    }

    /**
     * Get all usages info of subtree
     *
     * @param array $a_cskill_ids array of common skill ids ("skill_id" => skill_id, "tref_id" => tref_id)
     * @return array usages
     */
    public function getAllUsagesInfoOfSubtrees($a_cskill_ids)
    {
        // get nodes
        include_once("./Services/Skill/classes/class.ilVirtualSkillTree.php");
        $vtree = new ilVirtualSkillTree();
        $allnodes = array();
        foreach ($a_cskill_ids as $s) {
            $nodes = $vtree->getSubTreeForCSkillId($s["skill_id"] . ":" . $s["tref_id"]);
            foreach ($nodes as $n) {
                $allnodes[] = $n;
            }
        }

        return $this->getAllUsagesInfo($allnodes);
    }

    /**
     * Get all usages of template
     *
     * @param int $a_tempate_id template
     * @return array usages array
     */
    public function getAllUsagesOfTemplate($a_tempate_id)
    {
        $skill_logger = ilLoggerFactory::getLogger('skll');
        $skill_logger->debug("ilSkillUsage: getAllUsagesOfTemplate(" . $a_tempate_id . ")");

        // get all trefs for template id
        include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
        $trefs = ilSkillTemplateReference::_lookupTrefIdsForTemplateId($a_tempate_id);

        // get all usages of subtrees of template_id:tref
        $cskill_ids = array();
        foreach ($trefs as $tref) {
            $cskill_ids[] = array("skill_id" => $a_tempate_id, "tref_id" => $tref);
            $skill_logger->debug("ilSkillUsage: ... skill_id: " . $a_tempate_id . ", tref_id: " . $tref . ".");
        }

        $skill_logger->debug("ilSkillUsage: ... count cskill_ids: " . count($cskill_ids) . ".");

        return $this->getAllUsagesInfoOfSubtrees($cskill_ids);
    }

    /**
     * Get type info string
     *
     * @param string $a_type usage type
     * @return string lang string
     */
    public static function getTypeInfoString($a_type)
    {
        global $DIC;

        $lng = $DIC->language();
        
        return $lng->txt("skmg_usage_type_info_" . $a_type);
    }

    /**
     * Get type info string
     *
     * @param
     * @return
     */
    public static function getObjTypeString($a_type)
    {
        global $DIC;

        $lng = $DIC->language();
        
        switch ($a_type) {
            case self::TYPE_GENERAL:
            case self::RESOURCE:
                return $lng->txt("skmg_usage_obj_objects");
                break;
            
            case self::USER_ASSIGNED:
            case self::PERSONAL_SKILL:
            case self::USER_MATERIAL:
            case self::SELF_EVAL:
                return $lng->txt("skmg_usage_obj_users");
                break;

            case self::PROFILE:
                return $lng->txt("skmg_usage_obj_profiles");
                break;
        }
        
        return $lng->txt("skmg_usage_type_info_" . $a_type);
    }
}
