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

	protected $course_building_block_id = "";
	protected $crs_id = "-1";
	protected $building_block = "";
	protected $start_date = "";
	protected $end_date = "";
	protected $methods = array();
	protected $media = array();
	protected $crs_request_id = -1;

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

	public function getBuldingBlock() {
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
		$sql = "SELECT crs_id, bb_id, start_date, end_date, method, media FROM ".self::TABLE_NAME." WHERE id = ".$this->getId();

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

		$sql = "UPDATE ".self::TABLE_NAME." SET bb_id = '".$this->getBuldingBlock()->getId()."'"
									   .", start_date = '".$this->getStartDate()."'"
									   .", end_date = '".$this->getEndDate()."'"
									   .", method = '".$method_serial."'"
									   .", media = '".$media_serial."'"
									   .", last_change_user = ".$this->ilUser->getId().""
									   .", last_change_date = NOW()"
									   ." WHERE id = ".$this->getId();

		$this->db->manipulate($sql);

		self::courseUpdates($this->getCrsId(),$this->db);
	}

	public function save() {
		/*$method_serial = preg_replace('/\"/','\\\"',serialize($this->getMethods()));
		$media_serial = preg_replace('/\"/','\\\"',serialize($this->getMedia()));*/

		$method_serial = serialize($this->getMethods());
		$media_serial = serialize($this->getMedia());

		$sql = "INSERT INTO ".self::TABLE_NAME.""
				." (id, crs_id, bb_id, start_date, end_date, method, media, last_change_user, last_change_date, crs_request_id)"
				." VALUES ("
					.$this->getId().""
					.",'".$this->getCrsId()."'"
					.",'".$this->getBuldingBlock()->getId()."'"
					.",'".$this->getStartDate()."'"
					.",'".$this->getEndDate()."'"
					.",'".$method_serial."'"
					.",'".$media_serial."'"
					.",".$this->ilUser->getId().""
					.", NOW()"
					.",".$this->getCourseRequestId().""
					.")";

		$this->db->manipulate($sql);

		self::courseUpdates($this->getCrsId(),$this->db);
	}

	public function delete() {
		$query = "DELETE FROM ".self::TABLE_NAME." WHERE id = ".$this->db->quote($this->getId(),"integer");
		$this->db->manipulate($query);

		self::courseUpdates($this->getCrsId(),$this->db);
	}

	static public function getAllCourseBuildingBlocks($a_crs_ref_id,$a_request_id = null) {
		global $ilDB;

		$sql = "SELECT"
			  ." base.id, base.crs_id, base.bb_id, base.start_date, base.end_date, base.method, base.media,"
			  ." join1.title, join1.learning_dest, join1.content"
			  ." FROM ".self::TABLE_NAME." as base"
			  ." JOIN ".self::TABLE_NAME_JOIN1." as join1"
			  ."	ON  base.bb_id = join1.obj_id";
		
		if($a_crs_ref_id != -1) {
			$sql .= " WHERE base.crs_id = ".$a_crs_ref_id;
		} else {
			if($a_request_id !== null) {
				$sql .= " WHERE base.crs_request_id = ".$a_reques_id;
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

	static public function getDeleteLink($a_id,$a_crs_request_id) {
		global $ilCtrl,$ilUser;

		$ilCtrl->setParameterByClass("gevDecentralTrainingCourseBuildingBlockGUI", "id", $a_id);
		$ilCtrl->setParameterByClass("gevDecentralTrainingCourseBuildingBlockGUI", "crs_request_id", $a_crs_request_id);
		$lnk = $ilCtrl->getLinkTargetByClass("gevDecentralTrainingCourseBuildingBlockGUI", "delete");
		$ilCtrl->clearParametersByClass("gevDecentralTrainingCourseBuildingBlockGUI");
		return $lnk;
	}

	static public function getEditLink($a_id,$a_crs_request_id) {
		global $ilCtrl,$ilUser;

		$ilCtrl->setParameterByClass("gevDecentralTrainingCourseBuildingBlockGUI", "id", $a_id);
		$ilCtrl->setParameterByClass("gevDecentralTrainingCourseBuildingBlockGUI", "crs_request_id", $a_crs_request_id);
		$lnk = $ilCtrl->getLinkTargetByClass("gevDecentralTrainingCourseBuildingBlockGUI", "edit");
		$ilCtrl->clearParametersByClass("gevDecentralTrainingCourseBuildingBlockGUI");
		return $lnk;
	}

	static public function updateCrsBuildungBlocksCrsIdByCrsRequestId($a_crs_id, $a_crs_request_id) {
		global $ilDB;

		$sql = "UPDATE ".self::TABLE_NAME." SET crs_id = ".$a_crs_id.", crs_request_id = NULL WHERE crs_request_id = ".$a_crs_request_id;
		$ilDB->manipulate($sql);
	}

	static public function courseUpdates($a_crs_ref_id, $a_db = null) {
		if($a_crs_ref_id == -1 && $a_crs_ref_id === null) {
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
		$sql = "SELECT method, media FROM ".self::TABLE_NAME." WHERE crs_id = ".$a_crs_ref_id;
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
		$sql = "SELECT MIN(start_date) as start_date, MAX(end_date) as end_date FROM ".self::TABLE_NAME." WHERE crs_id = ".$a_crs_ref_id. " ORDER BY start_date";
		$res = $a_db->query($sql);

		$wp = null;

		if($a_db->numRows($res) > 0) {
			$row = $a_db->fetchAssoc($res);

			$start_date = split(" ",$row["start_date"]);
			$end_date = split(" ",$row["end_date"]);

			$start_time = split(":",$start_date[1]);
			$end_time = split(":",$end_date[1]);

			$minutes = 0;
			$hours = 0;
			if($end_time[1] < $start_time[1]) {
				$minutes = 60 - $start_time[1] + $end_time[1];
				$hours = -1;
			} else {
				$minutes = $end_time[1] - $start_time[1];
			}
			$hours = $hours + $end_time[0] - $start_time[0];
			$totalMinutes = $hours * 60 + $minutes;

			$wp = round($totalMinutes / self::DURATION_PER_POINT);
		}
		
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		gevCourseUtils::updateWP($wp, $a_crs_ref_id);
	}
}
?>