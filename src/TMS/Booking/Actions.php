<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Booking;

/**
 * This encapsulates the basic steps that need to be done to get a valid
 * booking for a user on a course.
 */
interface Actions {
	const STATE_BOOKED = "booked";
	const STATE_WAITING_LIST = "waiting_list";
	const STATE_REMOVED_FROM_COURSE = "removed_course";
	const STATE_REMOVED_FROM_WAITINGLIST = "removed_waitinglist";

	/**
	 * Book the given user on the course. 
	 *
	 * @param	int		$crs_ref_id
	 * @param	int		$user_id
	 *
	 * @throws \LogicException 	if user can't be booked to course or waitinglist
	 *
	 * @return	mixed	one of the STATEs
	 */
	public function bookUser($crs_ref_id, $user_id);

	/**
	 * Removes a user from course or waiting list
	 *
	 * @param	int		$crs_ref_id
	 * @param	int		$user_id
	 *
	 * @throws \LogicException 	if user can't be canceld from course or waitinglist
	 *
	 * @return mixed	one of the STATEs
	 */
	public function cancelUser($crs_ref_id, $user_id);
}

