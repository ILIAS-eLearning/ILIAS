<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Domain business logic
 *
 * @author killing@leifos.de
 */
class ilBookingManagerInternalDomainService
{

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Booking preferences
     *
     * @param ilObjBookingPool $pool
     * @param ilBookingPrefBasedBookGatewayRepository|null $book_repo
     * @return ilBookingPreferencesManager
     */
    public function preferences(ilObjBookingPool $pool, ilBookingPrefBasedBookGatewayRepository $book_repo = null)
    {
        if (!$book_repo) {
            $book_repo = new ilBookingPrefBasedBookGatewayRepository();
        }

        return new ilBookingPreferencesManager($pool, $book_repo);
    }
}
