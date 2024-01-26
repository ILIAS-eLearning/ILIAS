<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Booking preferences business logic
 *
 * @author killing@leifos.de
 */
class ilBookingPreferencesManager
{
    const BOOKINGS_PER_USER_DEFAULT = 1;

    /**
     * @var ilObjBookingPool
     */
    protected $pool;

    /**
     * @var int|null
     */
    protected $current_time;

    /**
     * @var int
     */
    protected $bookings_per_user;

    /**
     * @var ilBookingPrefBasedBookGatewayRepository
     */
    protected $book_repo;

    /**
     * Constructor
     * @param ilObjBookingPool $pool
     * @param int|null $current_time
     */
    public function __construct(
        ilObjBookingPool $pool,
        ilBookingPrefBasedBookGatewayRepository $book_repo,
        int $current_time = null,
        $bookings_per_user = self::BOOKINGS_PER_USER_DEFAULT
    ) {
        $this->current_time = ($current_time > 0)
            ? $current_time
            : time();
        $this->pool = $pool;
        $this->bookings_per_user = $bookings_per_user;
        $this->book_repo = $book_repo;
    }

    /**
     * Can participants hand in preferences
     *
     * @return bool
     */
    public function isGivingPreferencesPossible()
    {
        if ($this->pool->getScheduleType() == ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES &&
            $this->pool->getPreferenceDeadline() > $this->current_time) {
            return true;
        }
        return false;
    }

    /**
     * Can participants hand in preferences
     *
     * @return bool
     */
    public function isPreferenceDeadlineReached()
    {
        if ($this->pool->getScheduleType() == ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES &&
            $this->pool->getPreferenceDeadline() < $this->current_time) {
            return true;
        }
        return false;
    }

    /**
     * Calculate and store bookings
     *
     * @param ilBookingPreferences $preferences
     * @param int[] $booking_object_ids
     * @throws ilBookingCalculationException
     */
    public function storeBookings($preferences, $booking_object_ids = null)
    {
        $bookings = $this->calculateBookings($preferences, $booking_object_ids);
        $this->book_repo->storeBookings($this->pool->getId(), $bookings);
    }

    /**
     * Read the bookings
     *
     * @return int[][]
     */
    public function readBookings()
    {
        $booking_object_ids = array_map(function ($i) {
            return $i["booking_object_id"];
        }, ilBookingObject::getList($this->pool->getId()));
        return $this->book_repo->getBookings($booking_object_ids);
    }


    /**
     * Calculate bookings
     * @param ilBookingPreferences $preferences
     * @param int[] $booking_object_ids
     * @return array
     * @throws ilBookingCalculationException
     */
    public function calculateBookings(
        ilBookingPreferences $preferences,
        $booking_object_ids = null,
        $availability = null
    ) {
        $preferences = $preferences->getPreferences();

        // we calculate if a) any preferences are given and b) the deadline is reached
        /*if (!is_array($preferences) || count($preferences) == 0) {
            throw new ilBookingCalculationException("No preferences given.");
        }*/
        if (!$this->isPreferenceDeadlineReached()) {
            throw new ilBookingCalculationException("Preference deadline not reached.");
        }

        if ($booking_object_ids == null) {
            $booking_object_ids = array_map(function ($i) {
                return $i["booking_object_id"];
            }, ilBookingObject::getList($this->pool->getId()));
        }

        if ($availability == null) {
            $availability = [];
            foreach ($booking_object_ids as $book_obj_id) {
                $availability[$book_obj_id] = ilBookingReservation::getNumAvailablesNoSchedule($book_obj_id);
            }
        }

        // remove all objects from the preferences
        // that are already not available anymore
        // see bug 30204 (a tutor booked an object already before and made it unavailable)
        foreach ($availability as $book_obj_id => $cnt) {
            if ($cnt == 0) {
                $preferences = $this->removeObjectFromPreferences($book_obj_id, $preferences);
            }
        }

        $bookings = [];

        $end_phase_one = false;

        // phase one: assign lowest popular items to random user
        while (!$end_phase_one) {
            $popularity = $this->calculatePopularity($booking_object_ids, $preferences);

            $low_pop_book_obj_id = $this->getObjectWithLowestPopularity($popularity, $availability);
            if ($low_pop_book_obj_id > 0) {
                $user_ids = $this->getUsersForObject($preferences, $low_pop_book_obj_id);
                if (count($user_ids) > 0) {
                    $user_id = $this->selectRandomEntry($user_ids);
                    $this->addBooking($bookings, $preferences, $availability, $user_id, $low_pop_book_obj_id);
                }
            } else {
                $end_phase_one = true;
            }
        }

        $end_phase_two = false;

        // choose random user from and assign currently rarely assigned objects
        while (!$end_phase_two) {
            $random_user_id = $this->chooseRandomUserFromPreferences($preferences);
            if ($random_user_id > 0) {
                $rare_assigned_book_obj_id = $this->getMinimalAssignedEntryForUser($booking_object_ids, $bookings, $preferences[$random_user_id], $availability);
                if ($rare_assigned_book_obj_id > 0) {
                    $this->addBooking($bookings, $preferences, $availability, $random_user_id, $rare_assigned_book_obj_id);
                } else {
                    $preferences = $this->removeUserFromPreferences($random_user_id, $preferences);
                }
            } else {
                $end_phase_two = true;
            }
        }
        return $bookings;
    }

