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

namespace ILIAS\Exercise\IndividualDeadline;

class IndividualDeadlineManager
{
    public function __construct()
    {
    }

    public function deleteAllOfUserInExercise(int $exc_id, int $part_or_team_id, bool $is_team = false): void
    {
        foreach (\ilExAssignment::getInstancesByExercise($exc_id) as $ass) {
            $idl = \ilExcIndividualDeadline::getInstance(
                $ass->getId(),
                $part_or_team_id,
                $is_team
            );
            $idl->delete();
        }
    }
}
