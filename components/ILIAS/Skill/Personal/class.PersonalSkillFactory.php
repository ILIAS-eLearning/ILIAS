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
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class PersonalSkillFactory
{
    public function selectedUserSkill(
        int $skill_node_id,
        string $title
    ): SelectedUserSkill {
        return new SelectedUserSkill(
            $skill_node_id,
            $title
        );
    }

    public function assignedMaterial(
        int $user_id,
        int $top_skill_id,
        int $skill_id,
        int $level_id,
        int $wsp_id,
        int $tref_id
    ): AssignedMaterial {
        return new AssignedMaterial(
            $user_id,
            $top_skill_id,
            $skill_id,
            $level_id,
            $wsp_id,
            $tref_id
        );
    }
}
