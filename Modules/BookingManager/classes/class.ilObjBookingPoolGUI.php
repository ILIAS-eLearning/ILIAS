<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjBookingPoolGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
* 
* @ilCtrl_Calls ilObjBookingPoolGUI: ilPermissionGUI, ilBookingObjectGUI
* @ilCtrl_Calls ilObjBookingPoolGUI: ilBookingScheduleGUI, ilInfoScreenGUI, ilPublicUserProfileGUI
* @ilCtrl_Calls ilObjBookingPoolGUI: ilCommonActionDispatcherGUI, ilObjectCopyGUI
* @ilCtrl_IsCalledBy ilObjBookingPoolGUI: ilRepositoryGUI, ilAdministrationGUI
*/
class ilObjBookingPoolGUI extends ilObjectGUI
{
	/**
	* Constructor
	*
	*/
	function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		$this->type = "book";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		$this->lng->loadLanguageModule("book");
	}

	/**
	 * main switch
	 */
	function executeCommand()
	{
		global $tpl, $ilTabs, $ilNavigationHistory, $ilUser;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		if(!$next_class && $cmd == 'render')
		{
			if($ilUser->getId() != ANONYMOUS_USER_ID)
			{
				$this->ctrl->setCmdClass('ilBookingObjectGUI');
				$next_class = $this->ctrl->getNextClass($this);
			}
			else
			{
				$this->ctrl->redirect($this, "infoscreen");
			}
		}

		if(substr($cmd, 0, 4) == 'book')
		{
			$next_class = '';
		}

		$ilNavigationHistory->addItem($this->ref_id,
				"./goto.php?target=book_".$this->ref_id, "book");

		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilbookingobjectgui':
				$this->tabs_gui->setTabActive('render');
				include_once("Modules/BookingManager/classes/class.ilBookingObjectGUI.php");
				$object_gui =& new ilBookingObjectGUI($this);
				$ret =& $this->ctrl->forwardCommand($object_gui);
				break;

			case 'ilbookingschedulegui':
				$this->tabs_gui->setTabActive('schedules');
				include_once("Modules/BookingManager/classes/class.ilBookingScheduleGUI.php");
				$schedule_gui =& new ilBookingScheduleGUI($this);
				$ret =& $this->ctrl->forwardCommand($schedule_gui);
				break;

			case 'ilpublicuserprofilegui':
				$ilTabs->clearTargets();
				include_once("Services/User/classes/class.ilPublicUserProfileGUI.php");
				$profile = new ilPublicUserProfileGUI((int)$_GET["user_id"]);
				$profile->setBackUrl($this->ctrl->getLinkTarget($this, 'log'));
				$ret = $this->ctrl->forwardCommand($profile);
				$tpl->setContent($ret);
				break;

			case 'ilinfoscreengui':
				$this->infoScreen();
				break;
			
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			
			case "ilobjectcopygui":
				include_once "./Services/Object/classes/class.ilObjectCopyGUI.php";
				$cp = new ilObjectCopyGUI($this);
				$cp->setType("book");
				$this->ctrl->forwardCommand($cp);
				break;
			
			default:
				$cmd = $this->ctrl->getCmd();
				$cmd .= 'Object';
				$this->$cmd();
				break;
		}
		
		$this->addHeaderAction();
		return true;
	}

	protected function initCreationForms($a_new_type)
	{
		$forms = parent::initCreationForms($a_new_type);
		unset($forms[self::CFORM_IMPORT]);		
		
		return $forms;
	}

	protected function afterSave(ilObject $a_new_object)
	{
		$a_new_object->setOffline(true);
		$a_new_object->update();

		// always send a message
		ilUtil::sendSuccess($this->lng->txt("book_pool_added"),true);
		$this->ctrl->setParameter($this, "ref_id", $a_new_object->getRefId());
		$this->ctrl->redirect($this, "edit");
	}
	
	public function editObject()
	{
		// if we have no schedules yet - show info
		include_once "Modules/BookingManager/classes/class.ilBookingSchedule.php";
		if($this->object->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE &&
			!sizeof(ilBookingSchedule::getList($this->object->getId())))
		{
			ilUtil::sendInfo($this->lng->txt("book_schedule_warning_edit"));
		}

		return parent::editObject();
	}
	
	protected function initEditCustomForm(ilPropertyFormGUI $a_form)
	{
		$online = new ilCheckboxInputGUI($this->lng->txt("online"), "online");
		$a_form->addItem($online);

		$type = new ilRadioGroupInputGUI($this->lng->txt("book_schedule_type"), "stype");
		$type->setRequired(true);
		$a_form->addItem($type);
		
		// #14478
		include_once "Modules/BookingManager/classes/class.ilBookingObject.php";
		if(sizeof(ilBookingObject::getList($this->object->getId())))
		{
			$type->setDisabled(true);
		}
		
		$fixed = new ilRadioOption($this->lng->txt("book_schedule_type_fixed"), ilObjBookingPool::TYPE_FIX_SCHEDULE);
		$fixed->setInfo($this->lng->txt("book_schedule_type_fixed_info"));
		$type->addOption($fixed);
		
		$none = new ilRadioOption($this->lng->txt("book_schedule_type_none"), ilObjBookingPool::TYPE_NO_SCHEDULE);
		$none->setInfo($this->lng->txt("book_schedule_type_none_info"));
		$type->addOption($none);
		
		$limit = new ilNumberInputGUI($this->lng->txt("book_overall_limit"), "limit");
		$limit->setSize(4);
		$limit->setMinValue(1);
		$limit->setInfo($this->lng->txt("book_overall_limit_info"));
		$none->addSubItem($limit);
	
		$public = new ilCheckboxInputGUI($this->lng->txt("book_public_log"), "public");
		$public->setInfo($this->lng->txt("book_public_log_info"));
		$a_form->addItem($public);		
	}

	protected function getEditFormCustomValues(array &$a_values)
	{
		$a_values["online"] = !$this->object->isOffline();
		$a_values["public"] = $this->object->hasPublicLog();
		$a_values["stype"] = $this->object->getScheduleType();
		$a_values["limit"] = $this->object->getOverallLimit();
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{		
		$this->object->setOffline(!$a_form->getInput('online'));
		$this->object->setPublicLog($a_form->getInput('public'));
		$this->object->setScheduleType($a_form->getInput('stype'));
		$this->object->setOverallLimit($a_form->getInput('limit') ? $a_form->getInput('limit') : null);
	}

	/**
	* get tabs
	*/
	function setTabs()
	{
		global $ilAccess, $ilHelp, $ilUser;
		
		if (in_array($this->ctrl->getCmd(), array("create", "save")) && !$this->ctrl->getNextClass())
		{
			return;
		}
		
		$ilHelp->setScreenIdComponent("book");
		
		if ($ilAccess->checkAccess('read', '', $this->object->getRefId()))
		{
			if($ilUser->getId() != ANONYMOUS_USER_ID)
			{
				$this->tabs_gui->addTab("render",
						$this->lng->txt("book_booking_types"),
						$this->ctrl->getLinkTarget($this, "render"));
			}
		
			$this->tabs_gui->addTab("info",
					$this->lng->txt("info_short"),
					$this->ctrl->getLinkTarget($this, "infoscreen"));

			if($ilUser->getId() != ANONYMOUS_USER_ID || $this->object->hasPublicLog())
			{
				$this->tabs_gui->addTab("log",
					$this->lng->txt("book_log"),
					$this->ctrl->getLinkTarget($this, "log"));		
			}
		}
		
		if ($ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			if($this->object->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE)
			{
				$this->tabs_gui->addTab("schedules",
					$this->lng->txt("book_schedules"),
					$this->ctrl->getLinkTargetByClass("ilbookingschedulegui", "render"));
			}
			
			$this->tabs_gui->addTab("settings",
				$this->lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "edit"));
		}

		if($ilAccess->checkAccess('edit_permission', '', $this->object->getRefId()))
		{
			$this->tabs_gui->addTab("perm_settings",
				$this->lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"));
		}
	}

	/**
	 * First step in booking process
	 */
	function bookObject()
	{
		global $tpl;
		
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'render'));

		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$obj = new ilBookingObject((int)$_GET['object_id']);
				
		$this->lng->loadLanguageModule("dateplaner");
		$this->ctrl->setParameter($this, 'object_id', $obj->getId());
		
		if($this->object->getScheduleType() == ilObjBookingPool::TYPE_FIX_SCHEDULE)
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';		
			$schedule = new ilBookingSchedule($obj->getScheduleId());

			$tpl->setContent($this->renderSlots($schedule, array($obj->getId()), $obj->getTitle()));
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setHeaderText($this->lng->txt("book_confirm_booking_no_schedule"));

			$cgui->setFormAction($this->ctrl->getFormAction($this));
			$cgui->setCancel($this->lng->txt("cancel"), "render");
			$cgui->setConfirm($this->lng->txt("confirm"), "confirmedBooking");

			$cgui->addItem("object_id", $obj->getId(), $obj->getTitle());		

			$tpl->setContent($cgui->getHTML());
		}
	}

	protected function renderSlots(ilBookingSchedule $schedule, array $object_ids, $title)
	{
		global $ilUser;
		
		// fix
		if(!$schedule->getRaster())
		{
			$mytpl = new ilTemplate('tpl.booking_reservation_fix.html', true, true, 'Modules/BookingManager');

			$mytpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this));
			$mytpl->setVariable('TXT_TITLE', $this->lng->txt('book_reservation_title'));
			$mytpl->setVariable('TXT_INFO', $this->lng->txt('book_reservation_fix_info'));
			$mytpl->setVariable('TXT_OBJECT', $title);
			$mytpl->setVariable('TXT_CMD_BOOK', $this->lng->txt('book_confirm_booking'));
			$mytpl->setVariable('TXT_CMD_CANCEL', $this->lng->txt('cancel'));

			include_once 'Services/Calendar/classes/class.ilCalendarUserSettings.php';
			
			$user_settings = ilCalendarUserSettings::_getInstanceByUserId($ilUser->getId());

			$morning_aggr = $user_settings->getDayStart();
			$evening_aggr = $user_settings->getDayEnd();
			$hours = array();
			for($i = $morning_aggr;$i <= $evening_aggr;$i++)
			{
				switch($user_settings->getTimeFormat())
				{
					case ilCalendarSettings::TIME_FORMAT_24:
						if ($morning_aggr > 0 && $i == $morning_aggr)
						{
							$hours[$i] = sprintf('%02d:00',0)."-";
						}
						$hours[$i].= sprintf('%02d:00',$i);
						if ($evening_aggr < 23 && $i == $evening_aggr)
						{
							$hours[$i].= "-".sprintf('%02d:00',23);
						}
						break;

					case ilCalendarSettings::TIME_FORMAT_12:
						if ($morning_aggr > 0 && $i == $morning_aggr)
						{
							$hours[$i] = date('h a',mktime(0,0,0,1,1,2000))."-";
						}
						$hours[$i].= date('h a',mktime($i,0,0,1,1,2000));
						if ($evening_aggr < 23 && $i == $evening_aggr)
						{
							$hours[$i].= "-".date('h a',mktime(23,0,0,1,1,2000));
						}
						break;
				}
			}

			if(isset($_GET['seed']))
			{
				$find_first_open = false;
				$seed = new ilDate($_GET['seed'], IL_CAL_DATE);
			}
			else
			{
				$find_first_open = true;
				$seed = new ilDate(time(), IL_CAL_UNIX);
			}
			
			include_once 'Services/Calendar/classes/class.ilCalendarUtil.php';
			include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';			
			$week_start = $user_settings->getWeekStart();
			
			if(!$find_first_open)
			{
				$dates = array();
				$this->buildDatesBySchedule($week_start, $hours, $schedule, $object_ids, $seed, $dates);
			}
			else
			{
				$dates = array();
				$has_open_slot = $this->buildDatesBySchedule($week_start, $hours, $schedule, $object_ids, $seed, $dates);
				
				// find first open slot
				if(!$has_open_slot)
				{
					// 1 year is limit for search
					$limit = clone($seed);
					$limit->increment(ilDate::YEAR, 1);
					$limit = $limit->get(IL_CAL_UNIX);
					
					while(!$has_open_slot && $seed->get(IL_CAL_UNIX) < $limit)
					{
						$seed->increment(ilDate::WEEK, 1);
						
						$dates = array();
						$has_open_slot = $this->buildDatesBySchedule($week_start, $hours, $schedule, $object_ids, $seed, $dates);
					}	
				}
			}			
			
			include_once 'Services/Calendar/classes/class.ilCalendarHeaderNavigationGUI.php';
			$navigation = new ilCalendarHeaderNavigationGUI($this,$seed,ilDateTime::WEEK,'book');
			$mytpl->setVariable('NAVIGATION', $navigation->getHTML());

			foreach(ilCalendarUtil::_buildWeekDayList($seed,$week_start)->get() as $date)
			{
				$date_info = $date->get(IL_CAL_FKT_GETDATE,'','UTC');

				$mytpl->setCurrentBlock('weekdays');
				$mytpl->setVariable('TXT_WEEKDAY', ilCalendarUtil:: _numericDayToString($date_info['wday']));
				$mytpl->setVariable('TXT_DATE', $date_info['mday'].' '.ilCalendarUtil:: _numericMonthToString($date_info['mon']));
				$mytpl->parseCurrentBlock();
			}
			
			include_once 'Services/Calendar/classes/class.ilCalendarAppointmentColors.php';
			include_once 'Services/Calendar/classes/class.ilCalendarUtil.php';
			$color = array();
			$all = ilCalendarAppointmentColors::_getColorsByType('crs');
			for($loop = 0; $loop < 7; $loop++)
		    {
				$col = $all[$loop];
				$fnt = ilCalendarUtil::calculateFontColor($col);
				$color[$loop+1] = 'border-bottom: 1px solid '.$col.'; background-color: '.$col.'; color: '.$fnt;
			}
			
			$counter = 0;
			foreach($dates as $hour => $days)
			{
				$caption = $days;
				$caption = array_shift($caption);

				for($loop = 1; $loop < 8; $loop++)
			    {
					if(!isset($days[$loop]))
					{
						$mytpl->setCurrentBlock('dates');
						$mytpl->setVariable('DUMMY', '&nbsp;');
						$mytpl->parseCurrentBlock();
					}
					else
					{
						if(isset($days[$loop]['captions']))
						{
							foreach($days[$loop]['captions'] as $slot_id => $slot_caption)
							{								
								$mytpl->setCurrentBlock('choice');
								$mytpl->setVariable('TXT_DATE', $slot_caption);
								$mytpl->setVariable('VALUE_DATE', $slot_id);
								$mytpl->setVariable('DATE_COLOR', $color[$loop]);
								$mytpl->setVariable('TXT_AVAILABLE', 
									sprintf($this->lng->txt('book_reservation_available'), 
									$days[$loop]['available'][$slot_id]));
								$mytpl->parseCurrentBlock();
							}

							$mytpl->setCurrentBlock('dates');
							$mytpl->setVariable('DUMMY', '');
							$mytpl->parseCurrentBlock();
						}
						else if(isset($days[$loop]['in_slot']))
						{
							$mytpl->setCurrentBlock('dates');
							$mytpl->setVariable('DATE_COLOR', $color[$loop]);
							$mytpl->parseCurrentBlock();
						}
						else
						{
							$mytpl->setCurrentBlock('dates');
							$mytpl->setVariable('DUMMY', '&nbsp;');
							$mytpl->parseCurrentBlock();
						}
					}
				}

				$mytpl->setCurrentBlock('slots');
				$mytpl->setVariable('TXT_HOUR', $caption);
				if($counter%2)
				{
					$mytpl->setVariable('CSS_ROW', 'tblrow1');
				}
				else
				{
					$mytpl->setVariable('CSS_ROW', 'tblrow2');
				}
				$mytpl->parseCurrentBlock();

				$counter++;
			}
		}
		// flexible
		else
		{
			// :TODO: inactive for now
		}

		return $mytpl->get();
	}
	
	protected function buildDatesBySchedule($week_start, array $hours, $schedule, array $object_ids, $seed, array &$dates)
	{
		global $ilUser;
		
		include_once 'Services/Calendar/classes/class.ilCalendarUserSettings.php';			
		$user_settings = ilCalendarUserSettings::_getInstanceByUserId($ilUser->getId());		
		
		$map = array('mo', 'tu', 'we', 'th', 'fr', 'sa', 'su');
		$definition = $schedule->getDefinition();
		
		$has_open_slot = false;
		foreach(ilCalendarUtil::_buildWeekDayList($seed,$week_start)->get() as $date)
		{
			$date_info = $date->get(IL_CAL_FKT_GETDATE,'','UTC');

			$slots = array();
			if(isset($definition[$map[$date_info['isoday']-1]]))
			{
				$slots = array();
				foreach($definition[$map[$date_info['isoday']-1]] as $slot)
				{
					$slot = explode('-', $slot);
					$slots[] = array('from'=>str_replace(':', '', $slot[0]),
						'to'=>str_replace(':', '', $slot[1]));
				}
			}

			$last = array_pop(array_keys($hours));
			$slot_captions = array();
			foreach($hours as $hour => $period)
			{
				$dates[$hour][0] = $period;
				
				$period = explode("-", $period);
				
				// #13738
				if($user_settings->getTimeFormat() == ilCalendarSettings::TIME_FORMAT_12)
				{					
					if(stristr($period[0], "pm"))
					{
						$period[0] = (int)$period[0]+12;
					}
					else
					{
						$period[0] = (int)$period[0];
						if($period[0] == 12)
						{
							$period[0] = 0;
						}
					}					
					if(sizeof($period) == 2)
					{
						if(stristr($period[1], "pm"))
						{
							$period[1] = (int)$period[1]+12;
						}
						else
						{
							$period[1] = (int)$period[1];
							if($period[1] == 12)
							{
								$period[1] = 0;
							}
						}
					}					
				}
				
				if(sizeof($period) == 1)
				{
					$period_from = (int)substr($period[0], 0, 2)."00";
					$period_to = (int)substr($period[0], 0, 2)."59";
				}
				else
				{
					$period_from = (int)substr($period[0], 0, 2)."00";
					$period_to = (int)substr($period[1], 0, 2)."59";
				}		

				$column = $date_info['isoday'];
				if(!$week_start)
				{
					if($column < 7)
					{
						$column++;
					}
					else
					{
						$column = 1;
					}
				}

				if(sizeof($slots))
				{						
					$in = false;
					foreach($slots as $slot)
					{
						$slot_from = mktime(substr($slot['from'], 0, 2), substr($slot['from'], 2, 2), 0, $date_info["mon"], $date_info["mday"], $date_info["year"]);
						$slot_to = mktime(substr($slot['to'], 0, 2), substr($slot['to'], 2, 2), 0, $date_info["mon"], $date_info["mday"], $date_info["year"]);

						// always single object, we can sum up
						$nr_available = (array)ilBookingReservation::getAvailableObject($object_ids, $slot_from, $slot_to-1, false, true);						
						
						// check deadline
						if($slot_from < (time()+$schedule->getDeadline()*60*60) || !array_sum($nr_available))
						{
							continue;
						}

						// is slot active in current hour?
						if((int)$slot['from'] < $period_to && (int)$slot['to'] > $period_from)
						{
							$from = ilDatePresentation::formatDate(new ilDateTime($slot_from, IL_CAL_UNIX));
							$from = array_pop(explode(' ', $from));
							$to = ilDatePresentation::formatDate(new ilDateTime($slot_to, IL_CAL_UNIX));
							$to = array_pop(explode(' ', $to));

							// show caption (first hour) of slot
							$id = $slot_from.'_'.$slot_to;
							if(!in_array($id, $slot_captions))
							{
								$dates[$hour][$column]['captions'][$id] = $from.'-'.$to;
								$dates[$hour][$column]['available'][$id] = array_sum($nr_available);
								$slot_captions[] = $id;
							}

							$in = true;
						}
					}
					// (any) active slot
					if($in)
					{
						$has_open_slot = true;
						$dates[$hour][$column]['in_slot'] = $in;
					}
				}
			}
		}

		return $has_open_slot;
	}

	/**
	 * Book object - either of type or specific - for given dates
	 */
	function confirmedBookingObject()
	{				
		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';		
		
		$success = false;
		$rsv_ids = array();
		
		if($this->object->getScheduleType() == ilObjBookingPool::TYPE_NO_SCHEDULE)
		{	
			if($_POST['object_id'])
			{
				$object_id = $_POST['object_id'];
				if($object_id)
				{
					if(ilBookingReservation::isObjectAvailableNoSchedule($object_id))				
					{
						$rsv_ids[] = $this->processBooking($object_id);						
						$success = $object_id;	
					}
					else
					{
						// #11852
						ilUtil::sendFailure($this->lng->txt('book_reservation_failed_overbooked'), true);
						$this->ctrl->redirect($this, 'render');						
					}
				}
			}
		}	
		else
		{												
			if(!isset($_POST['date']))
			{
				ilUtil::sendFailure($this->lng->txt('select_one'));
				return $this->bookObject();
			}
						
			// single object reservation(s)
			if(isset($_GET['object_id']))
			{
				$confirm = array();
				
				$object_id = (int)$_GET['object_id'];
				if($object_id)
				{	
					$group_id = null;
					$nr = ilBookingObject::getNrOfItemsForObjects(array($object_id));
					// needed for recurrence
					if(true) // if($nr[$object_id] > 1 || sizeof($_POST['date']) > 1)
					{
						$group_id = ilBookingReservation::getNewGroupId();									
					}
					foreach($_POST['date'] as $date)
					{										
						$fromto = explode('_', $date);
						$fromto[1]--;

						$counter = ilBookingReservation::getAvailableObject(array($object_id), $fromto[0], $fromto[1], false, true);
						$counter = $counter[$object_id];
						if($counter)
						{					
							// needed for recurrence
							if(true) // if($counter > 1)
							{
								$confirm[$object_id."_".$fromto[0]."_".($fromto[1]+1)] = $counter;
							}
							else
							{								
								$rsv_ids[] = $this->processBooking($object_id, $fromto[0], $fromto[1], $group_id);
								$success = $object_id;									
							}
						}
					}
				}
				
				if(sizeof($confirm))
				{
					return $this->confirmBookingNumbers($confirm, $group_id);					
				}
			}
			/*
			// group object reservation(s)
			else
			{														
				$all_object_ids = array();
				foreach(ilBookingObject::getList((int)$_GET['type_id']) as $item)
				{
					$all_object_ids[] = $item['booking_object_id'];
				}

				$possible_objects = $counter = array();	
				sort($_POST['date']);			
				foreach($_POST['date'] as $date)
				{
					$fromto = explode('_', $date);
					$fromto[1]--;
					$possible_objects[$date] = ilBookingReservation::getAvailableObject($all_object_ids, $fromto[0], $fromto[1], false);		
					foreach($possible_objects[$date] as $obj_id)
					{
						$counter[$obj_id]++;
					}
				}

				if(max($counter))
				{			
					// we prefer the objects which are available for most slots
					arsort($counter);
					$counter = array_keys($counter);

					// book each slot
					foreach($possible_objects as $date => $available_ids)
					{
						$fromto = explode('_', $date);
						$fromto[1]--;

						// find "best" object for slot
						foreach($counter as $best_object_id)
						{
							if(in_array($best_object_id, $available_ids))
							{
								$object_id = $best_object_id;
								break;
							}
						}				
						$this->processBooking($object_id, $fromto[0], $fromto[1]);
						$success = true;	
					}
				}
			}			 
			*/
		}
		
		if($success)
		{
			$this->handleBookingSuccess($success, $rsv_ids);
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt('book_reservation_failed'), true);
			$this->ctrl->redirect($this, 'book');
		}
	}
	
	protected function handleBookingSuccess($a_obj_id, array $a_rsv_ids = null)
	{
		ilUtil::sendSuccess($this->lng->txt('book_reservation_confirmed'), true);
			
		// show post booking information?
		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$obj = new ilBookingObject($a_obj_id);
		$pfile = $obj->getPostFile();
		$ptext = $obj->getPostText();
		if(trim($ptext) || $pfile)
		{
			if(sizeof($a_rsv_ids))
			{
				$this->ctrl->setParameterByClass('ilbookingobjectgui', 'rsv_ids', implode(";", $a_rsv_ids));		
			}
			$this->ctrl->setParameterByClass('ilbookingobjectgui', 'object_id', $obj->getId());				
			$this->ctrl->redirectByClass('ilbookingobjectgui', 'displayPostInfo');
		}
		else
		{				
			$this->ctrl->redirect($this, 'render');
		}
	}
	
	protected function initBookingNumbersForm(array $a_objects_counter, $a_group_id, $a_reload = false)
	{
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, "confirmedBooking"));
		$form->setTitle($this->lng->txt("book_confirm_booking_schedule_number_of_objects"));
		$form->setDescription($this->lng->txt("book_confirm_booking_schedule_number_of_objects_info"));
		
		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$section = false;
		foreach($a_objects_counter as $id => $counter)
		{			
			$id = explode("_", $id);
			$book_id = $id[0]."_".$id[1]."_".$id[2]."_".$counter;
			
			$obj = new ilBookingObject($id[0]);
			
			if(!$section)
			{
				$section = new ilFormSectionHeaderGUI();
				$section->setTitle($obj->getTitle());
				$form->addItem($section);
				
				$section = true;
			}
			
			$period = /* $this->lng->txt("book_period").": ". */
				ilDatePresentation::formatPeriod(
					new ilDateTime($id[1], IL_CAL_UNIX),
					new ilDateTime($id[2], IL_CAL_UNIX));
			
			$nr_field = new ilNumberInputGUI($period, "conf_nr__".$book_id);
			$nr_field->setValue(1);
			$nr_field->setSize(3);
			$nr_field->setMaxValue($counter);
			$nr_field->setMinValue($counter ? 1 : 0);
			$nr_field->setRequired(true);
			$form->addItem($nr_field);				
		}
		
		// recurrence
		$this->lng->loadLanguageModule("dateplaner");
		$rec_mode = new ilSelectInputGUI($this->lng->txt("cal_recurrences"), "recm");
		$rec_mode->setRequired(true);
		$rec_mode->setOptions(array(			
			"-1" => $this->lng->txt("cal_no_recurrence"),
			1 => $this->lng->txt("cal_weekly"),
			2 => $this->lng->txt("r_14"),
			4 => $this->lng->txt("r_4_weeks")
		));
		$form->addItem($rec_mode);

		$rec_end = new ilDateTimeInputGUI($this->lng->txt("cal_repeat_until"), "rece");
		$rec_mode->addSubItem($rec_end);	
					
		if(!$a_reload)
		{
			// show date only if active recurrence
			$rec_mode->setHideSubForm(true, '>= 1');
		}
		else
		{
			// recurrence may not be changed on reload
			$rec_mode->setDisabled(true);
			$rec_end->setDisabled(true);			
		}
		
		if($a_group_id)
		{
			$grp = new ilHiddenInputGUI("grp_id");
			$grp->setValue($a_group_id);
			$form->addItem($grp);		
		}
				
		$form->addCommandButton("confirmedBookingNumbers", $this->lng->txt("confirm"));
		$form->addCommandButton("render", $this->lng->txt("cancel"));
		
		return $form;
	}
	
	function confirmBookingNumbers(array $a_objects_counter, $a_group_id, ilPropertyFormGUI $a_form = null)
	{
		global $tpl;
		
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'render'));

		if(!$a_form)
		{
			$a_form = $this->initBookingNumbersForm($a_objects_counter, $a_group_id);
		}
	
		$tpl->setContent($a_form->getHTML());
	}
	
	protected function addDaysDate($a_date, $a_days)
	{
		$date = date_parse($a_date);		
		$stamp = mktime(0, 0, 1, $date["month"], $date["day"]+$a_days, $date["year"]);
		return date("Y-m-d", $stamp);
	}
	
	protected function addDaysStamp($a_stamp, $a_days)
	{
		$date = getDate($a_stamp);		
		return mktime($date["hours"], $date["minutes"], $date["seconds"], 
			$date["mon"], $date["mday"]+$a_days, $date["year"]);
	}
	
	public function confirmedBookingNumbersObject()
	{
		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';								
		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		
		// convert post data to initial form config
		$counter = array();
		$current_first = $obj_id = null;
		foreach(array_keys($_POST) as $id)
		{
			if(substr($id, 0, 9) == "conf_nr__")
			{
				$id = explode("_", substr($id, 9));
				$counter[$id[0]."_".$id[1]."_".$id[2]] = (int)$id[3];		
				if(!$current_first)
				{
					$current_first = date("Y-m-d", $id[1]);
				}
			}
		}
		
		// recurrence
		if((int)$_POST["recm"] > 0 && $current_first)
		{
			ksort($counter);			
			$end = $_POST["rece"]["date"];
			$end = date("Y-m-d", mktime(23, 59, 59, $end["m"], $end["d"], $end["y"]));					
			$cycle = (int)$_POST["recm"]*7;			
			$cut = 0;		
			$org = $counter;
			while($cut < 1000 && $this->addDaysDate($current_first, $cycle) <= $end)
			{
				$cut++;
				$current_first = null;
				foreach($org as $item_id => $max)
				{
					$parts = explode("_", $item_id);
					$obj_id = $parts[0];
					
					$from = $this->addDaysStamp($parts[1], $cycle*$cut);
					$to = $this->addDaysStamp($parts[2], $cycle*$cut);
					
					$new_item_id = $obj_id."_".$from."_".$to;
					
					// form reload because of validation errors
					if(!isset($counter[$new_item_id]) && date("Y-m-d", $to) <= $end)
					{							
						// get max available for added dates
						$new_max = ilBookingReservation::getAvailableObject(array($obj_id), $from, $to-1, false, true);
						$new_max = (int)$new_max[$obj_id];

						$counter[$new_item_id] = $new_max;

						if(!$current_first)
						{
							$current_first = date("Y-m-d", $from);
						}
										
						// clone input 
						$_POST["conf_nr__".$new_item_id."_".$new_max] = $_POST["conf_nr__".$item_id."_".$max];		
					}
				}							
			}											
		}			
		
		$group_id = $_POST["grp_id"];

		$form = $this->initBookingNumbersForm($counter, $group_id, true);		
		if($form->checkInput())
		{					
			$success = false;
			$rsv_ids = array();
			foreach($counter as $id => $all_nr)
			{				
				$book_nr = $form->getInput("conf_nr__".$id."_".$all_nr);
				$parts = explode("_", $id);
				$obj_id = $parts[0];
				$from = $parts[1];
				$to = $parts[2]-1;
				
				// get currently available slots
				$counter = ilBookingReservation::getAvailableObject(array($obj_id), $from, $to, false, true);
				$counter = $counter[$obj_id];
				if($counter)
				{	
					// we can only book what is left
					$book_nr = min($book_nr, $counter);							
					for($loop = 0; $loop < $book_nr; $loop++)
					{
						$rsv_ids[] = $this->processBooking($obj_id, $from, $to, $group_id);
						$success = $obj_id;									
					}
				}
			}
			if($success)
			{
				$this->handleBookingSuccess($success, $rsv_ids);
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt('book_reservation_failed'), true);
				$this->ctrl->redirect($this, 'render');
			}
		}
		else
		{
			$form->setValuesByPost();
			
			// ilDateTimeInputGUI does NOT add hidden values on disabled!
			
			$rece_year = $_POST["rece"]["date"]["y"];
			$rece_month = str_pad($_POST["rece"]["date"]["m"], 2, "0", STR_PAD_LEFT);
			$rece_day = str_pad($_POST["rece"]["date"]["d"], 2, "0", STR_PAD_LEFT);
			
			$form->getItemByPostVar("rece")->setDate(new ilDate($rece_year."-".$rece_month."-".$rece_day, IL_CAL_DATE));
			$form->getItemByPostVar("recm")->setHideSubForm($_POST["recm"] < 1);
			
			$hidden_date = new ilHiddenInputGUI("rece[date][y]");
			$hidden_date->setValue($rece_year);			
			$form->addItem($hidden_date);
			$hidden_date = new ilHiddenInputGUI("rece[date][m]");
			$hidden_date->setValue($rece_month);			
			$form->addItem($hidden_date);
			$hidden_date = new ilHiddenInputGUI("rece[date][d]");
			$hidden_date->setValue($rece_day);			
			$form->addItem($hidden_date);
			
			return $this->confirmBookingNumbers($counter, $group_id, $form);				
		}		
	}
	
	/**
	 * Book object for date
	 * 
	 * @param int $a_object_id
	 * @param int $a_from timestamp
	 * @param int $a_to timestamp
	 * @param int $a_group_id 
	 * @return int
	 */
	function processBooking($a_object_id, $a_from = null, $a_to = null, $a_group_id = null)
	{
		global $ilUser, $ilAccess;
		
		// #11995
		if(!$ilAccess->checkAccess('read', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		};
		
		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
		$reservation = new ilBookingReservation();
		$reservation->setObjectId($a_object_id);
		$reservation->setUserId($ilUser->getID());
		$reservation->setFrom($a_from);
		$reservation->setTo($a_to);
		$reservation->setGroupId($a_group_id);
		$reservation->save();

		if($a_from)
		{
			$this->lng->loadLanguageModule('dateplaner');
			include_once 'Services/Calendar/classes/class.ilCalendarUtil.php';
			include_once 'Services/Calendar/classes/class.ilCalendarCategory.php';
			$def_cat = ilCalendarUtil::initDefaultCalendarByType(ilCalendarCategory::TYPE_BOOK,$ilUser->getId(),$this->lng->txt('cal_ch_personal_book'),true);

			include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
			$object = new ilBookingObject($a_object_id);

			include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
			$entry = new ilCalendarEntry;
			$entry->setStart(new ilDateTime($a_from, IL_CAL_UNIX));
			$entry->setEnd(new ilDateTime($a_to, IL_CAL_UNIX));
			$entry->setTitle($this->lng->txt('book_cal_entry').' '.$object->getTitle());
			$entry->setContextId($reservation->getId());
			$entry->save();

			include_once 'Services/Calendar/classes/class.ilCalendarCategoryAssignments.php';
			$assignment = new ilCalendarCategoryAssignments($entry->getEntryId());
			$assignment->addAssignment($def_cat->getCategoryId());
		}
		
		return $reservation->getId();
	}

	/**
	 *  List reservations
	 */
	function logObject()
	{
		global $tpl, $ilAccess;

		$this->tabs_gui->setTabActive('log');
				
		$show_all = ($ilAccess->checkAccess('write', '', $this->object->getRefId()) ||
			$this->object->hasPublicLog());
		
		$filter = null;
		if($_GET["object_id"])
		{
			$filter["object"] = (int)$_GET["object_id"];
		}

		include_once 'Modules/BookingManager/classes/class.ilBookingReservationsTableGUI.php';
		$table = new ilBookingReservationsTableGUI($this, 'log', $this->ref_id, 
			$this->object->getId(), $show_all, 
			($this->object->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE),
			$filter);
		$tpl->setContent($table->getHTML());
	}
	
	function logDetailsObject()
	{
		global $tpl, $ilAccess;

		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"),
			$this->ctrl->getLinkTarget($this, "log"));
				
		$show_all = ($ilAccess->checkAccess('write', '', $this->object->getRefId()) ||
			$this->object->hasPublicLog());
		
		$filter = null;
		if($_GET["object_id"])
		{
			$filter["object"] = (int)$_GET["object_id"];
		}

		include_once 'Modules/BookingManager/classes/class.ilBookingReservationsTableGUI.php';
		$table = new ilBookingReservationsTableGUI($this, 'log', $this->ref_id, 
			$this->object->getId(), $show_all, 
			($this->object->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE),
			$filter, $_GET["reservation_id"]);
		$tpl->setContent($table->getHTML());
	}
	
	/**
	 * Change status of given reservations
	 */
	function changeStatusObject()
	{
		global $ilAccess;
		
		$this->tabs_gui->setTabActive('log');
		
		if(!$_POST['reservation_id'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $this->logObject();
		}

		if ($ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
			ilBookingReservation::changeStatus($_POST['reservation_id'], (int)$_POST['tstatus']);
		}

		ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
		return $this->ctrl->redirect($this, 'log');
	}

	/**
	 * Apply filter from reservations table gui
	 */
	function applyLogFilterObject()
	{
		global $ilAccess;
				
		$show_all = ($ilAccess->checkAccess('write', '', $this->object->getRefId()) ||
			$this->object->hasPublicLog());
		
		include_once 'Modules/BookingManager/classes/class.ilBookingReservationsTableGUI.php';
		$table = new ilBookingReservationsTableGUI($this, 'log', $this->ref_id,
			$this->object->getId(), $show_all,
			($this->object->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE));
		$table->resetOffset();
		$table->writeFilterToSession();
		$this->logObject();
	}

	/**
	 * Reset filter in reservations table gui
	 */
	function resetLogFilterObject()
	{
		global $ilAccess;
				
		$show_all = ($ilAccess->checkAccess('write', '', $this->object->getRefId()) ||
			$this->object->hasPublicLog());
		
		include_once 'Modules/BookingManager/classes/class.ilBookingReservationsTableGUI.php';
		$table = new ilBookingReservationsTableGUI($this, 'log', $this->ref_id,
			$this->object->getId(), $show_all,
			($this->object->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE));
		$table->resetOffset();
		$table->resetFilter();
		$this->logObject();
	}

	public static function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			ilObjectGUI::_gotoRepositoryNode($a_target, "render");
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			ilObjectGUI::_gotoRepositoryRoot();
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}

	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen();
	}

	function infoScreen()
	{
		global $ilAccess, $ilCtrl;

		$this->tabs_gui->setTabActive('info');

		if (!$ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$info->enablePrivateNotes();

		if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$info->enableNews();
		}

		// no news editing for files, just notifications
		$info->enableNewsEditing(false);
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$news_set = new ilSetting("news");
			$enable_internal_rss = $news_set->get("enable_rss_for_internal");

			if ($enable_internal_rss)
			{
				$info->setBlockProperty("news", "settings", true);
				$info->setBlockProperty("news", "public_notifications_option", true);
			}
		}

		// forward the command
		if ($ilCtrl->getNextClass() == "ilinfoscreengui")
		{
			$ilCtrl->forwardCommand($info);
		}
		else
		{
			return $ilCtrl->getHTML($info);
		}
	}
	
	protected function getLogReservationIds()
	{		
		if($_POST["mrsv"])
		{
			return $_POST["mrsv"];			
		}
		else if($_GET["reservation_id"])
		{
			return array($_GET["reservation_id"]);
		}				
	}
	
	function rsvConfirmCancelObject()
	{
		global $ilCtrl, $lng, $tpl, $ilAccess, $ilUser;
	
		$ids = $this->getLogReservationIds();
		if(!sizeof($ids))
		{
			$this->ctrl->redirect($this, 'log');
		}
		
		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
		
		$max = array();
		foreach($ids as $idx => $id)
		{
			if(!is_numeric($id))
			{
				list($obj_id, $user_id, $from, $to) = explode("_", $id);
				
				$valid_ids = array();				
				foreach(ilBookingObject::getList($this->object->getId()) as $item)
				{
					$valid_ids[$item["booking_object_id"]] = $item["title"];
				}		
						
				if(($ilAccess->checkAccess("write", "", $this->ref_id) || $user_id == $ilUser->getId()) &&
					$from > time() &&
					in_array($obj_id, array_keys($valid_ids)))
				{				
					$rsv_ids = ilBookingReservation::getCancelDetails($obj_id, $user_id, $from, $to);					
					if(!sizeof($rsv_ids))
					{
						unset($ids[$idx]);
					}
					if(sizeof($rsv_ids) > 1)
					{
						$max[$id] = sizeof($rsv_ids);
						$ids[$idx] = $rsv_ids;
					}
					else
					{
						// only 1 in group?  treat as normal reservation
						$ids[$idx] = array_shift($rsv_ids);
					}
				}
				else
				{
					unset($ids[$idx]);
				}				
			}
		}
		
		if(!sizeof($ids))
		{
			$this->ctrl->redirect($this, 'log');
		}
		
		// show form instead
		if(sizeof($max) && max($max) > 1)
		{
			return $this->rsvConfirmCancelAggregationObject($ids);			
		}
		
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "log"));
			
		include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($ilCtrl->getFormAction($this, 'rsvCancel'));
		$conf->setHeaderText($lng->txt('book_confirm_cancel'));
		$conf->setConfirm($lng->txt('book_set_cancel'), 'rsvCancel');
		$conf->setCancel($lng->txt('cancel'), 'log');

		foreach($ids as $id)
		{		
			$rsv = new ilBookingReservation($id);
			$obj = new ilBookingObject($rsv->getObjectId());
			
			$details = $obj->getTitle();
			if($this->object->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE)
			{
				$details .= ", ".ilDatePresentation::formatPeriod(
					new ilDateTime($rsv->getFrom(), IL_CAL_UNIX),
					new ilDateTime($rsv->getTo()+1, IL_CAL_UNIX));
			}
			
			$conf->addItem('rsv_id[]', $id, $details);		
		}
	
		$tpl->setContent($conf->getHTML());		
	}
	
	function rsvConfirmCancelAggregationForm($a_ids)
	{		
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, "rsvCancel"));
		$form->setTitle($this->lng->txt("book_confirm_cancel_aggregation"));
		
		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';	
		
		ilDatePresentation::setUseRelativeDates(false);
		
		foreach($a_ids as $idx => $ids)
		{			
			if(is_array($ids))
			{
				$first = $ids;
				$first = array_shift($first);
			}
			else
			{
				$first = $ids;				
			}
			
			$rsv = new ilBookingReservation($first);
			$obj = new ilBookingObject($rsv->getObjectId());
		
			$caption = $obj->getTitle().", ".ilDatePresentation::formatPeriod(
					new ilDateTime($rsv->getFrom(), IL_CAL_UNIX),
					new ilDateTime($rsv->getTo()+1, IL_CAL_UNIX));
			
			$item = new ilNumberInputGUI($caption, "rsv_id_".$idx);
			$item->setRequired(true);
			$item->setMinValue(0);
			$item->setSize(4);			
			$form->addItem($item);
			
			if(is_array($ids))
			{
				$item->setMaxValue(sizeof($ids));
				
				foreach($ids as $id)
				{
					$hidden = new ilHiddenInputGUI("rsv_aggr[".$idx."][]");
					$hidden->setValue($id);
					$form->addItem($hidden);
				}
			}
			else 
			{
				$item->setMaxValue(1);
				
				$hidden = new ilHiddenInputGUI("rsv_aggr[".$idx."]");
				$hidden->setValue($ids);
				$form->addItem($hidden);
			}	
						
			if($_POST["rsv_id_".$idx])
			{
				$item->setValue((int)$_POST["rsv_id_".$idx]);
			}				
		}
				
		$form->addCommandButton("rsvCancel", $this->lng->txt("confirm"));
		$form->addCommandButton("log", $this->lng->txt("cancel"));	
		
		return $form;
	}
	
	function rsvConfirmCancelAggregationObject(array $a_ids = null)
	{
		global $tpl, $ilCtrl, $lng;
		
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "log"));
		
		// #13511
		ilUtil::sendQuestion($lng->txt("book_confirm_cancel"));		
		
		$form = $this->rsvConfirmCancelAggregationForm($a_ids);
		
		$tpl->setContent($form->getHTML());		
	}

	function rsvCancelObject()
	{
		global $ilAccess, $ilUser, $tpl, $lng, $ilCtrl;
				
		$ids = $_POST["rsv_id"];
		
		// parse aggregation form
		if($_POST["rsv_aggr"])
		{			
			$form = $this->rsvConfirmCancelAggregationForm($_POST["rsv_aggr"]);
			if(!$form->checkInput())
			{
				$this->tabs_gui->clearTargets();
				$this->tabs_gui->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, "log"));
				
				return $tpl->setContent($form->getHTML());		
			}
			
			$ids = array();
			foreach($_POST["rsv_aggr"] as $idx => $aggr_ids)
			{
				$max = (int)$_POST["rsv_id_".$idx];
				if($max)
				{
					if(!is_array($aggr_ids))
					{
						$ids[] = $aggr_ids;
					}
					else
					{
						$aggr_ids = array_slice($aggr_ids, 0, $max);
						$ids = array_merge($ids, $aggr_ids);
					}						
				}
			}			
		}
		
		if($ids)
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
			foreach($ids as $id)
			{				
				$obj = new ilBookingReservation($id);

				if (!$ilAccess->checkAccess("write", "", $this->ref_id) && $obj->getUserId() != $ilUser->getId())
				{
					ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
					$this->ctrl->redirect($this, 'log');
				}

				$obj->setStatus(ilBookingReservation::STATUS_CANCELLED);
				$obj->update();

				if($this->object->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE)
				{
					// remove user calendar entry (#11086)
					$cal_entry_id = $obj->getCalendarEntry();		
					if($cal_entry_id)
					{
						include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
						$entry = new ilCalendarEntry($cal_entry_id);
						$entry->delete();
					}
				}
			}
		}

		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->logObject();
	}

	/*
	function rsvUncancelObject()
	{
		global $ilAccess;
		
		if(!$ilAccess->checkAccess("write", "", $this->ref_id))
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
			$this->ctrl->redirect($this, 'log');
		}

		$ids = $this->getLogReservationIds();
		if($ids)
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';		
			foreach($ids as $id)
			{	
				$obj = new ilBookingReservation($id);
				$obj->setStatus(NULL);
				$obj->update();
			}
		}

		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->logObject();
	}
	*/
	
	function rsvInUseObject()
	{
		global $ilAccess;

		if(!$ilAccess->checkAccess("write", "", $this->ref_id))
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
			$this->ctrl->redirect($this, 'log');
		}

		$ids = $this->getLogReservationIds();
		if($ids)
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';		
			foreach($ids as $id)
			{		
				$obj = new ilBookingReservation($id);
				$obj->setStatus(ilBookingReservation::STATUS_IN_USE);
				$obj->update();
			}
		}

		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->logObject();
	}

	function rsvNotInUseObject()
	{
		global $ilAccess;
				
		if(!$ilAccess->checkAccess("write", "", $this->ref_id))
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
			$this->ctrl->redirect($this, 'log');
		}
		
		$ids = $this->getLogReservationIds();
		if($ids)
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';		
			foreach($ids as $id)
			{	
				$obj = new ilBookingReservation($id);
				$obj->setStatus(NULL);
				$obj->update();
			}
		}

		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->logObject();
	}

	function showProfileObject()
	{
		global $tpl, $ilCtrl;
		
		$this->tabs_gui->clearTargets();
		
		$user_id = (int)$_GET['user_id'];
		
		include_once 'Services/User/classes/class.ilPublicUserProfileGUI.php';
		$profile = new ilPublicUserProfileGUI($user_id);
		$profile->setBackUrl($this->ctrl->getLinkTarget($this, 'log'));
		$tpl->setContent($ilCtrl->getHTML($profile));
	}
	
	public function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "render"), "", $_GET["ref_id"]);
		}
	}		
}

?>