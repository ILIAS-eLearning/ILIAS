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

namespace ILIAS\Skill\Resource;

/**
 * Manages resources for skills. This is not about user assigned materials,
 * it is about resources that are assigned to skill levels in the
 * competence management administration of ILIAS.
 *
 * This can be either triggers (e.g. a course that triggers a competence level)
 * or resources that impart the knowledge of a competence level. Imparting
 * does not necessarily mean that it triggers a competence level.
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillResourcesManager implements \ilSkillUsageInfo
{
    protected SkillResourceDBRepository $skill_res_repo;
    protected \ilSkillLevelRepository $level_repo;

    public function __construct(
        SkillResourceDBRepository $skill_res_repo = null,
        \ilSkillLevelRepository $a_level_repo = null
    ) {
        global $DIC;

        $this->skill_res_repo = ($skill_res_repo)
            ?: $DIC->skills()->internal()->repo()->getResourceRepo();
        $this->level_repo = ($a_level_repo)
            ?: $DIC->skills()->internal()->repo()->getLevelRepo();
    }

    /**
     * @return array<int, SkillResource[]>
     */
    public function getResources(int $skill_id, int $tref_id): array
    {
        return $this->skill_res_repo->getAll($skill_id, $tref_id);
    }

    /**
     * @return SkillResource[]
     */
    public function getResourcesOfLevel(int $skill_id, int $tref_id, int $level_id): array
    {
        $all_resources = $this->skill_res_repo->getAll($skill_id, $tref_id);
        $resources_of_level = [];
        foreach ($all_resources as $lid => $level) {
            foreach ($level as $resource) {
                if ($lid == $level_id) {
                    $resources_of_level[] = $resource;
                }
            }
        }

        return $resources_of_level;
    }

    public function setResource(
        int $skill_id,
        int $tref_id,
        int $level_id,
        int $rep_ref_id,
        bool $imparting,
        bool $trigger
    ): void {
        $this->skill_res_repo->addOrUpdate($skill_id, $tref_id, $level_id, $rep_ref_id, $imparting, $trigger);
    }

    public function removeResource(
        int $skill_id,
        int $tref_id,
        int $level_id,
        int $rep_ref_id
    ): void {
        $this->skill_res_repo->remove($skill_id, $tref_id, $level_id, $rep_ref_id);
    }

    public function isLevelTooLow(int $tref_id, array $skill_levels, array $profile_levels, array $actual_levels): bool
    {
        $too_low = true;

        foreach ($skill_levels as $v) {
            $v["id"] = (int) $v["id"];
            $v["skill_id"] = (int) $v["skill_id"];
            foreach ($profile_levels as $pl) {
                if ($pl->getLevelId() === $v["id"] &&
                    $pl->getBaseSkillId() === $v["skill_id"]) {
                    $too_low = true;
                }
            }

            if ($actual_levels[$v["skill_id"]][$tref_id] == $v["id"]) {
                $too_low = false;
            }
        }
        return $too_low;
    }

    /**
     * @param \ILIAS\Skill\Profile\SkillProfileLevel[] $profile_levels
     */
    public function determineCurrentTargetLevel(array $skill_levels, array $profile_levels): int
    {
        $target_level = 0;
        foreach ($skill_levels as $l) {
            $l["id"] = (int) $l["id"];
            $l["skill_id"] = (int) $l["skill_id"];
            foreach ($profile_levels as $pl) {
                if ($pl->getLevelId() === $l["id"] &&
                    $pl->getBaseSkillId() === $l["skill_id"]) {
                    $target_level = $l["id"];
                }
            }
        }

        return $target_level;
    }

    /**
     * @param \ILIAS\Skill\Profile\SkillProfileLevel[] $profile_levels
     * @return SkillResource[]
     */
    public function getSuggestedResources(int $skill_id, int $tref_id, array $skill_levels, array $profile_levels): array
    {
        $target_level = $this->determineCurrentTargetLevel($skill_levels, $profile_levels);
        $target_level_order_nr = $this->level_repo->lookupLevelNumber($target_level);
        $resources = $this->getResources($skill_id, $tref_id);
        $imp_resources = [];
        foreach ($resources as $level) {
            foreach ($level as $r) {
                $res_level_order_nr = $this->level_repo->lookupLevelNumber($r->getLevelId());
                if ($r->getImparting() &&
                    $target_level_order_nr >= $res_level_order_nr) {
                    $imp_resources[$res_level_order_nr . "_" . $r->getLevelId()][] = $r;
                }
            }
        }

        ksort($imp_resources);
        return $imp_resources;
    }

    /**
     * @return SkillResourceLevel[]
     */
    public function getTriggerLevelsForRefId(int $rep_ref_id): array
    {
        return $this->skill_res_repo->getTriggerLevelsForRefId($rep_ref_id);
    }

    /**
     * @param array{skill_id: int, tref_id: int}[] $a_cskill_ids array of common skill ids
     *
     * @return array<string, array<string, array{key: string}[]>>
     */
    public static function getUsageInfo(array $a_cskill_ids): array
    {
        return \ilSkillUsage::getUsageInfoGeneric(
            $a_cskill_ids,
            \ilSkillUsage::RESOURCE,
            "skl_skill_resource",
            "rep_ref_id",
            "base_skill_id"
        );
    }
}
