<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Services/Object/classes/class.ilObject2.php');
require_once('class.ilDataCollectionTable.php');
require_once('class.ilDataCollectionCache.php');

/**
 * Class ilObjDataCollection
 *
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version $Id: class.ilObjFolder.php 25528 2010-09-03 10:37:11Z smeyer $
 *
 * @extends ilObject2
 */
class ilObjDataCollection extends ilObject2 {

	/**
	 * @var int
	 */
	protected $main_table_id;


	public function initType() {
		$this->type = "dcl";
	}


	public function doRead() {
		global $ilDB;

		$result = $ilDB->query("SELECT * FROM il_dcl_data WHERE id = " . $ilDB->quote($this->getId(), "integer"));

		$data = $ilDB->fetchObject($result);
		$this->setMainTableId($data->main_table_id);
		$this->setOnline($data->is_online);
		$this->setRating($data->rating);
		$this->setApproval($data->approval);
		$this->setPublicNotes($data->public_notes);
		$this->setNotification($data->notification);
	}


	protected function doCreate() {
		global $ilDB, $ilLog;

		$ilLog->write('doCreate');

		//Create Main Table - The title of the table is per default the title of the data collection object
		require_once('./Modules/DataCollection/classes/class.ilDataCollectionTable.php');
		$main_table = ilDataCollectionCache::getTableCache();
		$main_table->setObjId($this->getId());
		$main_table->setTitle($this->getTitle());
		$main_table->setAddPerm(1);
		$main_table->setEditPerm(1);
		$main_table->setDeletePerm(1);
		$main_table->setEditByOwner(1);
		$main_table->setLimited(0);
		$main_table->doCreate();

		$ilDB->insert("il_dcl_data", array(
			"id" => array( "integer", $this->getId() ),
			"main_table_id" => array( "integer", (int)$main_table->getId() ),
			"is_online" => array( "integer", (int)$this->getOnline() ),
			"rating" => array( "integer", (int)$this->getRating() ),
			"public_notes" => array( "integer", (int)$this->getPublicNotes() ),
			"approval" => array( "integer", (int)$this->getApproval() ),
			"notification" => array( "integer", (int)$this->getNotification() ),
		));
		$this->setMainTableId($main_table->getId());
	}


	/**
	 * @return bool
	 */
	public function doClone() {
		return false;
	}


