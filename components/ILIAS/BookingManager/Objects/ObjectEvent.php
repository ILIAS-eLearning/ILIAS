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

namespace ILIAS\BookingManager\Objects;

use ilBookingObject;
use ilObjBookingPool;
use ilObject;

class ObjectEvent
{
    public function handleDeletion(array $booking_pool_ref_ids): void
    {
        foreach (array_unique($booking_pool_ref_ids) as $booking_pool_ref_id) {
            if (!is_numeric($booking_pool_ref_id)) {
                continue;
            }
            $booking_pool_ref_id = (int) $booking_pool_ref_id;

            if (!ilObject::_exists($booking_pool_ref_id, true, 'book')) {
                continue;
            }

            $booking_pool = new ilObjBookingPool($booking_pool_ref_id, true);

            foreach (ilBookingObject::getList($booking_pool->getId()) as $booking_object) {
                $booking_object_id = $booking_object['booking_object_id'] ?? null;
                if ($booking_object_id === null) {
                    continue;
                }

                (new ilBookingObject($booking_object_id))->deleteReservationsAndCalEntries($booking_object_id);
            }
        }
    }
}
