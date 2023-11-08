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

namespace ILIAS\Skill\Node;

use ILIAS\Skill\Tree\SkillTreeManager;
use ILIAS\Skill\Personal\PersonalSkillManager;
use ILIAS\Skill\Personal\AssignedMaterialManager;
use ILIAS\Skill\Profile\SkillProfileManager;
use ILIAS\Skill\Profile\SkillProfileCompletionManager;
use ILIAS\Skill\Resource\SkillResourcesManager;
use ILIAS\Skill\Usage\SkillUsageManager;

/**
 * Skill deletion manager
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillDeletionManager
{
    protected SkillTreeManager $tree_manager;
    protected PersonalSkillManager $personal_manager;
    protected AssignedMaterialManager $material_manager;
    protected SkillProfileManager $profile_manager;
    protected SkillProfileCompletionManager $profile_completion_manager;
    protected SkillResourcesManager $resources_manager;
    protected SkillUsageManager $usage_manager;
    protected \ilSkillTreeRepository $tree_repo;
    protected \ilSkillLevelRepository $level_repo;
    protected \ilSkillUserLevelRepository $user_level_repo;
    protected \ilAppEventHandler $event_handler;

    public function __construct(
        SkillTreeManager $tree_manager = null,
        PersonalSkillManager $personal_manager = null,
        AssignedMaterialManager $material_manager = null,
        SkillProfileManager $profile_manager = null,
        SkillProfileCompletionManager $profile_completion_manager = null,
        SkillResourcesManager $resources_manager = null,
        SkillUsageManager $usage_manager = null,
        \ilSkillTreeRepository $tree_repo = null,
        \ilSkillLevelRepository $level_repo = null,
        \ilSkillUserLevelRepository $user_level_repo = null,
        \ilAppEventHandler $event_handler = null,
    ) {
        global $DIC;

        $this->tree_manager = ($tree_manager) ?: $DIC->skills()->internal()->manager()->getTreeManager();
        $this->personal_manager = ($personal_manager) ?: $DIC->skills()->internal()->manager()->getPersonalSkillManager();
        $this->material_manager = ($material_manager) ?: $DIC->skills()->internal()->manager()->getAssignedMaterialManager();
        $this->profile_manager = ($profile_manager) ?: $DIC->skills()->internal()->manager()->getProfileManager();
        $this->profile_completion_manager = ($profile_completion_manager) ?: $DIC->skills()->internal()->manager()->getProfileCompletionManager();
        $this->resources_manager = ($resources_manager) ?: $DIC->skills()->internal()->manager()->getResourceManager();
        $this->usage_manager = ($usage_manager) ?: $DIC->skills()->internal()->manager()->getUsageManager();
        $this->tree_repo = ($tree_repo) ?: $DIC->skills()->internal()->repo()->getTreeRepo();
        $this->level_repo = ($level_repo) ?: $DIC->skills()->internal()->repo()->getLevelRepo();
        $this->user_level_repo = ($user_level_repo) ?: $DIC->skills()->internal()->repo()->getUserLevelRepo();
        $this->event_handler = ($event_handler) ?: $DIC->event();
    }

    public function deleteTree(int $node_id): void
    {
        if ($node_id != \ilTree::POS_FIRST_NODE) {
            $tree = $this->tree_repo->getTreeForNodeId($node_id);
            $tree_obj = $this->tree_manager->getTree($tree->getTreeId());

            // delete competence profiles of tree
            $tree_profiles = $this->profile_manager->getProfilesForSkillTree($tree->getTreeId());
            foreach ($tree_profiles as $profile) {
                $this->profile_manager->delete($profile->getId());
                $this->profile_completion_manager->deleteEntriesForProfile($profile->getId());
            }

            $this->deleteNode($node_id, $tree);
            $this->tree_manager->deleteTree($tree_obj);
        }
    }

    public function deleteNode(int $node_id, \ilSkillTree $tree = null): void
    {
        if ($node_id != \ilTree::POS_FIRST_NODE) {
            if (!$tree) {
                $tree = $this->tree_repo->getTreeForNodeId($node_id);
            }
            $obj = \ilSkillTreeNodeFactory::getInstance($node_id);
            $node_data = $tree->getNodeData($node_id);
            if (is_object($obj)) {
                $obj_type = $obj->getType();
                switch ($obj_type) {
                    case "skrt":
                        $this->deleteSkillRoot($obj->getId(), $tree);
                        break;
                    case "skll":
                        $this->deleteSkill($obj->getId());
                        break;
                    case "scat":
                        $this->deleteSkillCategory($obj->getId(), $tree);
                        break;
                    case "sktr":
                        $this->deleteSkillTemplateReference($obj->getId());
                        break;
                    case "sktp":
                        $this->deleteSkillTemplate($obj->getId());
                        break;
                    case "sctp":
                        $this->deleteSkillCategoryTemplate($obj->getId(), $tree);
                        break;
                }
                $obj->delete();
            }
            if ($tree->isInTree($node_id)) {
                $tree->deleteTree($node_data);
            }
        }
    }

    protected function deleteSkillRoot(int $skrt_id, \ilSkillTree $tree): void
    {
        $childs = $tree->getChildsByTypeFilter(
            $skrt_id,
            ["skll", "scat", "sktp", "sctp", "sktr"]
        );
        foreach ($childs as $node) {
            $this->deleteNode((int) $node["obj_id"], $tree);
        }
    }

    protected function deleteSkill(int $skll_id): void
    {
        $this->level_repo->deleteLevelsOfSkill($skll_id);
        $this->user_level_repo->deleteUserLevelsOfSkill($skll_id);
        $this->usage_manager->removeUsagesForSkill($skll_id);
        $this->personal_manager->removePersonalSkillsForSkill($skll_id);
        $this->material_manager->removeAssignedMaterialsForSkill($skll_id);
        $this->profile_manager->deleteProfileLevelsForSkill($skll_id);
        $this->resources_manager->removeResourcesForSkill($skll_id);
        $this->event_handler->raise("components/ILIAS/Skill", "deleteSkill", ["node_id" => $skll_id, "is_reference" => false]);
    }

    protected function deleteSkillCategory(int $scat_id, \ilSkillTree $tree): void
    {
        $childs = $tree->getChildsByTypeFilter(
            $scat_id,
            ["skll", "scat", "sktr"]
        );
        foreach ($childs as $node) {
            $this->deleteNode((int) $node["obj_id"], $tree);
        }

        $this->personal_manager->removePersonalSkillsForSkill($scat_id);
    }

    protected function deleteSkillTemplateReference(int $sktr_id): void
    {
        $this->user_level_repo->deleteUserLevelsOfSkill($sktr_id, true);
        $this->usage_manager->removeUsagesForSkill($sktr_id, true);
        $this->personal_manager->removePersonalSkillsForSkill($sktr_id);
        $this->material_manager->removeAssignedMaterialsForSkill($sktr_id, true);
        $this->profile_manager->deleteProfileLevelsForSkill($sktr_id, true);
        $this->resources_manager->removeResourcesForSkill($sktr_id, true);
        $this->event_handler->raise("components/ILIAS/Skill", "deleteSkill", ["node_id" => $sktr_id, "is_reference" => true]);
    }

    protected function deleteSkillTemplate(int $sktp_id): void
    {
        $this->level_repo->deleteLevelsOfSkill($sktp_id);
        $this->user_level_repo->deleteUserLevelsOfSkill($sktp_id);
        $this->usage_manager->removeUsagesForSkill($sktp_id);
        $this->material_manager->removeAssignedMaterialsForSkill($sktp_id);
        $this->profile_manager->deleteProfileLevelsForSkill($sktp_id);
        $this->resources_manager->removeResourcesForSkill($sktp_id);
        $this->event_handler->raise("components/ILIAS/Skill", "deleteSkill", ["node_id" => $sktp_id, "is_reference" => false]);

        foreach (\ilSkillTemplateReference::_lookupTrefIdsForTopTemplateId($sktp_id) as $tref_id) {
            $this->deleteNode($tref_id);
        }
    }

    protected function deleteSkillCategoryTemplate(int $sctp_id, \ilSkillTree $tree): void
    {
        $childs = $tree->getChildsByTypeFilter(
            $sctp_id,
            ["sktp", "sctp"]
        );
        foreach ($childs as $node) {
            $this->deleteNode((int) $node["obj_id"], $tree);
        }

        foreach (\ilSkillTemplateReference::_lookupTrefIdsForTopTemplateId($sctp_id) as $tref_id) {
            $this->deleteNode($tref_id);
        }
    }

    public function updateProfileCompletions(\ilSkillTree $tree): void
    {
        $tree_profiles = $this->profile_manager->getProfilesForSkillTree($tree->getTreeId());
        foreach ($tree_profiles as $profile) {
            $this->profile_completion_manager->writeCompletionEntryForAllAssignedUsersOfProfile($profile->getId());
        }
    }
}
