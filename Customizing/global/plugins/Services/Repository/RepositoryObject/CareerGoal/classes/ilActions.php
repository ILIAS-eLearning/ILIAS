<?php
namespace CaT\Plugins\CareerGoal;

class ilActions {
	const F_TITLE = "title";
	const F_DESCRIPTION = "description";
	const F_LOWMARK = "lowmark";
	const F_SHOULD_SPECIFICATION = "should_specification";
	const F_DEFAULT_TEXT_FAILED = "default_text_failed";
	const F_DEFAULT_TEXT_PARTIAL = "default_text_partial";
	const F_DEFAULT_TEXT_SUCCESS = "default_text_success";

	const F_REQUIREMENT_OBJ_ID = "req_obj_id";
	const F_REQUIREMENT_TITLE = "req_title";
	const F_REQUIREMENT_DESCRIPTION = "req_description";
	const F_REQUIREMENT_CAREER_GOAL_ID = "req_career_goal_id";
	const F_REQUIREMENT_POSITION = "req_position";

	const F_OBSERVATION_OBJ_ID = "obs_obj_id";
	const F_OBSERVATION_TITLE = "obs_title";
	const F_OBSERVATION_DESCRIPTION = "obs_description";
	const F_OBSERVATION_CAREER_GOAL_ID = "obs_career_goal_id";
	const F_OBSERVATION_POSITION = "obs_position";
	const F_OBSERVATION_REQUIREMENTS = "obs_requirements";

	public function __construct(\CaT\Plugins\CareerGoal\ObjCareerGoal $object
								, \CaT\Plugins\CareerGoal\Settings\DB $settings_db
								, \CaT\Plugins\CareerGoal\Requirements\DB $requirements_db
								, \CaT\Plugins\CareerGoal\Observations\DB $observations_db) {
		$this->object = $object;
		$this->settings_db = $settings_db;
		$this->requirements_db = $requirements_db;
		$this->observations_db = $observations_db;
	}

	/**
	 * Update the object with the values from the array.
	 *
	 * @param	array	filled with fields according to F_*-constants
	 * @return  null
	 */
	public function update(array &$values) {
		assert('array_key_exists(self::F_TITLE, $values)');
		assert('is_string($values[self::F_TITLE])');
		$this->object->setTitle($values[self::F_TITLE]);
		if (array_key_exists(self::F_DESCRIPTION, $values)) {
			assert('is_string($values[self::F_DESCRIPTION])');
			$this->object->setDescription($values[self::F_DESCRIPTION]);
		}
		else {
			$this->object->setDescription("");
		}

		$values[self::F_LOWMARK] = $this->tofloat($values[self::F_LOWMARK]);
		$values[self::F_SHOULD_SPECIFICATION] = $this->tofloat($values[self::F_SHOULD_SPECIFICATION]);

		$this->object->updateSettings(function($s) use (&$values) {
			return $s
				->withLowmark($values[self::F_LOWMARK])
				->withShouldSpecifiaction($values[self::F_SHOULD_SPECIFICATION])
				->withDefaultTextFailed($values[self::F_DEFAULT_TEXT_FAILED])
				->withDefaultTextPartial($values[self::F_DEFAULT_TEXT_PARTIAL])
				->withDefaultTextSuccess($values[self::F_DEFAULT_TEXT_SUCCESS])
				;
		});
		$this->object->update();
	}

	/**
	 * Read the object to an array.
	 *
	 * @return array
	 */
	public function read() {
		$values = array();
		$values[self::F_TITLE] = $this->object->getTitle();
		$values[self::F_DESCRIPTION] = $this->object->getDescription();

		$settings = $this->object->getSettings();
		$values[self::F_LOWMARK] = $settings->getLowmark();
		$values[self::F_SHOULD_SPECIFICATION] = $settings->getShouldSpecification();
		$values[self::F_DEFAULT_TEXT_FAILED] = $settings->getDefaultTextFailed();
		$values[self::F_DEFAULT_TEXT_PARTIAL] = $settings->getDefaultTextPartial();
		$values[self::F_DEFAULT_TEXT_SUCCESS] = $settings->getDefaultTextSuccess();

		return $values;
	}

