<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Booking manager service
 *
 * @author killing@leifos.de
 */
class ilBookingManagerService
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Internal service, do not use in other components
     *
     * @return ilBookingManagerInternalService
     */
    public function internal()
    {
        return new ilBookingManagerInternalService();
    }
}
