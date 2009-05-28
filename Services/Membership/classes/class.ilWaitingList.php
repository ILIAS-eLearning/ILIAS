<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

/**
* Base class for course and group waiting lists
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesMembership
*/

abstract class ilWaitingList
{
	private $db = null;
	private $obj_id = 0;
	private $user_ids = array();
	private $users = array();
	


	/**
	 * Constructor
	 *
	 * @access public
	 * @param int obj_id 
	 */
	public function __construct($a_obj_id)
	{
		global $ilDB;

		$this->db = $ilDB;
		$this->obj_id = $a_obj_id;

		$this->read();
	}
	
	/**
	 * delete all
	 *
	 * @access public
	 * @param int obj_id
	 * @static
	 */
	public static function _deleteAll($a_obj_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_waiting_list WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ";
		$res = $ilDB->manipulate($query);

		return true;
	}
	
	/**
	 * Delete user
	 *
	 * @access public
	 * @param int user_id
	 * @static
	 */
	public static function _deleteUser($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_waiting_list WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer');
		$res = $ilDB->manipulate($query);

		return true;
	}	
	

	/**
	 * get obj id
	 *
	 * @access public
	 * @return int obj_id
	 */
	public function getObjId()
	{
		return $this->obj_id;
	}

	/**
	 * add to list
	 *
	 * @access public
	 * @param int usr_id
	 */
	public function addToList($a_usr_id)
	{
		global $ilDB;
		
		if($this->isOnList($a_usr_id))
		{
			return false;
		}
		$query = "INSERT INTO crs_waiting_list (obj_id,usr_id,sub_time) ".
			"VALUES (".
			$ilDB->quote($this->getObjId() ,'integer').", ".
			$ilDB->quote($a_usr_id ,'integer').", ".
			$ilDB->quote(time() ,'integer')." ".
			")";
		$res = $ilDB->manipulate($query);
		$this->read();

		return true;
	}

	/**
	 * update subscription time
	 *
	 * @access public
	 * @param int usr_id
	 * @param int subsctription time
	 */
	public function updateSubscriptionTime($a_usr_id,$a_subtime)
	{
		global $ilDB;
		
		$query = "UPDATE crs_waiting_list ".
			"SET sub_time = ".$ilDB->quote($a_subtime ,'integer')." ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($this->getObjId() ,'integer')." ";
		$res = $ilDB->manipulate($query);
		return true;
	}

	/**
	 * remove usr from list
	 *
	 * @access public
	 * @param int usr_id
	 * @return
	 */
	public function removeFromList($a_usr_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM crs_waiting_list ".
			" WHERE obj_id = ".$ilDB->quote($this->getObjId() ,'integer')." ".
			" AND usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
		$this->read();

		return true;
	}

	/**
	 * check if is on waiting list
	 *
	 * @access public
	 * @param int usr_id
	 * @return
	 */
	public function isOnList($a_usr_id)
	{
		return isset($this->users[$a_usr_id]) ? true : false;
	}
	
	/**
	 * Check if a user on the waiting list
	 * @return bool
	 * @param object $a_usr_id
	 * @param object $a_obj_id
	 * @access public
	 * @static
	 */
	public static function _isOnList($a_usr_id,$a_obj_id)
	{
		global $ilDB;
		
		$query = "SELECT usr_id ".
			"FROM crs_waiting_list ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id, 'integer')." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id, 'integer');
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false;
	}

	/**
	 * get number of users
	 *
	 * @access public
	 * @return int number of users
	 */
	public function getCountUsers()
	{
		return count($this->users);
	}
	
	/**
	 * get position
	 *
	 * @access public
	 * @param int usr_id
	 * @return position of user otherwise -1
	 */
	public function getPosition($a_usr_id)
	{
		return isset($this->users[$a_usr_id]) ? $this->users[$a_usr_id]['position'] : -1;
	}

	/**
	 * get all users on waiting list
	 *
	 * @access public
	 * @return array array(position,time,usr_id)
	 */
	public function getAllUsers()
	{
		return $this->users ? $this->users : array();
	}
	
	/**
	 * get user
	 *
	 * @access public
	 * @param int usr_id
	 * @return
	 */
	public function getUser($a_usr_id)
	{
		return isset($this->users[$a_usr_id]) ? $this->users[$a_usr_id] : false;
	}
	
	/**
	 * Get all user ids of users on waiting list
	 *
	 * 
	 */
	public function getUserIds()
	{
	 	return $this->user_ids ? $this->user_ids : array();
	}


	/**
	 * Read waiting list 
	 *
	 * @access private
	 * @param
	 * @return
	 */
	private function read()
	{
		global $ilDB;
		
		$this->users = array();

		$query = "SELECT * FROM crs_waiting_list ".
			"WHERE obj_id = ".$ilDB->quote($this->getObjId() ,'integer')." ORDER BY sub_time";

		$res = $this->db->query($query);
		$counter = 0;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			++$counter;
			$this->users[$row->usr_id]['position']	= $counter;
			$this->users[$row->usr_id]['time']		= $row->sub_time;
			$this->users[$row->usr_id]['usr_id']	= $row->usr_id;
			
			$this->user_ids[] = $row->usr_id;
		}
		return true;
	}

}
?>