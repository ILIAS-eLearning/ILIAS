<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for generali users.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/
class gevBuildingBlockUtils {
	static protected $instances = array();
	const TABLE_NAME = "dct_building_block";

	protected $building_block_id = "";
	protected $title = "";
	protected $content = "";
	protected $target = "";
	protected $is_wp_relevant = false;
	protected $is_active = false;
	protected $gdv_topic;
	protected $training_categories;
	protected $topic;
	protected $pool_id;

	static $possible_topics = array("Organisation" => "Organisation"
						   ,"Alterssicherung" => "Alterssicherung"
						   ,"Einkommenssicherung" => "Einkommenssicherung"
						   ,"Vermögenssicherung" => "Vermögenssicherung"
						   ,"Konzepte ohne Gesundheitsprüfung" => "Konzepte ohne Gesundheitsprüfung"
						   ,"Betriebliche Altersvorsorge" => "Betriebliche Altersvorsorge"
						   ,"Immobilienfinanzierung" => "Immobilienfinanzierung"
						   ,"Gewerbe" => "Gewerbe"
						   ,"Technische Versicherung" => "Technische Versicherung"
						   ,"Transportversicherung" => "Transportversicherung"
						   ,"AdcoCard" => "AdcoCard"
						   ,"Highlights Privatkunden" => "Highlights Privatkunden"
						   ,"myGenerali" => "myGenerali"
						   ,"Heilwesen" => "Heilwesen"
						   ,"KFZ-Versicherung" => "KFZ-Versicherung");

	protected function __construct($a_building_block_id = null) {
		global $ilDB, $ilUser;

		$this->db = $ilDB;
		$this->ilUser = $ilUser;
		
		if($a_building_block_id === null) {
			$a_building_block_id = $this->db->nextId("dct_building_block");
		}

		$this->building_block_id = $a_building_block_id;
		
	}

	public function getInstance($a_building_block_id = null) {
		if ($a_building_block_id !== null && array_key_exists($a_building_block_id, self::$instances)) {
			return self::$instances[$a_building_block_id];
		}
		
		self::$instances[$a_building_block_id] = new gevBuildingBlockUtils($a_building_block_id);
		return self::$instances[$a_building_block_id];
	}