	/**
	 * replace last comma with dot
	 *
	 * @param 	string 	$num
	 *
	 * @return 	float
	 */
	protected function tofloat($num) {
		$dotPos = strrpos($num, '.');
		$commaPos = strrpos($num, ',');
		$sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos : 
					((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

		if (!$sep) {
			return floatval(preg_replace("/[^0-9]/", "", $num));
		} 

		return floatval(
			preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
			preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
		);
	}

	/**********************
	 * REQUIREMENTS
	 *********************/

	/**
	 * get data for list view
	 *
	 * @param int 	$career_goal_id
	 *
	 * @return array
	 */
	public function getRequirementListData($career_goal_id) {
		return $this->requirements_db->getListData($career_goal_id);
	}

	/**
	 * get a requirement obj
	 *
	 * @param int 	$obj_id
	 *
	 * @return Requirements\Requirement
	 */
	public function getRequirement($obj_id) {
		return $this->requirements_db->select($obj_id);
	}

	/**
	 *
	 * @param int 	$obj_id
	 */
	public function deleteRequirement($obj_id) {
		$this->requirements_db->delete($obj_id);
	}

	/**
	 * get infos for requierement edit gui
	 *
	 * @param int 	$obj_id
	 *
	 * @return array
	 */
	public function readRequirement($obj_id) {
		$requirement = $this->requirements_db->select($obj_id);

		$values = array();

		$values[self::F_REQUIREMENT_OBJ_ID] = $requirement->getObjId();
		$values[self::F_REQUIREMENT_TITLE] = $requirement->getTitle();
		$values[self::F_REQUIREMENT_DESCRIPTION] = $requirement->getDescription();
		$values[self::F_REQUIREMENT_CAREER_GOAL_ID] = $requirement->getCareerGoalId();

		return $values;
	}

	/**
	 *
	 * @param array 	$post
	 */
	public function updateRequirement($post) {
		$requirement = $this->requirements_db->select((int)$post[self::F_REQUIREMENT_OBJ_ID]);

		$requirement = $requirement->withTitle($post[self::F_REQUIREMENT_TITLE])
								   ->withDescription($post[self::F_REQUIREMENT_DESCRIPTION]);

		$this->requirements_db->update($requirement);

	}

	/**
	 *
	 * @param array 	$post
	 */
	public function updateRequirementPosition($post) {
		foreach ($post[self::F_REQUIREMENT_OBJ_ID] as $value) {
			$requirement = $this->requirements_db->select((int)$value);

			$new_pos = (int)$post[self::F_REQUIREMENT_POSITION."_".$value];

			$requirement = $requirement->withPosition($new_pos);

			$this->requirements_db->update($requirement);
		}
	}

	/**
	 * change order numbers to display in ILIAS style
	 * 10, 20 , 30
	 *
	 * @param array 	$data
	 *
	 * @return array 	$data
	 */
	public function changePositionValues($data) {
		$pos = 10;

		foreach($data as $key => $value) {
			$value["position"] = $pos;
			$data[$key] = $value;
			$pos += 10;
		}

		return $data;
	}

	/**
	 *
	 * @param array 	$post
	 */
	public function createRequirement($post) {
		$this->requirements_db->create((int)$post[self::F_REQUIREMENT_CAREER_GOAL_ID], $post[self::F_REQUIREMENT_TITLE], $post[self::F_REQUIREMENT_DESCRIPTION]);
	}

	/**
	 * get values for new requirement form
	 *
	 * @return array
	 */
	public function readNewRequirement() {
		$values = array();

		$values[self::F_REQUIREMENT_CAREER_GOAL_ID] = $this->object->getId();

		return $values;
	}

	/**
	 * get all possible requirements for career goal
	 *
	 * @param int 	$career_goal_id
	 *
	 * @return Requirements\Requirement[]
	 */
	public function getPossibleRequirementsOptions($career_goal_id) {
		$requirements = $this->requirements_db->selectRequirementsFor($career_goal_id);

		$ret = array();
		foreach ($requirements as $key => $value) {
			$ret[$value->getObjId()] = $value->getTitle();
		}

		return $ret;
	}

	/**********************
	 * OBSERVATIONS
	 *********************/

	/**
	 * get data for list view
	 *
	 * @param int 	$career_goal_id
	 *
	 * @return array
	 */
	public function getObservationListData($career_goal_id) {
		return $this->observations_db->getListData($career_goal_id);
	}

	/**
	 * get values for new requirement form
	 *
	 * @return array
	 */
	public function readNewObservation() {
		$values = array();

		$values[self::F_OBSERVATION_CAREER_GOAL_ID] = $this->object->getId();

		return $values;
	}

	/**
	 *
	 * @param array 	$post
	 */
	public function createObservation($post) {
		$this->observations_db->create((int)$post[self::F_OBSERVATION_CAREER_GOAL_ID], $post[self::F_OBSERVATION_TITLE]
				, $post[self::F_OBSERVATION_DESCRIPTION], $post[self::F_OBSERVATION_REQUIREMENTS]);
	}

	public function readObservation($obj_id) {
		$observation = $this->observations_db->select($obj_id);

		$values = array();

		$values[self::F_OBSERVATION_OBJ_ID] = $observation->getObjId();
		$values[self::F_OBSERVATION_TITLE] = $observation->getTitle();
		$values[self::F_OBSERVATION_DESCRIPTION] = $observation->getDescription();
		$values[self::F_OBSERVATION_CAREER_GOAL_ID] = $observation->getCareerGoalId();
		$values[self::F_OBSERVATION_REQUIREMENTS] = $observation->getRequirements();

		return $values;
	}

	/**
	 *
	 * @param array 	$post
	 */
	public function updateObservation($post) {
		$observation = $this->observations_db->select((int)$post[self::F_OBSERVATION_OBJ_ID]);

		$observation = $observation->withTitle($post[self::F_OBSERVATION_TITLE])
								   ->withDescription($post[self::F_OBSERVATION_DESCRIPTION])
								   ->withRequirements($post[self::F_OBSERVATION_REQUIREMENTS]);

		$this->observations_db->update($observation);
	}

	/**
	 * get a observation obj
	 *
	 * @param int 	$obj_id
	 *
	 * @return Observations\Observation
	 */
	public function getObservation($obj_id) {
		return $this->observations_db->select($obj_id);
	}

	/**
	 *
	 * @param int 	$obj_id
	 */
	public function deleteObservation($obj_id) {
		$this->observations_db->delete($obj_id);
	}

	/**
	 *
	 * @param array 	$post
	 */
	public function updateObservationPosition($post) {
		foreach ($post[self::F_OBSERVATION_OBJ_ID] as $value) {
			$observation = $this->observations_db->select((int)$value);
			$new_pos = (int)$post[self::F_OBSERVATION_POSITION."_".$value];
			$observation = $observation->withPosition($new_pos);

			$this->observations_db->update($observation);
		}
	}
}