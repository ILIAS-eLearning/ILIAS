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
* @defgroup ModulesRemoteCourse Modules/RemoteCourse
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ModulesRemoteCourse
*/

class ilObjRemoteCourse extends ilObject
{
	const ACTIVATION_OFFLINE = 0;
	const ACTIVATION_UNLIMITED = 1;
	const ACTIVATION_LIMITED = 2;
	
	protected $availability_type;
	protected $end;
	protected $start;
	protected $local_information;

	/**
	 * Constructor
	 *
	 * @access public
	 * 
	 */
	public function __construct($a_id = 0,$a_call_by_reference = true)
	{
		global $ilDB;
		
		$this->type = "rcrs";
		$this->ilObject($a_id,$a_call_by_reference);
		$this->db = $ilDB;
	}
	
	/**
	 * Lookup online
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 */
	public static function _lookupOnline($a_obj_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM remote_course_settings ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ";
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		switch($row->availability_type)
		{
			case self::ACTIVATION_UNLIMITED:
				return true;
				
			case self::ACTIVATION_OFFLINE:
				return false;
				
			case self::ACTIVATION_LIMITED:
				return time() > $row->start && time < $row->end;
				
			default:
				return false;
		}
		
		return false;
	}
	
	/**
	 * get local information
	 *
	 * @access public
	 * 
	 */
	public function getLocalInformation()
	{
	 	return $this->local_information;
	}
	
	/**
	 * set local information
	 *
	 * @access public
	 * @param string local information
	 * 
	 */
	public function setLocalInformation($a_info)
	{
	 	$this->local_information = $a_info;
	}
	
	/**
	 * Set Availability type
	 *
	 * @access public
	 * @param int availability type
	 * 
	 */
	public function setAvailabilityType($a_type)
	{
	 	$this->availability_type = $a_type;
	}
	
	/**
	 * get availability type
	 *
	 * @access public
	 * 
	 */
	public function getAvailabilityType()
	{
	 	return $this->availability_type;
	}
	
	/**
	 * set starting time
	 *
	 * @access public
	 * @param int statrting time
	 * 
	 */
	public function setStartingTime($a_time)
	{
	 	$this->start = $a_time;
	}
	
	/**
	 * getStartingTime
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getStartingTime()
	{
	 	return $this->start;
	}

	/**
	 * set ending time
	 *
	 * @access public
	 * @param int statrting time
	 * 
	 */
	public function setEndingTime($a_time)
	{
	 	$this->end = $a_time;
	}
	
	/**
	 * get ending time
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getEndingTime()
	{
	 	return $this->end;
	}

	/**
	 * Update function 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function update()
	{
		global $ilDB;
		
		if (!parent::update())
		{			
			return false;
		}
		
		$query = "REPLACE INTO remote_course_settings ".
			"SET availability_type = ".$this->db->quote($this->getAvailabilityType()).", ".
			"start = ".$this->db->quote($this->getStartingTime()).", ".
			"end = ".$this->db->quote($this->getEndingTime()).", ".
			"local_information = ".$this->db->quote($this->getLocalInformation()).", ".
			"obj_id = ".$this->db->quote($this->getId())." ";
		$this->db->query($query);
		return true;
	}
	
	/**
	 * Delete this remote course
	 *
	 * @access public
	 * 
	 */
	public function delete()
	{
		if(!parent::delete())
		{
			return false;
		}
		
		//put here your module specific stuff
		
		return true;
	}
	
	/**
	 * read settings
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function read($a_force_db = false)
	{
		parent::read($a_force_db);

		$query = "SELECT * FROM remote_course_settings ".
			"WHERE obj_id = ".$this->db->quote($this->getId())." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setLocalInformation($row->local_information);
			$this->setAvailabilityType($row->availability_type);
			$this->setStartingTime($row->start);
			$this->setEndingTime($row->end);
		}
	}
}
?>