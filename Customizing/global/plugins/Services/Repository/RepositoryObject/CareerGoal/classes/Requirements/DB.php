<?php

namespace CaT\Plugins\CareerGoal\Requirements;

/**
 * interace for requiremnts database actions
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
interface DB {
	/**
	 * install tables and/or base values
	 */
	public function install();

	/**
	 * create new requirement
	 *
	 * @param 	int 			$obj_id
	 * @param 	int 			$career_goal_id
	 * @param 	int 			$title
	 * @param 	int 			$description
	 *
	 * return Requirement
	 */
	public function create($obj_id, $career_goal_id, $title, $description);

	/**
	 * update a requirement
	 *
	 * @param 	Requirement 	$requirement
	 */
	public function update(Requirement $requirement);

	/**
	 * select values of requirement for $obj_id
	 *
	 * @param 	int 			$obj_id
	 */
	public function select($obj_id);

	/**
	 * delete requirement for $obj_id
	 *
	 * @param 	int 			$obj_id
	 */
	public function delete($obj_id);
}