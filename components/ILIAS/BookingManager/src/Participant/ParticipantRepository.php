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

namespace ILIAS\BookingManager\Participant;

use ilDBConstants;
use ilDBInterface;

class ParticipantRepository
{
    public function __construct(
        private readonly ilDBInterface $database
    ) {
    }

    public function delete(int $user_id, int $pool_id): true
    {
        $this->database->manipulateF(
            "DELETE booking_reservation FROM booking_reservation 
             INNER JOIN booking_object ON booking_object.booking_object_id = booking_reservation.object_id 
             WHERE booking_reservation.user_id = %s AND booking_object.pool_id = %s",
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$user_id, $pool_id]
        );

        $this->database->manipulateF(
            "DELETE FROM booking_member WHERE user_id = %s AND booking_pool_id = %s",
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$user_id, $pool_id]
        );

        return true;
    }
}
