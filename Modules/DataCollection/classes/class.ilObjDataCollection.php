<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Object/classes/class.ilObject2.php";
require_once "class.ilDataCollectionTable.php";
require_once "class.ilDataCollectionCache.php";

/**
* Class ilObjDataCollection
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjFolder.php 25528 2010-09-03 10:37:11Z smeyer $
*
* @extends ilObject2
*/
class ilObjDataCollection extends ilObject2
{

	/*
	 * initType
	 */
	public function initType()
	{
		$this->type = "dcl";
	}
	
	/*
	 * doRead
	 */
	public function doRead()
	{
		global $ilDB;
		
		$result = $ilDB->query("SELECT * FROM il_dcl_data WHERE id = ".$ilDB->quote($this->getId(), "integer"));

		$data = $ilDB->fetchObject($result);
		$this->setMainTableId($data->main_table_id);
		$this->setOnline($data->is_online);
		$this->setRating($data->rating);
		$this->setApproval($data->approval);
		$this->setPublicNotes($data->public_notes);
		$this->setNotification($data->notification);
	}
	

	/*
	 * doCreate
	 * Ceate a New DataCollection Object
	 */
	protected function doCreate()
	{
		global $ilDB;

		//Create Main Table - The title of the table is per default the title of the data collection object
		include_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");
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
				"id" => array("integer", $this->getId()),
				"main_table_id" => array("integer", (int) $main_table->getId()),
				"is_online" => array("integer", (int) $this->getOnline()),
				"rating" => array("integer", (int) $this->getRating()),
				"public_notes" => array("integer", (int) $this->getPublicNotes()),
				"approval" => array("integer", (int) $this->getApproval()),
				"notification" => array("integer", (int) $this->getNotification()),
			));
	}
	
	/**
	 * doClone
	 * @return boolean
	 */
	public function doClone()
	{
		global $x;
		
		
		
		return true;
	}
	
	/*
	 * doDelete
	 */
	protected function doDelete()
	{
		global $ilDB;
		
		foreach($this->getTables() as $table)
		{
			$table->doDelete(true);
		}
		
		$query = "DELETE FROM il_dcl_data WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);
	}
	
	/*
	 * doUpdate
	 */
	public  function doUpdate()
	{
		global $ilDB;

		$ilDB->update("il_dcl_data", array(
			"id" => array("integer", $this->getId()),
			"main_table_id" => array("integer", (int) $this->getMainTableId()),
			"is_online" => array("integer", (int) $this->getOnline()),
			"rating" => array("integer", (int) $this->getRating()),
			"public_notes" => array("integer", (int) $this->getPublicNotes()),
			"approval" => array("integer", (int) $this->getApproval()),
			"notification" => array("integer", (int) $this->getNotification()),
			),
		array(
			"id" => array("integer", $this->getId())
			)
		);
	}	 
	
	
	/*
	 * sendNotification
	 */
	static function sendNotification($a_action, $a_table_id, $a_record_id = NULL)
	{
		global $ilUser, $ilAccess;
		
		$dclObj = new ilObjDataCollection($_GET['ref_id']);
		
		if($dclObj->getNotification() != 1)
		{
			return;
		}
		$obj_table = ilDataCollectionCache::getTableCache($a_table_id);
		$obj_dcl = $obj_table->getCollectionObject();
		
		// recipients
		include_once "./Services/Notification/classes/class.ilNotification.php";		
		$users = ilNotification::getNotificationsForObject(ilNotification::TYPE_DATA_COLLECTION,$obj_dcl->getId(),true);
		if(!sizeof($users))
		{
			return;
		}
		
		ilNotification::updateNotificationTime(ilNotification::TYPE_DATA_COLLECTION, $obj_dcl->getId(), $users);
		
		//FIXME  $_GET['ref_id]
		include_once "./Services/Link/classes/class.ilLink.php";
		$link = ilLink::_getLink($_GET['ref_id']);
		
		// prepare mail content
		// use language of recipient to compose message
		include_once "./Services/Language/classes/class.ilLanguageFactory.php";


		// send mails
		include_once "./Services/Mail/classes/class.ilMail.php";
		include_once "./Services/User/classes/class.ilObjUser.php";
		include_once "./Services/Language/classes/class.ilLanguageFactory.php";
		include_once("./Services/User/classes/class.ilUserUtil.php");
		include_once("./Services/User/classes/class.ilUserUtil.php");
		include_once("./Modules/DataCollection/classes/class.ilDataCollectionTable.php");
		
		foreach(array_unique($users) as $idx => $user_id)
		{
			// the user responsible for the action should not be notified
			// FIXME  $_GET['ref_id]
			if($user_id != $ilUser->getId() && $ilAccess->checkAccessOfUser($user_id, 'read', '', $_GET['ref_id']))
			{
				// use language of recipient to compose message
				$ulng = ilLanguageFactory::_getLanguageOfUser($user_id);
				$ulng->loadLanguageModule('dcl');
		
				$subject = sprintf($ulng->txt('dcl_change_notification_subject'), $obj_dcl->getTitle());
				// update/delete
                $message = $ulng->txt("dcl_hello")." ".ilObjUser::_lookupFullname($user_id).",\n\n";
				$message .= $ulng->txt('dcl_change_notification_dcl_'.$a_action).":\n\n";
				$message .= $ulng->txt('obj_dcl').": ".$obj_dcl->getTitle()."\n";
				$message .= $ulng->txt('dcl_table').": ".$obj_table->getTitle()."\n";
				if($a_record_id)
				{
                    $record = ilDataCollectionCache::getRecordCache($a_record_id);
					$message .= $ulng->txt('dcl_record_id').": ".$a_record_id.":\n";
                    $t = "";
                    foreach($record->getTable()->getVisibleFields() as $field){
                        if($record->getRecordField($field->getId())){
                            $t .= $record->getRecordField($field->getId())->getPlainText()." ";
                        }
                    }
                    $message .= $t."\n";
				}

				$message .= $ulng->txt('dcl_changed_by').": ".ilUserUtil::getNamePresentation($ilUser->getId())."\n\n";
				$message .= $ulng->txt('dcl_change_notification_link').": ".$link."\n\n";

                $message .= $ulng->txt('dcl_change_why_you_receive_this_email');
		
				$mail_obj = new ilMail(ANONYMOUS_USER_ID);
				$mail_obj->appendInstallationSignature(true);
				$mail_obj->sendMail(ilObjUser::_lookupLogin($user_id), "", "", $subject, $message, array(), array("system"));
			}
			else
			{
				unset($users[$idx]);
			}
		}
	}
	
	/**
	 * set main Table Id
	 */
	public function setMainTableId($a_val)
	{
		$this->main_table_id = $a_val;
	}
	
	/**
	 * get main Table Id
	 */
	public function getMainTableId()
	{
		return $this->main_table_id;
	}
	
	
	/**
	 * Clone DCL
	 *
	 * @param ilObjDataCollection new object
	 * @param int target ref_id
	 * @param int copy id
	 * @return ilObjPoll
	 */
	public function doCloneObject(ilObjDataCollection $new_obj, $a_target_id, $a_copy_id = 0)
	{		
		$new_obj->cloneStructure($this->getRefId());

		return $new_obj;
	}
	
	
	
	
	/**
	 * Attention only use this for objects who have not yet been created (use like: $x = new ilObjDataCollection; $x->cloneStructure($id))
	 * @param $original_id The original ID of the dataselection you want to clone it's structure
	 */
	public function cloneStructure($original_id)
	{
		$original = new ilObjDataCollection($original_id);
		
		$this->setApproval($original->getApproval());
		$this->setNotification($original->getNotification());
		$this->setOnline($original->getOnline());
		$this->setPublicNotes($original->getPublicNotes());
		$this->setRating($original->getRating());
		
		//delete old tables.
		foreach($this->getTables() as $table)
		{
			$table->doDelete(true);
		}

		//add new tables.
		foreach($original->getTables() as $table)
		{
			$new_table = ilDataCollectionCache::getTableCache();
			$new_table->setObjId($this->getId());
			$new_table->cloneStructure($table->getId());
			
			if($table->getId() == $original->getMainTableId())
			{
				$this->setMainTableId($new_table->getId());
			}
		}
		

		// update because maintable id is now set.
		$this->doUpdate();
		
	}


	/**
	 * setOnline
	 */
	public function setOnline($a_val)
	{
		$this->is_online = $a_val;
	}
	
	/**
	 * getOnline
	 */
	public function getOnline()
	{
		return $this->is_online;
	}
	
	/**
	 * setRating
	 */
	public function setRating($a_val)
	{
		$this->rating = $a_val;
	}
	
	/**
	 * getRating
	 */
	public function getRating()
	{
		return $this->rating;
	}
	
	/**
	 * setPublicNotes
	 */
	public function setPublicNotes($a_val)
	{
		$this->public_notes = $a_val;
	}
	
	/**
	 * getPublicNotes
	 */
	public function getPublicNotes()
	{
		return $this->public_notes;
	}
	
	/**
	 * setApproval
	 */
	public function setApproval($a_val)
	{
		$this->approval = $a_val;
	}
	
	/**
	 * getApproval
	 */
	public function getApproval()
	{
		return $this->approval;
	}
	
	/**
	 * setNotification
	 */
	public function setNotification($a_val)
	{
		$this->notification = $a_val;
	}
	
	/**
	 * getNotification
	 */
	public function getNotification()
	{
		return $this->notification;
	}
	
	/*
	 * hasPermissionToAddTable
	 */
	public function hasPermissionToAddTable()
	{
		return self::_checkAccess($this->getId());
	}
	
	/*
	 * _checkAccess
	 */
	public static function _checkAccess($data_collection_id)
	{
		global $ilAccess;
		
		$perm = false;
		$references = self::_getAllReferences($data_collection_id);
		
		if($ilAccess->checkAccess("add_entry", "", array_shift($references)))
		{
			$perm = true;
		}
			
		return $perm;
	}

	/**
	 * @param $ref int the reference id of the datacollection object to check.
	 * @return bool whether or not the current user has admin/write access to the referenced datacollection
	 */
	public static function _hasWriteAccess($ref)
	{
		global $ilAccess;

		return $ilAccess->checkAccess("write", "", $ref);
	}

	/**
	 * @param $ref int the reference id of the datacollection object to check.
	 * @return bool whether or not the current user has add/edit_entry access to the referenced datacollection
	 */
	public static function _hasReadAccess($ref)
	{
		global $ilAccess;
		
		return $ilAccess->checkAccess("add_entry", "", $ref);
	}
	
	/**
	 * _hasRecords
	 * @return boolean
	 */
	public function _hasRecords()
	{
		/*foreach($this->getTables() as $table)
		{
			if($table->_hasRecords())
			{
				return true;
			}
		}
		return false;
		*/
		return true;
	}

	/**
	 * @return ilDataCollectionTable[] Returns an array of tables of this collection with ids of the tables as keys.
	 */
	public function getTables()
	{
		global $ilDB;
		
		$query = "SELECT id FROM il_dcl_table WHERE obj_id = ".$ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($query);
		$tables = array();
		
		while($rec = $ilDB->fetchAssoc($set))
		{
			$tables[$rec['id']] = ilDataCollectionCache::getTableCache($rec['id']);
		}
		
		return $tables;
	}

	public function getVisibleTables(){
		$tables = array();
		foreach($this->getTables() as $table){
			if($table->getIsVisible())
				$tables[$table->getId()] = $table;
		}
		return $tables;
	}

}

?>