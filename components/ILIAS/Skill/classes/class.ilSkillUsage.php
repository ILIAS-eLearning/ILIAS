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

class ilSkillUsage
{
    public static function setUsage(int $obj_id, int $skill_id, int $tref_id, bool $use = true): void
    {
        global $DIC;

        $manager = $DIC->skills()->internal()->manager()->getUsageManager();

        if ($use) {
            $manager->addUsage($obj_id, $skill_id, $tref_id);
        } else {
            $manager->removeUsage($obj_id, $skill_id, $tref_id);
        }
    }
}
