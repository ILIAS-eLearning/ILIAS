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

include_once './Services/Calendar/classes/class.ilCalendarRecurrence.php';
include_once './Services/Booking/classes/class.ilBookingEntry.php';
include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourAppointments.php';

/**
 * Consultation hours editor
 * 
 * @ilCtrl_Calls: ilConsultationHoursGUI: ilPublicUserProfileGUI
 */
class ilConsultationHoursGUI
{
	const MODE_CREATE = 1;
	const MODE_UPDATE = 2;
	const MODE_MULTI = 3;
	
	protected $user_id;
	protected $ctrl;

	protected $booking = null;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $lng, $ilCtrl, $tpl, $ilUser;

		$user_id = (int)$_GET['user_id'];
		if($user_id)
		{
			if(in_array($user_id, array_keys(ilConsultationHourAppointments::getManagedUsers())))
			{
				$this->user_id = $user_id;
			}
			else
			{
				$user_id = false;
			}
		}
		if(!$user_id)
		{
			$this->user_id = $ilUser->getId();
		}
		
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
	}
	
	/**
	 * Execute command
	 * @return 
	 */
	public function executeCommand()
	{
		global $ilUser, $ilCtrl, $tpl, $ilHelp;
		
		$ilHelp->setScreenIdComponent("cal");
		
		switch($this->ctrl->getNextClass())
		{
			case "ilpublicuserprofilegui":				
				include_once('./Services/User/classes/class.ilPublicUserProfileGUI.php');
				$profile = new ilPublicUserProfileGUI($this->user_id);
				$profile->setBackUrl($this->getProfileBackUrl());
				$ret = $ilCtrl->forwardCommand($profile);
				$tpl->setContent($ret);
			    break;
			
			default:				
				$this->setTabs();
				
				if($ilUser->getId() != $this->user_id)
				{
					$ilCtrl->setParameter($this, 'user_id', $this->user_id);
				}		
				
				$cmd = $this->ctrl->getCmd('appointmentList');
				$this->$cmd();
		}
	}
	
	/**
	 * Get user id
	 * @return 
	 */
	public function getUserId()
	{
		return $this->user_id;
	}
	
	/**
	 * Show settings of consultation hours
	 * @todo add list/filter of consultation hours if user is responsible for more than one other consultation hour series.
	 * @return 
	 */
	protected function appointmentList()
	{
		global $ilToolbar, $ilHelp;

		$ilHelp->setScreenId("consultation_hours");
		
		$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
		$ilToolbar->addButton($this->lng->txt('cal_ch_add_sequence'),$this->ctrl->getLinkTarget($this,'createSequence'));
		
		include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHoursTableGUI.php';
		$tbl = new ilConsultationHoursTableGUI($this,'appointmentList',$this->getUserId());
		$tbl->parse();
		$this->tpl->setContent($tbl->getHTML());
	}
	
	/**
	 * Create new sequence
	 * @return 
	 */
	protected function createSequence()
	{
		$this->initFormSequence(self::MODE_CREATE);
		
		$this->booking = new ilBookingEntry();
		$this->form->getItemByPostVar('bo')->setValue($this->booking->getNumberOfBookings());	
		$this->form->getItemByPostVar('ap')->setValue(1);
		$this->form->getItemByPostVar('du')->setMinutes(15);
		$this->form->getItemByPostVar('st')->setDate(
			new ilDateTime(mktime(8,0,0,date('n',time()),date('d',time()),date('Y',time())),IL_CAL_UNIX));

		$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Init form
	 * @param int $a_mode
	 * @return 
	 */
	protected function initFormSequence($a_mode)
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		
		include_once('./Services/YUI/classes/class.ilYuiUtil.php');
		ilYuiUtil::initDomEvent();
		
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		
		switch($a_mode)
		{
			case self::MODE_CREATE:
				$this->form->setTitle($this->lng->txt('cal_ch_add_sequence'));
				$this->form->addCommandButton('saveSequence', $this->lng->txt('save'));
				$this->form->addCommandButton('appointmentList', $this->lng->txt('cancel'));
				break;

			/*
			case self::MODE_UPDATE:
				$this->form->setTitle($this->lng->txt('cal_ch_edit_sequence'));
				$this->form->addCommandButton('updateSequence', $this->lng->txt('save'));
				$this->form->addCommandButton('appointmentList', $this->lng->txt('cancel'));
				break;
			 */

			case self::MODE_MULTI:
				$this->form->setTitle($this->lng->txt('cal_ch_multi_edit_sequence'));
				$this->form->addCommandButton('updateMulti', $this->lng->txt('save'));
				$this->form->addCommandButton('appointmentList', $this->lng->txt('cancel'));
				break;
		}

		// Title 
		$ti = new ilTextInputGUI($this->lng->txt('title'),'ti');
		$ti->setSize(32);
		$ti->setMaxLength(128);
		$ti->setRequired(true);
		$this->form->addItem($ti);

		if($a_mode != self::MODE_MULTI)
		{
			// Start
			include_once './Services/Form/classes/class.ilDateTimeInputGUI.php';
			$dur = new ilDateTimeInputGUI($this->lng->txt('cal_start'),'st');
			$dur->setShowTime(true);
			$dur->setMinuteStepSize(5);
			$this->form->addItem($dur);

			// Duration
			$du = new ilDurationInputGUI($this->lng->txt('cal_ch_duration'),'du');
			$du->setShowMinutes(true);
			$du->setShowHours(false);
			$this->form->addItem($du);

			// Number of appointments
			$nu = new ilNumberInputGUI($this->lng->txt('cal_ch_num_appointments'),'ap');
			$nu->setInfo($this->lng->txt('cal_ch_num_appointments_info'));
			$nu->setSize(2);
			$nu->setMaxLength(2);
			$nu->setRequired(true);
			$nu->setMinValue(1);
			$this->form->addItem($nu);

			// Recurrence
			include_once('./Services/Calendar/classes/Form/class.ilRecurrenceInputGUI.php');
			$rec = new ilRecurrenceInputGUI($this->lng->txt('cal_recurrences'),'frequence');
			$rec->setEnabledSubForms(
				array(
					IL_CAL_FREQ_DAILY,
					IL_CAL_FREQ_WEEKLY,
					IL_CAL_FREQ_MONTHLY
				)
			);
			$this->form->addItem($rec);
		}
		
		// Number of bookings
		$nu = new ilNumberInputGUI($this->lng->txt('cal_ch_num_bookings'),'bo');
		$nu->setSize(2);
		$nu->setMaxLength(2);
		$nu->setMinValue(1);
		$nu->setRequired(true);
		$this->form->addItem($nu);

		// Deadline
		$dead = new ilDurationInputGUI($this->lng->txt('cal_ch_deadline'),'dead');
		$dead->setInfo($this->lng->txt('cal_ch_deadline_info'));
		$dead->setShowMinutes(false);
		$dead->setShowHours(true);
		$dead->setShowDays(true);
		$this->form->addItem($dead);

		// Location
		$lo = new ilTextInputGUI($this->lng->txt('cal_where'),'lo');
		$lo->setSize(32);
		$lo->setMaxLength(128);
		$this->form->addItem($lo);
		
		// Description
		$de = new ilTextAreaInputGUI($this->lng->txt('description'),'de');
		$de->setRows(10);
		$de->setCols(60);
		$this->form->addItem($de);

		// Target Object
		$tgt = new ilNumberInputGUI($this->lng->txt('cal_ch_target_object'),'tgt');
		$tgt->setInfo($this->lng->txt('cal_ch_target_object_info'));
		$tgt->setSize(6);
		$this->form->addItem($tgt);
	}
	
	/**
	 * Save new sequence
	 * @return 
	 */
	protected function saveSequence()
	{
		global $ilObjDataCache;
		
		$this->initFormSequence(self::MODE_CREATE);

		if($this->form->checkInput())
		{
			$this->form->setValuesByPost();
			
			$booking = new ilBookingEntry();
			$booking->setObjId($this->getUserId());
			$booking->setNumberOfBookings($this->form->getInput('bo'));

			$deadline = $this->form->getInput('dead');
			$deadline = $deadline['dd']*24+$deadline['hh'];
			$booking->setDeadlineHours($deadline);

			$tgt = $this->form->getInput('tgt');
			if($tgt)
			{
				$obj_id = $ilObjDataCache->lookupObjId($tgt);
				if(!$obj_id)
				{
					ilUtil::sendFailure($this->lng->txt('cal_ch_unknown_repository_object'));
					$this->tpl->setContent($this->form->getHTML());
					return;
				}
				
				$booking->setTargetObjId($obj_id);
			}
			
			$booking->save();
			
			$this->createAppointments($booking);
			
			ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
			$this->ctrl->redirect($this,'appointmentList');
		}
		else
		{
			$this->form->setValuesByPost();
			$this->tpl->setContent($this->form->getHTML());
		}
	}
	
	/**
	 * Create calendar appointments
	 * @param ilBookingEntry $booking
	 * @return 
	 */
	protected function createAppointments(ilBookingEntry $booking)
	{
		include_once './Services/Calendar/classes/class.ilDateList.php';
		$concurrent_dates = new ilDateList(ilDateList::TYPE_DATETIME);
		$start = clone $this->form->getItemByPostVar('st')->getDate();
		for($i = 0; $i < $this->form->getItemByPostVar('ap')->getValue(); $i++)
		{
			$concurrent_dates->add($start);
			$start = new ilDateTime($start->increment(ilDateTime::MINUTE,$this->form->getItemByPostVar('du')->getMinutes()),IL_CAL_UNIX);
		}
		
		include_once './Services/Calendar/classes/class.ilCalendarUtil.php';
		$def_cat = ilCalendarUtil::initDefaultCalendarByType(ilCalendarCategory::TYPE_CH,$this->getUserId(),$this->lng->txt('cal_ch_personal_ch'),true);
		
		// Add calendar appointment for each 
		include_once './Services/Calendar/classes/class.ilCalendarCategoryAssignments.php';
		include_once './Services/Calendar/classes/class.ilCalendarEntry.php';
		include_once './Services/Calendar/classes/class.ilCalendarRecurrenceCalculator.php';
		include_once './Services/Booking/classes/class.ilBookingPeriod.php';
		foreach($concurrent_dates as $dt)
		{
			$end = clone $dt;
			$end->increment(ilDateTime::MINUTE,$this->form->getItemByPostVar('du')->getMinutes());

			$calc = new ilCalendarRecurrenceCalculator(
				new ilBookingPeriod($dt,$end),
				$this->form->getItemByPostVar('frequence')->getRecurrence()
			);

			// Calculate with one year limit
			$limit = clone $dt;
			$limit->increment(ilDAteTime::YEAR,1);

			$date_list = $calc->calculateDateList($dt,$limit);

			foreach($date_list as $app_start)
			{
				$app_end = clone $app_start;
				$app_end->increment(ilDateTime::MINUTE,$this->form->getItemByPostVar('du')->getMinutes());
				
				
				$entry = new ilCalendarEntry();
				$entry->setContextId($booking->getId());
				$entry->setTitle($this->form->getInput('ti'));
				$entry->setDescription($this->form->getInput('de'));
				$entry->setLocation($this->form->getInput('lo'));
				$entry->setStart($app_start);
				$entry->setEnd($app_end);
				
				$entry->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
				$entry->save();
				
				$cat_assign = new ilCalendarCategoryAssignments($entry->getEntryId());
				$cat_assign->addAssignment($def_cat->getCategoryID());
			}
		}
	}
	
	/**
	 * Set tabs
	 * @return 
	 */
	protected function setTabs()
	{
		global $ilTabs, $ilUser, $ilCtrl;

		$ilCtrl->setParameter($this, 'user_id', '');
		$ilTabs->addTab('consultation_hours_'.$ilUser->getId(), $this->lng->txt('cal_ch_ch'), $this->ctrl->getLinkTarget($this,'appointmentList'));

		foreach(ilConsultationHourAppointments::getManagedUsers() as $user_id => $login)
		{
			$ilCtrl->setParameter($this, 'user_id', $user_id);
			$ilTabs->addTab('consultation_hours_'.$user_id, $this->lng->txt('cal_ch_ch').': '.$login, $this->ctrl->getLinkTarget($this,'appointmentList'));			
		}
		$ilCtrl->setParameter($this, 'user_id', '');

		$ilTabs->addTab('settings', $this->lng->txt('settings'), $this->ctrl->getLinkTarget($this,'settings'));

		$ilTabs->activateTab('consultation_hours_'.$this->getUserId());
	}

	/**
	 * Edit multiple sequence items
	 */
	public function edit()
	{
		global $ilTabs;
		
		if(!isset($_POST['apps']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $this->appointmentList();
		}

		$this->initFormSequence(self::MODE_MULTI);
		
		if($_POST['apps'] && !is_array($_POST['apps']))
		{
			$_POST['apps'] = explode(';', $_POST['apps']);
		}

		$hidden = new ilHiddenInputGUI('apps');
		$hidden->setValue(implode(';', $_POST['apps']));
		$this->form->addItem($hidden);
		
		include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
		$first = $_POST['apps'];
		$first = array_shift($_POST['apps']);
		$entry = new ilCalendarEntry($first);

		$this->form->getItemByPostVar('ti')->setValue($entry->getTitle());
		$this->form->getItemByPostVar('lo')->setValue($entry->getLocation());
		$this->form->getItemByPostVar('de')->setValue($entry->getDescription());
		
		include_once 'Services/Booking/classes/class.ilBookingEntry.php';
		$booking = new ilBookingEntry($entry->getContextId());

		$this->form->getItemByPostVar('bo')->setValue($booking->getNumberOfBookings());
		$this->form->getItemByPostVar('tgt')->setValue($booking->getTargetObjId());

		$deadline = $booking->getDeadlineHours();
		$this->form->getItemByPostVar('dead')->setDays(floor($deadline/24));
		$this->form->getItemByPostVar('dead')->setHours($deadline%24);

		$this->tpl->setContent($this->form->getHTML());
	}

	/**
	 * Update multiple sequence items
	 * @return
	 */
	protected function updateMulti()
	{
		global $ilObjDataCache;
		
		$this->initFormSequence(self::MODE_MULTI);

		if($this->form->checkInput())
		{
			$this->form->setValuesByPost();
			$apps = explode(';', $_POST['apps']);
			
			include_once 'Services/Booking/classes/class.ilBookingEntry.php';
			include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';

			// do collision-check if max bookings were reduced
			$first = $apps;
			$first = array_shift($first);
			$entry = ilBookingEntry::getInstanceByCalendarEntryId($first);
			if($this->form->getInput('bo') < $entry->getNumberOfBookings())
			{
			   $this->edit();
			   return;
			}

			// create new context
			
			$booking = new ilBookingEntry();
			
			$booking->setObjId($this->getUserId());
			$booking->setNumberOfBookings($this->form->getInput('bo'));

			$deadline = $this->form->getInput('dead');
			$deadline = $deadline['dd']*24+$deadline['hh'];
			$booking->setDeadlineHours($deadline);

			$tgt = $this->form->getInput('tgt');
			if($tgt)
			{
				// if value was not changed, we already have an object id
				if($tgt != $entry->getTargetObjId())
				{
					$obj_id = $ilObjDataCache->lookupObjId($tgt);
					if(!$obj_id)
					{
						ilUtil::sendFailure($this->lng->txt('cal_ch_unknown_repository_object'), true);
						$this->edit();
						return;
					}
					$booking->setTargetObjId($obj_id);
				}
				else
				{
					$booking->setTargetObjId($tgt);
				}
			}
			
			$booking->save();


			// update entries

			$title = $this->form->getInput('ti');
			$location = $this->form->getInput('lo');
			$description = $this->form->getInput('de');
			
			foreach($apps as $item_id)
			{
				$entry = new ilCalendarEntry($item_id);
				$entry->setContextId($booking->getId());
				$entry->setTitle($title);
				$entry->setLocation($location);
				$entry->setDescription($description);
				$entry->update();
			}

			ilBookingEntry::removeObsoleteEntries();

			ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
			$this->ctrl->redirect($this,'appointmentList');
		}
		$this->tpl->setContent($this->form->getHTML());
	}

	/**
	 * confirm delete for multiple entries
	 */
	public function confirmDelete()
	{
		global $tpl;
		
		if(!isset($_POST['apps']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $this->appointmentList();
		}

		include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');

		$this->ctrl->saveParameter($this,array('seed','app_id','dt'));

		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setHeaderText($this->lng->txt('cal_delete_app_sure'));
		$confirm->setCancel($this->lng->txt('cancel'),'cancel');

		include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
		foreach($_POST['apps'] as $entry_id)
		{
			$entry = new ilCalendarEntry($entry_id);
			$confirm->addItem('apps[]', $entry_id, ilDatePresentation::formatDate($entry->getStart()).', '.$entry->getTitle());
		}

		$confirm->setConfirm($this->lng->txt('delete'),'delete');
		$confirm->setCancel($this->lng->txt('cancel'),'appointmentList');
		
		$tpl->setContent($confirm->getHTML());
	}

	/**
	 * delete multiple entries
	 */
	public function delete()
	{
		global $tpl;

		if(!isset($_POST['apps']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $this->appointmentList();
		}

		include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
		include_once 'Services/Calendar/classes/class.ilCalendarCategoryAssignments.php';
		foreach($_POST['apps'] as $entry_id)
		{
			// cancel booking for users
			$entry = ilBookingEntry::getInstanceByCalendarEntryId($entry_id);
			if($entry)
			{
				foreach($entry->getCurrentBookings($entry_id) as $user_id)
				{
					$entry->cancelBooking($entry_id, $user_id);
				}
			}
			
			// remove calendar entries
			$entry = new ilCalendarEntry($entry_id);
			$entry->delete();

			ilCalendarCategoryAssignments::_deleteByAppointmentId($entry_id);				
		}
		
		ilBookingEntry::removeObsoleteEntries();

		ilUtil::sendSuccess($this->lng->txt('cal_deleted_app'), true);
		$this->ctrl->redirect($this, 'appointmentList');
	}

	/**
	 * show public profile of given user
	 */
	public function showProfile()
	{
		global $tpl, $ilTabs, $ilCtrl;

		$ilTabs->clearTargets();

		$user_id = (int)$_GET['user'];
	
		include_once 'Services/User/classes/class.ilPublicUserProfileGUI.php';
		$profile = new ilPublicUserProfileGUI($user_id);
		$profile->setBackUrl($this->getProfileBackUrl());
		$tpl->setContent($ilCtrl->getHTML($profile));
	}
	
	/**
	 * Build context-sensitive profile back url
	 * 
	 * @return string
	 */
	protected function getProfileBackUrl()
	{
		// from repository 
		if(isset($_REQUEST["ref_id"]))
		{
			$url = $this->ctrl->getLinkTargetByClass('ilCalendarMonthGUI');
		}
		// from panel
		else if(isset($_GET['panel']))
		{
			$url = $this->ctrl->getLinkTargetByClass('ilCalendarPresentationGUI');
		}
		// from appointments
		else
		{
			$url = $this->ctrl->getLinkTarget($this, 'appointmentList');
		}
		return $url;
	}

	/**
	 * display settings gui
	 */
	public function settings()
	{
		global $tpl, $ilTabs, $ilHelp;

		$ilHelp->setScreenId("consultation_hours_settings");
		$ilTabs->activateTab('settings');
		
		$form = $this->initSettingsForm();
		$tpl->setContent($form->getHTML());
	}

	/**
	 * build settings form
	 * @return object
	 */
	protected function initSettingsForm()
	{
		global $ilDB, $ilUser;

		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));

		$mng = new ilTextInputGUI($this->lng->txt('cal_ch_manager'), 'mng');
		$mng->setInfo($this->lng->txt('cal_ch_manager_info'));
		$form->addItem($mng);

		$mng->setValue(ilConsultationHourAppointments::getManager(true));

		$form->setTitle($this->lng->txt('settings'));
		$form->addCommandButton('updateSettings', $this->lng->txt('save'));
		// $form->addCommandButton('appointmentList', $this->lng->txt('cancel'));
		return $form;
	}

	/**
	 * save settings
	 */
	public function updateSettings()
	{
		global $ilDB, $ilCtrl, $ilUser, $tpl, $ilTabs;
		
		$form = $this->initSettingsForm();
		if($form->checkInput())
		{
			$mng = $form->getInput('mng');
			if(ilConsultationHourAppointments::setManager($mng))
			{
				ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
				$ilCtrl->redirect($this, 'appointmentList');
			}
			else
			{
				$ilTabs->activateTab('settings');

				ilUtil::sendFailure($this->lng->txt('cal_ch_unknown_user'));
				$field = $form->getItemByPostVar('mng');
				$field->setValue($mng);
				$tpl->setContent($form->getHTML());
				return;
			}
		}
	}
}
?>