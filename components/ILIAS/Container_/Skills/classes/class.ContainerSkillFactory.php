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

namespace ILIAS\Container\Skills;

use ILIAS\Skill\Profile\SkillProfile;

/**
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ContainerSkillFactory
{
    public function skill(
        int $cont_obj_id,
        int $skill_id,
        int $tref_id,
        string $title = "",
        SkillProfile $profile = null
    ): ContainerSkill {
        return new ContainerSkill(
            $cont_obj_id,
            $skill_id,
            $tref_id,
            $title,
            $profile
        );
    }

    public function memberSkill(
        int $cont_obj_id,
        int $user_id,
        int $skill_id,
        int $tref_id,
        int $level_id,
        bool $published
    ): ContainerMemberSkill {
        return new ContainerMemberSkill(
            $cont_obj_id,
            $user_id,
            $skill_id,
            $tref_id,
            $level_id,
            $published
        );
    }
}
