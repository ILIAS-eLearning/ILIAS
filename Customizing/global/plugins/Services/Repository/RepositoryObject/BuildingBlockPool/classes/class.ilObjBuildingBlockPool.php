<?php
require_once 'Services/Repository/classes/class.ilObjectPlugin.php';
require_once 'Services/GEV/Utils/classes/class.gevUserUtils.php';

/**
* This class performs all interactions with the database in order to get building block-content. Puplic methods may be accessed in 
* in the GUI via $this->object->{method-name}.
*/
class ilObjBuildingBlockPool extends ilObjectPlugin {
	const TABLE_NAME = "rep_obj_bbpool";

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
		$query = "DELETE FROM ".self::BUILDING_BLOCK_TABLE." WHERE pool_id = ".$this->getId();
		$this->gDB->manipulate($query);

		//delete from repository table
		$query = "DELETE FROM ".self::TABLE_NAME." WHERE obj_id = ".$this->getId();
		$this->gDb->manipulate($query);
	}

	public function doClone() {
		$query = "INSERT INTO ".self::BUILDING_BLOCK_TABLE."\n"
				." (title,content,target,is_wp_relevant,is_active,is_deleted,gdv_topic,traing_categories,topic, dbv_topic,move_to_course)\n"
				."     SELECT title,content,target,is_wp_relevant,is_active,is_deleted,gdv_topic,traing_categories,topic, dbv_topic,move_to_course \n"
				."     FROM ".self::BUILDING_BLOCK_TABLE." WHERE pool_id = ".$this->gDB->quote($this->getId());
	}
}