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
 * Skill profile
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class SkillRoleProfile extends SkillProfile
{
    protected int $role_id = 0;

    public function __construct(
        int $role_id,
        int $profile_id,
        string $title,
        string $description,
        int $skill_tree_id,
        string $image_id,
        int $ref_id
    ) {
        global $DIC;

        parent::__construct($profile_id, $title, $description, $skill_tree_id, $image_id, $ref_id);
        $this->role_id = $role_id;
    }

    public function getRoleId(): int
    {
        return $this->role_id;
    }
}
