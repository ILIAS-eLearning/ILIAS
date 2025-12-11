<?php

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
             WHERE booking_reservation.user_id = %s AND booking_object.pool_id = %s;",
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$user_id, $pool_id]
        );

        $this->database->manipulateF(
            "DELETE FROM booking_member WHERE user_id = %s AND booking_pool_id = %s;",
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$user_id, $pool_id]
        );

        return true;
    }

}
