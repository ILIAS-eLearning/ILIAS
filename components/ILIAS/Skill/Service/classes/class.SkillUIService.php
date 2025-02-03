<?php

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
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Skill\Service;

use ILIAS\Skill\Usage\UsagesUI;

/**
 * Skill UI service
 * @author famula@leifos.de
 */
class SkillUIService
{
    public function __construct()
    {
    }

    public function getGapUI(): void //int $user_id, int $profile_id,...
    {
    }

    public function getUsagesUI(string $cskill_id, array $usage, string $mode = ""): UsagesUI
    {
        return new UsagesUI($cskill_id, $usage, $mode);
    }
}
