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


include_once('./Services/Calendar/classes/class.ilCalendarUserSettings.php');
include_once './Services/Calendar/classes/class.ilCalendarRecurrence.php';

/**
* This class represents an input GUI for recurring events/appointments (course events or calendar appointments) 
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

class ilRecurrenceInputGUI extends ilCustomInputGUI
{
	const REC_LIMITED = 2;
	const REC_UNLIMITED = 1;
	
	protected $lng;
	
	protected $recurrence;
	protected $user_settings;
	
	protected $allow_unlimited_recurrences = true;
	
	protected $enabled_subforms = array(
		IL_CAL_FREQ_DAILY,
		IL_CAL_FREQ_WEEKLY,
		IL_CAL_FREQ_MONTHLY,
		IL_CAL_FREQ_YEARLY);

	/**
	 * Constructor
	 *
	 * @access public
	 * @param string title
	 * @param string postvar
	 */
	public function __construct($a_title, $a_postvar)
	{
		global $lng,$tpl,$ilUser;
		
		$this->lng = $lng;
		$this->lng->loadLanguageModule('dateplaner');

		$this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($ilUser->getId());
		$tpl->addJavascript("./Services/Calendar/js/recurrence_input.js");
		
		$this->setRecurrence(new ilCalendarRecurrence());
		
		parent::__construct($a_title,$a_postvar);
	}
	
	/**
	 * check input
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function checkInput()
	{
		global $lng;
		
		$this->loadRecurrence();

		if($_POST['frequence'] == 'NONE')
		{
			return true;
		}
		
		if(!isset($_POST['until_type']) or $_POST['until_type'] == REC_LIMITED)
		{
			if($_POST['count'] <= 0 or $_POST['count'] >= 100)
			{
				$this->setAlert($lng->txt("cal_rec_err_limit"));
				return false;
			}
		}
		

		return true;
	}
	
	/**
	 * load recurrence settings
	 * @access protected
	 * @return
	 */
	protected function loadRecurrence()
	{
		if(!$this->getRecurrence() instanceof ilCalendarRecurrence)
		{
			return false;
		}
		
		
		switch($_POST['frequence'])
		{
			case IL_CAL_FREQ_DAILY:
				$this->getRecurrence()->setFrequenceType($_POST['frequence']);
				$this->getRecurrence()->setInterval((int) $_POST['count_DAILY']);
				break;
			
			case IL_CAL_FREQ_WEEKLY:
				$this->getRecurrence()->setFrequenceType($_POST['frequence']);
				$this->getRecurrence()->setInterval((int) $_POST['count_WEEKLY']);
				if(is_array($_POST['byday_WEEKLY']))
				{
					$this->getRecurrence()->setBYDAY(ilUtil::stripSlashes(implode(',',$_POST['byday_WEEKLY'])));
				}				
				break;

			case IL_CAL_FREQ_MONTHLY:
				$this->getRecurrence()->setFrequenceType($_POST['frequence']);
				$this->getRecurrence()->setInterval((int) $_POST['count_MONTHLY']);
				switch((int) $_POST['subtype_MONTHLY'])
				{
					case 0:
						// nothing to do;
						break;
					
					case 1:
						switch((int) $_POST['monthly_byday_day'])
						{
							case 8:
								// Weekday
								$this->getRecurrence()->setBYSETPOS((int) $_POST['monthly_byday_num']);
								$this->getRecurrence()->setBYDAY('MO,TU,WE,TH,FR');
								break;
								
							case 9:
								// Day of month
								$this->getRecurrence()->setBYMONTHDAY((int) $_POST['monthly_byday_num']);
								break;
								
							default:
								$this->getRecurrence()->setBYDAY((int) $_POST['monthly_byday_num'].$_POST['monthly_byday_day']);
								break;
						}
						break;
					
					case 2:
						$this->getRecurrence()->setBYMONTHDAY((int) $_POST['monthly_bymonthday']);
						break;
				}
				break;			
			
			case IL_CAL_FREQ_YEARLY:
				$this->getRecurrence()->setFrequenceType($_POST['frequence']);
				$this->getRecurrence()->setInterval((int) $_POST['count_YEARLY']);
				switch((int) $_POST['subtype_YEARLY'])
				{
					case 0:
						// nothing to do;
						break;
					
					case 1:
						$this->getRecurrence()->setBYMONTH((int) $_POST['yearly_bymonth_byday']);
						$this->getRecurrence()->setBYDAY((int) $_POST['yearly_byday_num'].$_POST['yearly_byday']);
						break;
					
					case 2:
						$this->getRecurrence()->setBYMONTH((int) $_POST['yearly_bymonth_by_monthday']);
						$this->getRecurrence()->setBYMONTHDAY((int) $_POST['yearly_bymonthday']);
						break;
				}
				break;			
		}
		
		// UNTIL
		switch((int) $_POST['until_type'])
		{
			case 1:
				$this->getRecurrence()->setFrequenceUntilDate(null);
				// nothing to do
				break;
				
			case 2:
				$this->getRecurrence()->setFrequenceUntilDate(null);
				$this->getRecurrence()->setFrequenceUntilCount((int) $_POST['count']);
				break;
				
			case 3:
				$end_dt['year'] = (int) $_POST['until_end']['date']['y'];
				$end_dt['mon'] = (int) $_POST['until_end']['date']['m'];
				$end_dt['mday'] = (int) $_POST['until_end']['date']['d'];
				
				$this->getRecurrence()->setFrequenceUntilCount(0);
				$this->getRecurrence()->setFrequenceUntilDate(new ilDate($end_dt,IL_CAL_FKT_GETDATE,$this->timezone));
				break;
		}
		
		return true;
	}
	
	
	/**
	 * set recurrence object
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function setRecurrence($a_rec)
	{
		$this->recurrence = $a_rec;
	}
	
	/**
	 * Get Recurrence
	 * @return 
	 */
	public function getRecurrence()
	{
		return $this->recurrence;
	}
	
	/**
	 * Allow unlimited recurrences
	 * @param object $a_status
	 * @return 
	 */
	public function allowUnlimitedRecurrences($a_status)
	{
		$this->allow_unlimited_recurrences = $a_status;
	}
	
	/**
	 * Check if unlimited recurrence is allowed
	 * @return 
	 */
	public function isUnlimitedRecurrenceAllowed()
	{
		return $this->allow_unlimited_recurrences;
	}
	
	/**
	 * set enabled subforms
	 *
	 * @access public
	 * @param array(IL_CAL_FREQ_DAILY,IL_CAL_FREQ_WEEKLY...)
	 * @return
	 */
	public function setEnabledSubForms($a_sub_forms)
	{
		$this->enabled_subforms = $a_sub_forms;
	}
	
	/**
	 * get enabled subforms
	 *
	 * @access public
	 * @return
	 */
	public function getEnabledSubForms()
	{
		return $this->enabled_subforms;
	}
	
	/**
	 * insert 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function insert($a_tpl)
	{
		$tpl = new ilTemplate('tpl.recurrence_input.html',true,true,'Services/Calendar');
		
		$options = array('NONE' => $this->lng->txt('cal_no_recurrence'));
		if(in_array(IL_CAL_FREQ_DAILY, $this->getEnabledSubForms()))
		{
			$options[IL_CAL_FREQ_DAILY] = $this->lng->txt('cal_daily');
		}
		if(in_array(IL_CAL_FREQ_WEEKLY, $this->getEnabledSubForms()))
		{
			$options[IL_CAL_FREQ_WEEKLY] = $this->lng->txt('cal_weekly');
		}
		if(in_array(IL_CAL_FREQ_MONTHLY, $this->getEnabledSubForms()))
		{
			$options[IL_CAL_FREQ_MONTHLY] = $this->lng->txt('cal_monthly');
		}
		if(in_array(IL_CAL_FREQ_YEARLY, $this->getEnabledSubForms()))
		{
			$options[IL_CAL_FREQ_YEARLY] = $this->lng->txt('cal_yearly');
		}
		
		$tpl->setVariable('FREQUENCE',ilUtil::formSelect(
			$this->recurrence->getFrequenceType(),
			'frequence',
			$options,
			false,
			true,
			'',
			'',
			array('onchange' => 'ilHideFrequencies();','id' => 'il_recurrence_1')));
		
		$tpl->setVariable('TXT_EVERY',$this->lng->txt('cal_every'));

		// DAILY
		if(in_array(IL_CAL_FREQ_DAILY, $this->getEnabledSubForms()))
		{
			$tpl->setVariable('TXT_DAILY_FREQ_UNIT',$this->lng->txt('cal_day_s'));
			$tpl->setVariable('COUNT_DAILY_VAL',$this->recurrence->getInterval());
		}
		
		// WEEKLY
		if(in_array(IL_CAL_FREQ_WEEKLY, $this->getEnabledSubForms()))
		{
			$tpl->setVariable('TXT_WEEKLY_FREQ_UNIT',$this->lng->txt('cal_week_s'));
			$tpl->setVariable('COUNT_WEEKLY_VAL',$this->recurrence->getInterval());
			$this->buildWeekDaySelection($tpl);
		}
		
		// MONTHLY
		if(in_array(IL_CAL_FREQ_MONTHLY, $this->getEnabledSubForms()))
		{
			$tpl->setVariable('TXT_MONTHLY_FREQ_UNIT',$this->lng->txt('cal_month_s'));
			$tpl->setVariable('COUNT_MONTHLY_VAL',$this->recurrence->getInterval());
			$tpl->setVariable('TXT_ON_THE',$this->lng->txt('cal_on_the'));
			$tpl->setVariable('TXT_BYMONTHDAY',$this->lng->txt('cal_on_the'));
			$tpl->setVariable('TXT_OF_THE_MONTH',$this->lng->txt('cal_of_the_month'));
			$this->buildMonthlyByDaySelection($tpl);
			$this->buildMonthlyByMonthDaySelection($tpl);
		}

		// YEARLY
		if(in_array(IL_CAL_FREQ_YEARLY, $this->getEnabledSubForms()))
		{
			$tpl->setVariable('TXT_YEARLY_FREQ_UNIT',$this->lng->txt('cal_year_s'));
			$tpl->setVariable('COUNT_YEARLY_VAL',$this->recurrence->getInterval());
			$tpl->setVariable('TXT_ON_THE',$this->lng->txt('cal_on_the'));
			$this->buildYearlyByMonthDaySelection($tpl);
			$this->buildYearlyByDaySelection($tpl);
		}

		// UNTIL
		$this->buildUntilSelection($tpl);
		
		$a_tpl->setCurrentBlock("prop_custom");
		$a_tpl->setVariable("CUSTOM_CONTENT", $tpl->get());
		$a_tpl->parseCurrentBlock();
		
	}
	
	/**
	 * build weekday checkboxes
	 *
	 * @access protected
	 * @param object tpl
	 */
	protected function buildWeekDaySelection($tpl)
	{
		$days = array(0 => 'SU',1 => 'MO',2 => 'TU',3 => 'WE',4 => 'TH',5 => 'FR',6 => 'SA',7 => 'SU');
		
		$checked_days = array();
		foreach($this->recurrence->getBYDAYList() as $byday)
		{
			if(in_array($byday,$days))
			{
				$checked_days[] = $byday;
			}
		}
		
		for($i = (int) $this->user_settings->getWeekStart();$i < 7 + (int) $this->user_settings->getWeekStart();$i++)
		{
			$tpl->setCurrentBlock('byday_simple');
			
			if(in_array($days[$i],$checked_days))
			{
				$tpl->setVariable('BYDAY_WEEKLY_CHECKED','checked="checked"');
			}
			$tpl->setVariable('TXT_ON',$this->lng->txt('cal_on'));
			$tpl->setVariable('BYDAY_WEEKLY_VAL',$days[$i]);
			$tpl->setVariable('TXT_DAY_SHORT',ilCalendarUtil::_numericDayToString($i,false));
			$tpl->parseCurrentBlock();
		}
		
	}
	
	/**
	 * build monthly by day list (e.g second monday)
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function buildMonthlyByDaySelection($tpl)
	{
		$byday_list = $this->recurrence->getBYDAYList();
		$chosen_num_day = 1;
		$chosen_day = 'MO';
		$chosen = false;
		foreach($byday_list as $byday)
		{
			if(preg_match('/^(-?\d)([A-Z][A-Z])/',$byday,$parsed) === 1)
			{
				$chosen = true;
				$chosen_num_day = $parsed[1];
				$chosen_day = $parsed[2];
			}
		}
		// check for last day
		if(count($this->recurrence->getBYMONTHDAYList()) == 1)
		{
			$bymonthday = $this->recurrence->getBYMONTHDAY();
			if(in_array($bymonthday,array(1,2,3,4,5,-1)))
			{
				$chosen = true;
				$chosen_num_day = $bymonthday;
				$chosen_day = 9;
			}	
		}
		// Check for first, second... last weekday
		if(count($this->recurrence->getBYSETPOSList()) == 1)
		{
			$bysetpos = $this->recurrence->getBYSETPOS();
			if(in_array($bysetpos,array(1,2,3,4,5,-1)))
			{
				if($this->recurrence->getBYDAYList() == array('MO','TU','WE','TH','FR'))
				{
					$chosen = true;
					$chosen_num_day = $bysetpos;
					$chosen_day = 8;
				}
			}
		}
		
		

		if($chosen)
		{
			$tpl->setVariable('M_BYDAY_CHECKED','checked="checked"');
		}

		$num_options = array(
			1 => $this->lng->txt('cal_first'),
			2 => $this->lng->txt('cal_second'),
			3 => $this->lng->txt('cal_third'),
			4 => $this->lng->txt('cal_fourth'),
			5 => $this->lng->txt('cal_fifth'),
		   -1 => $this->lng->txt('cal_last'));
		   
		$tpl->setVariable('SELECT_BYDAY_NUM_MONTHLY',ilUtil::formSelect(
			$chosen_num_day,
			'monthly_byday_num',
			$num_options,
			false,
			true,
			'',
			'',
			array('onchange' => "ilUpdateSubTypeSelection('sub_monthly_radio_1');")));
			
		$days = array(0 => 'SU',1 => 'MO',2 => 'TU',3 => 'WE',4 => 'TH',5 => 'FR',6 => 'SA',7 => 'SU');
		
		for($i = (int) $this->user_settings->getWeekStart();$i < 7 + (int) $this->user_settings->getWeekStart();$i++)
		{
			$days_select[$days[$i]] = ilCalendarUtil::_numericDayToString($i);
		}
		$days_select[8] = $this->lng->txt('cal_weekday');
		$days_select[9] = $this->lng->txt('cal_day_of_month');
		$tpl->setVariable('SEL_BYDAY_DAY_MONTHLY',ilUtil::formSelect(
			$chosen_day,
			'monthly_byday_day',
			$days_select,
			false,
			true,
			'',
			'',
			array('onchange' => "ilUpdateSubTypeSelection('sub_monthly_radio_1');")));
	}
	
	/**
	 * build monthly bymonthday selection
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function buildMonthlyByMonthDaySelection($tpl)
	{
		$tpl->setVariable('TXT_IN',$this->lng->txt('cal_in'));
		
		$chosen_day = 1;
		$chosen = false;
		if(count($bymonthday = $this->recurrence->getBYMONTHDAYList()) == 1)
		{
			foreach($bymonthday as $mday)
			{
				if($mday > 0 and $mday < 32)
				{
					$chosen = true;
					$chosen_day = $mday;
				}
			}
		}

		if($chosen)
		{
			$tpl->setVariable('M_BYMONTHDAY_CHECKED','checked="checked"');
		}
		
		for($i = 1; $i < 32;$i++)
		{
			$options[$i] = $i;
		}
		$tpl->setVariable('SELECT_BYMONTHDAY',ilUtil::formSelect(
			$chosen_day,
			'monthly_bymonthday',
			$options,
			false,
			true,
			'',
			'',
			array('onchange' => "ilUpdateSubTypeSelection('sub_monthly_radio_2');")));
	}
	
	/**
	 * 
	 *
	 * @access protected
	 * @param objet tpl
	 * @return
	 */
	protected function buildYearlyByMonthDaySelection($tpl)
	{
		$tpl->setVariable('TXT_Y_EVERY',$this->lng->txt('cal_every'));

			
		$chosen = false;
		$chosen_month = 1;
		$chosen_day = 1;
		foreach($this->recurrence->getBYMONTHList() as $month)
		{
			if($this->recurrence->getBYMONTHDAYList())
			{
				$chosen_month = $month;
				$chosen = true;
				break;
			}
		}
		foreach($this->recurrence->getBYMONTHDAYList() as $day)
		{
			$chosen_day = $day;
		}
			
		for($i = 1; $i < 32;$i++)
		{
			$options[$i] = $i;
		}
		$tpl->setVariable('SELECT_BYMONTHDAY_NUM_YEARLY',ilUtil::formSelect(
			$chosen_day,
			'yearly_bymonthday',
			$options,
			false,
			true,
			
			'',
			'',
			array('onchange' => "ilUpdateSubTypeSelection('sub_yearly_radio_2');")));
		
		$options = array();	
		for($m = 1;$m < 13;$m++)
		{
			$options[$m] = ilCalendarUtil::_numericMonthToString($m);
		}
		$tpl->setVariable('SELECT_BYMONTH_YEARLY',ilUtil::formSelect(
			$chosen_month,
			'yearly_bymonth_by_monthday',
			$options,
			false,
			true,
			'',
			'',
			array('onchange' => "ilUpdateSubTypeSelection('sub_yearly_radio_2');")));
			
			
		if($chosen)
		{
			$tpl->setVariable('Y_BYMONTHDAY_CHECKED','checked="checked"');
		}
		
	}
	
	/**
	 * 
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function buildYearlyByDaySelection($tpl)
	{
		$tpl->setVariable('TXT_ON_THE',$this->lng->txt('cal_on_the'));

		$chosen_num_day = 1;
		$chosen_day = 'MO';
		$chosen = false;
		foreach($this->recurrence->getBYDAYList() as $byday)
		{
			if(preg_match('/^(-?\d)([A-Z][A-Z])/',$byday,$parsed) === 1)
			{
				$chosen = true;
				$chosen_num_day = $parsed[1];
				$chosen_day = $parsed[2];
			}
		}


		$num_options = array(
			1 => $this->lng->txt('cal_first'),
			2 => $this->lng->txt('cal_second'),
			3 => $this->lng->txt('cal_third'),
			4 => $this->lng->txt('cal_fourth'),
			5 => $this->lng->txt('cal_fifth'),
		   -1 => $this->lng->txt('cal_last'));
		   
		$tpl->setVariable('SELECT_BYDAY_NUM_YEARLY',ilUtil::formSelect(
			$chosen_num_day,
			'yearly_byday_num',
			$num_options,
			false,
			true,
			'',
			'',
			array('onchange' => "ilUpdateSubTypeSelection('sub_yearly_radio_1');")));
			
		
		$days = array(0 => 'SU',1 => 'MO',2 => 'TU',3 => 'WE',4 => 'TH',5 => 'FR',6 => 'SA',7 => 'SU');
		for($i = (int) $this->user_settings->getWeekStart();$i < 7 + (int) $this->user_settings->getWeekStart();$i++)
		{
			$days_select[$days[$i]] = ilCalendarUtil::_numericDayToString($i);
		}
		$tpl->setVariable('SELECT_BYDAY_DAY_YEARLY',ilUtil::formSelect(
			$chosen_day,
			'yearly_byday',
			$days_select,
			false,
			true,
			'',
			'',
			array('onchange' => "ilUpdateSubTypeSelection('sub_yearly_radio_1');")));
			
	
		$chosen = false;
		$chosen_month = 1;
		foreach($this->recurrence->getBYMONTHList() as $month)
		{
			if($this->recurrence->getBYMONTHDAYList())
			{
				$chosen_month = $month;
				$chosen = true;
				break;
			}
		}
		$options = array();	
		for($m = 1;$m < 13;$m++)
		{
			$options[$m] = ilCalendarUtil::_numericMonthToString($m);
		}
		$tpl->setVariable('SELECT_BYMONTH_BYDAY',ilUtil::formSelect(
			$chosen_month,
			'yearly_bymonth_byday',
			$options,
			false,
			true,
			'',
			'',
			array('onchange' => "ilUpdateSubTypeSelection('sub_yearly_radio_1');")));
			
	}
	
	/**
	 * build selection for ending date
	 *
	 * @access protected
	 * @param object tpl
	 * @return
	 */
	protected function buildUntilSelection($tpl)
	{
		
		if($this->isUnlimitedRecurrenceAllowed())
		{
			$tpl->setVariable('TXT_NO_ENDING',$this->lng->txt('cal_no_ending'));
		}
		
		$tpl->setVariable('TXT_UNTIL_CREATE',$this->lng->txt('cal_create'));
		$tpl->setVariable('TXT_APPOINTMENTS',$this->lng->txt('cal_appointments'));
		
		$tpl->setVariable('VAL_COUNT',$this->recurrence->getFrequenceUntilCount() ? 
			$this->recurrence->getFrequenceUntilCount() : 
			2);
			
		if($this->recurrence->getFrequenceUntilDate())
		{
			$tpl->setVariable('UNTIL_END_CHECKED','checked="checked"');
		}
		elseif($this->recurrence->getFrequenceUntilCount() or !$this->isUnlimitedRecurrenceAllowed())
		{
			$tpl->setVariable('UNTIL_COUNT_CHECKED','checked="checked"');
		}
		else
		{
			$tpl->setVariable('UNTIL_NO_CHECKED','checked="checked"');
		}

		$tpl->setVariable('TXT_UNTIL_END',$this->lng->txt('cal_repeat_until'));
		$dt = new ilDateTimeInputGUI('','until_end');
		$dt->setDate(
			$this->recurrence->getFrequenceUntilDate() ? $this->recurrence->getFrequenceUntilDate() : new ilDate(time(),IL_CAL_UNIX));
		$tpl->setVariable('UNTIL_END_DATE',$dt->getTableFilterHTML());
	}	
}
?>
