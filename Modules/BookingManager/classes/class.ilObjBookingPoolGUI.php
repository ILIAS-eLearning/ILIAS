<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjBookingPoolGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
* 
* @ilCtrl_Calls ilObjBookingPoolGUI: ilPermissionGUI, ilBookingTypeGUI, ilBookingObjectGUI, ilBookingScheduleGUI, ilInfoScreenGUI, ilPublicUserProfileGUI
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
		global $tpl, $ilTabs;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		if(!$next_class && $cmd == 'render')
		{
			$this->ctrl->setCmdClass('ilBookingTypeGUI');
			$next_class = $this->ctrl->getNextClass($this);
		}

		if(substr($cmd, 0, 4) == 'book')
		{
			$next_class = '';
		}

		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilbookingtypegui':
				$this->tabs_gui->setTabActive('render');
				include_once("Modules/BookingManager/classes/class.ilBookingTypeGUI.php");
				$type_gui =& new ilBookingTypeGUI($this);
				$ret =& $this->ctrl->forwardCommand($type_gui);
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
				$this->tabs_gui->setTabActive('schedules');
				include_once("Services/User/classes/class.ilPublicUserProfileGUI.php");
				$profile =& new ilPublicUserProfileGUI((int)$_GET["user"]);
				$ret =& $this->ctrl->forwardCommand($profile);
				break;

			case 'ilinfoscreengui':
				$this->infoScreen();
				break;
			
			default:
				$cmd = $this->ctrl->getCmd();
				$cmd .= 'Object';
				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Display creation form
	 */
	function createObject()
	{
		global $tpl;

		$form = $this->initForm();
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Display update form
	 */
	function editObject()
	{
		global $tpl;

		$this->tabs_gui->setTabActive('edit');

		$form = $this->initForm("edit");
		$tpl->setContent($form->getHTML());
	}

	/**
	* Init property form
	* @return	object
	*/
	function initForm($a_mode = "create")
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

		$form_gui = new ilPropertyFormGUI();

		$title = new ilTextInputGUI($this->lng->txt("title"), "standard_title");
		$title->setRequired(true);
		$title->setSize(40);
		$title->setMaxLength(120);
		$form_gui->addItem($title);

		$desc = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
		$desc->setCols(37);
		$desc->setRows(2);
		$form_gui->addItem($desc);

		if ($a_mode == "edit")
		{
			$offline = new ilCheckboxInputGUI($this->lng->txt("offline"), "offline");
			$form_gui->addItem($offline);

			$public = new ilCheckboxInputGUI($this->lng->txt("book_public_log"), "public");
			$public->setInfo($this->lng->txt("book_public_log_info"));
			$form_gui->addItem($public);

			$slots = new ilNumberInputGUI($this->lng->txt("book_slots_no"), "slots");
			$slots->setRequired(true);
			$slots->setSize(4);
			$slots->setMinValue(1);
			$slots->setMaxValue(24);
			$slots->setInfo($this->lng->txt("book_slots_no_info"));
			$form_gui->addItem($slots);

			$form_gui->setTitle($this->lng->txt("settings"));
			$title->setValue($this->object->getTitle());
			$desc->setValue($this->object->getDescription());
			$offline->setChecked($this->object->isOffline());
			$public->setChecked($this->object->hasPublicLog());
			$slots->setValue($this->object->getNumberOfSlots());
			$form_gui->addCommandButton("update", $this->lng->txt("save"));
			$form_gui->addCommandButton("render", $this->lng->txt("cancel"));
		}
		else
		{
			$form_gui->setTitle($this->lng->txt("book_create_title"));
			$form_gui->addCommandButton("save", $this->lng->txt("save"));
			$form_gui->addCommandButton("cancel", $this->lng->txt("cancel"));
		}
		$form_gui->setFormAction($this->ctrl->getFormAction($this));

		return $form_gui;
	}
	
	/**
	* create new dataset
	*/
	function saveObject()
	{
		global $rbacadmin, $ilUser, $tpl, $ilCtrl;

		$form = $this->initForm();
		if($form->checkInput())
		{
			$_POST["new_type"] = "book";
			$_POST["Fobject"]["title"] = $form->getInput("standard_title");
			$_POST["Fobject"]["desc"] = $form->getInput("description");

			// always call parent method first to create an object_data entry & a reference
			$newObj = parent::saveObject();

			$newObj->setOffline(true);
			$newObj->setNumberOfSlots(4);
			$newObj->update();
			
			// always send a message
			ilUtil::sendSuccess($this->lng->txt("book_pool_added"),true);

			// BEGIN ChangeEvent: Record object creation
			global $ilUser;
			require_once('Services/Tracking/classes/class.ilChangeEvent.php');
			if (ilChangeEvent::_isActive())
			{
				ilChangeEvent::_recordWriteEvent($newObj->getId(), $ilUser->getId(), 'create');
			}
			// END ChangeEvent: Record object creation

			$this->ctrl->setParameter($this, "ref_id", $newObj->getRefId());
			$this->ctrl->redirect($this, "edit");
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHTML());
		}
	}
	
	/**
	* update dataset
	*/
	function updateObject()
	{
		global $rbacadmin, $ilUser, $tpl, $ilCtrl;

		$form = $this->initForm("edit");
		if($form->checkInput())
		{
			$_POST["Fobject"]["title"] = $form->getInput("standard_title");
			$_POST["Fobject"]["desc"] = $form->getInput("description");

			$this->object->setOffline($form->getInput('offline'));
			$this->object->setPublicLog($form->getInput('public'));
			$this->object->setNumberOfSlots($form->getInput('slots'));

			parent::updateObject();

			$ilCtrl->redirect($this, "render");
		}
		else
		{
			$this->tabs_gui->setTabActive('edit');
			$form->setValuesByPost();
			$tpl->setContent($form->getHTML());
		}
	}

	function afterUpdate()
	{
		
	}

	/**
	* get tabs
	*/
	function setTabs()
	{
		global $ilAccess;
		
		if (in_array($this->ctrl->getCmd(), array("create", "save")) && !$this->ctrl->getNextClass())
		{
			return;
		}

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
			$this->tabs_gui->addTab("schedules",
				$this->lng->txt("book_schedules"),
				$this->ctrl->getLinkTargetByClass("ilbookingschedulegui", "render"));

			$this->tabs_gui->addTab("edit",
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
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'render'));

		if(isset($_GET['object_id']))
		{
			$this->ctrl->setParameter($this, 'object_id', (int)$_GET['object_id']);
			$this->renderBookingByObject((int)$_GET['object_id']);
		}
		else
		{
			$this->ctrl->setParameter($this, 'type_id', (int)$_GET['type_id']);
			$this->renderBookingByType((int)$_GET['type_id']);
		}
	}

	/**
	 * Render list of available dates for object
	 * @param	int	$a_object_id
	 */
	protected function renderBookingByObject($a_object_id)
    {
		global $tpl;

		$this->lng->loadLanguageModule("dateplaner");

		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
		$obj = new ilBookingObject($a_object_id);
		$schedule = new ilBookingSchedule($obj->getScheduleId());
		
		$tpl->setContent($this->renderList($schedule, array($a_object_id), $obj->getTitle()));
	}

	/**
	 * Render list of available dates for type
	 * @param	int	$a_type_id
	 */
	protected function renderBookingByType($a_type_id)
    {
		global $tpl;

		$this->lng->loadLanguageModule("dateplaner");
		
		include_once 'Modules/BookingManager/classes/class.ilBookingType.php';
		include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$type = new ilBookingType($a_type_id);
		$schedule = new ilBookingSchedule($type->getScheduleId());
		$object_ids = array();
		foreach(ilBookingObject::getList($a_type_id) as $item)
		{
			$object_ids[] = $item['booking_object_id'];
		}

		$tpl->setContent($this->renderList($schedule, $object_ids, $type->getTitle()));
	}

	protected function renderList(ilBookingSchedule $schedule, array $object_ids, $title)
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
				$seed = new ilDate($_GET['seed'], IL_CAL_DATE);
			}
			else
			{
				$seed = new ilDate(time(), IL_CAL_UNIX);
			}
			include_once 'Services/Calendar/classes/class.ilCalendarHeaderNavigationGUI.php';
			$navigation = new ilCalendarHeaderNavigationGUI($this,$seed,ilDateTime::WEEK,'book');
			$mytpl->setVariable('NAVIGATION', $navigation->getHTML());

			$week_start = $user_settings->getWeekStart();
			if($week_start)
			{
				$map = array('mo', 'tu', 'we', 'th', 'fr', 'sa', 'su');
			}
			else
			{
				$map = array('su', 'mo', 'tu', 'we', 'th', 'fr', 'sa');
			}
			
			include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
			include_once 'Services/Calendar/classes/class.ilCalendarUtil.php';
			$definition = $schedule->getDefinition();
			$dates = array();
			foreach(ilCalendarUtil::_buildWeekDayList($seed,$week_start)->get() as $date)
			{
				$date_info = $date->get(IL_CAL_FKT_GETDATE,'','UTC');

				$mytpl->setCurrentBlock('weekdays');
				$mytpl->setVariable('TXT_WEEKDAY', $date_info['weekday']);
				$mytpl->setVariable('TXT_DATE', $date_info['mday'].' '.$date_info['month']);
				$mytpl->parseCurrentBlock();

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
				
				$old  = 0;
				$last = array_pop(array_keys($hours));
				$in = false;
				foreach($hours as $hour => $period)
				{
					$dates[$hour][0] = $period;

					$compare = $hour.'59';
					if(sizeof($slots))
					{
						foreach($slots as $slot)
						{
							$slot_from = mktime(substr($slot['from'], 0, 2), substr($slot['from'], 2, 2), 0, $date_info["mon"], $date_info["mday"], $date_info["year"]);
							$slot_to = mktime(substr($slot['to'], 0, 2), substr($slot['to'], 2, 2), 0, $date_info["mon"], $date_info["mday"], $date_info["year"]);

							// check deadline
							if($slot_from < (time()+$schedule->getDeadline()*60*60) || !ilBookingReservation::getAvailableObject($object_ids, $slot_from, $slot_to))
							{
								continue;
							}

							if(($slot['to'] <= $compare && $slot['to'] > $old) || ($slot['to'] > $compare && $hour == $last))
							{
								$in = false;
							}
							if(($slot['from'] <= $compare && $slot['from'] > $old) || ($slot['from'] < $compare && !$old))
							{
								$from = ilDatePresentation::formatDate(new ilDateTime($slot_from, IL_CAL_UNIX));
								$from = array_pop(explode(' ', $from));
								$to = ilDatePresentation::formatDate(new ilDateTime($slot_to, IL_CAL_UNIX));
								$to = array_pop(explode(' ', $to));
								
								$dates[$hour][$date_info['isoday']]['caption'] = $from.'-'.$to;
								$dates[$hour][$date_info['isoday']]['id'] = $slot_from.'_'.$slot_to;
								$in = $slot_from.'_'.$slot_to;
							}
						}
						if($in)
						{
							$dates[$hour][$date_info['isoday']]['in_slot'] = $in;
						}
					}
					$old = $compare;
				}
			}

			include_once 'Services/Calendar/classes/class.ilCalendarAppointmentColors.php';
			include_once 'Services/Calendar/classes/class.ilCalendarUtil.php';
			$color = array();
			$all = ilCalendarAppointmentColors::_getColorsByType('crs');
			for($loop = 0; $loop < 7; $loop++)
		    {
				$col = $all[$loop];
				$fnt = ilCalendarUtil::calculateFontColor($col);
				$color[$loop] = 'border-bottom: 1px solid '.$col.'; background-color: '.$col.'; color: '.$fnt;
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
						if(isset($days[$loop]['caption']))
						{
							
							$mytpl->setCurrentBlock('choice');
							$mytpl->setVariable('TXT_DATE', $days[$loop]['caption']);
							$mytpl->setVariable('VALUE_DATE', $days[$loop]['id']);
							$mytpl->setVariable('DATE_COLOR', $color[$loop]);
							$mytpl->parseCurrentBlock();

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

	/**
	 * Book object - either of type or specific - for given dates
	 */
	function confirmedBookingObject()
	{
		global $ilUser;
		
		if(!isset($_POST['date']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $this->bookObject();
		}

		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
		$fromto = explode('_', $_POST['date']);

		if(isset($_GET['object_id']))
		{
			$object_id = (int)$_GET['object_id'];
		}
		// choose object of type
		else
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
			$ids = array();
			foreach(ilBookingObject::getList((int)$_GET['type_id']) as $item)
			{
				$ids[] = $item['booking_object_id'];
			}
			$object_id = ilBookingReservation::getAvailableObject($ids, $fromto[0], $fromto[1]);
		}

		if($object_id)
		{
			$reservation = new ilBookingReservation();
			$reservation->setObjectId($object_id);
			$reservation->setUserId($ilUser->getID());
			$reservation->setFrom($fromto[0]);
			$reservation->setTo($fromto[1]);
			$reservation->save();

			$this->lng->loadLanguageModule('dateplaner');
			include_once 'Services/Calendar/classes/class.ilCalendarUtil.php';
			include_once 'Services/Calendar/classes/class.ilCalendarCategory.php';
			$def_cat = ilCalendarUtil::initDefaultCalendarByType(ilCalendarCategory::TYPE_BOOK,$ilUser->getId(),$this->lng->txt('cal_ch_personal_book'),true);

			include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
			$object = new ilBookingObject($object_id);

			include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
			$entry = new ilCalendarEntry;
			$entry->setStart(new ilDateTime($fromto[0], IL_CAL_UNIX));
			$entry->setEnd(new ilDateTime($fromto[1], IL_CAL_UNIX));
			$entry->setTitle($this->lng->txt('book_cal_entry').' '.$object->getTitle());
			$entry->setContextId($reservation->getId());
			$entry->save();

			include_once 'Services/Calendar/classes/class.ilCalendarCategoryAssignments.php';
			$assignment = new ilCalendarCategoryAssignments($entry->getEntryId());
			$assignment->addAssignment($def_cat->getCategoryId());

			ilUtil::sendSuccess($this->lng->txt('book_reservation_confirmed'), true);
			$this->ctrl->redirect($this, 'render');
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt('book_reservation_failed'), true);
			$this->ctrl->redirect($this, 'book');
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
		$table = new ilBookingReservationsTableGUI($this, 'log', $this->ref_id);
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
		$table = new ilBookingReservationsTableGUI($this, 'log', $this->ref_id);
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
		$table = new ilBookingReservationsTableGUI($this, 'log', $this->ref_id);
		$table->resetOffset();
		$table->resetFilter();
		$this->logObject();
	}

	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			$_GET["cmd"] = "render";
			$_GET["ref_id"] = $a_target;
			include("repository.php");
			exit;
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			include("repository.php");
			exit;
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
		global $tpl;
		
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'log'));

		$user_id = (int)$_GET['user_id'];

		include_once 'Services/User/classes/class.ilPublicUserProfileGUI.php';
		$profile = new ilPublicUserProfileGUI($user_id);
		$tpl->setContent($profile->getHTML());
	}
}

?>