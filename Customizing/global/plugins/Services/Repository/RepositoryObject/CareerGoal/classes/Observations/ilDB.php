<?php

namespace CaT\Plugins\CareerGoal\Observations;
use CaT\Plugins\CareerGoal\Requirements;

class ilDB implements DB {
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
	public function create($career_goal_id, $title, $description, array $requirements) {
		$obj_id = $this->getObjId();
		$position = $this->getNextPosition($career_goal_id);

		$requirement = new Observation($obj_id, $career_goal_id, $title, $description, $position, $requirements);

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

		$this->addObservationRequirement($obj_id, $requirements);
	}

	protected function addObservationRequirement($obj_id, array $requirements) {
		assert('is_int($obj_id)');

		foreach ($requirements as $req) {
			$values = array
					( "obs_id" => array("integer", $obj_id)
					 ,"req_id" => array("integer", $req)
					);

			$this->getDB()->insert(self::TABLE_OBS_REQ, $values);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function update(Observation $observation) {
		$values = array
				( "title" => array("text", $observation->getTitle())
				, "description" => array("text", $observation->getDescription())
				, "position" => array("integer", $observation->getPosition())
				, "last_change" => array("text", date("Y-m-d H:i:s"))
				, "last_change_user" => array("integer", $this->user->getId())
				);

		$where = array
				( "obj_id" => array("integer", $observation->getObjId())
				, "career_goal_id" => array("integer", $observation->getCareerGoalId())
				);

		$this->getDB()->update(self::TABLE_NAME, $values, $where);

		$this->updateObservationRequirement($observation->getObjId(), $observation->getRequirements());
	}

	protected function updateObservationRequirement($obj_id, array $requirements) {
		$this->deleteObservationRequirement($obj_id);
		$this->addObservationRequirement($obj_id, $requirements);
	}

	protected function deleteObservationRequirement($obj_id) {
		assert('is_int($obj_id)');

		$delete = "DELETE FROM ".self::TABLE_OBS_REQ."\n"
				 ." WHERE obs_id = ".$this->getDB()->quote($obj_id, "integer");

		$this->getDB()->manipulate($delete);
	}

	/**
	 * @inheritdoc
	 */
	public function select($obj_id) {
		$select = "SELECT A.career_goal_id, A.title, A.description, A.position, B.req_id\n"
				." FROM ".self::TABLE_NAME." A\n"
				." LEFT JOIN ".self::TABLE_OBS_REQ." B\n"
				."    ON A.obj_id = B.obs_id\n"
				." WHERE obj_id = ".$this->getDB()->quote($obj_id, "integer");

		$res = $this->getDB()->query($select);

		if($this->getDB()->numRows($res) == 0) {
			throw new \InvalidArgumentException("Invalid id '$obj_id' for Requirement-object");
		}

		$career_goal_id = null;
		$title = null;
		$description = null;
		$position = null;
		$requirements = array();

		while($row = $this->getDB()->fetchAssoc($res)) {
			if($career_goal_id === null) {
				$career_goal_id = (int)$row["career_goal_id"];
				$title = $row["title"];
				$description = $row["description"];
				$position = $row["position"];
			}

			$requirements[] = (int)$row["req_id"];
		}

		$observation = new Observation((int)$obj_id
								 , $career_goal_id
								 , $title
								 , $description
								 , $position
								 , $requirements
							);

		return $observation;
	}

	/**
	 * @inheritdoc
	 */
	public function delete($obj_id) {
		$this->deleteObservationRequirement($obj_id);

		$delete = "DELETE FROM ".self::TABLE_NAME."\n"
				." WHERE obj_id = ".$this->getDB()->quote($obj_id, "integer");

		$this->getDB()->manipulate($delete);
	}

	/**
	 * @inheritdoc
	 */
	public function selectObservationsFor($career_goal_id) {}

	/**
	 * @inheritdoc
	 */
	public function getListData($career_goal_id) {
		$select = "SELECT A.obj_id as obj_id, A.title as title, A.description as description, A.position as position, GROUP_CONCAT(C.title SEPARATOR ', ') as requirements\n"
				." FROM ".self::TABLE_NAME." A\n"
				." LEFT JOIN ".self::TABLE_OBS_REQ." B\n"
				."     ON A.obj_id = B.obs_id\n"
				." LEFT JOIN ".Requirements\ilDB::TABLE_NAME." C\n"
				."     ON B.req_id = C.obj_id\n"
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

	/**
	 * @inheritdoc
	 */
	public function getObjId() {
		return $this->getDB()->nextId(self::TABLE_NAME);
	}

	protected function getNextPosition($career_goal_id) {
		$select = "SELECT MAX(position) as max_pos\n"
				." FROM ".self::TABLE_NAME."\n"
				." WHERE career_goal_id = ".$this->getDB()->quote($career_goal_id, "integer");

		$res = $this->getDB()->query($select);
		$row = $this->getDB()->fetchAssoc($res);

		return ($row["max_pos"] + 10);
	}

	/**
	 * @inheritdoc
	 */
	public function getTAListData($career_goal_id) {
		$select = "SELECT A.obj_id as obj_id, A.title as title, A.description as description, A.position as position\n"
				."    , C.title as req_title, C.Description as req_description, C.position as req_position\n"
				." FROM ".self::TABLE_NAME." A\n"
				." LEFT JOIN ".self::TABLE_OBS_REQ." B\n"
				."     ON A.obj_id = B.obs_id\n"
				." LEFT JOIN ".Requirements\ilDB::TABLE_NAME." C\n"
				."     ON B.req_id = C.obj_id\n"
				." WHERE A.career_goal_id = ".$this->getDB()->quote($career_goal_id, "integer")."\n"
				." ORDER BY A.position, C.position";

		$res = $this->getDB()->query($select);
		$ret = array();

		while($row = $this->getDB()->fetchAssoc($res)) {
			$ret[] = $row;
		}

		return $ret;
	}

	/**
	 * @inheritdoc
	 */
	public function getDataForCopy($career_goal_id) {
		$select = "SELECT A.obj_id, A.title as title, A.description as description, A.position as position\n"
				."    , C.title as req_title, C.Description as req_description, C.position as req_position\n"
				." FROM ".self::TABLE_NAME." A\n"
				." LEFT JOIN ".self::TABLE_OBS_REQ." B\n"
				."     ON A.obj_id = B.obs_id\n"
				." LEFT JOIN ".Requirements\ilDB::TABLE_NAME." C\n"
				."     ON B.req_id = C.obj_id\n"
				." WHERE A.career_goal_id = ".$this->getDB()->quote($career_goal_id, "integer")."\n"
				." ORDER BY A.position, C.position";

		$res = $this->getDB()->query($select);
		$ret = array();
		$obj_id = null;
		$ret_ar = array();
		while($row = $this->getDB()->fetchAssoc($res)) {
			if($obj_id != $row["obj_id"]) {
				if(!empty($ret_ar)) {
					$ret[] = $ret_ar;
					$ret_ar = array();
				}
				$ret_ar["title"] = $row["title"];
				$ret_ar["description"] = $row["description"];
				$ret_ar["position"] = $row["position"];
				$ret_ar["requirements"] = array();
				$obj_id = $row["obj_id"];
			}
			
			$ret_ar["requirements"][] = array("title"=>$row["req_title"], "description"=>$row["req_description"], "position"=>$row["req_position"]);
		}

		$ret[] = $ret_ar;
		return $ret;
	}
}