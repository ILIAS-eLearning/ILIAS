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
use ILIAS\Skill\GapAnalysisSkill;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ContainerSkill implements GapAnalysisSkill
{
    protected int $cont_obj_id = 0;
    protected int $skill_id = 0;
    protected int $tref_id = 0;
    protected string $title = "";
    protected ?SkillProfile $profile = null;

    public function __construct(
        int $cont_obj_id,
        int $skill_id,
        int $tref_id,
        string $title = "",
        SkillProfile $profile = null
    ) {
        $this->cont_obj_id = $cont_obj_id;
        $this->skill_id = $skill_id;
        $this->tref_id = $tref_id;
        $this->title = $title;
        $this->profile = $profile;
    }

    public function getContainerObjectId(): int
    {
        return $this->cont_obj_id;
    }

    public function getBaseSkillId(): int
    {
        return $this->skill_id;
    }

    public function getTrefId(): int
    {
        return $this->tref_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getProfile(): ?SkillProfile
    {
        return $this->profile;
    }
}
