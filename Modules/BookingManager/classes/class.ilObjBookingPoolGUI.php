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
* @ilCtrl_Calls ilObjBookingPoolGUI: ilCommonActionDispatcherGUI
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
		global $tpl, $ilTabs, $ilNavigationHistory;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		if(!$next_class && $cmd == 'render')
		{
			$this->ctrl->setCmdClass('ilBookingObjectGUI');
			$next_class = $this->ctrl->getNextClass($this);
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
		return array(self::CFORM_NEW => $this->initCreateForm($a_new_type));
	}

	protected function afterSave(ilObject $a_new_object)
	{
		$a_new_object->setOffline(true);
		$a_new_object->setNumberOfSlots(4);
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
		
		$fixed = new ilRadioOption($this->lng->txt("book_schedule_type_fixed"), ilObjBookingPool::TYPE_FIX_SCHEDULE);
		$fixed->setInfo($this->lng->txt("book_schedule_type_fixed_info"));
		$type->addOption($fixed);
		
		$none = new ilRadioOption($this->lng->txt("book_schedule_type_none"), ilObjBookingPool::TYPE_NO_SCHEDULE);
		$none->setInfo($this->lng->txt("book_schedule_type_none_info"));
		$type->addOption($none);

		$slots = new ilNumberInputGUI($this->lng->txt("book_slots_no"), "slots");
		$slots->setRequired(true);
		$slots->setSize(4);
		$slots->setMinValue(1);
		$slots->setMaxValue(24);
		$slots->setInfo($this->lng->txt("book_slots_no_info"));
		$fixed->addSubItem($slots);
				
		$public = new ilCheckboxInputGUI($this->lng->txt("book_public_log"), "public");
		$public->setInfo($this->lng->txt("book_public_log_info"));
		$a_form->addItem($public);		
	}

	protected function getEditFormCustomValues(array &$a_values)
	{
		$a_values["online"] = !$this->object->isOffline();
		$a_values["public"] = $this->object->hasPublicLog();
		$a_values["slots"] = $this->object->getNumberOfSlots();
		$a_values["stype"] = $this->object->getScheduleType();
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{
		$this->object->setOffline(!$a_form->getInput('online'));
		$this->object->setPublicLog($a_form->getInput('public'));
		$this->object->setNumberOfSlots($a_form->getInput('slots'));
		$this->object->setScheduleType($a_form->getInput('stype'));
	}

	/**
	* get tabs
	*/
	function setTabs()
	{
		global $ilAccess, $ilHelp;
		
		if (in_array($this->ctrl->getCmd(), array("create", "save")) && !$this->ctrl->getNextClass())
		{
			return;
		}
		
		$ilHelp->setScreenIdComponent("book");

		$this->tabs_gui->addTab("render",
				$this->lng->txt("book_booking_types"),
				$this->ctrl->getLinkTarget($this, "render"));

		$this->tabs_gui->addTab("info",
				$this->lng->txt("info_short"),
				$this->ctrl->getLinkTarget($this, "infoscreen"));

		if ($ilAccess->checkAccess('write', '', $this->object->getRefId()) ||
			$this->object->hasPublicLog())
		{
			$this->tabs_gui->addTab("log",
				$this->lng->txt("book_log"),
				$this->ctrl->getLinkTarget($this, "log"));
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

						// check deadline
						if($slot_from < (time()+$schedule->getDeadline()*60*60) || !ilBookingReservation::getAvailableObject($object_ids, $slot_from, $slot_to-1))
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
		global $tpl;
		
		$success = false;
		
		if($this->object->getScheduleType() == ilObjBookingPool::TYPE_NO_SCHEDULE)
		{	
			if($_POST['object_id'])
			{
				$this->processBooking($_POST['object_id']);
				$success = $_POST['object_id'];	
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
				foreach($_POST['date'] as $date)
				{
					$fromto = explode('_', $date);
					$fromto[1]--;

					$object_id = (int)$_GET['object_id'];
					if($object_id)
					{	
						$this->processBooking($object_id, $fromto[0], $fromto[1]);
						$success = $object_id;		
					}
				}
			}
			/*
			// group object reservation(s)
			else
			{				
				include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
				include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';											
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
			ilUtil::sendSuccess($this->lng->txt('book_reservation_confirmed'), true);
			
			// show post booking information?
			include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
			$obj = new ilBookingObject($success);
			$pfile = $obj->getPostFile();
			$ptext = $obj->getPostText();
			if(trim($ptext) || $pfile)
			{
				$this->ctrl->setParameterByClass('ilbookingobjectgui', 'object_id', $obj->getId());				
				$this->ctrl->redirectByClass('ilbookingobjectgui', 'displayPostInfo');
			}
			else
			{				
				$this->ctrl->redirect($this, 'render');
			}
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt('book_reservation_failed'), true);
			$this->ctrl->redirect($this, 'book');
		}
	}
	
	/**
	 * Book object for date
	 * 
	 * @param int $a_object_id
	 * @param int $a_from timestamp
	 * @param int $a_to timestamp
	 */
	function processBooking($a_object_id, $a_from = null, $a_to = null)
	{
		global $ilUser;
		
		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
		$reservation = new ilBookingReservation();
		$reservation->setObjectId($a_object_id);
		$reservation->setUserId($ilUser->getID());
		$reservation->setFrom($a_from);
		$reservation->setTo($a_to);
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
	}

	/**
	 *  List reservations
	 */
	function logObject()
	{
		global $tpl;

		$this->tabs_gui->setTabActive('log');

		include_once 'Modules/BookingManager/classes/class.ilBookingReservationsTableGUI.php';
		$table = new ilBookingReservationsTableGUI($this, 'log', $this->ref_id, 
			$this->object->getId(), ($this->object->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE));
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
		include_once 'Modules/BookingManager/classes/class.ilBookingReservationsTableGUI.php';
		$table = new ilBookingReservationsTableGUI($this, 'log', $this->ref_id,
			$this->object->getId(), ($this->object->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE));
		$table->resetOffset();
		$table->writeFilterToSession();
		$this->logObject();
	}

	/**
	 * Reset filter in reservations table gui
	 */
	function resetLogFilterObject()
	{
		include_once 'Modules/BookingManager/classes/class.ilBookingReservationsTableGUI.php';
		$table = new ilBookingReservationsTableGUI($this, 'log', $this->ref_id,
			$this->object->getId(), ($this->object->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE));
		$table->resetOffset();
		$table->resetFilter();
		$this->logObject();
	}

	function _goto($a_target)
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

	function rsvCancelObject()
	{
		global $ilAccess, $ilUser;
		
		$this->tabs_gui->setTabActive('log');

		$id = (int)$_GET['reservation_id'];	
		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
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
			// remove user calendar entry
			include_once 'Services/Calendar/classes/class.ilCalendarCategory.php';
			include_once 'Services/Calendar/classes/ConsultationHours/class.ilConsultationHourAppointments.php';
			$apps = ilConsultationHourAppointments::getAppointmentIds($obj->getUserId(), $obj->getId(), NULL, ilCalendarCategory::TYPE_BOOK);
			if($apps)
			{
				include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
				$entry = new ilCalendarEntry($apps[0]);
				$entry->delete();
			}
		}

		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->logObject();
	}

	function rsvUncancelObject()
	{
		global $ilAccess;

		$this->tabs_gui->setTabActive('log');

		if (!$ilAccess->checkAccess("write", "", $this->ref_id))
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
			$this->ctrl->redirect($this, 'log');
		}

		$id = (int)$_GET['reservation_id'];
		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
		$obj = new ilBookingReservation($id);
		$obj->setStatus(NULL);
		$obj->update();

		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->logObject();
	}

	function rsvInUseObject()
	{
		global $ilAccess;

		$this->tabs_gui->setTabActive('log');

		if (!$ilAccess->checkAccess("write", "", $this->ref_id))
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
			$this->ctrl->redirect($this, 'log');
		}

		$id = (int)$_GET['reservation_id'];
		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
		$obj = new ilBookingReservation($id);
		$obj->setStatus(ilBookingReservation::STATUS_IN_USE);
		$obj->update();

		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->logObject();
	}

	function rsvNotInUseObject()
	{
		global $ilAccess;
		
		$this->tabs_gui->setTabActive('log');

		if (!$ilAccess->checkAccess("write", "", $this->ref_id))
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
			$this->ctrl->redirect($this, 'log');
		}

		$id = (int)$_GET['reservation_id'];
		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
		$obj = new ilBookingReservation($id);
		$obj->setStatus(NULL);
		$obj->update();

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