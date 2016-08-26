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
	 * @param 	int 			$obj_id
	 */
	public function create($obj_id);

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
}