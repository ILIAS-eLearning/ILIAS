<?php
require_once 'Services/Repository/classes/class.ilObjectPlugin.php';
require_once 'Services/GEV/Utils/classes/class.gevUserUtils.php';
require_once 'Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php';

/**
* This class performs all interactions with the database in order to get building block-content. Puplic methods may be accessed in 
* in the GUI via $this->object->{method-name}.
*/
class ilObjBuildingBlockPool extends ilObjectPlugin {
	const TABLE_NAME = "rep_obj_bbpool";
	const PLUGIN_TYPE = "xbbp";

	protected $gDb;
	protected $gUser;
	protected $online;

	public function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);

		global $ilDB, $ilUser;
		$this->gUser = $ilUser;
		$this->user_utils = gevUserUtils::getInstanceByObj($ilUser);
		$this->gDb = $ilDB;
	}

	public function initType() {
		$this->setType(self::PLUGIN_TYPE);
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
		$this->gDb->manipulate($up = "UPDATE ".self::TABLE_NAME." SET ".
			" is_online = ".$this->gDb->quote($this->getOnline(), "integer").
			" WHERE obj_id = ".$this->gDb->quote($this->getId(), "integer")
			);
	}

	public function doRead() {
		$res = $this->gDb->query("SELECT * FROM ".self::TABLE_NAME." ".
			" WHERE obj_id = ".$this->gDb->quote($this->getId(), "integer")
			);

		while ($row = $this->gDb->fetchAssoc($res))
		{
			$this->setOnline($row["is_online"]);
		}
	}

	public function doDelete() {
		//delete from building block table
		gevBuildingBlockUtils::deleteBuildingBlocksByPoolId($this->getId());

		//delete from repository table
		$query = "DELETE FROM ".self::TABLE_NAME." WHERE obj_id = ".$this->getId();
		$this->gDb->manipulate($query);
	}

	protected function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
	{
		gevBuildingBlockUtils::cloneBuildingBlocksFromToByPoolIds($this->getId(), $new_obj->getId());
	}

	public function setOnline($online) {
		assert(is_bool($online));

		$this->online = $online;
	}

	public function getOnline() {
		return $this->online;
	}
}