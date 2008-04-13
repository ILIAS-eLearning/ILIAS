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
	protected $lng;
	
	protected $recurrence;
	protected $user_settings;
	
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
		$tpl->setVariable('BODY_ATTRIBUTES','onLoad="ilHideFrequencies();"');
		
		parent::__construct($a_title,$a_postvar);
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
		$options = array('NONE' => $this->lng->txt('cal_no_recurrences'),
			IL_CAL_FREQ_DAILY => $this->lng->txt('cal_daily'),
			IL_CAL_FREQ_WEEKLY=> $this->lng->txt('cal_weekly'),
			IL_CAL_FREQ_MONTHLY => $this->lng->txt('cal_monthly'),
			IL_CAL_FREQ_YEARLY => $this->lng->txt('cal_yearly'));
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
		$tpl->setVariable('TXT_DAILY_FREQ_UNIT',$this->lng->txt('cal_day_s'));
		$tpl->setVariable('COUNT_DAILY_VAL',$this->recurrence->getInterval());
		
		// WEEKLY
		$tpl->setVariable('TXT_WEEKLY_FREQ_UNIT',$this->lng->txt('cal_week_s'));
		$tpl->setVariable('COUNT_WEEKLY_VAL',$this->recurrence->getInterval());
		$this->buildWeekDaySelection($tpl);
		
		// MONTHLY
		$tpl->setVariable('TXT_MONTHLY_FREQ_UNIT',$this->lng->txt('cal_month_s'));
		$tpl->setVariable('COUNT_MONTHLY_VAL',$this->recurrence->getInterval());
		$tpl->setVariable('TXT_ON_THE',$this->lng->txt('cal_on_the'));
		$tpl->setVariable('TXT_BYMONTHDAY',$this->lng->txt('cal_on_the'));
		$tpl->setVariable('TXT_OF_THE_MONTH',$this->lng->txt('cal_of_the_month'));
		$this->buildMonthlyByDaySelection($tpl);
		$this->buildMonthlyByMonthDaySelection($tpl);

		// YEARLY
		$tpl->setVariable('TXT_YEARLY_FREQ_UNIT',$this->lng->txt('cal_year_s'));
		$tpl->setVariable('COUNT_YEARLY_VAL',$this->recurrence->getInterval());
		$tpl->setVariable('TXT_ON_THE',$this->lng->txt('cal_on_the'));
		$this->buildYearlyByMonthDaySelection($tpl);
		$this->buildYearlyByDaySelection($tpl);

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
			true));
			
		$days = array(0 => 'SU',1 => 'MO',2 => 'TU',3 => 'WE',4 => 'TH',5 => 'FR',6 => 'SA',7 => 'SU');
		
		for($i = (int) $this->user_settings->getWeekStart();$i < 7 + (int) $this->user_settings->getWeekStart();$i++)
		{
			$days_select[$days[$i]] = ilCalendarUtil::_numericDayToString($i);
		}
		$tpl->setVariable('SEL_BYDAY_DAY_MONTHLY',ilUtil::formSelect(
			$chosen_day,
			'monthly_byday_day',
			$days_select,
			false,
			true));
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
			true));
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
			true));
		
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
			true));
			
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
			true));
		
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
			true));
	
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
			true));
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
		$tpl->setVariable('TXT_NO_ENDING',$this->lng->txt('cal_no_ending'));
		$tpl->setVariable('TXT_UNTIL_CREATE',$this->lng->txt('cal_create'));
		$tpl->setVariable('TXT_APPOINTMENTS',$this->lng->txt('cal_appointments'));
		
		$tpl->setVariable('VAL_COUNT',$this->recurrence->getFrequenceUntilCount() ? 
			$this->recurrence->getFrequenceUntilCount() : 
			5);
		if($this->recurrence->getFrequenceUntilCount())
		{
			$tpl->setVariable('UNTIL_COUNT_CHECKED','checked="checked"');
		}
		else
		{
			$tpl->setVariable('UNTIL_NO_CHECKED','checked="checked"');
		}
	}	
}
?>