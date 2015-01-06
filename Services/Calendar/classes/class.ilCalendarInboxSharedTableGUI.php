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

include_once('./Services/Table/classes/class.ilTable2GUI.php');
include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
include_once('./Services/Calendar/classes/class.ilCalendarSharedStatus.php');

/**
* Show shared calendars for a specific user
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/
class ilCalendarInboxSharedTableGUI extends ilTable2GUI
{
	protected $cal_data = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @param object parent gui object
	 * @param string parent command
	 * @return
	 */
	public function __construct($a_parent_obj,$a_parent_cmd)
	{
		global $ilCtrl;
		
		parent::__construct($a_parent_obj,$a_parent_cmd);
		
		$this->setRowTemplate('tpl.calendar_inbox_shared_row.html','Services/Calendar');
		
		$this->addColumn('','cal_ids','1px');
		$this->addColumn($this->lng->txt('name'),'title','50%');
		$this->addColumn($this->lng->txt('cal_apps'),'apps','20%');
		$this->addColumn($this->lng->txt('create_date'),'create_date','20%');
		$this->addColumn($this->lng->txt('cal_accepted'),'accepted','5%');
		
		$this->addMultiCommand('acceptShared',$this->lng->txt('cal_share_accept'));
		$this->addMultiCommand('declineShared',$this->lng->txt('cal_share_decline'));
		$this->setSelectAllCheckbox('cal_ids');
		$this->setPrefix('shared');
		
		$this->setFormAction($ilCtrl->getFormActionByClass(get_class($this->getParentObject())));
		$this->setTitle($this->lng->txt('cal_shared_calendars'));
		
	}
	
	/**
	 * 
	 *
	 * @access public
	 * @param array row data
	 * @return
	 */
	public function fillRow($a_set)
	{
		$this->tpl->setVariable('VAL_ID',$a_set['cal_id']);
		$this->tpl->setVariable('CALENDAR_NAME',$a_set['name']);
		$this->tpl->setVariable('TXT_FROM',$this->lng->txt('owner'));
		
		$name = ilObjUser::_lookupName($a_set['owner']);
		$this->tpl->setVariable('LASTNAME',$name['lastname']);
		$this->tpl->setVariable('FIRSTNAME',$name['firstname']);
		
		$this->tpl->setVariable('APPS_COUNT',$a_set['apps']);
		$this->tpl->setVariable('CREATE_DATE',ilDatePresentation::formatDate(new ilDateTime($a_set['create_date'],IL_CAL_DATETIME)));
		
		if($a_set['accepted'])
		{
			$this->tpl->setVariable('ACC_IMG',ilUtil::getImagePath('icon_ok.svg'));
			$this->tpl->setVariable('ALT_ACC',$this->lng->txt('cal_accepted'));
		}
		if($a_set['declined'])
		{
			$this->tpl->setVariable('DEC_IMG',ilUtil::getImagePath('icon_ok.svg'));
			$this->tpl->setVariable('ALT_DEC',$this->lng->txt('cal_declined'));
		}
	}
	
	
	/**
	 * set calendars
	 *
	 * @access public
	 * @param array calendar data
	 * @return bool false if no entries are found
	 */
	public function setCalendars($a_calendars)
	{
		$this->cal_data = $a_calendars ? $a_calendars : array();
	}
	
	/**
	 * parse calendar data
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function parse()
	{
		global $ilUser;
		
		$counter = 0;
		$calendars = array();
		
		$status = new ilCalendarSharedStatus($ilUser->getId());
		
		foreach($this->cal_data as $data)
		{
			if($status->isDeclined($data['cal_id']) || $status->isAccepted($data['cal_id']))
			{
				continue;
			}
			
			$tmp_calendar = new ilCalendarCategory($data['cal_id']);
			
			$calendars[$counter]['cal_id'] = $data['cal_id'];
			$calendars[$counter]['create_date'] = $data['create_date'];
			$calendars[$counter]['obj_type'] = $data['obj_type'];
			$calendars[$counter]['name'] = $tmp_calendar->getTitle();
			$calendars[$counter]['owner'] = $tmp_calendar->getObjId();
			$calendars[$counter]['apps'] = count(ilCalendarCategoryAssignments::_getAssignedAppointments(array($data['cal_id'])));
			$calendars[$counter]['accepted'] = $status->isAccepted($data['cal_id']);
			$calendars[$counter]['declined'] = $status->isDeclined($data['cal_id']);
			
			$counter++;
		}
		
		$this->setData($calendars ? $calendars : array());
		return count($calendars) ? true : false;
	}
	
}
?>