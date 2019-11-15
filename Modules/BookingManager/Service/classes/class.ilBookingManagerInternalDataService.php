<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author killing@leifos.de
 */
class ilBookingManagerInternalDataService
{
    protected $preferences_factory;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->preferences_factory = new ilBookingPreferencesFactory();
    }

    /**
     * @return ilBookingPreferencesFactory
     */
    public function preferencesFactory() {
        return new ilBookingPreferencesFactory();
    }

}