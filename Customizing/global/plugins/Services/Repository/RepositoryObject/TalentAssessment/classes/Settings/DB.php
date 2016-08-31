<?php

namespace CaT\Plugins\TalentAssessment\Settings;

interface DB {
	/**
	 * Install tables
	 */
	public function install();

	/**
	 * create new settings entries
	 *
	 * @param 	int 				$obj_id
	 * @param 	int 				$state
	 * @param 	int 				$career_goal_id
	 * @param 	string 				$username
	 * @param 	string 				$firstname
	 * @param 	string 				$lastname
	 * @param 	string 				$email
	 * @param 	ilDateTime|null 	$start_date
	 * @param 	ilDateTime|null 	$end_date
	 * @param 	int|null 			$venue
	 * @param 	int|null 			$org_unit
	 * @param 	bool 				$started
	 *
	 * @return TalentAssessment
	 */
	public function create($obj_id, $state, $career_goal_id, $username, $firstname, $lastname, $email, $start_date, $end_date, $venue, $org_unit, $started);

	/**
	 * updates settings entries
	 *
	 * @param 	CareerGoal 		$settings
	 */
	public function update(TalentAssessment $settings);

	/**
	 * delete setting entry
	 *
	 * @param 	int 			$obj_id
	 */
	public function delete($obj_id);

	/**
	 * select settings values
	 *
	 * @param 	int 			$obj_id
	 */
	public function select($obj_id);

	/**
	 * @return array[] int => string
	 */
	public function getCareerGoalsOptions();
	
	/**
	 * @return array[] int => string
	 */
	public function getVenueOptions();
	
	/**
	 * @return array[] int => string
	 */
	public function getOrgUnitOptions();
}