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

namespace ILIAS\Skill\Service;

use ILIAS\Skill\Personal;

/**
 * @author famula@leifos.de
 */
class SkillPersonalService
{
    protected Personal\PersonalSkillManager $personal_manager;
    protected Personal\AssignedMaterialManager $ass_mat_manager;
    protected Personal\SelfEvaluationManager $self_eval_manager;

    public function __construct(SkillInternalService $internal_service)
    {
        $this->personal_manager = $internal_service->manager()->getPersonalSkillManager();
        $this->ass_mat_manager = $internal_service->manager()->getAssignedMaterialManager();
        $this->self_eval_manager = $internal_service->manager()->getSelfEvaluationManager();
    }

    /**
     * @return array<int, Personal\SelectedUserSkill>
     */
    public function getSelectedUserSkills(int $user_id): array
    {
        return $this->personal_manager->getSelectedUserSkills($user_id);
    }

    public function addPersonalSkill(int $user_id, int $skill_node_id): void
    {
        $this->personal_manager->addPersonalSkill($user_id, $skill_node_id);
    }

    /**
     * Get assigned materials (for a skill level and user)
     * @return Personal\AssignedMaterial[]
     */
    public function getAssignedMaterials(int $user_id, int $tref_id, int $level_id): array
    {
        return $this->ass_mat_manager->getAssignedMaterials($user_id, $tref_id, $level_id);
    }
}
