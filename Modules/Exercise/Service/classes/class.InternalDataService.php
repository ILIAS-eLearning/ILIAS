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

namespace ILIAS\Exercise;

use ILIAS\Exercise\Assignment\Assignment;
use ILIAS\Exercise\IRSS\ResourceInformation;
use ILIAS\Exercise\Team\TeamMember;

/**
 * Internal factory for data objects
 */
class InternalDataService
{
    public function __construct()
    {
    }

    public function assignment(
        int $id,
        int $exc_id,
        string $title,
        int $order_nr,
        int $type,
        string $instructions,
        bool $mandatory,
        int $deadline_mode,
        int $deadline,
        int $deadline2,
        int $relative_deadline,
        int $rel_deadline_last_submission
    ): Assignment {
        return new Assignment(
            $id,
            $exc_id,
            $title,
            $order_nr,
            $type,
            $instructions,
            $mandatory,
            $deadline_mode,
            $deadline,
            $deadline2,
            $relative_deadline,
            $rel_deadline_last_submission
        );
    }

    public function resourceInformation(
        string $rid,
        string $title,
        int $size,
        int $creation_timestamp,
        string $mime_type,
        string $src
    ): ResourceInformation {
        return new ResourceInformation(
            $rid,
            $title,
            $size,
            $creation_timestamp,
            $mime_type,
            $src
        );
    }

    public function teamMember(
        int $team_id,
        int $assignment_id,
        int $user_id
    ): TeamMember {
        return new TeamMember(
            $team_id,
            $assignment_id,
            $user_id
        );
    }

}
