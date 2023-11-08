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

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ContainerMemberSkill
{
    protected int $cont_obj_id = 0;
    protected int $user_id = 0;
    protected int $skill_id = 0;
    protected int $tref_id = 0;
    protected int $level_id = 0;
    protected bool $published = false;

    public function __construct(
        int $cont_obj_id,
        int $user_id,
        int $skill_id,
        int $tref_id,
        int $level_id,
        bool $published
    ) {
        $this->cont_obj_id = $cont_obj_id;
        $this->user_id = $user_id;
        $this->skill_id = $skill_id;
        $this->tref_id = $tref_id;
        $this->level_id = $level_id;
        $this->published = $published;
    }

    public function getContainerObjectId(): int
    {
        return $this->cont_obj_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getBaseSkillId(): int
    {
        return $this->skill_id;
    }

    public function getTrefId(): int
    {
        return $this->tref_id;
    }

    public function getLevelId(): int
    {
        return $this->level_id;
    }

    public function getPublished(): bool
    {
        return $this->published;
    }
}
