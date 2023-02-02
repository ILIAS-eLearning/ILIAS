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

namespace ILIAS\Skill\Personal;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class AssignedMaterialManager implements \ilSkillUsageInfo
{
    protected AssignedMaterialDBRepository $ass_mat_repo;
    protected PersonalSkillDBRepository $personal_repo;

    public function __construct(
        AssignedMaterialDBRepository $ass_mat_repo = null,
        PersonalSkillDBRepository $personal_repo = null
    ) {
        global $DIC;

        $this->ass_mat_repo = ($ass_mat_repo) ?: $DIC->skills()->internal()->repo()->getAssignedMaterialRepo();
        $this->personal_repo = ($personal_repo) ?: $DIC->skills()->internal()->repo()->getPersonalSkillRepo();
    }

    /**
     * Assign material to skill level
     *
     * @param int $user_id user id
     * @param int $top_skill_id the "selectable" top skill
     * @param int $tref_id template reference id
     * @param int $basic_skill_id the basic skill the level belongs to
     * @param int $level_id level id
     * @param int $wsp_id workspace object
     */
    public function assignMaterial(
        int $user_id,
        int $top_skill_id,
        int $tref_id,
        int $basic_skill_id,
        int $level_id,
        int $wsp_id
    ): void {
        $this->ass_mat_repo->assign($user_id, $top_skill_id, $tref_id, $basic_skill_id, $level_id, $wsp_id);
    }

    /**
     * Get assigned materials (for a skill level and user)
     * @return AssignedMaterial[]
     */
    public function getAssignedMaterials(int $user_id, int $tref_id, int $level_id): array
    {
        return $this->ass_mat_repo->get($user_id, $tref_id, $level_id);
    }

    /**
     * Count assigned materials (for a skill level and user)
     */
    public function countAssignedMaterials(int $user_id, int $tref_id, int $level_id): int
    {
        return $this->ass_mat_repo->count($user_id, $tref_id, $level_id);
    }

    public function removeAssignedMaterial(int $user_id, int $tref_id, int $level_id, int $wsp_id): void
    {
        $this->ass_mat_repo->remove($user_id, $tref_id, $level_id, $wsp_id);
    }

    public function removeAssignedMaterials(int $user_id): void
    {
        $this->ass_mat_repo->removeAll($user_id);
    }

    /**
     * @param array{skill_id: int, tref_id: int}[] $cskill_ids
     *
     * @return array<string, array<string, array{key: string}[]>>
     */
    public static function getUsageInfo(array $cskill_ids): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $personal_repo = $DIC->skills()->internal()->repo()->getPersonalSkillRepo();

        // material
        $usages = \ilSkillUsage::getUsageInfoGeneric(
            $cskill_ids,
            \ilSkillUsage::USER_MATERIAL,
            "skl_assigned_material",
            "user_id"
        );

        // users that use the skills as personal skills
        $pskill_ids = [];
        $tref_ids = [];
        foreach ($cskill_ids as $cs) {
            $cs["tref_id"] = (int) $cs["tref_id"];
            $cs["skill_id"] = (int) $cs["skill_id"];
            if ($cs["tref_id"] > 0) {
                if (\ilSkillTemplateReference::_lookupTemplateId($cs["tref_id"]) === $cs["skill_id"]) {
                    $pskill_ids[$cs["tref_id"]] = $cs["tref_id"];
                    $tref_ids[$cs["tref_id"]] = $cs["skill_id"];
                }
            } else {
                $pskill_ids[$cs["skill_id"]] = $cs["skill_id"];
            }
        }
        $usages = $personal_repo->getUsages($usages, $pskill_ids, $tref_ids);

        return $usages;
    }
}
