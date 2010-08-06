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

/**
 * Consultation hours editor
 * 
 * @ilCtrl_Calls: ilConsultationHoursGUI:
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
	public function __construct($a_user_id)
	{
		global $lng,$ilCtrl,$tpl;
		
		$this->user_id = $a_user_id;
		
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
		$this->setTabs();
		
		switch($this->ctrl->getNextClass())
		{
			default:
				
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
		global $ilToolbar;
		
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
	}
	
	/**
	 * Save new sequence
	 * @return 
	 */
	protected function saveSequence()
	{
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

			$booking->save();
			
			$this->createAppointments($booking);
			
			ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
			$this->createSequence();
			$this->ctrl->redirect($this,'appointmentList');
		}
		$this->tpl->setContent($this->form->getHTML());
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
			$concurrent_dates->add(
				$start = new ilDateTime($start->increment(ilDateTime::MINUTE,$this->form->getItemByPostVar('du')->getMinutes()),IL_CAL_UNIX));
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
		global $ilTabs;
		
		$ilTabs->addTab('consultation_hours', $this->lng->txt('cal_ch_ch'), $this->ctrl->getLinkTarget($this,'appointmentList'));
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
		$this->initFormSequence(self::MODE_MULTI);

		if($this->form->checkInput())
		{
			$this->form->setValuesByPost();
			$apps = explode(';', $_POST['apps']);

			include_once 'Services/Booking/classes/class.ilBookingEntry.php';
			include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';

			// do collision-check if max bookings were reduced
			$first = $apps;
			$first = array_shift($apps);
			$entry = ilBookingEntry::getInstanceByCalendarEntryId($first);
			if($this->form->getInput('bo') < $entry->getNumberOfBookings())
			{
			   // :TODO:
			   return;
			}


			// create new context
			
			
			$booking = new ilBookingEntry();
			
			$booking->setObjId($this->getUserId());
			$booking->setNumberOfBookings($this->form->getInput('bo'));

			$deadline = $this->form->getInput('dead');
			$deadline = $deadline['dd']*24+$deadline['hh'];
			$booking->setDeadlineHours($deadline);
			
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
		foreach($_POST['apps'] as $entry_id)
		{
			$entry = new ilCalendarEntry($entry_id);
			$entry->delete();

			// :TODO: notifications
		}

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
		if(isset($_GET['panel']))
		{
			$ilTabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTargetByClass('ilCalendarPresentationGUI'));
		}
		else
		{
			$ilTabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'appointmentList'));
		}

		$user_id = (int)$_GET['user'];

		include_once 'Services/User/classes/class.ilPublicUserProfileGUI.php';
		$profile = new ilPublicUserProfileGUI($user_id);
		$tpl->setContent($profile->getHTML());
	}
}
?>