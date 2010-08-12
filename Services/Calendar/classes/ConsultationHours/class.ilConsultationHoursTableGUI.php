<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';
include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourAppointments.php';

/**
* Consultation hours administration
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesCalendar
*/
class ilConsultationHoursTableGUI extends ilTable2GUI
{
	private $user_id = 0; 
	
	/**
	 * Constructor
	 * @param object $a_gui
	 * @param object $a_cmd
	 * @param object $a_user_id
	 * @return 
	 */
	public function __construct($a_gui,$a_cmd,$a_user_id)
	{
		global $lng,$ilCtrl;
		
		$this->user_id = $a_user_id;
		$this->setId('chtg_'.$this->getUserId());
		parent::__construct($a_gui,$a_cmd);
		
		$this->addColumn('','f',1);
		$this->addColumn($this->lng->txt('title'),'title');
		$this->addColumn($this->lng->txt('cal_start'),'start');
		$this->addColumn($this->lng->txt('cal_ch_num_bookings'),'num_bookings');
		$this->addColumn($this->lng->txt('cal_ch_bookings'));
		$this->addColumn($this->lng->txt('cal_ch_target_object'));
		$this->addColumn('');
		
		$this->setRowTemplate('tpl.ch_upcoming_row.html','Services/Calendar');
		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject(),$this->getParentCmd()));
		$this->setTitle($this->lng->txt('cal_ch_ch'));
		
		$this->enable('sort');
		$this->enable('header');
		$this->enable('numinfo');
		
		$this->setDefaultOrderField('start');
		$this->setSelectAllCheckbox('apps');
		$this->setShowRowsSelector(true); 

		$this->addMultiCommand('edit', $this->lng->txt('edit'));
		$this->addMultiCommand('confirmDelete', $this->lng->txt('delete'));
	}
	
	/**
	 * get user id
	 * @return 
	 */
	public function getUserId()
	{
		return $this->user_id;
	}
	
	/**
	 * Fill row
	 * @return 
	 */
	public function fillRow($row)
	{
		global $ilCtrl;
		
		$this->tpl->setVariable('VAL_ID',$row['id']);
		$this->tpl->setVariable('TITLE',$row['title']);
		$this->tpl->setVariable('START',$row['start_p']);
		$this->tpl->setVariable('NUM_BOOKINGS',$row['num_bookings']);
		$this->tpl->setVariable('TARGET', $row['target']);

		if($row['bookings'])
		{
			$this->tpl->setCurrentBlock('bookings');
			foreach($row['bookings'] as $user_id => $name)
			{
				$ilCtrl->setParameter($this->getParentObject(), 'user', $user_id);
				$this->tpl->setVariable('URL_BOOKING', $ilCtrl->getLinkTarget($this->getParentObject(), 'showprofile'));
				$ilCtrl->setParameter($this->getParentObject(), 'user', '');
				$this->tpl->setVariable('TXT_BOOKING', $name);
				$this->tpl->parseCurrentBlock();
			}
		}

		$this->tpl->setVariable('BOOKINGS',implode(', ', $row['bookings']));
	}
	
	/**
	 * Parse appointments
	 * @return 
	 */
	public function parse()
	{
		global $ilDB, $ilObjDataCache;

		include_once 'Services/Booking/classes/class.ilBookingEntry.php';

		$data = array();
		$counter = 0;
		foreach(ilConsultationHourAppointments::getAppointments($this->getUserId()) as $app)
		{
			$data[$counter]['id'] = $app->getEntryId();
			$data[$counter]['title'] = $app->getTitle();
			$data[$counter]['description'] = $app->getDescription();
			$data[$counter]['start'] = $app->getStart()->get(IL_CAL_UNIX);
			$data[$counter]['start_p'] = ilDatePresentation::formatDate($app->getStart());

			$booking = new ilBookingEntry($app->getContextId());
			$users = array();
			foreach($booking->getCurrentBookings($app->getEntryId()) as $user_id)
			{
				$users[$user_id] = ilObjUser::_lookupFullname($user_id);
			}
			$data[$counter]['num_bookings'] = sizeof($users);
			$data[$counter]['bookings'] = $users;

			$target = $booking->getTargetObjId();
			if($target)
			{
				$data[$counter]['target'] = $ilObjDataCache->lookupTitle($target);
			}
			
			$counter++;
		}
		
		$this->setData($data);
	}
}
?>