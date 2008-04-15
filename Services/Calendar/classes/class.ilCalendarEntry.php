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

include_once('Services/Calendar/classes/class.ilDate.php');


/** 
* Model for a calendar entry. 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesCalendar 
*/

include_once('./Services/Calendar/interfaces/interface.ilDatePeriod.php');

class ilCalendarEntry implements ilDatePeriod
{
	protected $log;
	protected $db;
	
	
	protected $entry_id;
	protected $title;
	protected $description;
	protected $location;
	protected $further_informations;
	protected $start = null;
	protected $fullday;
	protected $end = null;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int cal_entry id
	 * 
	 */
	public function __construct($a_id = 0)
	{
		global $ilDB,$ilLog;
		
		$this->log = $ilLog;
		$this->db = $ilDB;
		
		if($this->entry_id = $a_id)
		{
			$this->read();
		}
	}
	
	/**
	 * delete entry
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _delete($a_entry_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM cal_entries ".
			"WHERE cal_id = ".$ilDB->quote($a_entry_id)." ";
		$ilDB->query($query);
		return true;
	}
	
	/**
	 * get entry id
	 *
	 * @access public
	 * 
	 */
	public function getEntryId()
	{
	 	return $this->entry_id;
	}
	
	
	/**
	 * get start
	 *
	 * @access public
	 * @return
	 */
	public function getStart()
	{
		return $this->start ? $this->start : $this->start = new ilDateTime();
		
	}
	
	/**
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function setStart(ilDateTime $a_start)
	{
		$this->start = $a_start;
	}
	
	/**
	 * get end
	 * @access public
	 * @return ilDateTime end
	 */
	public function getEnd()
	{
		return $this->end ? $this->end : $this->end = new ilDateTime();
	}
	
	/**
	 * set end
	 * @access public
	 * @param
	 */
	public function setEnd($a_end)
	{
		$this->end = $a_end;
	}
	
	/**
	 * set title
	 *
	 * @access public
	 * @param string title
	 * 
	 */
	public function setTitle($a_title)
	{
	 	$this->title = $a_title;
	}
	
	/**
	 * get title
	 *
	 * @access public
	 * 
	 */
	public function getTitle()
	{
	 	return $this->title;
	}
	
	/**
	 * set description
	 *
	 * @access public
	 * @param string description
	 * 
	 */
	public function setDescription($a_description)
	{
	 	$this->description = $a_description;
	}
	
	/**
	 * get description
	 *
	 * @access public
	 */
	public function getDescription()
	{
	 	return $this->description;
	}
	
	/**
	 * set location
	 *
	 * @access public
	 * @param string location
	 * 
	 */
	public function setLocation($a_location)
	{
	 	$this->location = $a_location;
	}
	
	/**
	 * get location
	 *
	 * @access public
	 */
	public function getLocation()
	{
	 	return $this->location;
	}
	
	/**
	 * set further informations
	 *
	 * @access public
	 * @param string further informations
	 * 
	 */
	public function setFurtherInformations($a_informations)
	{
	 	$this->further_informations = $a_informations;
	}
	
	/**
	 * get further informations
	 *
	 * @access public
	 */
	public function getFurtherInformations()
	{
	 	return $this->further_informations;
	}
	
	/**
	 * set fullday event
	 * Fullday events do not change their time in different timezones.
	 * It is possible to create fullday events with a duration of more than one day. 
 	 *
	 * @access public
	 * @param bool fullday
	 * 
	 */
	public function setFullday($a_fullday)
	{
	 	$this->fullday = (bool) $a_fullday;
	}
	
	/**
	 * is fullday
	 *
	 * @access public
	 */
	public function isFullday()
	{
	 	return (bool) $this->fullday;
	}
	
	/**
	 * update
	 *
	 * @access public
	 * 
	 */
	public function update()
	{
	 	$query = "UPDATE cal_entries ".
	 		"SET title = ".$this->db->quote($this->getTitle()).", ".
	 		"description = ".$this->db->quote($this->getDescription()).", ".
	 		"location = ".$this->db->quote($this->getLocation()).", ".
	 		"fullday = ".($this->isFullday() ? 1 : 0).", ".
	 		"start = ".$this->db->quote($this->getStart()->get(IL_CAL_DATETIME,'','UTC')).", ".
	 		"end = ".$this->db->quote($this->getEnd()->get(IL_CAL_DATETIME,'','UTC')).", ".
	 		"informations = ".$this->db->quote($this->getFurtherInformations()).", ".
	 		"public = 0 ".
	 		"WHERE cal_id = ".$this->db->quote($this->getEntryId())." ";
			
	 		
	 	$res = $this->db->query($query);
		return true;
	}
	
	/**
	 * save one entry
	 *
	 * @access public
	 * 
	 */
	public function save()
	{
	 	$query = "INSERT INTO cal_entries ".
	 		"SET title = ".$this->db->quote($this->getTitle()).", ".
	 		"description = ".$this->db->quote($this->getDescription()).", ".
	 		"location = ".$this->db->quote($this->getLocation()).", ".
	 		"fullday = ".($this->isFullday() ? 1 : 0).", ".
	 		"start = ".$this->db->quote($this->getStart()->get(IL_CAL_DATETIME,'','UTC')).", ".
	 		"end = ".$this->db->quote($this->getEnd()->get(IL_CAL_DATETIME,'','UTC')).", ".
	 		"informations = ".$this->db->quote($this->getFurtherInformations()).", ".
	 		"public = 0 ";
	 		
	 	$res = $this->db->query($query);
		$this->entry_id = $this->db->getLastInsertId();		
		return true;
	}
	
	/**
	 * delete
	 *
	 * @access public
	 * @return
	 */
	public function delete()
	{
		$query = "DELETE FROM cal_entries ".
			"WHERE cal_id = ".$this->db->quote($this->getEntryId())." ";
		$this->db->query($query);
		return true;
	}
	
	/**
	 * validate
	 *
	 * @access public
	 * @return
	 */
	public function validate()
	{
		return (bool) strlen($this->getTitle());
	}
	
	
	
	/**
	 * @access protected
	 * @param
	 * 
	 */
	protected function read()
	{
	 	$query = "SELECT * FROM cal_entries WHERE cal_id = ".$this->db->quote($this->getEntryId())." ";
	 	$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setTitle($row->title);
			$this->setDescription($row->description);
			$this->setLocation($row->location);
			$this->setFurtherInformations($row->informations);
			$this->setFullday((bool) $row->fullday);
			
			if($this->isFullday())
			{
				$this->start = new ilDate($row->start,IL_CAL_DATETIME);
				$this->end = new ilDate($row->end,IL_CAL_DATETIME);
			}
			else
			{
				$this->start = new ilDateTime($row->start,IL_CAL_DATETIME,'UTC');
				$this->end = new ilDateTime($row->end,IL_CAL_DATETIME,'UTC');
			}
		}
		
	}
}
?>