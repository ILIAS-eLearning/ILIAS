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

namespace ILIAS\BookingManager;

use ilBookingPreferences;
use ilBookingPreferencesFactory;
use ILIAS\BookingManager\BookingProcess\WeekGridEntry;
use ILIAS\BookingManager\Settings\Settings;

/**
 * Repository internal data service
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDataService
{
    protected ilBookingPreferencesFactory $preferences_factory;

    public function __construct()
    {
        $this->preferences_factory = new ilBookingPreferencesFactory();
        //$this->..._factory = new ...\DataFactory();
    }

    public function preferences(array $preferences): ilBookingPreferences
    {
        return $this->preferences_factory->preferences($preferences);
    }

    public function weekEntry(
        int $start,
        int $end,
        string $html
    ): WeekGridEntry {
        return new WeekGridEntry(
            $start,
            $end,
            $html
        );
    }

    public function settings(
        int $id,
        bool $public_log,
        int $schedule_type,
        int $overall_limit = 0,
        int $reservation_period = 0,
        bool $reminder_status = false,
        int $reminder_day = 1,
        int $pref_deadline = 0,
        int $preference_nr = 0,
        bool $messages = false
    ): Settings {
        return new Settings(
            $id,
            $public_log,
            $schedule_type,
            $overall_limit,
            $reservation_period,
            $reminder_status,
            $reminder_day,
            $pref_deadline,
            $preference_nr,
            $messages
        );
    }
}
