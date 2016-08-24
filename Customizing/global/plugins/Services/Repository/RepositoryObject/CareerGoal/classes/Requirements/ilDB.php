<?php

namespace CaT\Plugins\CareerGoal\Requirements;

class ilDB implements DB {
	const TABLE_NAME = "";

	public function __construct($db, $user) {
		$this->db = $db;
		$this->user = $user;
	}

	/**
	 * @inheritdoc
	 */
	public function install() {
		$this->table();
	}

	/**
	 * @inheritdoc
	 */
	public function create($obj_id, $career_goal_id, $title, $description) {
		$settings = new Requirement($obj_id, $career_goal_id, $title, $description);

		$values = array
				( "obj_id" => array("integer", $settings->getObjId())
				, "career_goal_id" => array("integer", $settings->getCareerGoalId())
				, "title" => array("text", $settings->getTitle())
				, "description" => array("text", $settings->getDescription())
				);
		$this->getDB()->insert(self::TABLE_NAME, $values);

		return $settings;
	}

	/**
	 * @inheritdoc
	 */
	public function update(Requirement $requirement) {
		$values = array
				( "title" => array("text", $settings->getTitle())
				, "description" => array("text", $settings->getDescription())
				);

		$where = array
				( "obj_id" => array("integer", $settings->getObjId())
				, "career_goal_id" => array("integer", $settings->getCareerGoalId())
				);

		$this->getDB()->update(self::TABLE_NAME, $values, $where);
	}

	/**
	 * @inheritdoc
	 */
	public function select($obj_id) {

	}

	/**
	 * @inheritdoc
	 */
	public function delete($obj_id) {

	}

	protected function table() {

	}

	protected function getDB() {
		if(!$this->db) {
			throw new \Exception("no Database");
		}
		return $this->db;
	}
}