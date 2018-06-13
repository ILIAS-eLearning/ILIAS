<?php

/**
 * cat-tms-patch start
 */


class ilBookableFilter {
	public function __construct() {

	}

	/**
	 * Is today in booking period of course
	 *
	 * @param ilDateTime 	$crs_start
	 * @param int 	$booking_start
	 * @param int 	$booking_end
	 *
	 * @return bool
	 */
	public function isInBookingPeriod(ilDateTime $crs_start, $booking_start, $booking_end) {
		$today_string = date("Y-m-d");

		$booking_start_date = clone $crs_start;
		$booking_start_date->increment(ilDateTime::DAY, -1 * $booking_start);
		$start_string = $booking_start_date->get(IL_CAL_DATE);

		$booking_end_date = clone $crs_start;
		$booking_end_date->increment(ilDateTime::DAY, -1 * $booking_end);
		$end_string = $booking_end_date->get(IL_CAL_DATE);

		if($today_string >= $start_string && $today_string <= $end_string) {
			return true;
		}

		return false;
	}

	/**
	 * Check course is booked up
	 *
	 * @param int 	$member_count
	 * @param int 	$max_member
	 * @param string 	$waiting_list
	 *
	 * @return bool
	 */
	public function courseBookedUp($member_count, $max_member, $waiting_list) {
		if($max_member && $max_member == $member_count && $waiting_list == "no_waitinglist") {
			return true;
		}

		return false;
	}

	/**
	 * Check course title starts with
	 *
	 * @param string 	$crs_title
	 * @param string 	$search_string
	 *
	 * @return bool
	 */
	public function crsTitleStartsWith($crs_title, $search_string)
	{
		$length = strlen($search_string);
		return (substr($crs_title, 0, $length) === $search_string);
	}

	/**
	 * Check minimum member not reached
	 *
	 * @param int 	$crs_ref_id
	 * @param int 	$min_member
	 *
	 * @return bool
	 */
	public function minMemberReached($crs_ref_id, $min_member) {
		require_once("Modules/Course/classes/class.ilCourseParticipants.php");
		$course_member = (int)ilCourseParticipants::lookupNumberOfParticipants($crs_ref_id);
		if($course_member >= $min_member) {
			return true;
		}

		return false;
	}

	/**
	 * Check provider is selected for course
	 *
	 * @param int 	$crs_id
	 * @param int 	$provider_id
	 *
	 * @return bool
	 */
	public function providerSelected($crs_id, $provider_id) {
		$vplug = ilPluginAdmin::getPluginObjectById('trainingprovider');
		$pactions = $vplug->getActions();
		$passignment = $pactions->getAssignment((int)$crs_id);
		$drop_by_filter = false;

		if($passignment) {
			if($passignment->isListAssignment()) {
				$provider_id = $passignment->getProviderId();
				if(array_key_exists(ilTrainingSearchGUI::F_PROVIDER, $filter) 
					&& $provider_id != (int)$filter[ilTrainingSearchGUI::F_PROVIDER]
				) {
					$drop_by_filter = true;
				}
			}
		}

		return array($drop_by_filter);
	}

	/**
	 * Check course start is in filter period
	 *
	 * @param ilDateTime 	$start_date
	 * @param string 	$filter_start
	 * @param string 	$filter_end
	 *
	 * @return bool
	 */
	public function courseInFilterPeriod(ilDateTime $start_date, $filter_start, $filter_end) {
		$filter_start = strftime("%Y-%m-%d", strtotime($filter_start));
		$filter_end = strftime("%Y-%m-%d", strtotime($filter_end));

		$start_date_check = $start_date->get(IL_CAL_DATE);
		if($start_date_check < $filter_start || $start_date_check > $filter_end) {
			return false;
		}

		return true;
	}

	/**
	 * Check course is expired
	 *
	 * @param ilDateTime 	$start_date
	 *
	 * @return bool
	 */
	public function courseIsExpired(ilDateTime $start_date) {
		$today = date("Y-m-d");
		$start_date->increment(ilDateTime::DAY, 1);

		if($today >= $start_date->get(IL_CAL_DATE)) {
			return true;
		}

		return false;
	}

	/**
	 * Checks crs has selected target group
	 *
	 * @param int[] 	$target_group_ids
	 * @param int 	$filter_target_group
	 *
	 * @return bool
	 */
	public function courseHasTargetGroups($target_group_ids, $filter_target_group) {
		return in_array($filter_target_group, $target_group_ids);
	}

	/**
	 * Checks crs has selected topic
	 *
	 * @param int[] 	$topic_ids
	 * @param int 	$filter_topic_id
	 *
	 * @return bool
	 */
	public function courseHasTopics($topic_ids, $filter_topic_id) {
		return in_array($filter_topic_id, $topic_ids);
	}

	/**
	 * Checks crs has selected type
	 *
	 * @param int 	$type_id
	 * @param int 	$filter_type_id
	 *
	 * @return bool
	 */
	public function courseHasType($type_id, $filter_type_id) {
		return $type_id == $filter_type_id;
	}

	/**
	 * Checks crs has selected venue
	 *
	 * @param int 	$venue_id
	 * @param int 	$filter_venue_id
	 *
	 * @return bool
	 */
	public function courseHasVenue($venue_id, $filter_venue_id) {
		return $venue_id == $filter_venue_id;
	}

		/**
	 * Checks crs has selected provider
	 *
	 * @param int 	$provider_id
	 * @param int 	$filter_provider_id
	 *
	 * @return bool
	 */
	public function courseHasProvider($provider_id, $filter_provider_id) {
		return $provider_id == $filter_provider_id;
	}
}

/**
 * cat-tms-patch end
 */