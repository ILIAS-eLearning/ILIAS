<?php

namespace CaT\Plugins\CareerGoal\Observations;

class ilDB {
	const TABLE_NAME = "rep_obj_xcgo_obs";
	const TABLE_OBS_REQ = "rep_obj_xcgo_obs_req";

	public function __construct($db, $user) {
		$this->db = $db;
		$this->user = $user;
	}
	/**
	 * @inheritdoc
	 */
	public function install() {
		$this->createTables();
	}

	/**
	 * @inheritdoc
	 */
	public function create($career_goal_id, $title, $description) {}

	/**
	 * @inheritdoc
	 */
	public function update(Observation $observation) {}

	/**
	 * @inheritdoc
	 */
	public function select($obj_id) {}

	/**
	 * @inheritdoc
	 */
	public function delete($obj_id) {}

	/**
	 * @inheritdoc
	 */
	public function getObjId() {}

	/**
	 * @inheritdoc
	 */
	public function selectObservationsFor($career_goal_id) {}

	/**
	 * @inheritdoc
	 */
	public function getListData($career_goal_id) {}

	protected function createTables() {
		if(!$this->getDB()->tableExists(self::TABLE_NAME)) {
			$fields = 
				array('obj_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'career_goal_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'title' => array(
						'type' 		=> 'text',
						'length'	=> 255,
						'notnull' 	=> true
					),
					'description' => array(
						'type' 		=> 'clob',
						'notnull' 	=> false
					),
					'last_change' => array(
						'type' 		=> 'timestamp',
						'notnull' 	=> true
					),
					'last_change_user' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					)
				);

			$this->getDB()->createTable(self::TABLE_NAME, $fields);
			$this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id", "career_goal_id"));
			$this->getDB()->createSequence(self::TABLE_NAME);
		}

		if(!$this->getDB()->tableExists(self::TABLE_OBS_REQ)) {
			$fields = 
				array('obs_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'req_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					)
				);

			$this->getDB()->createTable(self::TABLE_OBS_REQ, $fields);
			$this->getDB()->addPrimaryKey(self::TABLE_OBS_REQ, array("obs_id", "req_id"));
		}
	}

	protected function getDB() {
		if(!$this->db) {
			throw new \Exception("no Database");
		}
		return $this->db;
	}
}