    /**
     * Add booking
     *
     * @param $bookings
     * @param $preferences
     * @param $availability
     * @param $user_id
     * @param $book_obj_id
     */
    protected function addBooking(&$bookings, &$preferences, &$availability, $user_id, $book_obj_id)
    {
        $bookings[$user_id][] = $book_obj_id;
        $availability[$book_obj_id]--;
        if (count($bookings[$user_id]) >= $this->bookings_per_user) {
            $preferences = $this->removeUserFromPreferences($user_id, $preferences);
        } else {
            $preferences = $this->removePreference($user_id, $book_obj_id, $preferences);
        }
        if ($availability[$book_obj_id] <= 0) {
            $preferences = $this->removeObjectFromPreferences($book_obj_id, $preferences);
        }
    }

    
    /**
     * Select a random entry of an array
     *
     * @param
     * @return
     */
    protected function selectRandomEntry($items)
    {
        $nr = rand(0, count($items) - 1);
        return $items[$nr];
    }
    
    
    /**
     * Get users for object
     *
     * @param $preferences
     * @param $sel_obj_id
     * @return array
     */
    protected function getUsersForObject($preferences, $sel_obj_id)
    {
        $user_ids = [];
        foreach ($preferences as $user_id => $obj_ids) {
            foreach ($obj_ids as $obj_id) {
                if ($obj_id == $sel_obj_id) {
                    $user_ids[] = $user_id;
                }
            }
        }
        return $user_ids;
    }
    
    
    /**
     * Calculate popularity (number of preferences each object got from users)
     *
     * @param array $booking_object_ids
     * @param array $preferences
     * @return array
     */
    protected function calculatePopularity(array $booking_object_ids, array $preferences)
    {
        $popularity = [];
        foreach ($booking_object_ids as $book_obj_id) {
            $popularity[$book_obj_id] = 0;
        }
        foreach ($preferences as $user_id => $bobj_ids) {
            foreach ($bobj_ids as $bobj_id) {
                $popularity[$bobj_id] += 1;
            }
        }

        return $popularity;
    }


    /**
     * Get an availabe object with lowest popularity > 0
     *
     * @param array $popularity
     * @return int
     */
    protected function getObjectWithLowestPopularity($popularity, $availability)
    {
        asort($popularity, SORT_NUMERIC);
        foreach ($popularity as $obj_id => $pop) {
            if ($pop > 0 && $availability[$obj_id] > 0) {
                return (int) $obj_id;
            }
        }
        return 0;
    }

    /**
     * Remove a preference from the preference array
     *
     * @param int $user_id
     * @param int $obj_id
     * @param array $preferences
     * @return array
     */
    protected function removePreference($user_id, $obj_id, $preferences)
    {
        if (is_array($preferences[$user_id])) {
            $preferences[$user_id] = array_filter($preferences[$user_id], function ($i) use ($obj_id) {
                return ($i != $obj_id);
            });
        }
        return $preferences;
    }

    /**
     * Remove an object from the preference array
     *
     * @param int $user_id
     * @param int $obj_id
     * @param array $preferences
     * @return array
     */
    protected function removeObjectFromPreferences($obj_id, $preferences)
    {
        $new_preferences = [];
        foreach ($preferences as $user_id => $obj_ids) {
            $new_preferences[$user_id] = array_filter($preferences[$user_id], function ($i) use ($obj_id) {
                return ($i != $obj_id);
            });
        }
        return $new_preferences;
    }

    /**
     * Remove user from preference array
     *
     * @param int $user_id
     * @param array $preferences
     * @return array
     */
    protected function removeUserFromPreferences($user_id, $preferences)
    {
        if (is_array($preferences[$user_id])) {
            unset($preferences[$user_id]);
        }
        return $preferences;
    }

    /**
     * Choose random user from the preference array
     *
     * @param array $preferences
     * @return int
     */
    protected function chooseRandomUserFromPreferences($preferences)
    {
        $user_ids = array_keys($preferences);
        return $this->selectRandomEntry($user_ids);
    }

    /**
     * Get an available object within the preferences (if no preferences left, even outside of preferences)
     * of a user that is currently minimal assigned
     *
     * @param int[] $booking_object_ids
     * @param int[][] $bookings
     * @param int[] $user_preferences
     * @return int
     */
    protected function getMinimalAssignedEntryForUser($booking_object_ids, $bookings, $user_preferences, $availability)
    {
        // count the assignments per object
        $count_assignments = [];
        foreach ($booking_object_ids as $obj_id) {
            $count_assignments[$obj_id] = 0;
        }
        foreach ($bookings as $user => $obj_ids) {
            foreach ($obj_ids as $obj_id) {
                $count_assignments[$obj_id]++;
            }
        }

        // sort the objects by number of assignments, return the first one being found in the user preferences
        asort($count_assignments, SORT_NUMERIC);
        foreach ($count_assignments as $obj_id => $cnt) {
            // if no preferences left for user, even assign object outside preferences
            // otherwise choose object from preferences
            if ((count($user_preferences) == 0 || in_array($obj_id, $user_preferences))
                && $availability[$obj_id] > 0) {
                return (int) $obj_id;
            }
        }
        return 0;
    }

    public function hasRun() : bool
    {
        return $this->book_repo->hasRun($this->pool->getId());
    }

    public function resetRun() : void
    {
        $this->book_repo->resetRun($this->pool->getId());
    }
}
