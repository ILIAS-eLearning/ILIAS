<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for booking preference data objects
 *
 * @author killing@leifos.de
 */
class ilBookingPreferencesFactory
{
    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * @param array $preferences
     * @return ilBookingPreferences
     */
    public function preferences(array $preferences) {
        return new ilBookingPreferences($preferences);
    }

}