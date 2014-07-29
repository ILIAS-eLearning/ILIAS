<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Editing history for object custom user fields
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesMembership 
*/
class ilObjectCustomUserFieldHistory
{
	private $obj_id = 0;
	private $user_id = 0;
	private $update_user = 0;
	private $editing_time = null;
	
	/**
	 * Constructor
	 * @param type $a_obj_id
	 * @param type $a_user_id
	 */
	public function __construct($a_obj_id, $a_user_id)
	{
		$this->obj_id = $a_obj_id;
		$this->user_id = $a_user_id;
		$this->read();
	}
	
	/**
	 * Get entries by obj_id
	 * @global type $ilDB
	 * @param type $a_obj_id
	 * @return \ilDateTime
	 */
	public static function lookupEntriesByObjectId($a_obj_id)
	{
		global $ilDB;
		
		$query = 'SELECT * FROM obj_user_data_hist '.
				'WHERE obj_id = '.$ilDB->quote($a_obj_id,'integer');
		$res = $ilDB->query($query);
		
		$users = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$users[$row->usr_id]['update_user'] = $row->update_user;
			$users[$row->usr_id]['editing_time'] = new ilDateTime($row->editing_time,IL_CAL_DATETIME,  ilTimeZone::UTC);
		}
		return $users;
	}

		/**
	 * Set update user 
	 * @param int $a_id
	 */
	public function setUpdateUser($a_id)
	{
		$this->update_user = $a_id;
	}
	
	/**
	 * get update user
	 * @return type
	 */
	public function getUpdateUser()
	{
		return $this->update_user;
	}
	
	/**
	 * Set editing time
	 * @param ilDateTime $dt
	 */
	public function setEditingTime(ilDateTime $dt)
	{
		$this->editing_time = $dt;
	}
	
	/**
	 * Get editing time
	 * @return ilDateTime
	 */
	public function getEditingTime()
	{
		return $this->editing_time;
	}
	
	/**
	 * Save entry
	 */
	public function save()
	{
		global $ilDB;
		
		$this->delete();
		
		$query = 'INSERT INTO obj_user_data_hist (obj_id, usr_id, update_user, editing_time) '.
				'VALUES( '.
				$ilDB->quote($this->obj_id,'integer').', '.
				$ilDB->quote($this->user_id,'integer').', '.
				$ilDB->quote($this->getUpdateUser(),'integer').', '.
				$ilDB->quote($this->getEditingTime()->get(IL_CAL_DATETIME,'', ilTimeZone::UTC)).' '.
				')';
		$ilDB->manipulate($query);
	}
	
	/**
	 * Delete one entry
	 */
	public function delete()
	{
		global $ilDB;
		
		$query = 'DELETE FROM obj_user_data_hist '.
				'WHERE obj_id = '.$ilDB->quote($this->obj_id,'integer').' '.
				'AND usr_id = '.$ilDB->quote($this->user_id,'integer');
		$ilDB->manipulate($query);
	}
	
	/**
	 * read entry
	 * @global type $ilDB
	 */
	protected function read()
	{
		global $ilDB;
		
		$query = 'SELECT * FROM obj_user_data_hist '.
				'WHERE obj_id = '.$ilDB->quote($this->obj_id,'integer').' '.
				'AND usr_id = '.$ilDB->quote($this->user_id,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setEditingTime(new ilDateTime($row->editing_time,IL_CAL_DATETIME,  ilTimeZone::UTC));
			$this->setUpdateUser($row->update_user);
		}
	}
}

?>