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

namespace ILIAS\Help\GuidedTour\UserFinished;

use ilDBInterface;

class UserFinishedDBRepository
{
    public function __construct(
        protected ilDBInterface $db
    ) {
    }

    public function setFinished(int $tour_id, int $user_id): void
    {
        $this->db->replace("help_gt_user_finished", [
            "tour_id" => ["integer", $tour_id],
            "user_id" => ["integer", $user_id],
        ], []);
    }

    public function hasFinished(int $tour_id, int $user_id): bool
    {
        $set = $this->db->queryF(
            "SELECT * FROM help_gt_user_finished " .
            " WHERE tour_id = %s AND user_id = %s",
            ["integer", "integer"],
            [$tour_id, $user_id]
        );
        if ($rec = $this->db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    public function resetTour(int $tour_id): void
    {
        $this->db->manipulateF(
            "DELETE FROM help_gt_user_finished WHERE " .
            "tour_id = %s",
            ["integer"],
            [$tour_id]
        );
    }

    public function deleteByUser(int $user_id): void
    {
        $this->db->manipulateF(
            "DELETE FROM help_gt_user_finished WHERE " .
            "user_id = %s",
            ["integer"],
            [$user_id]
        );
    }
}
