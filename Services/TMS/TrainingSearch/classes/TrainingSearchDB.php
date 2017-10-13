<?php

/**
 * cat-tms-patch start
 */

interface TrainingSearchDB {
	/**
	 * Get courses user can book
	 *
	 * @param int 	$user_id
	 * @param array<int, int | string | string[]>
	 *
	 * @return BookableCourse[]
	 */
	public function getBookableTrainingsFor($user_id, array $filter);

	/**
	 * Create new bookable course
	 *
	 * @param int		$ref_id
	 * @param string 	$crs_title
	 * @param string 	$type
	 * @param ilDateTime 	$start_date
	 * @param int 	$bookings_available
	 * @param string[] 	$target_group
	 * @param string 	$goals
	 * @param string[] 	$topics
	 * @param ilDateTime 	$end_date
	 * @param string 	$city
	 * @param string 	$address
	 * @param string 	$costs
	 *
	 * @return BookableCourse
	 */
	public function getBookableCourse($ref_id,
				$crs_title,
				$type,
				ilDateTime $start_date,
				$bookings_available,
				array $target_group,
				$goals,
				array $topics,
				ilDateTime $end_date,
				$city,
				$address,
				$costs = "KOSTEN"
	);
}
