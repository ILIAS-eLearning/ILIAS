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
	 * @param 	int 			$career_goal_id
	 * @param 	int 			$title
	 * @param 	int 			$description
	 *
	 * return Requirement
	 */
	public function create($career_goal_id, $title, $description);

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

	/**
	 * get next obj id
	 *
	 * @return 	int 			$obj_id
	 */
	public function getObjId();

	/**
	 * get alls requirements for career goal id
	 *
	 * @param 	int 			$career_goal_id
	 *
	 * @return 	Requirement[]
	 */
	public function selectRequirementsFor($career_goal_id);

	/**
	 * get data for requirements list
	 *
	 * @param 	int 			$career_goal_id
	 *
	 * @return array 			string => string
	 */
	public function getListData($career_goal_id);
}