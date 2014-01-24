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
* Stores status (accepted/declined) of shared calendars
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/
class ilCalendarSharedStatus
{
	const STATUS_ACCEPTED = 1;
	const STATUS_DECLINED = 2;
	const STATUS_DELETED = 3;
	
	protected $db = null;
	
	private $usr_id = 0;
	
	private $calendars = array();
	private $writable = array();
	

	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_usr_id)
	{
		global $ilDB;
		
		$this->usr_id = $a_usr_id;
		$this->db = $ilDB;
		
		$this->read();
	}
	
	/**
	 * is accepted
	 *
	 * @access public
	 * @param int cal_id
	 * @return
	 */
	public function isAccepted($a_cal_id)
	{
		return isset($this->calendars[$a_cal_id]) and $this->calendars[$a_cal_id] == self::STATUS_ACCEPTED;
	}
	
	/**
	 * is declined
	 *
	 * @access public
	 * @param int cal_id
	 * @return
	 */
	public function isDeclined($a_cal_id)
	{
		return isset($this->calendars[$a_cal_id]) and $this->calendars[$a_cal_id] == self::STATUS_DECLINED;
	}
	
	/**
	 * get accepted shared calendars
	 *
	 * @access public
	 * @param int usr_id
	 * @return array int array of calendar ids
	 */ 
	public function getAcceptedCalendars($a_usr_id)
	{
		global $ilDB;
		
		$query = "SELECT cal_id FROM cal_shared_status ".
			"WHERE status = ".$ilDB->quote(self::STATUS_ACCEPTED ,'integer')." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$cal_ids[] = $row->cal_id;
		}
		return $cal_ids ? $cal_ids : array();
	}
	
	/**
	 * check if a status is set for an calendar
	 *
	 * @access public
	 * @param int usr_id
	 * @param int calendar id
	 * @return bool
	 * @static
	 */
	public static function hasStatus($a_usr_id,$a_calendar_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM cal_shared_status ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ".
			"AND cal_id = ".$ilDB->quote($a_calendar_id ,'integer')." ";
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false;
	}
	
	/**
	 * Delete by user
	 *
	 * @access public
	 * @param int usr_id
	 * @return bool
	 * @static
	 */
	public static function deleteUser($a_usr_id)
	{
		global $ilUser,$ilDB;
		
		$query = "DELETE FROM cal_shared_status ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
		return true;
	}
	
	/**
	 * Delete calendar
	 *
	 * @access public
	 * @param int calendar id
	 * @return bool
	 * @static
	 */
	public static function deleteCalendar($a_calendar_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM cal_shared_status ".
			"WHERE cal_id = ".$ilDB->quote($a_calendar_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
		return true;
	}
	
	/**
	 * delete status
	 *
	 * @access public
	 * @param int usr_id or role_id
	 * @param int calendar_id
	 * @return
	 * @static
	 */
	public static function deleteStatus($a_id,$a_calendar_id)
	{
		global $ilDB,$rbacreview;
		
		
		if(ilObject::_lookupType($a_id) == 'usr')
		{
			$query = "DELETE FROM cal_shared_status ".
				"WHERE cal_id = ".$ilDB->quote($a_calendar_id ,'integer')." ".
				"AND usr_id = ".$ilDB->quote($a_id ,'integer')." ";
			$res = $ilDB->manipulate($query);
			
		}
		elseif(ilObject::_lookupType($a_id) == 'role')
		{
			$assigned_users = $rbacreview->assignedUsers($a_id);
			
			if(!count($assigned_users))
			{
				return true;
			}
			
			$query = "DELETE FROM cal_shared_status ".
				"WHERE cal_id = ".$ilDB->quote($a_calendar_id ,'integer')." ".
				"AND ".$ilDB->in('usr_id',$assigned_users,false,'integer');
			$res = $ilDB->manipulate($query);
			
		}		
		
		return true;
	}
	
	
	
	/**
	 * accept calendar
	 *
	 * @access public
	 * @param int calendar id
	 * @return
	 */
	public function accept($a_calendar_id)
	{
		global $ilDB;
		
		self::deleteStatus($this->usr_id,$a_calendar_id);
		
		$query = "INSERT INTO cal_shared_status (cal_id,usr_id,status) ".
			"VALUES ( ".
			$this->db->quote($a_calendar_id ,'integer').", ".
			$this->db->quote($this->usr_id ,'integer').", ".
			$this->db->quote(self::STATUS_ACCEPTED ,'integer')." ".
			")";
		$res = $ilDB->manipulate($query);
		
		$this->calendars[$a_calendar_id] = self::STATUS_ACCEPTED;
		
		return true;
	}
	
	/**
	 * decline calendar
	 *
	 * @access public
	 * @param int calendar id
	 * @return
	 */
	public function decline($a_calendar_id)
	{
		global $ilDB;

		self::deleteStatus($this->usr_id,$a_calendar_id);
		
		$query = "INSERT INTO cal_shared_status (cal_id,usr_id,status) ".
			"VALUES ( ".
			$this->db->quote($a_calendar_id ,'integer').", ".
			$this->db->quote($this->usr_id ,'integer').", ".
			$this->db->quote(self::STATUS_DECLINED ,'integer')." ".
			")";
		$res = $ilDB->manipulate($query);
		
		$this->calendars[$a_calendar_id] = self::STATUS_DECLINED;

		return true;
	
	}
	
	/**
	 * read
	 *
	 * @access protected
	 * @return
	 */
	protected function read()
	{
		global $ilDB;
		
		$query = "SELECT * FROM cal_shared_status ".
			"WHERE usr_id = ".$this->db->quote($this->usr_id ,'integer')." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->calendars[$row->cal_id] = $row->status; 
		}
	}
	
}
?>