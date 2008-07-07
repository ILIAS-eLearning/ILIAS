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
	
	protected $db = null;
	
	private $usr_id = 0;
	
	private $calendars = array();
	

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
			"WHERE status = ".$ilDB->quote(self::STATUS_ACCEPTED)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)." ";
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
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND cal_id = ".$ilDB->quote($a_calendar_id)." ";
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
		global $ilUser;
		
		$query = "DELETE FROM cal_shared_status ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)." ";
		$ilDB->query($query);
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
			"WHERE cal_id = ".$ilDB->quote($a_calendar_id)." ";
		$ilDB->query($query);
		return true;
	}
	
	/**
	 * delete status
	 *
	 * @access public
	 * @param int usr_id
	 * @param int calendar_id
	 * @return
	 * @static
	 */
	public static function deleteStatus($a_usr_id,$a_calendar_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM cal_shared_status ".
			"WHERE cal_id = ".$ilDB->quote($a_calendar_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)." ";
		$ilDB->query($query);
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
		self::deleteStatus($this->usr_id,$a_calendar_id);
		
		$query = "INSERT INTO cal_shared_status ".
			"SET cal_id = ".$this->db->quote($a_calendar_id).", ".
			"usr_id = ".$this->db->quote($this->usr_id).", ".
			"status = ".$this->db->quote(self::STATUS_ACCEPTED)." ";
		$this->db->query($query);
		
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
		self::deleteStatus($this->usr_id,$a_calendar_id);
		
		$query = "INSERT INTO cal_shared_status ".
			"SET cal_id = ".$this->db->quote($a_calendar_id).", ".
			"usr_id = ".$this->db->quote($this->usr_id).", ".
			"status = ".$this->db->quote(self::STATUS_DECLINED)." ";
		$this->db->query($query);
		
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
		$query = "SELECT * FROM cal_shared_status ".
			"WHERE usr_id = ".$this->db->quote($this->usr_id)." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->calendars[$row->cal_id] = $row->status; 
		}
	}
	
}
?>