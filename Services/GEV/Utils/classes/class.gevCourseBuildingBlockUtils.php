<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for generali users.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/
require_once("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");

class gevCourseBuildingBlockUtils {
	static protected $instances = array();
	const TABLE_NAME = "dct_crs_building_block";
	const TABLE_NAME_JOIN1 = "dct_building_block";
	const DURATION_PER_POINT = 45;
	const MAX_DURATION_MINUTES = 720;

	protected $course_building_block_id = "";
	protected $crs_id = null;
	protected $building_block = "";
	protected $start_date = "";
	protected $end_date = "";
	protected $methods = array();
	protected $media = array();
	protected $crs_request_id = null;

	protected function __construct($a_course_building_block_id) {
		global $ilDB, $ilUser;
				
		$this->course_building_block_id = $a_course_building_block_id;
		$this->db = $ilDB;
		$this->ilUser = $ilUser;
	}

	public function getInstance($a_course_building_block_id) {
		if (array_key_exists($a_block_unit_id, self::$instances)) {
			return self::$instances[$a_course_building_block_id];
		}
		
		self::$instances[$a_course_building_block_id] = new gevCourseBuildingBlockUtils($a_course_building_block_id);
		return self::$instances[$a_course_building_block_id];
	}

	public function getId() {
		return $this->course_building_block_id;
	}

	public function getCrsId() {
		return $this->crs_id;
	}

	public function setCrsId($a_crs_id) {
		$this->crs_id = $a_crs_id;
	}

	public function getStartDate() {
		return $this->start_date;
	}

	public function setStartDate($a_start_date) {
		$this->start_date = $a_start_date;
	}

	public function getEndDate() {
		return $this->end_date;
	}

	public function setEndDate($a_end_date) {
		$this->end_date = $a_end_date;
	}

	public function getStartTime() {
		$expl = explode(" ", $this->getStartDate());
		$expl = explode(":", $expl[1]);
		return $expl[0].":".$expl[1];
	}

	public function getEndTime() {
		$expl = explode(" ", $this->getEndDate());
		$expl = explode(":", $expl[1]);
		return $expl[0].":".$expl[1];
	}

	public function getMethods() {
		return $this->methods;
	}

	public function setMethods(array $a_methods) {
		$this->methods = $a_methods;
	}

	public function getMedia() {
		return $this->media;
	}

	public function setMedia($a_media) {
		$this->media = $a_media;
	}

	public function getBuildingBlock() {
		return $this->building_block;
	}

	public function setBuildingBlock($a_building_block_id) {
		$bb_utils = gevBuildingBlockUtils::getInstance($a_building_block_id);
		$bb_utils->loadData();
		$this->building_block = $bb_utils;
	}

	public function getCourseRequestId() {
		return $this->crs_request_id;
	}

	public function setCourseRequestId($a_crs_request_id) {
		$this->crs_request_id = $a_crs_request_id;
	}

	public function getTime() {
		$start_date = $this->getStartDate();
		$arr_start_date = split(" ",$start_date);

		$end_date = $this->getEndDate();
		$arr_end_date = split(" ",$end_date);

		$ret = array("start"=>array("time"=>$arr_start_date[1],"date"=>$arr_start_date[0])
					,"end"=>array("time"=>$arr_end_date[1],"date"=>$arr_end_date[0]));
		
		return $ret;
	}

	public function loadData() {
		$sql = "SELECT crs_id, bb_id, start_date, end_date, method, media\n"
			  ."  FROM ".self::TABLE_NAME." WHERE id = ".$this->db->quote($this->getId(), "integer");

		$res = $this->db->query($sql);
		
		if($this->db->numRows($res) > 0) {
			$row = $this->db->fetchAssoc($res);
			$this->setCrsId($row["crs_id"]);
			$this->setBuildingBlock($row["bb_id"]);
			$this->setStartDate($row["start_date"]);
			$this->setEndDate($row["end_date"]);
			$this->setMethods(unserialize($row["method"]));
			$this->setMedia(unserialize($row["media"]));
		}
	}

