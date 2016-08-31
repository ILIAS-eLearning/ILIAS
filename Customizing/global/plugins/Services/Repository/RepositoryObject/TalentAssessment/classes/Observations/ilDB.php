<?php

namespace CaT\Plugins\TalentAssessment\Observations;
use CaT\Plugins\CareerGoal\Observations as BaseObservations;

class ilDB implements DB {
	const TABLE_OBSERVATIONS = "rep_obj_xtas_obs";
	const TABLE_OBSERVATIONS_NOTICE = "rep_obj_xtas_obs_not";
	const TABLE_OBS_REQ = "rep_obj_xtas_obs_req";
	const TABLE_REQUIREMENTS = "rep_obj_xtas_req";
	const TABLE_REQUIREMENTS_POINTS = "rep_obj_xtas_req_pts";

	public function __construct($db, $user, BaseObservations\DB $base_observations_db) {
		$this->db = $db;
		$this->user = $user;
		$this->base_observations_db = $base_observations_db;
	}

	public function install() {
		$this->createTables();
	}

	protected function createTables() {
		if(!$this->getDB()->tableExists(self::TABLE_OBSERVATIONS)) {
			$fields = 
				array('obj_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'ta_id' => array(
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

			$this->getDB()->createTable(self::TABLE_OBSERVATIONS, $fields);
			$this->getDB()->addPrimaryKey(self::TABLE_OBSERVATIONS, array("obj_id", "ta_id"));
			$this->getDB()->createSequence(self::TABLE_OBSERVATIONS);
		}

		if(!$this->getDB()->tableExists(self::TABLE_REQUIREMENTS)) {
			$fields = 
				array('obj_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'obs_id' => array(
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

			$this->getDB()->createTable(self::TABLE_REQUIREMENTS, $fields);
			$this->getDB()->addPrimaryKey(self::TABLE_REQUIREMENTS, array("obj_id", "obs_id"));
			$this->getDB()->createSequence(self::TABLE_REQUIREMENTS);
		}

		if(!$this->getDB()->tableExists(self::TABLE_OBSERVATIONS_NOTICE)) {
			$fields = 
				array('obs_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'observator_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'notice' => array(
						'type' 		=> 'text',
						'length'	=> 255,
						'notnull' 	=> true
					),
					'last_change' => array(
						'type' 		=> 'timestamp',
						'notnull' 	=> true
					)
				);

			$this->getDB()->createTable(self::TABLE_OBSERVATIONS_NOTICE, $fields);
			$this->getDB()->addPrimaryKey(self::TABLE_OBSERVATIONS_NOTICE, array("obs_id", "observator_id"));
		}

		if(!$this->getDB()->tableExists(self::TABLE_REQUIREMENTS_POINTS)) {
			$fields = 
				array('req_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'observator_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'points' => array(
						'type' 		=> 'float',
						'notnull' 	=> true
					),
					'last_change' => array(
						'type' 		=> 'timestamp',
						'notnull' 	=> true
					)
				);

			$this->getDB()->createTable(self::TABLE_REQUIREMENTS_POINTS, $fields);
			$this->getDB()->addPrimaryKey(self::TABLE_REQUIREMENTS_POINTS, array("req_id", "observator_id"));
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getBaseObservations($career_goal_id) {
		return $this->base_observations_db->getTAListData($career_goal_id);
	}

	/**
	 * @inheritdoc
	 */
	public function getObservations($obj_id) {
		$select = "SELECT A.obj_id, A.title, A.description, A.position\n"
				."    , B.obj_id as req_obj_id, B.title as req_title, A.description as req_description\n"
				."    , C.notice\n"
				."    , D.points\n"
				." FROM ".self::TABLE_OBSERVATIONS." A\n"
				." LEFT JOIN ".self::TABLE_REQUIREMENTS." B\n"
				."     ON A.obj_id = B.obs_id\n"
				." LEFT JOIN ".self::TABLE_OBSERVATIONS_NOTICE." C\n"
				."     ON A.obj_id = C.obs_id\n"
				."         AND C.observator_id = ".$this->getDB()->quote($this->user->getId())."\n"
				." LEFT JOIN ".self::TABLE_REQUIREMENTS_POINTS." D\n"
				."     ON B.obj_id = D.req_id\n"
				."         AND D.observator_id = ".$this->getDB()->quote($this->user->getId())."\n"
				." WHERE A.ta_id = ".$this->getDB()->quote($obj_id, "integer")."\n"
				." ORDER BY A.position, B.position";

		$res = $this->getDB()->query($select);

		$ret = array();
		$pos = null;
		$ret_ar = array();
		while($row = $this->getDB()->fetchAssoc($res)) {
			if($pos != $row["position"]) {
				if(!empty($ret_ar)) {
					$ret[] = $ret_ar;
				}

				$ret_ar = array();
				$ret_ar["title"] = $row["title"];
				$ret_ar["description"] = $row["description"];
				$ret_ar["obs_id"] = $row["obj_id"];
				$ret_ar["notice"] = $row["notice"];
				$ret_ar["requirements"] = array();
				$pos = $row["position"];
			}

			$ret_ar["requirements"][] = array("obj_id"=>$row["req_obj_id"], "title"=>$row["req_title"], "description"=>$row["req_description"], "value"=>$row["points"]);
		}

		$ret[] = $ret_ar;

		return $ret;
	}

	/**
	 * @inheritdoc
	 */
	public function copyObservations($ta_obj_id, $career_goal_id) {
		$obs = $this->base_observations_db->getDataForCopy($career_goal_id);

		foreach ($obs as $key => $ob) {
			$obj_id = $this->getObjId(self::TABLE_OBSERVATIONS);
			$values = array("obj_id" => array("integer", $obj_id)
					  , "ta_id" => array("integer", $ta_obj_id)
					  , "title" => array("text", $ob["title"])
					  , "description" => array("text", $ob["description"])
					  , "position" => array("integer", $ob["position"])
					  , "last_change" => array("text", date("Y-m-d H:i:s"))
					  , "last_change_user" => array("integer",$this->user->getId())
			);
			$this->getDB()->insert(self::TABLE_OBSERVATIONS, $values);

			$reqs = $ob["requirements"];
			foreach ($reqs as $key => $req) {
				$req_obj_id = $this->getObjId(self::TABLE_REQUIREMENTS);
				$values = array("obj_id" => array("integer", $req_obj_id)
					  , "obs_id" => array("integer", $obj_id)
					  , "title" => array("text", $req["title"])
					  , "description" => array("text", $req["description"])
					  , "position" => array("integer", $req["position"])
					  , "last_change" => array("text", date("Y-m-d H:i:s"))
					  , "last_change_user" => array("integer",$this->user->getId())
				);
				$this->getDB()->insert(self::TABLE_REQUIREMENTS, $values);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function setNotice($obs_id, $notice) {
		$insert = "INSERT INTO ".self::TABLE_OBSERVATIONS_NOTICE."\n"
				."    (obs_id, observator_id, notice, last_change)\n"
				." VALUES \n"
				."    (\n"
				.$this->getDB()->quote($obs_id, "integer")
				.", ".$this->getDB()->quote($this->user->getId(), "integer")
				.", ".$this->getDB()->quote($notice, "text")
				.", NOW())\n"
				." ON DUPLICATE KEY UPDATE notice = ".$this->getDB()->quote($notice, "text").", last_change = NOW()";

		$this->getDB()->manipulate($insert);
	}

	/**
	 * @inheritdoc
	 */
	public function setPoints($req_id, $points) {
		$insert = "INSERT INTO ".self::TABLE_REQUIREMENTS_POINTS."\n"
				."    (req_id, observator_id, points, last_change)\n"
				." VALUES \n"
				."    (\n"
				.$this->getDB()->quote($req_id, "integer")
				.", ".$this->getDB()->quote($this->user->getId(), "integer")
				.", ".$this->getDB()->quote($points, "float")
				.", NOW())\n"
				." ON DUPLICATE KEY UPDATE points = ".$this->getDB()->quote($points, "float").", last_change = NOW()";

		$this->getDB()->manipulate($insert);
	}

	protected function getDB() {
		if(!$this->db) {
			throw new \Exception("no Database");
		}
		return $this->db;
	}

	protected function getObjId($table) {
		return $this->getDB()->nextId($table);
	}
}