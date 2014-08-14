<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/Calendar/classes/class.ilDate.php');
include_once('./Services/Calendar/interfaces/interface.ilDatePeriod.php');

define('IL_CAL_TRANSLATION_NONE',0);
define('IL_CAL_TRANSLATION_SYSTEM',1);


/** 
* Model for a calendar entry. 
* 
* @author Stefan Meyer <meyer@leifos.com>
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
	protected $is_milestone = false;
	protected $completion = 0;

	protected $notification = false;

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
	 * clone instance
	 */
	public function __clone()
	{
		$this->entry_id = NULL;
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
	public function getPresentationTitle($a_shorten = true)
	{
		global $lng;
		
		if($this->getTranslationType() == IL_CAL_TRANSLATION_NONE)
		{
			$title = $this->getTitle();
		}
		elseif(strlen($this->getSubtitle()))
		{
			// parse dynamic title?
			if(preg_match("/#([a-z]+)#/", $this->getSubtitle(), $matches))
			{
				$subtitle = $this->parseDynamicTitle($matches[1]);
			}
			else
			{
				$subtitle = $lng->txt($this->getSubtitle());
			}
			$title = $this->getTitle().
				(strlen($subtitle) 
				? ' ('.$subtitle.')'
				: '');
		}		
		else
		{
			$title = $lng->txt($this->getTitle());
		}

		if($a_shorten)
		{
			return ilUtil::shortenText(ilUtil::shortenWords($title,20),40,true);
		}
		return $title;
	}
	
	public function getPresentationStyle()
	{
		// see parseDynamicTitle()
		return $this->presentation_style;
	}
	
	protected function parseDynamicTitle($a_type)
	{
		global $lng;
		
		$title = $style = "";
		switch($a_type)
		{
			case "consultationhour":
				include_once 'Services/Booking/classes/class.ilBookingEntry.php';
				$entry = new ilBookingEntry($this->getContextId());
				if($entry)
				{
					if($entry->isOwner())
					{
						$max = (int)$entry->getNumberOfBookings();
						$current = (int)$entry->getCurrentNumberOfBookings($this->getEntryId());						
						if(!$current)
						{
							$style = ';border-left-width: 5px; border-left-style: solid; border-left-color: green';
							$title = $lng->txt('cal_book_free');
						}
						elseif($current >= $max)
						{
							$style = ';border-left-width: 5px; border-left-style: solid; border-left-color: red';
							$title = $lng->txt('cal_booked_out');
						}
						else
						{
							$style = ';border-left-width: 5px; border-left-style: solid; border-left-color: yellow';
							$title = $current.'/'.$max;
						}
					}				
					else
					{
						/*
						 * if($entry->hasBooked($this->getEntryId()))
						 */
						include_once 'Services/Calendar/classes/ConsultationHours/class.ilConsultationHourAppointments.php';
						$apps = ilConsultationHourAppointments::getAppointmentIds($entry->getObjId(), $this->getContextId(), $this->getStart());
						$orig_event = $apps[0];
						if($entry->hasBooked($orig_event))
						{
							$style = ';border-left-width: 5px; border-left-style: solid; border-left-color: green';
							$title = $lng->txt('cal_date_booked');
						}
					}
				}												
				break;						
		}
		
		if($style)
		{
			$this->presentation_style = $style;
		}
		return $title;
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
	 * is milestone
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function isMilestone()
	{
		return (bool) $this->is_milestone;
	}
	
	/**
	 * set milestone
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function setMilestone($a_status)
	{
		$this->is_milestone = $a_status;
	}

	/**
	* Set Completion.
	*
	* @param	int	$a_completion	Completion
	*/
	function setCompletion($a_completion)
	{
		$this->completion = $a_completion;
	}

	/**
	* Get Completion.
	*
	* @return	int	Completion
	*/
	function getCompletion()
	{
		return $this->completion;
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
	 * Enable course group notification
	 * @param bool $a_status 
	 */
	public function enableNotification($a_status)
	{
		$this->notification = $a_status;
	}
	
	/**
	 * Check if course group notification is enabled
	 * @return bool
	 */
	public function isNotificationEnabled()
	{
		return (bool) $this->notification;
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
	 	$utc_timestamp = $now->get(IL_CAL_DATETIME,'',ilTimeZone::UTC);
	 	
	 	
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
	 		"context_id = ".$this->db->quote($this->getContextId() ,'integer').", ".
			"completion = ".$this->db->quote($this->getCompletion(), 'integer').", ".
			"is_milestone = ".$this->db->quote($this->isMilestone() ? 1 : 0, 'integer').", ".
			'notification = '.$this->db->quote($this->isNotificationEnabled() ? 1 : 0,'integer').' '.
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
	 	$utc_timestamp = $now->get(IL_CAL_DATETIME,'',ilTimeZone::UTC);

	 	$query = "INSERT INTO cal_entries (cal_id,title,last_update,subtitle,description,location,fullday,starta,enda, ".
			"informations,auto_generated,context_id,translation_type, completion, is_milestone, notification) ".
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
	 		$this->db->quote($this->getTranslationType() ,'integer').", ".
			$this->db->quote($this->getCompletion(), 'integer').", ".
			$this->db->quote($this->isMilestone() ? 1 : 0, 'integer').", ".
			$this->db->quote($this->isNotificationEnabled() ? 1 : 0,'integer').' '.
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
		
		include_once './Services/Calendar/classes/class.ilCalendarCategoryAssignments.php';
		ilCalendarCategoryAssignments::_deleteByAppointmentId($this->getEntryId());
		
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
		global $ilErr,$lng;
		
		$success = true;
		$ilErr->setMessage('');
		if(!strlen($this->getTitle()))
		{
			$success = false;
			$ilErr->appendMessage($lng->txt('err_missing_title'));
		}
		if(ilDateTime::_before($this->getEnd(),$this->getStart(),''))
		{
			$success = false;
			$ilErr->appendMessage($lng->txt('err_end_before_start'));
		}
		return $success;
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
			$this->setCompletion($row->completion);
			$this->setMilestone($row->is_milestone);
			$this->enableNotification((bool) $row->notification);
			
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
	
	/**
	 * 
	 * @param ilLanguage $lng
	 * @return 
	 */
	public function appointmentToMailString($lng)
	{
		$body = $lng->txt('cal_details');
		$body .= "\n\n";
		$body .= $lng->txt('title').': '.$this->getTitle()."\n";
		
		ilDatePresentation::setUseRelativeDates(false);
		$body .= $lng->txt('date').': '.ilDatePresentation::formatPeriod($this->getStart(), $this->getEnd())."\n";
		ilDatePresentation::setUseRelativeDates(true);
		
		if(strlen($this->getLocation()))
		{
			$body .= $lng->txt('cal_where').': '.$this->getLocation()."\n";
		}
	
		if(strlen($this->getDescription()))
		{
			$body .= $lng->txt('description').': '.$this->getDescription()."\n";
		}
		return $body;
	}
	
	
	/**
	* Write users responsible for a milestone
	*/
	function writeResponsibleUsers($a_users)
	{
		global $ilDB;
		
		$ilDB->manipulateF("DELETE FROM cal_entry_responsible WHERE cal_id = %s",
			array("integer"), array($this->getEntryId()));
		
		if (is_array($a_users))
		{
			foreach ($a_users as $user_id)
			{
				$ilDB->manipulateF("INSERT INTO cal_entry_responsible (cal_id, user_id) ".
					" VALUES (%s,%s)", array("integer", "integer"),
					array($this->getEntryId(), $user_id));
			}
		}
		
		$this->responsible_users = $a_users;
	}
	
	/**
	* Read responsible users
	*/
	function readResponsibleUsers()
	{
		global $ilDB;
		
		$set = $ilDB->queryF("SELECT * FROM cal_entry_responsible WHERE cal_id = %s",
			array("integer"), array($this->getEntryId()));

		$return = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$n = ilObjUser::_lookupName($rec["user_id"]);
			$return[] = array_merge($n,
				array("login" => ilObjUser::_lookupLogin($rec["user_id"])));
		}

		return $return;
	}
}
?>
