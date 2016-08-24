<?php
namespace CaT\Plugins\CareerGoal\Settings;

interface DB {
	/**
	 * Install tables
	 */
	public function install();

	/**
	 * create new settings entries
	 *
	 * @param 	int 			$obj_id
	 * @param 	float 			$lowmark
	 * @param 	float 			$should_specifiaction
	 * @param 	string 			$default_text_failed
	 * @param 	string 			$default_text_partial
	 * @param 	string 			$default_text_success
	 */
	public function create($obj_id, $lowmark, $should_specifiaction, $default_text_failed, $default_text_partial, $default_text_success);

	/**
	 * updates settings entries
	 *
	 * @param 	CareerGoal 		$settings
	 */
	public function update(CareerGoal $settings);

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