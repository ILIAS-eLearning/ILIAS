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

namespace ILIAS\Skill\Profile;

/**
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillProfileFactory
{
    public function profile(
        int $id,
        string $title,
        string $description,
        int $skill_tree_id,
        string $image_id = "",
        int $ref_id = 0
    ): SkillProfile {
        return new SkillProfile(
            $id,
            $title,
            $description,
            $skill_tree_id,
            $image_id,
            $ref_id
        );
    }

    public function profileLevel(
        int $profile_id,
        int $base_skill_id,
        int $tref_id,
        int $level_id,
        int $order_nr
    ): SkillProfileLevel {
        return new SkillProfileLevel(
            $profile_id,
            $base_skill_id,
            $tref_id,
            $level_id,
            $order_nr
        );
    }

    public function profileCompletion(
        int $profile_id,
        int $user_id,
        string $date,
        bool $fulfilled
    ): SkillProfileCompletion {
        return new SkillProfileCompletion(
            $profile_id,
            $user_id,
            $date,
            $fulfilled
        );
    }

    public function profileUserAssignment(
        string $name,
        int $id
    ): SkillProfileUserAssignment {
        return new SkillProfileUserAssignment(
            $name,
            $id
        );
    }

    public function profileRoleAssignment(
        string $name,
        int $id,
        string $obj_title,
        string $obj_type,
        int $obj_id
    ): SkillProfileRoleAssignment {
        return new SkillProfileRoleAssignment(
            $name,
            $id,
            $obj_title,
            $obj_type,
            $obj_id
        );
    }
}
