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

/**
 * Automatic booking of overdue pools using booking by preference
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingPrefAutoBooking
{
    protected \ILIAS\BookingManager\InternalService $service;

    public function __construct()
    {
        global $DIC;
        $this->service = $DIC->bookingManager()->internal();
    }

    /**
     * @throws ilBookingCalculationException
     */
    public function run(): void
    {
        $service = $this->service;

        $pref_repo = $service->repo()->preferences();
        $book_repo = $service->repo()->preferenceBasedBooking();

        // for all pools with an overdue preference based booking
        foreach ($book_repo->getPoolsWithOverdueBooking() as $pool_id) {
            $pool = new ilObjBookingPool($pool_id, false);
            $manager = $service->domain()->preferences($pool);

            // get preferences and do the booking
            $preferences = $pref_repo->getPreferences($pool_id);
            $manager->storeBookings($preferences);
        }
    }
}
