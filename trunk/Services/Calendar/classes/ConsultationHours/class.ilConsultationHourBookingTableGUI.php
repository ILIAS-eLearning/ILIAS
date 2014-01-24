<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroup.php';
include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilConsultationHourBookingTableGUI extends ilTable2GUI
{
	private $user_id = 0;
	
	private $today = null;
	
	/**
	 * Constructor
	 * @param type $a_parent_obj
	 * @param type $a_parent_cmd
	 * @param type $a_user_id
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_user_id)
	{
		$this->user_id = $a_user_id;
		$this->setId('chboo_'.$this->user_id);
		parent::__construct($a_parent_obj,$a_parent_cmd);
		
		$this->initTable();
		
		
		$this->today = new ilDateTime(time(),IL_CAL_UNIX);
	}
	
	/**
	 * Init table
	 */
	protected function initTable()
	{
		$this->setRowTemplate('tpl.ch_booking_row.html','Services/Calendar');
		
		$this->setTitle($GLOBALS['lng']->txt('cal_ch_bookings_tbl'));
		$this->setFormAction($GLOBALS['ilCtrl']->getFormAction($this->getParentObject(),$this->getParentCmd()));
		
		$this->addColumn('','','1px');
		$this->addColumn($GLOBALS['lng']->txt('cal_start'),'start');
		$this->addColumn($GLOBALS['lng']->txt('name'),'name');
		$this->addColumn($GLOBALS['lng']->txt('cal_ch_booking_message_tbl'),'comment');
		$this->addColumn($GLOBALS['lng']->txt('title'),'title');
		$this->addColumn($GLOBALS['lng']->txt('actions'), '');
		
		$this->enable('sort');
		$this->enable('header');
		$this->enable('num_info');
		
		$this->setDefaultOrderField('start');
		$this->setSelectAllCheckbox('bookuser');
		$this->setShowRowsSelector(true);
		$this->addMultiCommand('confirmRejectBooking', $this->lng->txt('cal_ch_reject_booking'));
		$this->addMultiCommand('confirmDeleteBooking', $this->lng->txt('cal_ch_delete_booking'));
	}
	
	/**
	 * Fill row
	 * @param type $a_set
	 */
	public function fillRow($row)
	{
		global $ilCtrl;
		
		$this->tpl->setVariable('START',$row['start_str']);
		$this->tpl->setVariable('NAME',$row['name']);
		$this->tpl->setVariable('COMMENT',$row['comment']);
		$this->tpl->setVariable('TITLE',$row['title']);
		$this->tpl->setVariable('VAL_ID',$row['id']);
		
		include_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
		$list = new ilAdvancedSelectionListGUI();
		$list->setId('act_chboo_'.$row['id']);
		$list->setListTitle($this->lng->txt('actions'));

		$ilCtrl->setParameter($this->getParentObject(),'bookuser',$row['id']);
		
		$start = new ilDateTime($row['start'],IL_CAL_UNIX);
		if(ilDateTime::_after($start, $this->today,IL_CAL_DAY))
		{
			$list->addItem(
				$this->lng->txt('cal_ch_reject_booking'),
				'',
				$ilCtrl->getLinkTarget($this->getParentObject(),'confirmRejectBooking')
			);
		}
		$list->addItem(
			$this->lng->txt('cal_ch_delete_booking'),
			'',
			$ilCtrl->getLinkTarget($this->getParentObject(),'confirmDeleteBooking')
		);
		$this->tpl->setVariable('ACTIONS',$list->getHTML());
	}


	/**
	 * Parse Groups
	 * @param array $groups
	 */
	public function parse(array $appointments)
	{
		global $ilCtrl;
		
		$rows = array();
		$counter = 0;
		foreach($appointments as $app)
		{
			include_once './Services/Calendar/classes/class.ilCalendarEntry.php';
			$cal_entry = new ilCalendarEntry($app);
			
			include_once './Services/Booking/classes/class.ilBookingEntry.php';
			foreach(ilBookingEntry::lookupBookingsForAppointment($app) as $user_id)
			{
				include_once './Services/User/classes/class.ilUserUtil.php';
				$rows[$counter]['name'] = ilUserUtil::getNamePresentation(
						$user_id,
						true,
						true,
						$ilCtrl->getLinkTarget($this->getParentObject(),$this->getParentCmd()),
						true,
						true
				);
				
				$message = ilBookingEntry::lookupBookingMessage($app, $user_id);
				if(strlen(trim($message)))
				{
					$rows[$counter]['comment'] = ('"'.$message.'"');
				}
				$rows[$counter]['title'] = $cal_entry->getTitle();
				$rows[$counter]['start'] = $cal_entry->getStart()->get(IL_CAL_UNIX);
				$rows[$counter]['start_str'] = ilDatePresentation::formatDate($cal_entry->getStart());
				$rows[$counter]['id'] = $app.'_'.$user_id;
				++$counter;
			}
		}
		$this->setData($rows);
	}
}
?>
