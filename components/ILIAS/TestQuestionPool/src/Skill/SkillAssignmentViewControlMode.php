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

enum SkillAssignmentViewControlMode: string
{
    case ALL = 'all';
    case ASSIGNED = 'assigned';
    case UNASSIGNED = 'unassigned';

    public function getLabel(): string
    {
        return match($this) {
            self::ALL => 'qpl_skl_all_questions',
            self::ASSIGNED => 'qpl_skl_assigned_questions',
            self::UNASSIGNED => 'qpl_skl_unassigned_questions',
        };
    }
}
