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

/**
 * Booking preferences. Reflects the booking preferences of one
 * booking pool.
 * (data object)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingPreferences
{
    /**
     * keys are user ids, values is an array of int, where every value represents a booking object id
     * @var int[][]
     */
    protected array $preferences;

    /**
     * Constructor
     * @param int[][] $preferences
     */
    public function __construct(array $preferences)
    {
        $this->preferences = [];
        foreach ($preferences as $user_id => $obj_ids) {
            if ($user_id > 0 && is_array($obj_ids)) {
                foreach ($obj_ids as $obj_id) {
                    if (!isset($this->preferences[$user_id]) || !in_array($obj_id, $this->preferences[$user_id])) {
                        $this->preferences[$user_id][] = $obj_id;
                    }
                }
            }
        }
    }

    /**
     * Get user preferences
     * @return int[][]
     */
    public function getPreferences(): array
    {
        return $this->preferences;
    }
}
