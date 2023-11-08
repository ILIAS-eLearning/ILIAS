<?php

declare(strict_types=1);

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

namespace ILIAS\Skill\Usage;

use ILIAS\Skill\Personal\AssignedMaterialManager;
use ILIAS\Skill\Profile\SkillProfileManager;
use ILIAS\Skill\Resource\SkillResourcesManager;
use ILIAS\Skill\Tree\SkillTreeFactory;

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
 * @author Alex Killing <killing@leifos.com>
 */
class SkillUsageManager implements SkillUsageInfo
{
    public const TYPE_GENERAL = "gen";
    public const USER_ASSIGNED = "user";
    public const PERSONAL_SKILL = "pers";
    public const USER_MATERIAL = "mat";
    public const SELF_EVAL = "seval";
    public const PROFILE = "prof";
    public const RESOURCE = "res";

    /**
     * @var SkillUsageInfo[]
     */
    protected array $classes = [\ilBasicSkill::class, AssignedMaterialManager::class, SkillProfileManager::class,
                                SkillResourcesManager::class, SkillUsageManager::class];

    protected SkillUsageDBRepository $usage_repo;
    protected SkillTreeFactory $tree_factory;
    protected \ilSkillTreeRepository $tree_repo;
    protected SkillProfileManager $profile_manager;
    protected \ilLanguage $lng;

    public function __construct(
        SkillUsageDBRepository $usage_repo = null,
        SkillTreeFactory $tree_factory = null,
        \ilSkillTreeRepository $tree_repo = null,
        SkillProfileManager $profile_manager = null
    ) {
        global $DIC;

        $this->usage_repo = ($usage_repo) ?: $DIC->skills()->internal()->repo()->getUsageRepo();
        $this->tree_factory = ($tree_factory) ?: $DIC->skills()->internal()->factory()->tree();
        $this->tree_repo = ($tree_repo) ?: $DIC->skills()->internal()->repo()->getTreeRepo();
        $this->profile_manager = ($profile_manager) ?: $DIC->skills()->internal()->manager()->getProfileManager();
        $this->lng = $DIC->language();
    }

    public function addUsage(int $obj_id, int $skill_id, int $tref_id): void
    {
        $this->usage_repo->add($obj_id, $skill_id, $tref_id);
    }

    public function removeUsage(int $obj_id, int $skill_id, int $tref_id): void
    {
        $this->usage_repo->remove($obj_id, $skill_id, $tref_id);
    }

    public function removeUsagesFromObject(int $obj_id): void
    {
        $this->usage_repo->removeFromObject($obj_id);
    }

    public function removeUsagesForSkill(int $node_id, bool $is_referenece = false): void
    {
        $this->usage_repo->removeForSkill($node_id, $is_referenece);
    }

    /**
     * @return int[]
     */
    public function getUsages(int $skill_id, int $tref_id): array
    {
        $obj_ids = $this->usage_repo->getUsages($skill_id, $tref_id);

        return $obj_ids;
    }

    /**
     * @inheritdoc
     */
    public static function getUsageInfo(array $a_cskill_ids): array
    {
        $class = new self();
        return $class->getUsageInfoGeneric(
            $a_cskill_ids,
            self::TYPE_GENERAL,
            "skl_usage",
            "obj_id"
        );
    }

    /**
     * Get standard usage query
     * @param array{skill_id: int, tref_id: int}[] $cskill_ids
     *
     * @return array<string, array<string, array{key: string}[]>>
     */
    public function getUsageInfoGeneric(
        array $cskill_ids,
        string $usage_type,
        string $table,
        string $key_field,
        string $skill_field = "skill_id",
        string $tref_field = "tref_id"
    ): array {
        if (count($cskill_ids) === 0) {
            return [];
        }

        $usages = $this->usage_repo->getUsageInfoGeneric(
            $cskill_ids,
            $usage_type,
            $table,
            $key_field,
            $skill_field,
            $tref_field
        );

        return $usages;
    }

    /**
     * @param array{skill_id: int, tref_id: int}[] $cskill_ids array of common skill ids ("skill_id" => skill_id, "tref_id" => tref_id)
     * @return array<string, array<string, array{key: string}[]>>
     */
    public function getAllUsagesInfo(array $cskill_ids): array
    {
        $classes = $this->classes;

        $usages = [];
        foreach ($classes as $class) {
            $usages = array_merge_recursive($usages, $class::getUsageInfo($cskill_ids));
        }
        return $usages;
    }