	public function update() {
		$method_serial = serialize($this->getMethods());
		$media_serial = serialize($this->getMedia());

		$sql = "UPDATE ".self::TABLE_NAME."\n"
			  ."   SET bb_id = ".$this->db->quote($this->getBuildingBlock()->getId(), "integer")."\n"
			  ."     , start_date = ".$this->db->quote($this->getStartDate(), "timestamp")."\n"
			  ."     , end_date = ".$this->db->quote($this->getEndDate(), "timestamp")."\n"
			  ."     , method = ".$this->db->quote($method_serial, "text")."\n"
			  ."     , media = ".$this->db->quote($media_serial, "text")."\n"
			  ."     , last_change_user = ".$this->db->quote($this->ilUser->getId(), "integer")."\n"
			  ."     , last_change_date = NOW()"
			  ." WHERE id = ".$this->db->quote($this->getId(), "integer");

		$this->db->manipulate($sql);

		if($this->getCrsId() !== null) {
			self::courseUpdates($this->getCrsId(),$this->db);
		}
	}

	public function save() {
		/*$method_serial = preg_replace('/\"/','\\\"',serialize($this->getMethods()));
		$media_serial = preg_replace('/\"/','\\\"',serialize($this->getMedia()));*/

		$method_serial = serialize($this->getMethods());
		$media_serial = serialize($this->getMedia());

		$sql = "INSERT INTO ".self::TABLE_NAME.""
			  ." (id, crs_id, bb_id, start_date, end_date, method, media, last_change_user, last_change_date, crs_request_id)\n"
			  ." VALUES ( ".$this->db->quote($this->getId(), "integer")."\n"
			  ."        , ".$this->db->quote($this->getCrsId(), "integer")."\n"
			  ."        , ".$this->db->quote($this->getBuildingBlock()->getId(), "integer")."\n"
			  ."        , ".$this->db->quote($this->getStartDate(), "timestamp")."\n"
			  ."        , ".$this->db->quote($this->getEndDate(), "timestamp")."\n"
			  ."        , ".$this->db->quote($method_serial, "text")."\n"
			  ."        , ".$this->db->quote($media_serial, "text")."\n"
			  ."        , ".$this->db->quote($this->ilUser->getId(), "integer")."\n"
			  ."        , NOW()\n"
			  ."        , ".$this->db->quote($this->getCourseRequestId(), "integer")."\n"
			  ."        )";

		$this->db->manipulate($sql);

		if($this->getCrsId() !== null) {
			self::courseUpdates($this->getCrsId(),$this->db);
		}
	}

	public function delete() {
		$query = "DELETE FROM ".self::TABLE_NAME." WHERE id = ".$this->db->quote($this->getId(),"integer");
		$this->db->manipulate($query);

		if($this->getCrsId() !== null) {
			self::courseUpdates($this->getCrsId(),$this->db);
		}
	}

	static public function getAllCourseBuildingBlocksRaw($a_crs_ref_id,$a_request_id = null) {
		global $ilDB;

		$sql = "SELECT\n"
			  ."    base.id, base.crs_id, base.bb_id, base.start_date, base.end_date, base.method, base.media,\n"
			  ."    join1.title, join1.learning_dest, join1.content, base.crs_request_id, base.bb_id\n"
			  ." FROM ".self::TABLE_NAME." as base\n"
			  ." JOIN ".self::TABLE_NAME_JOIN1." as join1\n"
			  ."   ON  base.bb_id = join1.obj_id\n";
		
		if($a_crs_ref_id !== null) {
			$sql .= " WHERE base.crs_id = ".$ilDB->quote($a_crs_ref_id, "integer")."\n";
		} else {
			if($a_request_id !== null) {
				$sql .= " WHERE base.crs_request_id = ".$ilDB->db->quote($a_request_id, "integer")."\n";
			}
		}
	
		$sql .= " ORDER BY base.start_date";

		$ret = array();
		$res = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($res)) {
			$ret[] = $row;
		}

