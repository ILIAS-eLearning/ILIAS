<?php

namespace CaT\Plugins\CareerGoal\Requirements;

use CaT\Plugins\CareerGoal\Observations;

class ilDB implements DB {
	const TABLE_NAME = "rep_obj_xcgo_req";

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
	public function create($career_goal_id, $title, $description) {
		$obj_id = $this->getObjId();
		$position = $this->getNextPosition($career_goal_id);

		$requirement = new Requirement($obj_id, $career_goal_id, $title, $description, $position);

		$values = array
				( "obj_id" => array("integer", $requirement->getObjId())
				, "career_goal_id" => array("integer", $requirement->getCareerGoalId())
				, "title" => array("text", $requirement->getTitle())
				, "description" => array("text", $requirement->getDescription())
				, "position" => array("integer", $requirement->getPosition())
				, "last_change" => array("text", date("Y-m-d H:i:s"))
				, "last_change_user" => array("integer", $this->user->getId())
				);
		$this->getDB()->insert(self::TABLE_NAME, $values);
	}

	/**
	 * @inheritdoc
	 */
	public function update(Requirement $requirement) {
		$values = array
				( "title" => array("text", $requirement->getTitle())
				, "description" => array("text", $requirement->getDescription())
				, "position" => array("integer", $requirement->getPosition())
				, "last_change" => array("text", date("Y-m-d H:i:s"))
				, "last_change_user" => array("integer", $this->user->getId())
				);

		$where = array
				( "obj_id" => array("integer", $requirement->getObjId())
				, "career_goal_id" => array("integer", $requirement->getCareerGoalId())
				);

		$this->getDB()->update(self::TABLE_NAME, $values, $where);
	}

	/**
	 * @inheritdoc
	 */
	public function select($obj_id) {
		$select = "SELECT career_goal_id, title, description, position\n"
				." FROM ".self::TABLE_NAME."\n"
				." WHERE obj_id = ".$this->getDB()->quote($obj_id, "integer");

		$res = $this->getDB()->query($select);
		$row = $this->getDB()->fetchAssoc($res);

		if(empty($row)) {
			throw new \InvalidArgumentException("Invalid id '$obj_id' for Requirement-object");
		}

		$requirement = new Requirement((int)$obj_id
								 , (int)$row["career_goal_id"]
								 , $row["title"]
								 , $row["description"]
								 , $row["position"]
							);

		return $requirement;

	}

	/**
	 * @inheritdoc
	 */
	public function delete($obj_id) {
		assert('is_int($obj_id)');
		if($this->cheCheckForObservations($obj_id)) {
			$delete = "DELETE FROM ".self::TABLE_NAME."\n"
				." WHERE obj_id = ".$this->getDB()->quote($obj_id, "integer");

			$this->getDB()->manipulate($delete);
		}
	}

	protected function cheCheckForObservations($obj_id) {
		$select = "SELECT count(obs_id) as obs\n"
				." FROM ".Observations\ilDB::TABLE_OBS_REQ."\n"
				." WHERE req_id = ".$this->getDB()->quote($obj_id, "integer");

		$res = $this->getDB()->query($select);
		$row = $this->getDB()->fetchAssoc($res);

		if($row["obs"] > 0) {
			return false;
		}

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function getObjId() {
		return $this->getDB()->nextId(self::TABLE_NAME);
	}

	/**
	 * @inheritdoc
	 */
	public function selectRequirementsFor($career_goal_id) {
		$select = "SELECT obj_id, title, description\n"
				." FROM ".self::TABLE_NAME."\n"
				." WHERE career_goal_id = ".$this->getDB()->quote($career_goal_id)."\n"
				." ORDER BY title";

		$res = $this->getDB()->query($select);

		$ret = array();

		while($row = $this->getDB()->fetchAssoc($res)) {
			$requirement = new Requirement((int)$row["obj_id"]
								 , (int)$career_goal_id
								 , $row["title"]
								 , $row["description"]
							);

			$ret[] = $requirement;
		}

		return $ret;
	}

	public function getListData($career_goal_id) {
		$select = "SELECT A.obj_id as obj_id, A.title as title, A.description as description, A.position as position, GROUP_CONCAT(C.title SEPARATOR ', ') as observations\n"
				." FROM ".self::TABLE_NAME." A\n"
				." LEFT JOIN ".Observations\ilDB::TABLE_OBS_REQ." B\n"
				."     ON A.obj_id = B.req_id\n"
				." LEFT JOIN ".Observations\ilDB::TABLE_NAME." C\n"
				."     ON B.obs_id = C.obj_id\n"
				." WHERE A.career_goal_id = ".$this->getDB()->quote($career_goal_id, "integer")."\n"
				." GROUP BY A.obj_id, A.title, A.description\n"
				." ORDER BY A.position";

		$res = $this->getDB()->query($select);
		$ret = array();

		while($row = $this->getDB()->fetchAssoc($res)) {
			$ret[] = $row;
		}

		return $ret;
	}

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
					'position' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
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
	}

	protected function getDB() {
		if(!$this->db) {
			throw new \Exception("no Database");
		}
		return $this->db;
	}

	protected function getNextPosition($career_goal_id) {
		$select = "SELECT MAX(position) as max_pos\n"
				." FROM ".self::TABLE_NAME."\n"
				." WHERE career_goal_id = ".$this->getDB()->quote($career_goal_id, "integer");

		$res = $this->getDB()->query($select);
		$row = $this->getDB()->fetchAssoc($res);

		return ($row["max_pos"] + 10);
	}
}