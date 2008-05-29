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
include_once('./Services/Calendar/interfaces/interface.ilDatePeriod.php');

define('IL_CAL_TRANSLATION_NONE',0);
define('IL_CAL_TRANSLATION_SYSTEM',1);


/** 
* Model for a calendar entry. 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesCalendar 
*/
class ilCalendarEntry implements ilDatePeriod
{
	protected $log;
	protected $db;
	
	
	protected $entry_id;
	protected $last_update;
	protected $title;
	protected $subtitle;
	protected $description;
	protected $location;
	protected $further_informations;
	protected $start = null;
	protected $fullday;
	protected $end = null;
	protected $is_auto_generated = false; 
	protected $context_id = 0;
	protected $translation_type = IL_CAL_TRANSLATION_NONE;

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
		
		include_once('./Services/Calendar/classes/class.ilCalendarRecurrence.php');
		ilCalendarRecurrence::_delete($a_entry_id);
		
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
	 * get last update
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getLastUpdate()
	{
		return $this->last_update ? $this->last_update : new ilDateTime(time(),IL_CAL_UNIX);
	}
	
	/**
	 * set last update
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function setLastUpdate($a_date)
	{
		$this->last_update = $a_date;
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
	 * get title for presentation.
	 * Special handling for auto generated appointments 
	 *
	 * @access public
	 * @return
	 */
	public function getPresentationTitle()
	{
		global $ilUser,$lng;
		
		if($this->getTranslationType() == IL_CAL_TRANSLATION_NONE)
		{
			return $this->getTitle();
		}
		return $this->getTitle().' ('.$lng->txt($this->getSubtitle()).')';
	}
	
	/**
	 * set subtitle
	 * Used for automatic generated appointments.
	 * Will be appended to the title.
	 *
	 * @access public
	 * @param string subtitle
	 * @return
	 */
	public function setSubtitle($a_subtitle)
	{
		$this->subtitle = $a_subtitle;
	}
	
	/**
	 * get subtitle
	 *
	 * @access public
	 * @return
	 */
	public function getSubtitle()
	{
		return $this->subtitle;
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
	 * is auto generated
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function isAutoGenerated()
	{
		return (bool) $this->is_auto_generated;
	}
	
	/**
	 * set auto generated
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function setAutoGenerated($a_status)
	{
		$this->is_auto_generated = $a_status;
	}
	
	/**
	 * set context id
	 *
	 * @access public
	 * @param int context id
	 * @return
	 */
	public function setContextId($a_context_id)
	{
		$this->context_id = $a_context_id;
	}
	
	/**
	 * get context id
	 *
	 * @access public
	 * @return
	 */
	public function getContextId()
	{
		return $this->context_id;
	}
	
	/**
	 * 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function setTranslationType($a_type)
	{
		$this->translation_type = $a_type;
	}
	
	/**
	 * get translation type
	 *
	 * @access public
	 * @return int translation type
	 */
	public function getTranslationType()
	{
		return $this->translation_type;
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
	 		"last_update = UTC_TIMESTAMP(), ".
	 		"subtitle = ".$this->db->quote($this->getSubtitle()).", ".
	 		"description = ".$this->db->quote($this->getDescription()).", ".
	 		"location = ".$this->db->quote($this->getLocation()).", ".
	 		"fullday = ".($this->isFullday() ? 1 : 0).", ".
	 		"start = ".$this->db->quote($this->getStart()->get(IL_CAL_DATETIME,'','UTC')).", ".
	 		"end = ".$this->db->quote($this->getEnd()->get(IL_CAL_DATETIME,'','UTC')).", ".
	 		"informations = ".$this->db->quote($this->getFurtherInformations()).", ".
	 		"auto_generated =  ".$this->db->quote($this->isAutoGenerated()).", ".
	 		"translation_type = ".$this->db->quote($this->getTranslationType()).", ".
	 		"context_id = ".$this->db->quote($this->getContextId())." ".
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
	 		"last_update = UTC_TIMESTAMP(), ".
	 		"subtitle = ".$this->db->quote($this->getSubtitle()).", ".
	 		"description = ".$this->db->quote($this->getDescription()).", ".
	 		"location = ".$this->db->quote($this->getLocation()).", ".
	 		"fullday = ".($this->isFullday() ? 1 : 0).", ".
	 		"start = ".$this->db->quote($this->getStart()->get(IL_CAL_DATETIME,'','UTC')).", ".
	 		"end = ".$this->db->quote($this->getEnd()->get(IL_CAL_DATETIME,'','UTC')).", ".
	 		"informations = ".$this->db->quote($this->getFurtherInformations()).", ".
	 		"auto_generated = ".$this->db->quote($this->isAutoGenerated()).", ".
	 		"context_id = ".$this->db->quote($this->getContextId()).", ".
	 		"translation_type = ".$this->db->quote($this->getTranslationType())." ";
	 		
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
		include_once('./Services/Calendar/classes/class.ilCalendarRecurrence.php');
		ilCalendarRecurrence::_delete($this->getEntryId());
		
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
			$this->setLastUpdate(new ilDateTime($row->last_update,IL_CAL_DATETIME,'UTC'));
			$this->setTitle($row->title);
			$this->setSubtitle($row->subtitle);
			$this->setDescription($row->description);
			$this->setLocation($row->location);
			$this->setFurtherInformations($row->informations);
			$this->setFullday((bool) $row->fullday);
			$this->setAutoGenerated($row->auto_generated);
			$this->setContextId($row->context_id);
			$this->setTranslationType($row->translation_type);
			
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