<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Survey\Settings;

/**
 * Survey settings factory
 *
 * @author killing@leifos.de
 */
class SettingsFactory
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }
    
    /**
     * Access settings
     *
     * @param int $start_date
     * @param int $end_date
     * @param bool $access_by_codes
     * @return AccessSettings
     */
    public function accessSettings(
        int $start_date,
        int $end_date,
        bool $access_by_codes
    ) {
        return new AccessSettings(
            $start_date,
            $end_date,
            $access_by_codes
        );
    }
}
