<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjBookingPoolGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
* 
* @ilCtrl_Calls ilObjBookingPoolGUI: ilPermissionGUI, ilBookingTypeGUI, ilBookingObjectGUI, ilBookingScheduleGUI
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

		$desc = new ilTextInputGUI($this->lng->txt("description"), "description");
		$desc->setSize(40);
		$desc->setMaxLength(120);
		$form_gui->addItem($desc);

		$offline = new ilCheckboxInputGUI($this->lng->txt("offline"), "offline");
		$form_gui->addItem($offline);

		$public = new ilCheckboxInputGUI($this->lng->txt("book_public_log"), "public");
		$public->setInfo($this->lng->txt("book_public_log_info"));
		$form_gui->addItem($public);
		
		if ($a_mode == "edit")
		{
			$form_gui->setTitle($this->lng->txt("settings"));
			$title->setValue($this->object->getTitle());
			$desc->setValue($this->object->getDescription());
			$offline->setChecked($this->object->isOffline());
			$public->setChecked($this->object->hasPublicLog());
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
		global $rbacadmin, $ilUser, $tpl;

		$form = $this->initForm();
		if($form->checkInput())
		{
			$_POST["new_type"] = "book";
			$_POST["Fobject"]["title"] = $form->getInput("standard_title");
			$_POST["Fobject"]["desc"] = $form->getInput("description");

			// always call parent method first to create an object_data entry & a reference
			$newObj = parent::saveObject();

			$newObj->setOffline($form->getInput('offline'));
			$newObj->setPublicLog($form->getInput('public'));
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

			$this->redirectToRefId($_GET["ref_id"]);
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
		
		if (in_array($this->ctrl->getCmd(), array("create", "save")))
		{
			return;
		}

		$this->tabs_gui->addTab("render",
				$this->lng->txt("book_booking_types"),
				$this->ctrl->getLinkTarget($this, "render"));

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

			include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
			$mytpl->setCurrentBlock('dates');
			$counter = 0;
			foreach($this->slotsToDates($schedule->getDefinition(), $schedule->getDeadline()) as $idx => $date)
			{
				if(!ilBookingReservation::getAvailableObject($object_ids, $date['from'], $date['to']))
				{
					continue;
				}
				if($counter > 15)
				{
					break;
				}

				$range = ilDatePresentation::formatPeriod(
					new ilDateTime($date['from'], IL_CAL_UNIX),
					new ilDateTime($date['to'], IL_CAL_UNIX)).'<br />';
			    if(is_numeric(substr($range, 0, 2)))
				{
					$range = $this->lng->txt(ucfirst($date['day']).'_short').', '.$range;
				}

				if($idx%2)
				{
					$mytpl->setVariable('CSS_ROW', 'tblrow1');
				}
				else
				{
					$mytpl->setVariable('CSS_ROW', 'tblrow2');
				}

				$mytpl->setVariable('TXT_DATE', $range);
				$mytpl->setVariable('VALUE_DATE', $date['from'].'_'.$date['to']);
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
	 * Convert schedule definition to timestamps for given number of weeks
	 * @param	array	$definition
	 * @param	int		$deadline
	 * @param	int		$weeks
	 * @return	array
	 */
	protected function slotsToDates(array $definition, $deadline = NULL)
    {
		$map = array('mo'=>'monday', 'tu'=>'tuesday', 'we'=>'wednesday',
				'th'=>'thursday', 'fr'=>'friday', 'sa'=>'saturday', 'su'=>'sunday');
	    $map_num = array_flip(array_keys($map));
	    $res = array();
		for($offset = -1; $offset < 10; $offset++)
		{
			foreach($definition as $weekday => $slots)
			{
				foreach($slots as $slot)
				{
					$slot = explode('-', $slot);

					// special case today
					if($offset == -1 && strtolower(date('l')) == $map[$weekday])
					{
						$from = strtotime('today '.$slot[0]);
						$to = strtotime('today '.$slot[1]);
					}
					// in first week start after today
					else if($offset > -1 || date('N') < ($map_num[$weekday])+1)
					{
						$from = strtotime('next '.$map[$weekday].' + '.$offset.' weeks '.$slot[0]);
						$to = strtotime('next '.$map[$weekday].' + '.$offset.' weeks '.$slot[1]);
					}

					// check deadline
					if($from > time() && !$deadline || $from > time()+$deadline*60*60)
					{
						$res[] = array('from'=>$from, 'to'=>$to, 'day'=>$weekday);
					}
				}
			}
		}
		return $res;
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
			$reservation->setStatus(ilBookingReservation::STATUS_RESERVED);
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
}

?>