	protected function doDelete() {
		global $ilDB;

		foreach ($this->getTables() as $table) {
			$table->doDelete(true);
		}

		$query = "DELETE FROM il_dcl_data WHERE id = " . $ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);
	}


	public function doUpdate() {
		global $ilDB;

		$ilDB->update("il_dcl_data", array(
			"id" => array( "integer", $this->getId() ),
			"main_table_id" => array( "integer", (int)$this->getMainTableId() ),
			"is_online" => array( "integer", (int)$this->getOnline() ),
			"rating" => array( "integer", (int)$this->getRating() ),
			"public_notes" => array( "integer", (int)$this->getPublicNotes() ),
			"approval" => array( "integer", (int)$this->getApproval() ),
			"notification" => array( "integer", (int)$this->getNotification() ),
		), array(
			"id" => array( "integer", $this->getId() )
		));
	}


	/**
	 * @param      $a_action
	 * @param      $a_table_id
	 * @param null $a_record_id
	 */
	public static function sendNotification($a_action, $a_table_id, $a_record_id = NULL) {
		global $ilUser, $ilAccess;

		// If coming from trash, never send notifications and don't load dcl Object
		if ($_GET['ref_id'] == SYSTEM_FOLDER_ID) {
			return;
		}

		$dclObj = new ilObjDataCollection($_GET['ref_id']);

		if ($dclObj->getNotification() != 1) {
			return;
		}
		$obj_table = ilDataCollectionCache::getTableCache($a_table_id);
		$obj_dcl = $obj_table->getCollectionObject();

		// recipients
		require_once('./Services/Notification/classes/class.ilNotification.php');
		$users = ilNotification::getNotificationsForObject(ilNotification::TYPE_DATA_COLLECTION, $obj_dcl->getId(), true);
		if (!sizeof($users)) {
			return;
		}

		ilNotification::updateNotificationTime(ilNotification::TYPE_DATA_COLLECTION, $obj_dcl->getId(), $users);

		//FIXME  $_GET['ref_id]
		require_once('./Services/Link/classes/class.ilLink.php');
		$link = ilLink::_getLink($_GET['ref_id']);

		// prepare mail content
		// use language of recipient to compose message
		require_once('./Services/Language/classes/class.ilLanguageFactory.php');

		// send mails
		require_once('./Services/Mail/classes/class.ilMail.php');
		require_once('./Services/User/classes/class.ilObjUser.php');
		require_once('./Services/Language/classes/class.ilLanguageFactory.php');
		require_once('./Services/User/classes/class.ilUserUtil.php');
		require_once('./Services/User/classes/class.ilUserUtil.php');
		require_once('./Modules/DataCollection/classes/class.ilDataCollectionTable.php');

		foreach (array_unique($users) as $idx => $user_id) {
			// the user responsible for the action should not be notified
			// FIXME  $_GET['ref_id]
			if ($user_id != $ilUser->getId() && $ilAccess->checkAccessOfUser($user_id, 'read', '', $_GET['ref_id'])) {
				// use language of recipient to compose message
				$ulng = ilLanguageFactory::_getLanguageOfUser($user_id);
				$ulng->loadLanguageModule('dcl');

				$subject = sprintf($ulng->txt('dcl_change_notification_subject'), $obj_dcl->getTitle());
				// update/delete
				$message = $ulng->txt("dcl_hello") . " " . ilObjUser::_lookupFullname($user_id) . ",\n\n";
				$message .= $ulng->txt('dcl_change_notification_dcl_' . $a_action) . ":\n\n";
				$message .= $ulng->txt('obj_dcl') . ": " . $obj_dcl->getTitle() . "\n\n";
				$message .= $ulng->txt('dcl_table') . ": " . $obj_table->getTitle() . "\n\n";
				$message .= $ulng->txt('dcl_record') . ":\n";
				$message .= "------------------------------------\n";
				if ($a_record_id) {
					$record = ilDataCollectionCache::getRecordCache($a_record_id);
					if (!$record->getTableId()) {
						$record->setTableId($a_table_id);
					}
					//					$message .= $ulng->txt('dcl_record_id').": ".$a_record_id.":\n";
					$t = "";
					foreach ($record->getTable()->getVisibleFields() as $field) {
						if ($record->getRecordField($field->getId())) {
							$t .= $field->getTitle() . ": " . $record->getRecordField($field->getId())->getPlainText() . "\n";
						}
					}
					$message .= $t . "\n";
				}
				$message .= "------------------------------------\n";
				$message .= $ulng->txt('dcl_changed_by') . ": " . $ilUser->getFullname() . " " . ilUserUtil::getNamePresentation($ilUser->getId())
					. "\n\n";
				$message .= $ulng->txt('dcl_change_notification_link') . ": " . $link . "\n\n";

				$message .= $ulng->txt('dcl_change_why_you_receive_this_email');

				$mail_obj = new ilMail(ANONYMOUS_USER_ID);
				$mail_obj->appendInstallationSignature(true);
				$mail_obj->sendMail(ilObjUser::_lookupLogin($user_id), "", "", $subject, $message, array(), array( "system" ));
			} else {
				unset($users[$idx]);
			}
		}
	}


	/**
	 * @param $a_val
	 */
	public function setMainTableId($a_val) {
		$this->main_table_id = $a_val;
	}


	/**
	 * @return mixed
	 */
	public function getMainTableId() {
		return $this->main_table_id;
	}


	/**
	 * Clone DCL
	 *
	 * @param ilObjDataCollection new object
	 * @param int                 target ref_id
	 * @param int                 copy id
	 *
	 * @return ilObjPoll
	 */
	public function doCloneObject(ilObjDataCollection $new_obj, $a_target_id, $a_copy_id = 0) {
		$new_obj->cloneStructure($this->getRefId());

		return $new_obj;
	}

	//TODO: Find better way to copy data (including references)
	/*public function doCloneObject(ilObjDataCollection $new_obj, $a_target_id, $a_copy_id = 0) {
		//$new_obj->delete();
		$created_new_id = $new_obj->getId();
		$obj_id = $this->getId();

		$exp = new ilExport();
		$exp->exportObject($this->getType(), $obj_id, "5.0.0");

		$file_name = substr(strrchr($exp->export_run_dir, DIRECTORY_SEPARATOR), 1);

		$import = new ilImport((int)$a_target_id);
		$new_id = $import->importObject(null, $exp->export_run_dir.".zip", $file_name.".zip", $this->getType(), "", true);

		$new_obj->delete();

		if ($new_id > 0)
		{
			$obj = ilObjectFactory::getInstanceByObjId($new_id);
			$obj->setId($created_new_id);

			$obj->createReference();
			$obj->putInTree($a_target_id);
			$obj->setPermissions($a_target_id);


		}

		return $obj;
	}*/

	/**
	 * Attention only use this for objects who have not yet been created (use like: $x = new ilObjDataCollection; $x->cloneStructure($id))
	 *
	 * @param $original_id The original ID of the dataselection you want to clone it's structure
	 */
	public function cloneStructure($original_id) {
		$original = new ilObjDataCollection($original_id);

		$this->setApproval($original->getApproval());
		$this->setNotification($original->getNotification());
		$this->setOnline(false); // FSX: Standard is offline on cloned objects
		$this->setPublicNotes($original->getPublicNotes());
		$this->setRating($original->getRating());

		// delete old tables.
		foreach ($this->getTables() as $table) {
			$table->doDelete(true);
		}

		// add new tables.
		foreach ($original->getTables() as $table) {
			$new_table = new ilDataCollectionTable();
			$new_table->setObjId($this->getId());
			$new_table->cloneStructure($table);

			if ($table->getId() == $original->getMainTableId()) {
				$this->setMainTableId($new_table->getId());
			}
		}

		// update because maintable id is now set.
		$this->doUpdate();

		// Set new field-ID of referenced fields
		foreach ($original->getTables() as $origTable) {
			foreach ($origTable->getRecordFields() as $origField) {
				if ($origField->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_REFERENCE) {
					$newRefId = NULL;
					$origFieldRefObj = ilDataCollectionCache::getFieldCache($origField->getFieldRef());
					$origRefTable = ilDataCollectionCache::getTableCache($origFieldRefObj->getTableId());
					// Lookup the new ID of the referenced field in the actual DC
					$tableId = ilDataCollectionTable::_getTableIdByTitle($origRefTable->getTitle(), $this->getId());
					$fieldId = ilDataCollectionField::_getFieldIdByTitle($origFieldRefObj->getTitle(), $tableId);
					$field = ilDataCollectionCache::getFieldCache($fieldId);
					$newRefId = $field->getId();
					// Set the new refID in the actual DC
					$tableId = ilDataCollectionTable::_getTableIdByTitle($origTable->getTitle(), $this->getId());
					$fieldId = ilDataCollectionField::_getFieldIdByTitle($origField->getTitle(), $tableId);
					$field = ilDataCollectionCache::getFieldCache($fieldId);
					$field->setPropertyvalue($newRefId, ilDataCollectionField::PROPERTYID_REFERENCE);
					$field->doUpdate();
				}
			}
		}
	}


	/**
	 * setOnline
	 */
	public function setOnline($a_val) {
		$this->is_online = $a_val;
	}


	/**
	 * getOnline
	 */
	public function getOnline() {
		return $this->is_online;
	}


	/**
	 * setRating
	 */
	public function setRating($a_val) {
		$this->rating = $a_val;
	}


	/**
	 * getRating
	 */
	public function getRating() {
		return $this->rating;
	}


	/**
	 * setPublicNotes
	 */
	public function setPublicNotes($a_val) {
		$this->public_notes = $a_val;
	}


	/**
	 * getPublicNotes
	 */
	public function getPublicNotes() {
		return $this->public_notes;
	}


	/**
	 * setApproval
	 */
	public function setApproval($a_val) {
		$this->approval = $a_val;
	}


	/**
	 * getApproval
	 */
	public function getApproval() {
		return $this->approval;
	}


	/**
	 * setNotification
	 */
	public function setNotification($a_val) {
		$this->notification = $a_val;
	}


	/**
	 * getNotification
	 */
	public function getNotification() {
		return $this->notification;
	}


	/**
	 * @return bool
	 * @deprecated
	 */
	public function hasPermissionToAddTable() {
		return ilObjDataCollectionAccess::_checkAccess2($this->getId());
	}


	/**
	 * @param $data_collection_id
	 *
	 * @deprecated
	 * @return bool
	 */
	public static function _checkAccess($data_collection_id) {
		return ilObjDataCollectionAccess::_checkAccess2($data_collection_id);
	}


	/**
	 * @param $ref int the reference id of the datacollection object to check.
	 *
	 * @deprecated
	 * @return bool whether or not the current user has admin/write access to the referenced datacollection
	 */
	public static function _hasWriteAccess($ref) {
		return ilObjDataCollectionAccess::_hasWriteAccess($ref);
	}


	/**
	 * @param $ref int the reference id of the datacollection object to check.
	 *
	 * @deprecated
	 * @return bool whether or not the current user has add/edit_entry access to the referenced datacollection
	 */
	public static function _hasReadAccess($ref) {
		return ilObjDataCollectionAccess::_hasReadAccess($ref);
	}


	/**
	 * @return bool
	 *
	 * @deprecated
	 */
	public function _hasRecords() {
		return true;
	}


	/**
	 * @return ilDataCollectionTable[] Returns an array of tables of this collection with ids of the tables as keys.
	 */
	public function getTables() {
		global $ilDB;

		$query = "SELECT id FROM il_dcl_table WHERE obj_id = " . $ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($query);
		$tables = array();

		while ($rec = $ilDB->fetchAssoc($set)) {
			$tables[$rec['id']] = ilDataCollectionCache::getTableCache($rec['id']);
		}

		return $tables;
	}


	/**
	 * @return array
	 */
	public function getVisibleTables() {
		$tables = array();
		foreach ($this->getTables() as $table) {
			if ($table->getIsVisible()) {
				$tables[$table->getId()] = $table;
			}
		}

		return $tables;
	}


	/**
	 * Checks if a DataCollection has a table with a given title
	 *
	 * @param $title  Title of table
	 * @param $obj_id Obj-ID of the table
	 *
	 * @return bool
	 */
	public static function _hasTableByTitle($title, $obj_id) {
		global $ilDB;
		$result = $ilDB->query('SELECT * FROM il_dcl_table WHERE obj_id = ' . $ilDB->quote($obj_id, 'integer') . ' AND title = '
			. $ilDB->quote($title, 'text'));

		return ($ilDB->numRows($result)) ? true : false;
	}


	public function getStyleSheetId() { }
}

?>