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

	const EVENT_USER_BOOKED_COURSE = 'user_booked_self_on_course'; //B01
	const EVENT_USER_BOOKED_WAITING = 'user_booked_self_on_waiting'; //B02
	const EVENT_SUPERIOR_BOOKED_COURSE = 'superior_booked_user_on_course'; //B03
	const EVENT_SUPERIOR_BOOKED_WAITING = 'superior_booked_user_on_waiting'; //B04
	const EVENT_ADMIN_BOOKED_COURSE = 'admin_booked_user_on_course'; //B05
	const EVENT_ADMIN_BOOKED_WAITING = 'admin_booked_user_on_waiting'; //B06
	const EVENT_USER_FILLEDUP_FROM_WAITING = 'user_booked_on_course_by_waiting'; //B07

	const EVENT_USER_CANCELED_COURSE = 'user_canceled_self_from_course'; //C01
	const EVENT_USER_CANCELED_WAITING = 'user_canceled_self_from_waiting';//C02
	const EVENT_SUPERIOR_CANCELED_COURSE = 'superior_canceled_user_from_course'; //C03
	const EVENT_SUPERIOR_CANCELED_WAITING = 'superior_canceled_user_from_waiting'; //C04
	const EVENT_ADMIN_CANCELED_COURSE = 'admin_canceled_user_from_course'; //C05
	const EVENT_ADMIN_CANCELED_WAITING = 'admin_canceled_user_from_waiting'; //C06




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
	 * @throws \LogicException 	if user can't be canceled from course or waitinglist
	 *
	 * @return mixed	one of the STATEs
	 */
	public function cancelUser($crs_ref_id, $user_id);
}

