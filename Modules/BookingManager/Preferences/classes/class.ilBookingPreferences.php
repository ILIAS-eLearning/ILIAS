<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Booking preferences. Reflects the booking preferences of one
 * booking pool.
 *
 * (data object)
 * @author killing@leifos.de
 */
class ilBookingPreferences
{
    /**
     * keys are user ids, values is an array of int, where every value represents a booking object id
     *
     * @var int[][]
     */
    protected $preferences;

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
                    if (!is_array($this->preferences[$user_id]) || !in_array($obj_id, $this->preferences[$user_id])) {
                        $this->preferences[$user_id][] = $obj_id;
                    }
                }
            }
        }
    }

    /**
     * Get user preferences
     *
     * @return array
     */
    public function getPreferences()
    {
        return $this->preferences;
    }



}