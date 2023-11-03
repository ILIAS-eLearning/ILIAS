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
class AssignedMaterial
{
    protected int $user_id = 0;
    protected int $top_skill_id = 0;
    protected int $skill_id = 0;
    protected int $level_id = 0;
    protected int $wsp_id = 0;
    protected int $tref_id = 0;

    public function __construct(
        int $user_id,
        int $top_skill_id,
        int $skill_id,
        int $level_id,
        int $wsp_id,
        int $tref_id
    ) {
        $this->user_id = $user_id;
        $this->top_skill_id = $top_skill_id;
        $this->skill_id = $skill_id;
        $this->level_id = $level_id;
        $this->wsp_id = $wsp_id;
        $this->tref_id = $tref_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getTopSkillId(): int
    {
        return $this->top_skill_id;
    }

    public function getSkillId(): int
    {
        return $this->skill_id;
    }

    public function getLevelId(): int
    {
        return $this->level_id;
    }

    public function getWorkspaceId(): int
    {
        return $this->wsp_id;
    }

    public function getTrefId(): int
    {
        return $this->tref_id;
    }
}
