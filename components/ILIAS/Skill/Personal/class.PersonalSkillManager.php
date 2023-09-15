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
class PersonalSkillManager
{
    protected PersonalSkillDBRepository $personal_repo;

    public function __construct(
        \ILIAS\Skill\Personal\PersonalSkillDBRepository $personal_repo = null
    ) {
        global $DIC;

        $this->personal_repo = ($personal_repo) ?: $DIC->skills()->internal()->repo()->getPersonalSkillRepo();
    }

    /**
     * @return array<int, SelectedUserSkill>
     */
    public function getSelectedUserSkills(int $user_id): array
    {
        return $this->personal_repo->get($user_id);
    }

    public function addPersonalSkill(int $user_id, int $skill_node_id): void
    {
        $this->personal_repo->add($user_id, $skill_node_id);
    }

    public function removePersonalSkill(int $user_id, int $skill_node_id): void
    {
        $this->personal_repo->remove($user_id, $skill_node_id);
    }

    public function removePersonalSkills(int $user_id): void
    {
        $this->personal_repo->removeAll($user_id);
    }
}
