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

use ILIAS\Skill\Service\SkillInternalFactoryService;
use ILIAS\Skill\Profile\SkillProfile;
use ILIAS\Skill\Profile\SkillProfileManager;

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
 */
class ilSkillUsage implements ilSkillUsageInfo
{
    public const TYPE_GENERAL = "gen";
    public const USER_ASSIGNED = "user";
    public const PERSONAL_SKILL = "pers";
    public const USER_MATERIAL = "mat";
    public const SELF_EVAL = "seval";
    public const PROFILE = "prof";
    public const RESOURCE = "res";
    
    /**
     * @var ilSkillUsageInfo[]
     */
    protected array $classes = [ilBasicSkill::class, ilPersonalSkill::class, SkillProfile::class,
                                ilSkillResources::class, ilSkillUsage::class];

    protected ilSkillTreeRepository $tree_repo;
    protected SkillInternalFactoryService $tree_factory;
    protected SkillProfileManager $profile_manager;

    public function __construct()
    {
        global $DIC;

        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
        $this->tree_factory = $DIC->skills()->internal()->factory();
        $this->profile_manager = $DIC->skills()->internal()->manager()->getProfileManager();
    }

    public static function setUsage(int $a_obj_id, int $a_skill_id, int $a_tref_id, bool $a_use = true) : void
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
                []
            );
        } else {
            $ilDB->manipulate(
                $q = "DELETE FROM skl_usage WHERE " .
                " obj_id = " . $ilDB->quote($a_obj_id, "integer") .
                " AND skill_id = " . $ilDB->quote($a_skill_id, "integer") .
                " AND tref_id = " . $ilDB->quote($a_tref_id, "integer")
            );
        }
    }
    
    /**
     * @return int[]
     */
    public static function getUsages(int $a_skill_id, int $a_tref_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query(
            "SELECT obj_id FROM skl_usage " .
            " WHERE skill_id = " . $ilDB->quote($a_skill_id, "integer") .
            " AND tref_id = " . $ilDB->quote($a_tref_id, "integer")
        );
        $obj_ids = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $obj_ids[] = (int) $rec["obj_id"];
        }
        
        return $obj_ids;
    }

    /**
     * @param array{skill_id: int, tref_id: int}[] $a_cskill_ids
     *
     * @return array<string, array<string, array{key: string}[]>>
     */
    public static function getUsageInfo(array $a_cskill_ids) : array
    {
        return self::getUsageInfoGeneric(
            $a_cskill_ids,
            ilSkillUsage::TYPE_GENERAL,
            "skl_usage",
            "obj_id"
        );
    }
    
    /**
     * Get standard usage query
     * @param array{skill_id: int, tref_id: int}[] $a_cskill_ids
     *
     * @return array<string, array<string, array{key: string}[]>>
     */
    public static function getUsageInfoGeneric(
        array $a_cskill_ids,
        string $a_usage_type,
        string $a_table,
        string $a_key_field,
        string $a_skill_field = "skill_id",
        string $a_tref_field = "tref_id"
    ) : array {
        global $DIC;

        $a_usages = [];

        $ilDB = $DIC->database();

        if (count($a_cskill_ids) == 0) {
            return [];
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

        return $a_usages;
    }

    /**
     * @param array{skill_id: int, tref_id: int}[] $a_cskill_ids array of common skill ids ("skill_id" => skill_id, "tref_id" => tref_id)
     * @return array<string, array<string, array{key: string}[]>>
     */
    public function getAllUsagesInfo(array $a_cskill_ids) : array
    {
        $classes = $this->classes;
        
        $usages = [];
        foreach ($classes as $class) {
            $usages = array_merge_recursive($usages, $class::getUsageInfo($a_cskill_ids));
        }
        return $usages;
    }

    /**
     * @param array $a_tree_ids array of common skill ids ("skill_id" => skill_id, "tref_id" => tref_id)
     * @return array<string, array<string, array{key: string}[]>>
     */
    public function getAllUsagesInfoOfTrees(array $a_tree_ids) : array
    {
        // get nodes

        $allnodes = [];
        foreach ($a_tree_ids as $t) {
            $vtree = $this->tree_factory->tree()->getGlobalVirtualTree();
            $nodes = $vtree->getSubTreeForTreeId($t);
            foreach ($nodes as $n) {
                $allnodes[] = $n;
            }
        }

        return $this->getAllUsagesInfo($allnodes);
    }

    /**
     * @return array<string, array<string, array{key: string}[]>>
     */
    public function getAllUsagesInfoOfSubtree(int $a_skill_id, int $a_tref_id = 0) : array
    {
        // get nodes
        $vtree = $this->tree_repo->getVirtualTreeForNodeId($a_skill_id);
        $nodes = $vtree->getSubTreeForCSkillId($a_skill_id . ":" . $a_tref_id);

        return $this->getAllUsagesInfo($nodes);
    }

    /**
     * @param array $a_cskill_ids array of common skill ids ("skill_id" => skill_id, "tref_id" => tref_id)
     * @return array<string, array<string, array{key: string}[]>>
     */
    public function getAllUsagesInfoOfSubtrees(array $a_cskill_ids) : array
    {
        // get nodes
        $allnodes = [];
        foreach ($a_cskill_ids as $s) {
            $vtree = $this->tree_repo->getVirtualTreeForNodeId($s["skill_id"]);
            $nodes = $vtree->getSubTreeForCSkillId($s["skill_id"] . ":" . $s["tref_id"]);
            foreach ($nodes as $n) {
                $allnodes[] = $n;
            }
        }

        return $this->getAllUsagesInfo($allnodes);
    }

    /**
     * @return array<string, array<string, array{key: string}[]>>
     */
    public function getAllUsagesOfTemplate(int $a_template_id) : array
    {
        $skill_logger = ilLoggerFactory::getLogger('skll');
        $skill_logger->debug("ilSkillUsage: getAllUsagesOfTemplate(" . $a_template_id . ")");

        // get all trefs for template id
        $trefs = ilSkillTemplateReference::_lookupTrefIdsForTemplateId($a_template_id);

        // get all usages of subtrees of template_id:tref
        $cskill_ids = [];
        foreach ($trefs as $tref) {
            $cskill_ids[] = array("skill_id" => $a_template_id, "tref_id" => $tref);
            $skill_logger->debug("ilSkillUsage: ... skill_id: " . $a_template_id . ", tref_id: " . $tref . ".");
        }

        $skill_logger->debug("ilSkillUsage: ... count cskill_ids: " . count($cskill_ids) . ".");

        return $this->getAllUsagesInfoOfSubtrees($cskill_ids);
    }

    public static function getTypeInfoString(string $a_type) : string
    {
        global $DIC;

        $lng = $DIC->language();
        
        return $lng->txt("skmg_usage_type_info_" . $a_type);
    }

    public static function getObjTypeString(string $a_type) : string
    {
        global $DIC;

        $lng = $DIC->language();
        
        switch ($a_type) {
            case self::TYPE_GENERAL:
            case self::RESOURCE:
                return $lng->txt("skmg_usage_obj_objects");
            
            case self::USER_ASSIGNED:
            case self::PERSONAL_SKILL:
            case self::USER_MATERIAL:
            case self::SELF_EVAL:
                return $lng->txt("skmg_usage_obj_users");

            case self::PROFILE:
                return $lng->txt("skmg_usage_obj_profiles");

            default:
                return $lng->txt("skmg_usage_type_info_" . $a_type);
        }
    }

    /**
     * @return int[]
     */
    public function getAssignedObjectsForSkill(int $a_skill_id, int $a_tref_id) : array
    {
        //$objects = $this->getAllUsagesInfoOfSubtree($a_skill_id, $a_tref_id);
        $objects = self::getUsages($a_skill_id, $a_tref_id);

        return $objects;
    }

    /**
     * @return string[]
     */
    public function getAssignedObjectsForSkillTemplate(int $a_template_id) : array
    {
        $usages = $this->getAllUsagesOfTemplate($a_template_id);
        $obj_usages = array_column($usages, "gen");

        return array_column(current(array_reverse($obj_usages)) ?: [], 'key');
    }

    /**
     * @return int[]
     */
    public function getAssignedObjectsForSkillProfile(int $a_profile_id) : array
    {
        $profile = $this->profile_manager->getById($a_profile_id);
        $skills = $profile->getSkillLevels();
        $objects = [];

        // usages for skills within skill profile
        foreach ($skills as $skill) {
            $obj_usages = self::getUsages($skill["base_skill_id"], $skill["tref_id"]);
            foreach ($obj_usages as $id) {
                if (!in_array($id, $objects)) {
                    $objects[] = $id;
                }
            }
        }

        // courses and groups which are using skill profile
        $roles = $this->profile_manager->getAssignedRoles($profile->getId());
        foreach ($roles as $role) {
            if (($role["object_type"] == "crs" || $role["object_type"] == "grp")
                && !in_array($role["object_id"], $objects)) {
                $objects[] = $role["object_id"];
            }
        }

        return $objects;
    }
}
