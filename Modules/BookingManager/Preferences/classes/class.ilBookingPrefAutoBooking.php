<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Automatic booking of overdue pools using booking by preference
 *
 * @author killing@leifos.de
 */
class ilBookingPrefAutoBooking
{

    /**
     * @var ilBookingManagerInternalService
     */
    protected $service;

    /**
     * Constructor
     */
    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->service = $DIC->bookingManager()->internal();
    }

    /**
     * Run
     * @throws ilBookingCalculationException
     */
    public function run()
    {
        $service = $this->service;

        $pref_repo = $service->repo()->getPreferencesRepo();
        $book_repo = $service->repo()->getPreferenceBasedBookingRepo();

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
