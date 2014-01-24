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

include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/
class ilCalendarAppointmentsTableGUI extends ilTable2GUI
{
	private $cat_id = 0;
	private $categories = null;
	private $is_editable = false;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj,$a_parent_cmd,$a_category_id)
	{
	 	global $lng,$ilCtrl;
	 	
	 	
	 	$this->categories = ilCalendarCategories::_getInstance();
	 	$this->cat_id = $a_category_id;
	 	$this->is_editable = $this->categories->isEditable($this->cat_id); 
	 	
	 	$this->lng = $lng;
		$this->lng->loadLanguageModule('dateplaner');
	 	$this->ctrl = $ilCtrl;
		
		$this->setId('calcalapps');
	 	
		parent::__construct($a_parent_obj,$a_parent_cmd);
		$this->setFormName('appointments');
	 	$this->addColumn('','f',"1");
	 	$this->addColumn($this->lng->txt('cal_start'),'dt_sort',"30%");
	 	$this->addColumn($this->lng->txt('title'),'title',"60%");
	 	$this->addColumn($this->lng->txt('cal_duration'),'duration',"20%");
	 	$this->addColumn($this->lng->txt('cal_recurrences'),'frequence',"10%");
	 	
	 	if($this->is_editable)
	 	{
	 		$this->addMultiCommand('askDeleteAppointments',$this->lng->txt('delete'));
			$this->enable('select_all');
	 	}
	 	else
	 	{
	 		$this->disable('select_all');
	 	}
	 	
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.show_appointment_row.html","Services/Calendar");
		
		$this->setShowRowsSelector(true);
		$this->enable('sort');
		$this->enable('header');
		$this->enable('numinfo');
		
		$this->setDefaultOrderField('dt_sort');
		$this->setSelectAllCheckbox('appointments');
	}
	
	
	/**
	 * fill row
	 *
	 * @access protected
	 * @param array set of data
	 * @return
	 */
	protected function fillRow($a_set)
	{
		if($a_set['deletable'])
		{
			$this->tpl->setVariable('VAL_ID',$a_set['id']);
		}
		
		$this->tpl->setVariable('VAL_DESCRIPTION',$a_set['description']);
		
		$this->tpl->setVariable('VAL_TITLE_LINK',$a_set['title']);
		$this->ctrl->setParameterByClass('ilcalendarappointmentgui','app_id',$a_set['id']);
		$this->tpl->setVariable('VAL_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','edit'));

		switch($a_set['frequence'])
		{
			case IL_CAL_FREQ_DAILY:
				$this->tpl->setVariable('VAL_FREQUENCE',$this->lng->txt('cal_daily'));
				break;
				
			case IL_CAL_FREQ_WEEKLY:
				$this->tpl->setVariable('VAL_FREQUENCE',$this->lng->txt('cal_weekly'));
				break;
			
			case IL_CAL_FREQ_MONTHLY:
				$this->tpl->setVariable('VAL_FREQUENCE',$this->lng->txt('cal_monthly'));
				break;
			
			case IL_CAL_FREQ_YEARLY:
				$this->tpl->setVariable('VAL_FREQUENCE',$this->lng->txt('cal_yearly'));
				break;
			
			default:
				//$this->tpl->setVariable('VAL_FREQUENCE',$this->lng->txt('cal_no_recurrence'));
				$this->tpl->setVariable('VAL_FREQUENCE','');
				break;
		}
		$this->tpl->setVariable('VAL_BEGIN',$a_set['dt']);
		if($a_set['duration'])
		{
			$this->tpl->setVariable('VAL_DURATION',ilFormat::_secondsToString($a_set['duration']));	
		}
		else
		{
			$this->tpl->setVariable('VAL_DURATION','');
		}
	}
	
	/**
	 * Get 
	 * @return ilDateTime
	 */
	protected static function getToday()
	{
		return $this->today;
	}

	/**
	 * set appointments
	 *
	 * @access public
	 * @return
	 */
	public function setAppointments($a_apps)
	{		
		include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
		include_once('./Services/Calendar/classes/class.ilCalendarRecurrences.php');
		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');

		$cat = new ilCalendarCategory($this->cat_id);
			
		foreach($a_apps as $cal_entry_id)
		{
			$entry = new ilCalendarEntry($cal_entry_id);
 			$rec = ilCalendarRecurrences::_getFirstRecurrence($entry->getEntryId());

			// booking
			if($cat->getType() == ilCalendarCategory::TYPE_CH)
			{
				include_once 'Services/Booking/classes/class.ilBookingEntry.php';
				$book = new ilBookingEntry($entry->getContextId());
				if($book)
				{
					$title = $entry->getTitle();
					if($book->isOwner())
					{
						$max = (int)$book->getNumberOfBookings();
						$current = (int)$book->getCurrentNumberOfBookings($entry->getEntryId());
						if($max > 1)
						{
							$title .= ' ('.$current.'/'.$max.')';
						}
						else if($current == $max)
						{
							$title .= ' ('.$this->lng->txt('cal_booked_out').')';
						}
						else
						{
							$title .= ' ('.$this->lng->txt('cal_book_free').')';
						}
					}
					else if($book->hasBooked($entry->getEntryId()))
					{
						$title .= ' ('.$this->lng->txt('cal_date_booked').')';
					}
				}
			}
		    else
			{
				$title = $entry->getPresentationTitle();
			}
			
			$tmp_arr['id'] = $entry->getEntryId();
			$tmp_arr['title'] = $title;
			$tmp_arr['description'] = $entry->getDescription();
			$tmp_arr['fullday'] = $entry->isFullday();
 			$tmp_arr['begin'] = $entry->getStart()->get(IL_CAL_UNIX);
 			$tmp_arr['end'] = $entry->getEnd()->get(IL_CAL_UNIX);
			
			$tmp_arr['dt_sort'] = $entry->getStart()->get(IL_CAL_UNIX);
			$tmp_arr['dt'] = ilDatePresentation::formatPeriod(
					$entry->getStart(),
					$entry->getEnd()
			);
 			
 			#$tmp_arr['duration'] = ($dur = $tmp_arr['end'] - $tmp_arr['begin']) ? $dur : 60 * 60 * 24;
 			$tmp_arr['duration'] = $tmp_arr['end'] - $tmp_arr['begin'];
 			if($tmp_arr['fullday'])
 			{
 				$tmp_arr['duration'] += (60 * 60 * 24);
 			}

 			if(!$tmp_arr['fullday'] and $tmp_arr['end'] == $tmp_arr['begin'])
 			{
 				$tmp_arr['duration'] = '';
 			}
 			$tmp_arr['frequence'] = $rec->getFrequenceType();
 			$tmp_arr['deletable'] = (!$entry->isAutoGenerated() and $this->is_editable);
			
			$appointments[] = $tmp_arr;		
		}
		$this->setData($appointments ? $appointments : array());
	}
	
}
?>