<?php
require_once 'Services/Repository/classes/class.ilObjectPlugin.php';
require_once 'Services/GEV/Utils/classes/class.gevUserUtils.php';

/**
* This class performs all interactions with the database in order to get building block-content. Puplic methods may be accessed in 
* in the GUI via $this->object->{method-name}.
*/
class ilObjBuildingBlockPool extends ilObjectPlugin {
	const TABLE_NAME = "rep_obj_bbpool";
	const BUILDING_BLOCK_TABLE = "dct_building_block";

	protected $gDb;
	protected $gUser;

	public function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);

		global $ilDB, $ilUser;
		$this->gUser = $ilUser;
		$this->user_utils = gevUserUtils::getInstanceByObj($ilUser);
		$this->gDb = $ilDB;
	}

	public function initType() {
		$this->setType("xbbp");
	}

	/***************************
	* Save, Update, Read
	***************************/

	public function doCreate() {
		$query = "INSERT INTO ".self::TABLE_NAME."\n"
				." (obj_id, is_online, last_changed_date, last_changed_user)\n"
				." VALUES (".$this->getId().",1,NOW(),".$this->gUser->getId().")";

		$this->gDb->manipulate($query);
	}

	public function doUpdate() {

	}

	public function doRead() {

	}

	public function doDelete() {
		//delete from building block table
		$query = "UPDATE ".self::BUILDING_BLOCK_TABLE." SET is_deleted = 1 WHERE pool_id = ".$this->getId();
		$this->gDb->manipulate($query);

		//delete from repository table
		$query = "DELETE FROM ".self::TABLE_NAME." WHERE obj_id = ".$this->getId();
		$this->gDb->manipulate($query);
	}

	protected function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
	{
		$insert = "INSERT INTO ".self::BUILDING_BLOCK_TABLE."\n"
				." (obj_id, title, content, target, is_wp_relevant, is_active, is_deleted, last_change_user
					, last_change_date, gdv_topic, training_categories, topic, dbv_topic, move_to_course, pool_id)"
				." VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

		$insert_types = array("integer","text","text","text","integer","integer","integer","integer","timestamp","text","text","text","text","integer","integer");

		$statement = $this->gDb->prepare($insert, $insert_types);

		$query = "SELECT title, content, target, is_wp_relevant, is_active, is_deleted, last_change_user
					, last_change_date, gdv_topic, training_categories, topic, dbv_topic, move_to_course\n"
					." FROM ".self::BUILDING_BLOCK_TABLE."\n"
					." WHERE pool_id = ".$this->gDb->quote($this->getId(),"integer");

		$res = $this->gDb->query($query);

		while($row = $this->gDb->fetchAssoc($res)) {
			$next_id = $this->gDb->nextId(self::BUILDING_BLOCK_TABLE);
			array_unshift($row, $next_id);
			array_push($row,$new_obj->getId());
			$this->gDb->execute($statement,array_values($row));
		}
	}
}