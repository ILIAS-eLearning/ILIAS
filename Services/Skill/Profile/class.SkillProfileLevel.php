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
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillProfileLevel
{
    protected int $profile_id = 0;
    protected int $base_skill_id = 0;
    protected int $tref_id = 0;
    protected int $level_id = 0;
    protected int $order_nr = 0;

    public function __construct(
        int $profile_id,
        int $base_skill_id,
        int $tref_id,
        int $level_id,
        int $order_nr
    ) {
        $this->profile_id = $profile_id;
        $this->base_skill_id = $base_skill_id;
        $this->tref_id = $tref_id;
        $this->level_id = $level_id;
        $this->order_nr = $order_nr;
    }

    public function getProfileId(): int
    {
        return $this->profile_id;
    }

    public function getBaseSkillId(): int
    {
        return $this->base_skill_id;
    }

    public function getTrefId(): int
    {
        return $this->tref_id;
    }

    public function getLevelId(): int
    {
        return $this->level_id;
    }

    public function getOrderNr(): int
    {
        return $this->order_nr;
    }
}
