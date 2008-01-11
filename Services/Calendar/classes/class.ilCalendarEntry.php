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

include_once('Services/Calendar/classes/class.ilDateTime.php');


/** 
* Model for a calendar entry. 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesCalendar 
*/

class ilCalendarEntry
{
	protected $log;
	protected $db;
	
	protected 
	
	protected $entry_id;
	protected $title;
	protected $description;
	protected $location;
	protected $further_informations;
	protected $start = null;
	protected $is_fullday;
	protected $end = null;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int cal_entry id
	 * 
	 */
	public function __construct($a_id)
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
	 * get cal entry id
	 *
	 * @access public
	 */
	public function getEntryId()
	{
	 	return $this->entry_id;
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
	 	$this->is_fullday = (bool) $a_fullday;
	}
	
	/**
	 * is fullday
	 *
	 * @access public
	 */
	public function isFullday()
	{
	 	return (bool) $this->is_fullday;
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
		while($row = $res->fetchRow())
		{
			$this->setTitle($row['title']);
			$this->setDescription($row['description']);
			$this->setLocation($row['location']);
			$this->setFurtherInformations($row['further_informations']);
			$this->setFullday((bool) $row['is_fullday']);
			$this->start = new ilDateTime($row['start'],ilDateTime::FORMAT_DATETIME);
			$this->end = new ilDateTime($row['end'],ilDateTime::FORMAT_DATETIME);
			
		}
		
	}
}


?>