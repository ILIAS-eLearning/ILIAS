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
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillResource
{
    protected int $skill_id = 0;
    protected int $tref_id = 0;
    protected int $level_id = 0;
    protected int $rep_ref_id = 0;
    protected bool $imparting = false;
    protected bool $trigger = false;

    public function __construct(int $skill_id, int $tref_id, int $level_id, int $rep_ref_id, bool $imparting, bool $trigger)
    {
        $this->skill_id = $skill_id;
        $this->tref_id = $tref_id;
        $this->level_id = $level_id;
        $this->rep_ref_id = $rep_ref_id;
        $this->imparting = $imparting;
        $this->trigger = $trigger;
    }

    public function getBaseSkillId(): int
    {
        return $this->skill_id;
    }

    public function getTrefId(): int
    {
        return $this->tref_id;
    }

    /**
     * Skill level id
     */
    public function getLevelId(): int
    {
        return $this->level_id;
    }

    /**
     * Ref id of the repository resource
     */
    public function getRepoRefId(): int
    {
        return $this->rep_ref_id;
    }

    /**
     * True, if the resource triggers the skill level (false otherwise)
     */
    public function getImparting(): bool
    {
        return $this->imparting;
    }

    /**
     * True, if the resource imparts knowledge of the skill level (false otherwise)
     */
    public function getTrigger(): bool
    {
        return $this->trigger;
    }
}
