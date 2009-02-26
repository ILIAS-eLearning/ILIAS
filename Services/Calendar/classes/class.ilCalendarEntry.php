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
			"WHERE cal_id = ".$ilDB->quote($a_entry_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
		
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
			$title = $this->getTitle();
		}
		else
		{
			$title = $this->getTitle().' ('.$lng->txt($this->getSubtitle()).')';
		}
		
		return ilUtil::shortenText(ilUtil::shortenWords($title,20),40,true);
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
	 	global $ilDB;
	 	
	 	$now = new ilDateTime(time(),IL_CAL_UNIX);
	 	$utc_timestamp = $now->get(IL_CAL_TIMESTAMP,'',ilTimeZone::UTC);
	 	
	 	
	 	$query = "UPDATE cal_entries ".
	 		"SET title = ".$this->db->quote($this->getTitle() ,'text').", ".
	 		"last_update = ".$ilDB->quote($utc_timestamp,'timestamp').", ".
	 		"subtitle = ".$this->db->quote($this->getSubtitle() ,'text').", ".
	 		"description = ".$this->db->quote($this->getDescription(),'text').", ".
	 		"location = ".$this->db->quote($this->getLocation() ,'text').", ".
	 		"fullday = ".$ilDB->quote($this->isFullday() ? 1 : 0,'integer').", ".
	 		"starta = ".$this->db->quote($this->getStart()->get(IL_CAL_DATETIME,'','UTC'),'timestamp').", ".
	 		"enda = ".$this->db->quote($this->getEnd()->get(IL_CAL_DATETIME,'','UTC'),'timestamp').", ".
	 		"informations = ".$this->db->quote($this->getFurtherInformations() ,'text').", ".
	 		"auto_generated =  ".$this->db->quote($this->isAutoGenerated() ,'integer').", ".
	 		"translation_type = ".$this->db->quote($this->getTranslationType() ,'integer').", ".
	 		"context_id = ".$this->db->quote($this->getContextId() ,'integer')." ".
	 		"WHERE cal_id = ".$this->db->quote($this->getEntryId() ,'integer')." ";
	 	$res = $ilDB->manipulate($query);

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
	 	global $ilDB;
	 	
	 	$next_id = $ilDB->nextId('cal_entries');
	 	$now = new ilDateTime(time(),IL_CAL_UNIX);
	 	$utc_timestamp = $now->get(IL_CAL_TIMESTAMP,'',ilTimeZone::UTC);

	 	$query = "INSERT INTO cal_entries (cal_id,title,last_update,subtitle,description,location,fullday,starta,enda, ".
			"informations,auto_generated,context_id,translation_type) ".
			"VALUES( ".
			$ilDB->quote($next_id,'integer').", ".
	 		$this->db->quote($this->getTitle(),'text').", ".
	 		$ilDB->quote($utc_timestamp,'timestamp').", ".
	 		$this->db->quote($this->getSubtitle(),'text').", ".
	 		$this->db->quote($this->getDescription() ,'text').", ".
	 		$this->db->quote($this->getLocation() ,'text').", ".
	 		$ilDB->quote($this->isFullday() ? 1 : 0,'integer').", ".
	 		$this->db->quote($this->getStart()->get(IL_CAL_DATETIME,'','UTC'),'timestamp').", ".
	 		$this->db->quote($this->getEnd()->get(IL_CAL_DATETIME,'','UTC'),'timestamp').", ".
	 		$this->db->quote($this->getFurtherInformations() ,'text').", ".
	 		$this->db->quote($this->isAutoGenerated() ,'integer').", ".
	 		$this->db->quote($this->getContextId() ,'integer').", ".
	 		$this->db->quote($this->getTranslationType() ,'integer')." ".
	 		")";
	 	$res = $ilDB->manipulate($query);	
		
		$this->entry_id = $next_id;		
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
		global $ilDB;
		
		include_once('./Services/Calendar/classes/class.ilCalendarRecurrence.php');
		ilCalendarRecurrence::_delete($this->getEntryId());
		
		$query = "DELETE FROM cal_entries ".
			"WHERE cal_id = ".$this->db->quote($this->getEntryId() ,'integer')." ";
		$res = $ilDB->manipulate($query);
		
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
	 	global $ilDB;
	 	
	 	$query = "SELECT * FROM cal_entries WHERE cal_id = ".$this->db->quote($this->getEntryId() ,'integer')." ";
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
				$this->start = new ilDate($row->starta,IL_CAL_DATETIME);
				$this->end = new ilDate($row->enda,IL_CAL_DATETIME);
			}
			else
			{
				$this->start = new ilDateTime($row->starta,IL_CAL_DATETIME,'UTC');
				$this->end = new ilDateTime($row->enda,IL_CAL_DATETIME,'UTC');
			}
		}
		
	}
}
?>