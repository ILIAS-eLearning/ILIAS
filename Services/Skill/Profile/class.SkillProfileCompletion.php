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
class SkillProfileCompletion
{
    protected int $profile_id = 0;
    protected int $user_id = 0;
    protected string $date = "";
    protected bool $fulfilled = false;

    public function __construct(
        int $profile_id,
        int $user_id,
        string $date,
        bool $fulfilled
    ) {
        $this->profile_id = $profile_id;
        $this->user_id = $user_id;
        $this->date = $date;
        $this->fulfilled = $fulfilled;
    }

    public function getProfileId(): int
    {
        return $this->profile_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getFulfilled(): bool
    {
        return $this->fulfilled;
    }
}