    /**
     * @param array $tree_ids array of common skill ids ("skill_id" => skill_id, "tref_id" => tref_id)
     * @return array<string, array<string, array{key: string}[]>>
     */
    public function getAllUsagesInfoOfTrees(array $tree_ids): array
    {
        // get nodes

        $allnodes = [];
        foreach ($tree_ids as $t) {
            $vtree = $this->tree_factory->getGlobalVirtualTree();
            $nodes = $vtree->getSubTreeForTreeId((string) $t);
            foreach ($nodes as $n) {
                $allnodes[] = $n;
            }
        }

        return $this->getAllUsagesInfo($allnodes);
    }

    /**
     * @return array<string, array<string, array{key: string}[]>>
     */
    public function getAllUsagesInfoOfSubtree(int $skill_id, int $tref_id = 0): array
    {
        // get nodes
        $vtree = $this->tree_repo->getVirtualTreeForNodeId($skill_id);
        $nodes = $vtree->getSubTreeForCSkillId($skill_id . ":" . $tref_id);

        return $this->getAllUsagesInfo($nodes);
    }

    /**
     * @param array $cskill_ids array of common skill ids ("skill_id" => skill_id, "tref_id" => tref_id)
     * @return array<string, array<string, array{key: string}[]>>
     */
    public function getAllUsagesInfoOfSubtrees(array $cskill_ids): array
    {
        // get nodes
        $allnodes = [];
        foreach ($cskill_ids as $s) {
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
    public function getAllUsagesOfTemplate(int $template_id): array
    {
        $skill_logger = \ilLoggerFactory::getLogger("skll");
        $skill_logger->debug("SkillUsageManager: getAllUsagesOfTemplate(" . $template_id . ")");

        // get all trefs for template id
        $trefs = \ilSkillTemplateReference::_lookupTrefIdsForTemplateId($template_id);

        // get all usages of subtrees of template_id:tref
        $cskill_ids = [];
        foreach ($trefs as $tref) {
            $cskill_ids[] = array("skill_id" => $template_id, "tref_id" => $tref);
            $skill_logger->debug("SkillUsageManager: ... skill_id: " . $template_id . ", tref_id: " . $tref . ".");
        }

        $skill_logger->debug("SkillUsageManager: ... count cskill_ids: " . count($cskill_ids) . ".");

        return $this->getAllUsagesInfoOfSubtrees($cskill_ids);
    }

    public function getTypeInfoString(string $type): string
    {
        return $this->lng->txt("skmg_usage_type_info_" . $type);
    }

    public function getObjTypeString(string $type): string
    {
        switch ($type) {
            case self::TYPE_GENERAL:
            case self::RESOURCE:
                return $this->lng->txt("skmg_usage_obj_objects");

            case self::USER_ASSIGNED:
            case self::PERSONAL_SKILL:
            case self::USER_MATERIAL:
            case self::SELF_EVAL:
                return $this->lng->txt("skmg_usage_obj_users");

            case self::PROFILE:
                return $this->lng->txt("skmg_usage_obj_profiles");

            default:
                return $this->lng->txt("skmg_usage_type_info_" . $type);
        }
    }

    /**
     * @return int[]
     */
    public function getAssignedObjectsForSkill(int $skill_id, int $tref_id): array
    {
        //$objects = $this->getAllUsagesInfoOfSubtree($a_skill_id, $a_tref_id);
        $objects = $this->getUsages($skill_id, $tref_id);

        return $objects;
    }

    /**
     * @return string[]
     */
    public function getAssignedObjectsForSkillTemplate(int $template_id): array
    {
        $usages = $this->getAllUsagesOfTemplate($template_id);
        $obj_usages = array_column($usages, "gen");

        return array_column(current(array_reverse($obj_usages)) ?: [], "key");
    }

    /**
     * @return int[]
     */
    public function getAssignedObjectsForSkillProfile(int $profile_id): array
    {
        $skills = $this->profile_manager->getSkillLevels($profile_id);
        $objects = [];

        // usages for skills within skill profile
        foreach ($skills as $skill) {
            $obj_usages = $this->getUsages($skill->getBaseSkillId(), $skill->getTrefId());
            foreach ($obj_usages as $id) {
                if (!in_array($id, $objects) && \ilObject::_hasUntrashedReference($id)) {
                    $objects[] = $id;
                }
            }
        }

        // courses and groups which are using skill profile
        $roles = $this->profile_manager->getRoleAssignments($profile_id);
        foreach ($roles as $role) {
            if (($role->getObjType() == "crs" || $role->getObjType() == "grp")
                && !in_array($role->getObjId(), $objects)) {
                $obj_ref_id = \ilObject::_getAllReferences($role->getObjId());
                $obj_ref_id = end($obj_ref_id);
                if ($role->getId() === \ilParticipants::getDefaultMemberRole($obj_ref_id)) {
                    $objects[] = $role->getObjId();
                }
            }
        }

        return $objects;
    }
}
