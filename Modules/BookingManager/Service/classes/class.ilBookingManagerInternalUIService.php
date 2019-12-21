<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Internal UI
 *
 * @author killing@leifos.de
 */
class ilBookingManagerInternalUIService
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @param ilObjBookingPool $pool
     * @return ilBookingPreferencesGUI
     */
    public function getPreferencesGUI(ilObjBookingPool $pool)
    {
        return new ilBookingPreferencesGUI($pool);
    }
}