	public function getId() {
		return $this->building_block_id;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setTitle($a_title) {
		$this->title = $a_title;
	}

	public function getContent() {
		return $this->content;
	}

	public function setContent($a_content) {
		$this->content = $a_content;
	}

	public function getTarget() {
		return $this->target;
	}

	public function setTarget($a_target) {
		$this->target = $a_target;
	}

	public function isWPRelevant() {
		return $this->is_wp_relevant;
	}

	public function setIsWPRelevant($a_is_wp_relevant) {
		$this->is_wp_relevant = $a_is_wp_relevant;
	}

	public function isActive() {
		return $this->is_active;
	}

	public function setIsActive($a_is_active) {
		$this->is_active = $a_is_active;
	}

	public function setGDVTopic($gdv_topic) {
		$this->gdv_topic = $gdv_topic;
	}

	public function getGDVTopic() {
		return $this->gdv_topic;
	}

	public function setTrainingCategories(array $training_categories) {
		$this->training_categories = $training_categories;
	}

	public function getTrainingCategories() {
		return $this->training_categories;
	}

	public function getTrainingCategoriesAsString() {
		return implode($this->training_categories);
	}

	public function getTopic() {
		return $this->topic;
	}

	public function setTopic($topic) {
		$this->topic = $topic;
	}

	public function getDBVTopic() {
		return $this->dbv_topic;
	}

	public function setDBVTopic($dbv_topic) {
		$this->dbv_topic = $dbv_topic;
	}

	public function setMoveToCourse($move_to_course) {
		$this->move_to_course = $move_to_course;
	}

	public function getMoveToCourse() {
		return $this->move_to_course;
	}

	public function getMoveToCourseText() {
		return ($this->move_to_course) ? "Ja" : "Nein";
	}

	public function getPoolId() {
		return $this->pool_id;
	}

	public function setPoolId($pool_id) {
		$this->pool_id = $pool_id;
	}

	public function loadData() {
		$sql = "SELECT obj_id, title, content, target, is_wp_relevant, is_active, gdv_topic, training_categories,topic, dbv_topic, move_to_course\n".
			   "  FROM ".self::TABLE_NAME.
			   " WHERE obj_id = ".$this->db->quote($this->getId(), "integer");

		$res = $this->db->query($sql);
		
		if($this->db->numRows($res) > 0) {
			$row = $this->db->fetchAssoc($res);

			$this->title = $row["title"];
			$this->content = $row["content"];
			$this->target = $row["target"];
			$this->is_wp_relevant = $row["is_wp_relevant"];
			$this->is_active = $row["is_active"];
			$this->gdv_topic = $row["gdv_topic"];
			$this->training_categories = unserialize($row["training_categories"]);
			$this->topic = $row["topic"];
			$this->dbv_topic = $row["dbv_topic"];
			$this->move_to_course = $row["move_to_course"];
		}
	}

	public function update() {
		$sql = "UPDATE ".self::TABLE_NAME
			  ."   SET title = ".$this->db->quote($this->getTitle(), "text")."\n"
			  ."     , content = ".$this->db->quote($this->getContent(), "text")."\n"
			  ."     , target = ".$this->db->quote($this->getTarget(), "text")."\n"
			  ."     , is_wp_relevant = ".$this->db->quote($this->isWPRelevant(), "integer")."\n"
			  ."     , is_active = ".$this->db->quote($this->isActive(), "integer")."\n"
			  ."     , last_change_user = ".$this->db->quote($this->ilUser->getId(), "integer")."\n"
			  ."     , last_change_date = NOW()\n"
			  ."     , gdv_topic = ".$this->db->quote($this->getGDVTopic(), "text")."\n"
			  ."     , training_categories = ".$this->db->quote(serialize($this->getTrainingCategories()), "text")."\n"
			  ."     , topic = ".$this->db->quote($this->getTopic(), "text")."\n"
			  ."     , dbv_topic = ".$this->db->quote($this->getDBVTopic(), "text")."\n"
			  ."     , move_to_course = ".$this->db->quote($this->getMoveToCourse(), "integer")."\n"
			  ." WHERE obj_id = ".$this->db->quote($this->getId(), "integer");



		$this->db->manipulate($sql);

		return;
	}

	public function save() {
		
		$isWPRelevant = (!$this->isWPRelevant()) ? "0" : "1";
		$isActive = ($this->isActive() === "") ? "0" : "1";

		$sql = "INSERT INTO ".self::TABLE_NAME.""
			  ." (obj_id, title, content, target, is_wp_relevant, is_active, last_change_user\n"
			  .", last_change_date, is_deleted, gdv_topic, training_categories, topic, dbv_topic, move_to_course\n"
			  .", pool_id)"
			  ." VALUES (".$this->db->quote($this->getId(), "integer")."\n"
			  ."        ,".$this->db->quote($this->getTitle(), "text")."\n"
			  ."        ,".$this->db->quote($this->getContent(), "text")."\n"
			  ."        ,".$this->db->quote($this->getTarget(), "text")."\n"
			  ."        ,".$this->db->quote($isWPRelevant, "integer")."\n"
			  ."        ,".$this->db->quote($isActive, "integer")."\n"
			  ."        ,".$this->db->quote($this->ilUser->getId(), "integer")."\n"
			  ."        , NOW()\n"
			  ."        , 0\n"
			  ."        ,".$this->db->quote($this->getGDVTopic(), "text")."\n"
			  ."        ,".$this->db->quote(serialize($this->getTrainingCategories()), "text")."\n"
			  ."        ,".$this->db->quote($this->getTopic(), "text")."\n"
			  ."        ,".$this->db->quote($this->getDBVTopic(), "text")."\n"
			  ."        ,".$this->db->quote($this->getMoveToCourse(), "integer")."\n"
			  ."        ,".$this->db->quote($this->getPoolId(), "integer")."\n"
			  .")";
		
		$this->db->manipulate($sql);

		return;
	}

	static public function getAllBuildingBlocks($a_search_opts,$a_order, $a_order_direction, $offset = null, $limit = null, $user_id = null) {
		global $ilDB;

		$bb_pool_where = "";
		if($user_id !== null && !gevUserUtils::getInstance($user_id)->isSystemAdmin()) {
			$bb_pools = $bb_pool = gevUserUtils::getBuildingBlockPoolsUserHasPermissionsTo($user_id, array("visible", gevSettings::EDIT_BUILDING_BLOCKS));
			$bb_pool_where .= " AND ".$ilDB->in("bb.pool_id", $bb_pools, false, "integer");
		}

		$add_where = self::createAdditionalWhere($a_search_opts);
		$sql = "SELECT bb.obj_id, bb.title, bb.content, bb.target\n"
			  ."     , bb.is_wp_relevant, bb.is_active, bb.gdv_topic, bb.training_categories, bb.topic, bb.dbv_topic\n"
			  ."     , usr.login, bb.last_change_date, bb.move_to_course\n"
			  ."  FROM ".self::TABLE_NAME." bb\n"
			  ."  JOIN usr_data usr ON usr_id = last_change_user\n"
			  ."  WHERE is_deleted = ".$ilDB->quote(0,"integer")."\n";
		$sql .= $bb_pool_where;
		$sql .= $add_where;
		$sql .= " ORDER BY title";
		if($a_order !== null) {
			$sql .= " ORDER BY ".$a_order." ".$a_order_direction;
		}

		if($limit !== null) {
			$sql .= " LIMIT ".$limit;
		}

		if($offset !== null) {
			$sql .= " OFFSET ".$offset;
		}

		$ret = array();
		$res = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($res)) {
			$row["training_categories"] = unserialize($row["training_categories"]);
			$ret[] = $row;
		}

		return $ret;
	}

