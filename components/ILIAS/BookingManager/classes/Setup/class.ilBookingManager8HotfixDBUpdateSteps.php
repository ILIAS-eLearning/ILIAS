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

namespace ILIAS\BookingManager\Setup;

class ilBookingManager8HotfixDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $db = $this->db;
        $set1 = $db->queryF(
            "SELECT * FROM booking_object " .
            " WHERE schedule_id = %s ",
            ["integer"],
            [1]
        );
        while ($rec1 = $db->fetchAssoc($set1)) {
            $set2 = $db->queryF(
                "SELECT * FROM booking_schedule " .
                " WHERE pool_id = %s ORDER BY booking_schedule_id ASC LIMIT 1",
                ["integer"],
                [$rec1["pool_id"]]
            );
            if ($rec2 = $db->fetchAssoc($set2)) {
                if ((int) $rec2["booking_schedule_id"] !== 1) {
                    $db->update(
                        "booking_object",
                        [
                        "schedule_id" => ["intger", $rec2["booking_schedule_id"]]
                    ],
                        [    // where
                            "booking_object_id" => ["integer", $rec1["booking_object_id"]]
                        ]
                    );
                }
            }
        }
    }
}
