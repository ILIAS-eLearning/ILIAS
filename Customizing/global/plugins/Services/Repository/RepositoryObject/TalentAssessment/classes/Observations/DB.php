<?php

namespace CaT\Plugins\TalentAssessment\Observations;

interface DB {
	public function install();

	/**
	 * get the observations from selected career goal
	 *
	 * @param 	int 	$career_goal_id
	 *
	 * @return 	array[]
	 */
	public function getBaseObservations($career_goal_id);

	/**
	 * get copied observations
	 *
	 * @param 	int 	$obj_id
	 *
	 * @return 	array[]
	 */
	public function getObservations($obj_id);

	/**
	 * copy observations from selected career goal
	 *
	 * @param 	int 	$obj_id
	 * @param 	int 	$career_goal_id
	 */
	public function copyObservations($obj_id, $career_goal_id);

	/**
	 * save notice for observation
	 *
	 * @param 	int 	$obs_id
	 * @param 	string 	$notice
	 */
	public function setNotice($obs_id, $notice);

	/**
	 * save points for requirement
	 *
	 * @param 	int 	$req_id
	 * @param 	float 	$points
	 */
	public function setPoints($req_id, $points);

	/**
	 * get data for overview tables
	 *
	 * @param 	int 	$obj_id 	object id of the talent assessment
	 * @param 	array 	$observator
	 *
	 * @return array[]
	 */
	public function getObservationOverviewData($obj_id, $observator);

	/**
	 * Delete entered observation results when observator is deleted.
	 *
	 * @param 	int 	$obj_id
	 * @param 	int 	$user_id
	 *
	 * @return null
	 */
	public function deleteObservationResults($obj_id, $user_id);
}