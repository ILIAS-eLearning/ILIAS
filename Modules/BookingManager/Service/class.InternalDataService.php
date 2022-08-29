<?php

declare(strict_types=1);

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

namespace ILIAS\BookingManager;

use ilBookingPreferences;
use ilBookingPreferencesFactory;

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
}