	static public function getAllBuildingBlocksForCopy($pool_id,$a_order, $a_order_direction, $offset = null, $limit = null) {
		global $ilDB;

		$sql = "SELECT obj_id, title, content, target\n"
			  ."     , is_wp_relevant, is_active, gdv_topic, training_categories, topic, dbv_topic\n"
			  ."	 move_to_course\n"
			  ."  FROM ".self::TABLE_NAME."\n"  
			  ."  WHERE is_deleted = ".$ilDB->quote(0,"integer")."\n"
			  ."  AND pool_id != ".$ilDB->quote($pool_id, "integer");

		if($a_order !== null) {
			$sql .= " ORDER BY ".$a_order." ".$a_order_direction;
		}

		if($limit !== null) {
			$sql .= " LIMIT ".$limit;
		}

		if($offset !== null) {
			$sql .= " OFFSET ".$offset;
		}

		$ret = array();
		$res = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($res)) {
			$row["training_categories"] = unserialize($row["training_categories"]);
			$ret[] = $row;
		}

		return $ret;
	}

	static public function countAllBuildingBlocks($a_search_opts, $user_id = null) {
		global $ilDB;

		$bb_pool_where = "";
		if($user_id !== null && !gevUserUtils::getInstance($user_id)->isSystemAdmin()) {
			$bb_pools = $bb_pool = gevUserUtils::getBuildingBlockPoolsUserHasPermissionsTo($user_id, array("visible", gevSettings::EDIT_BUILDING_BLOCKS));
			$bb_pool_where .= " AND ".$ilDB->in("pool_id", $bb_pools, false, "integer");
		}

		$add_where = self::createAdditionalWhere($a_search_opts);
		$sql = "SELECT count(obj_id) as cnt\n"
			  ."  FROM ".self::TABLE_NAME."\n"
			  ."  WHERE is_deleted = ".$ilDB->quote(0,"integer")."\n";
		$sql .= $bb_pool_where;
		$sql .= $add_where;

		$ret = array();
		$res = $ilDB->query($sql);
		$row = $ilDB->fetchAssoc($res);

		return $row["cnt"];
	}

		static public function countAllBuildingBlocksForCopy($pool_id) {
		global $ilDB;

		$add_where = self::createAdditionalWhere($a_search_opts);
		$sql = "SELECT count(obj_id) as cnt\n"
			  ."  FROM ".self::TABLE_NAME."\n"
			  ."  WHERE is_deleted = ".$ilDB->quote(0,"integer")."\n"
			  ."  AND pool_id != ".$ilDB->quote($pool_id, "integer");
		$sql .= $add_where;

		$ret = array();
		$res = $ilDB->query($sql);
		$row = $ilDB->fetchAssoc($res);

		return $row["cnt"];
	}

	static private function createAdditionalWhere($a_search_opts) {
		global $ilDB;
		$ret = "";

		foreach ($a_search_opts as $key => $value) {
			switch($key) {
				case "title":
				case "content":
				case "target":
					$ret .= " AND ".$key." LIKE ".$ilDB->quote("%".$value."%", "text");
					break;
				case "is_wp_relevant":
				case "is_active":
					if($value != -1) {
						if($value == "ja") {
							$ret .= " AND ".$key." = 1";
						} elseif($value == "nein"){
							$ret .= " AND ".$key." = 0";
						}
					}
					break;
				case "pool_id":
					$ret .= " AND pool_id = ".$ilDB->quote($value,"integer");
					break;
				default:
					throw new ilException("Unknown search option: $key");
			}
			
		}

		return $ret;
	}

	static public function deleteBuildingBlock($a_obj_id) {
		global $ilDB;

		$query = "UPDATE ".self::TABLE_NAME." SET is_deleted = 1 WHERE obj_id = ".$ilDB->quote($a_obj_id,"integer");
		$ilDB->manipulate($query);

		return;
	}

	static public function getDeleteLink($a_obj_id) {
		global $ilCtrl,$ilUser;

		$ilCtrl->setParameterByClass("gevDecentralTrainingBuildingBlockAdminGUI", "obj_id", $a_obj_id);
		$lnk = $ilCtrl->getLinkTargetByClass("gevDecentralTrainingBuildingBlockAdminGUI", "delete");
		$ilCtrl->clearParametersByClass("gevDecentralTrainingBuildingBlockAdminGUI");
		return $lnk;
	}

	static public function getEditLink($a_obj_id) {
		global $ilCtrl,$ilUser;

		$ilCtrl->setParameterByClass("gevDecentralTrainingBuildingBlockAdminGUI", "obj_id", $a_obj_id);
		$lnk = $ilCtrl->getLinkTargetByClass("gevDecentralTrainingBuildingBlockAdminGUI", "edit");
		$ilCtrl->clearParametersByClass("gevDecentralTrainingBuildingBlockAdminGUI");
		return $lnk;
	}

	static function getPossibleBuildingBlocks() {
		global $ilDB;

		$sql = "SELECT obj_id, title FROM ".self::TABLE_NAME." WHERE is_deleted = 0 AND is_active = 1";
		$res = $ilDB->query($sql);

		$ret = array();

		while ($row = $ilDB->fetchAssoc($res)) {
			$ret[$row["obj_id"]] = $row["title"];
		}
		
		return $ret;
	}

	static function getPossibleBuildingBlocksGroupByTopic($user_id) {
		global $ilDB;

		$bb_pool = gevUserUtils::getBuildingBlockPoolsUserHasPermissionsTo($user_id, array(gevSettings::USE_BUILDING_BLOCK, "visible"));

		$query = "SELECT obj_id, title, topic\n"
				." FROM ".self::TABLE_NAME."\n"
				." WHERE is_deleted = 0\n"
				."     AND is_active = 1\n"
				."     AND ".$ilDB->in("pool_id", $bb_pool, false, "integer")."\n"
				." ORDER BY topic, title";
		$res = $ilDB->query($query);

		$ret = array();
		$curr_topic = "";
		$bla = array();

		while ($row = $ilDB->fetchAssoc($res)) {
			if($curr_topic != $row["topic"] && $curr_topic != "") {
				$ret[$curr_topic] = $bla;
				$bla = array();
				$curr_topic = $row["topic"];
			}

			if($curr_topic == "") {
				$curr_topic = $row["topic"];
			}

			$bla[$row["obj_id"]] = $row["title"];
		}

		if(!empty($bla)) {
			$ret[$curr_topic] = $bla;
		}
		
		return $ret;
	}

	static function getAllPossibleTopics() {
		return self::$possible_topics;
	}

	static function getAllInBuildingBlocksSelectedTopics($user_id) {
		global $ilDB;

		$bb_pool = gevUserUtils::getBuildingBlockPoolsUserHasPermissionsTo($user_id, array(gevSettings::USE_BUILDING_BLOCK, "visible"));

		$sql = "SELECT DISTINCT topic FROM ".self::TABLE_NAME." WHERE is_deleted = 0 AND is_active = 1 AND ".$ilDB->in("pool_id", $bb_pool, false, "integer")."\n";
		$res = $ilDB->query($sql);

		$ret = array();

		while ($row = $ilDB->fetchAssoc($res)) {
			$ret[$row["topic"]] = $row["topic"];
		}
		
		return $ret;
	}

	static function getPossibleBuildingBlocksByTopicName($topic, $user_id) {
		global $ilDB;
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		$bb_pool = gevUserUtils::getBuildingBlockPoolsUserHasPermissionsTo($user_id, array(gevSettings::USE_BUILDING_BLOCK, "visible"));

		$sql = "SELECT obj_id, title, topic\n"
			   ." FROM ".self::TABLE_NAME."\n"
			   ." WHERE is_deleted = 0\n"
			   ."    AND is_active = 1\n"
			   ."    AND ".$ilDB->in("pool_id", $bb_pool, false, "integer")."\n";

		if($topic != "0") {
			$sql .= " AND topic = ".$ilDB->quote($topic,"text")."\n";
			$sql .= " ORDER BY title";
		} else {
			$sql .= " ORDER BY topic, title";
		}

		$res = $ilDB->query($sql);

		$ret = array();
		$curr_topic = "";
		$bla = array();

		while ($row = $ilDB->fetchAssoc($res)) {
			if($curr_topic != $row["topic"] && $curr_topic != "") {
				$ret[$curr_topic] = $bla;
				$bla = array();
				$curr_topic = $row["topic"];
			}

			if($curr_topic == "") {
				$curr_topic = $row["topic"];
			}

			$bla[$row["obj_id"]] = $row["title"];
		}

		if(!empty($bla)) {
			$ret[$curr_topic] = $bla;
		}
		
		return $ret;
	}

	static function getBuildingBlockInfosById($id) {
		global $ilDB;

		$sql = "SELECT content, target, if(is_wp_relevant,'Ja','Nein') AS wp"
			   ." FROM ".self::TABLE_NAME."\n"
			   ." WHERE obj_id = ".$ilDB->quote($id, "integer");

		$res = $ilDB->query($sql);

		if($ilDB->numRows($res) > 0) {
			return $ilDB->fetchAssoc($res);
		}

		return array("content" => "", "target" => "");
	}

	static public function getMoveToCourseOptions() {
		return array("Ja"=>"Ja","Nein"=>"Nein");
	}

	static public function copyBuildingBlocksTo($bb_ids, $target_pool_id) {
		foreach ($bb_ids as $bb_id) {
			$bb_utils = gevBuildingBlockUtils::getInstance($bb_id);
			$bb_utils->loadData();
			$bb_utils->copyTo($target_pool_id);
		}
	}

	protected function copyTo($target_pool_id) {
		$cpy_bb_utils = gevBuildingBlockUtils::getInstance();

		$cpy_bb_utils->setTitle($this->getTitle());
		$cpy_bb_utils->setContent($this->getContent());
		$cpy_bb_utils->setTarget($this->getTarget());
		$cpy_bb_utils->setIsWPRelevant($this->isWPRelevant());
		$cpy_bb_utils->setIsActive(($this->isActive() != "1") ? "" : $this->isActive());
		$cpy_bb_utils->setGDVTopic($this->getGDVTopic());

		$cpy_bb_utils->setTrainingCategories($this->getTrainingCategories());
		$cpy_bb_utils->setTopic($this->getTopic());
		$cpy_bb_utils->setDBVTopic($this->getDBVTopic());
		$cpy_bb_utils->setMoveToCourse($this->getMoveToCourse());
		$cpy_bb_utils->setPoolId($target_pool_id);

		$cpy_bb_utils->save();
	}
}
?>