		return $ret;
	}
	
	static public function getAllCourseBuildingBlocks($a_crs_ref_id, $a_request_id = null) {
		return array_map(function($row) {
			$obj = new gevCourseBuildingBlockUtils($row["id"]);
			$obj->setCrsId($row["crs_id"]);
			$obj->setStartDate($row["start_date"]);
			$obj->setEndDate($row["end_date"]);
			$obj->setMethods(unserialize($row["method"]));
			$obj->setMedia(unserialize($row["media"]));
			$obj->setCourseRequestId($row["crs_request_id"]);
			$obj->setBuildingBlock($row["bb_id"]);
			return $obj;
		}, self::getAllCourseBuildingBlocksRaw($a_crs_ref_id, $a_request_id));
	}

	static public function updateCrsBuildungBlocksCrsIdByCrsRequestId($a_crs_id, $a_crs_request_id) {
		global $ilDB;

		$sql = "UPDATE ".self::TABLE_NAME."\n"
			  ."   SET crs_id = ".$ilDB->quote($a_crs_id, "integer")."\n"
			  ."     , crs_request_id = NULL\n"
			  ." WHERE crs_request_id = ".$ilDB->quote($a_crs_request_id, "integer");
		$ilDB->manipulate($sql);
	}

	static public function courseUpdates($a_crs_ref_id, $a_db = null) {
		if($a_crs_ref_id === null) {
			return;
		}

		if($a_db === null) {
			global $ilDB;
			$a_db = $ilDB;
		}

		self::updateCourseMethodAndMedia($a_crs_ref_id, $a_db);
		self::updateWP($a_crs_ref_id, $a_db);
	}

	static private function updateCourseMethodAndMedia($a_crs_ref_id, $a_db) {
		$sql = "SELECT method, media\n"
			  ."  FROM ".self::TABLE_NAME."\n"
			  ." WHERE crs_id = ".$a_db->quote($a_crs_ref_id, "integer");
		$res = $a_db->query($sql);

		$methods = array();
		$media = array();
		while($row = $a_db->fetchAssoc($res)) {
			$new_methods = unserialize($row["method"]);
		
			foreach($new_methods as $val) {
				if(!in_array($val, $methods)) {
					$methods[] = $val;
				}
			}

			$new_media = unserialize($row["media"]);
			
			foreach($new_media as $val) {
				if(!in_array($val, $media)) {
					$media[] = $val;
				}
			}
		}
		
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		gevCourseUtils::updateMethod($methods,$a_crs_ref_id);
		gevCourseUtils::updateMedia($media,$a_crs_ref_id);
	}

	static private function updateWP($a_crs_ref_id, $a_db) {
		$sql = "SELECT base.id, base.start_date, base.end_date "
		      ." FROM ".self::TABLE_NAME." base"
		      ." JOIN ".self::TABLE_NAME_JOIN1." join1"
		      ." ON base.bb_id = join1.obj_id WHERE join1.is_wp_relevant = 1"
		      ." ORDER BY base.start_date";
		
		$res = $a_db->query($sql);
		$totalMinutes = 0;
		while($row = $a_db->fetchAssoc($res)) {
			$start_date = split(" ",$row["start_date"]);
			$end_date = split(" ",$row["end_date"]);
			
			$start = split(":",$start_date[1]);
			$end = split(":",$end_date[1]);

			$minutes = 0;
			$hours = 0;
			if($end[1] < $start[1]) {
				$minutes = 60 - $start[1] + $end[1];
				$hours = -1;
			} else {
				$minutes = $end[1] - $start[1];
			}
			$hours = $hours + $end[0] - $start[0];
			$totalMinutes += $hours * 60 + $minutes;
		}
		
		$wp = null;
		$wp = round($totalMinutes / self::DURATION_PER_POINT);
		
		if($wp < 0) {
			$wp = 0;
		}

		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		gevCourseUtils::updateWP($wp, $a_crs_ref_id);
	}

	static public function getMaxDurationReached($a_crs_ref_id, $a_crs_request_id, array $a_time) {
		global $ilDB;

		if($a_crs_ref_id == null && $a_crs_request_id === null) {
			throw new Exception("gevCourseBuildingBlockUtils::getMaxDurationReached: Either set course_ref_id or course_request_id.");
		}

		$sql = "SELECT SUM(TIME_TO_SEC(TIMEDIFF(end_date, start_date))/60) as minutes_diff FROM ".self::TABLE_NAME;
		
		if($a_crs_ref_id !== null) {
			$sql .= " WHERE crs_id = ".$a_crs_ref_id. " ORDER BY start_date";
		} else {
			$sql .= " WHERE crs_request_id = ".$a_crs_request_id. " ORDER BY start_date";
		}
		
		$res = $ilDB->query($sql);

		$old_time_diff = 0;
		if($ilDB->numRows($res) > 0) {
			$row = $ilDB->fetchAssoc($res);
			$old_time_diff = $row["minutes_diff"];
			$old_time_diff = (int) $old_time_diff;
		}

		if($old_time_diff > self::MAX_DURATION_MINUTES) {
			return true;
		}

		$start_time = $a_time["start"]["time"];
		$end_time  = $a_time["end"]["time"];

		$start = split(":",$start_time);
		$end = split(":",$end_time);

		$minutes = 0;
		$hours = 0;
		if($end[1] < $start[1]) {
			$minutes = 60 - $start[1] + $end[1];
			$hours = -1;
		} else {
			$minutes = $end[1] - $start[1];
		}
		$hours = $hours + $end[0] - $start[0];
		$totalMinutes = $hours * 60 + $minutes;

		if(($old_time_diff + $totalMinutes) > self::MAX_DURATION_MINUTES) {
			return true;
		}

		return false;
	}

	static public function getMaxDurationReachedOnUpdate($a_crs_ref_id, $a_crs_request_id, array $a_time,$a_updated_crs_building_block_id) {
		global $ilDB;

		if($a_crs_ref_id == null && $a_crs_request_id === null) {
			throw new Exception("gevCourseBuildingBlockUtils::getMaxDurationReached: Either set course_ref_id or course_request_id.");
		}

		$sql = "SELECT id, end_date, start_date FROM ".self::TABLE_NAME;
		
		if($a_crs_ref_id !== null) {
			$sql .= " WHERE crs_id = ".$a_crs_ref_id. " ORDER BY start_date";
		} else {
			$sql .= " WHERE crs_request_id = ".$a_crs_request_id. " ORDER BY start_date";
		}
		$res = $ilDB->query($sql);

		$old_time_diff = 0;
		$dates = array();
		while($row = $ilDB->fetchAssoc($res)) {
			if($row["id"] != $a_updated_crs_building_block_id) {
				$start_date = split(" ",$row["start_date"]);
				$end_date = split(" ",$row["end_date"]);
				$dates[$row["id"]] = array("start_time"=>$start_date[1],"end_time"=>$end_date[1]);
			} else {
				$dates[$row["id"]] = array("start_time"=>$a_time["start"]["time"],"end_time"=>$a_time["end"]["time"]);
			}
		}

		$start_time = "00:00:00";
		$end_time  = "00:00:00";

		foreach ($dates as $key => $value) {
			if($start_time == "00:00:00") {
				$start_time = $value["start_time"];
			}

			if($end_time == "00:00:00") {
				$end_time = $value["end_time"];
			}

			if($start_time > $value["start_time"]) {
				$start_time = $vlaue["start_time"];
			}

			if($end_time < $value["end_time"]) {
				$end_time = $value["end_time"];
			}
		}

		$start = split(":",$start_time);
		$end = split(":",$end_time);

		$minutes = 0;
		$hours = 0;
		if($end[1] < $start[1]) {
			$minutes = 60 - $start[1] + $end[1];
			$hours = -1;
		} else {
			$minutes = $end[1] - $start[1];
		}
		$hours = $hours + $end[0] - $start[0];
		$totalMinutes = $hours * 60 + $minutes;

		if($totalMinutes > self::MAX_DURATION_MINUTES) {
			return true;
		}

		return false;
	}

	static public function getRemainingTime($a_crs_ref_id,$a_crs_request_id) {
		global $ilDB;

		if($a_crs_ref_id === null && $a_crs_request_id === null) {
			throw new Exception("gevCourseBuildingBlockUtils::getRemainingTime: Either set course_ref_id or course_request_id.");
		}

		$sql = "SELECT SUM(TIME_TO_SEC(TIMEDIFF(end_date, start_date))/60) as minutes_diff\n"
			  ."  FROM ".self::TABLE_NAME;
		
		if($a_crs_ref_id !== null) {
			$sql .= " WHERE crs_id = ".$ilDB->quote($a_crs_ref_id, "integer"). " ORDER BY start_date";
		} else {
			$sql .= " WHERE crs_request_id = ".$ilDB->quote($a_crs_request_id, "integer"). " ORDER BY start_date";
		}

		$res = $ilDB->query($sql);

		$old_time_diff = 0;
		if($ilDB->numRows($res) > 0) {
			$row = $ilDB->fetchAssoc($res);
			$old_time_diff = $row["minutes_diff"];
		}

		return self::MAX_DURATION_MINUTES - $old_time_diff;
	}
}
?>