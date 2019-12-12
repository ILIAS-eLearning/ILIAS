<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Booking manager internal service
 *
 * @author killing@leifos.de
 */
class ilBookingManagerInternalService
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Booking service ui
     *
     * @return ilBookingManagerInternalUIService
     */
    public function ui()
    {
        return new ilBookingManagerInternalUIService();
    }

    /**
     * Booking service repos
     *
     * @return ilBookingManagerInternalRepoService
     */
    public function repo()
    {
        return new ilBookingManagerInternalRepoService($this->data());
    }

    /**
     * Booking service data objects
     * @return ilBookingManagerInternalDataService
     */
    public function data()
    {
        return new ilBookingManagerInternalDataService();
    }

    /**
     * @return ilBookingManagerInternalDomainService
     */
    public function domain()
    {
        return new ilBookingManagerInternalDomainService();
    }
}
