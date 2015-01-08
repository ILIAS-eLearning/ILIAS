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
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/
class ilMiniCalendarGUI
{
	const PRESENTATION_CALENDAR = 1;

	protected $seed;
	protected $mode = null;
	protected $user_settings = null;
	protected $tpl = null;
	protected $lng;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct(ilDate $seed, $a_par_obj)
	{
		global $ilUser,$lng;
		
		$this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($ilUser->getId());
		$this->tpl = new ilTemplate('tpl.minical.html',true,true,'Services/Calendar');
		$this->lng = $lng;
		$this->lng->loadLanguageModule('dateplaner');
		$this->seed = $seed;
		$this->setParentObject($a_par_obj);
	}
	
	/**
	* Set Parent GUI object.
	*
	* @param	object	$a_parentobject	Parent GUI object
	*/
	function setParentObject($a_parentobject)
	{
		$this->parentobject = $a_parentobject;
	}

	/**
	* Get Parent GUI object.
	*
	* @return	object	Parent GUI object
	*/
	function getParentObject()
	{
		return $this->parentobject;
	}

	/**
	* Get HTML for calendar
	*/
	function getHTML()
	{
		global $lng;
		
		$ftpl = new ilTemplate("tpl.calendar_block_frame.html", true, true,
			"Services/Calendar");
		
		$tpl = new ilTemplate("tpl.calendar_block.html", true, true,
			"Services/Calendar");
		$this->addMiniMonth($tpl);

		$ftpl->setVariable("BLOCK_TITLE", $lng->txt("calendar"));
		$ftpl->setVariable("CONTENT", $tpl->get());
		return $ftpl->get();
	}
	
	/**
	* Add mini version of monthly overview
	* (Maybe extracted to another class, if used in pd calendar tab
	*/
	function addMiniMonth($a_tpl)
	{
		global $ilCtrl, $lng,$ilUser;
		
		// weekdays
		include_once('Services/Calendar/classes/class.ilCalendarUtil.php');
		$a_tpl->setCurrentBlock('month_header_col');
		$a_tpl->setVariable('TXT_WEEKDAY', $lng->txt("cal_week_abbrev"));
		$a_tpl->parseCurrentBlock();
		for($i = (int) $this->user_settings->getWeekStart();$i < (7 + (int) $this->user_settings->getWeekStart());$i++)
		{
			$a_tpl->setCurrentBlock('month_header_col');
			$a_tpl->setVariable('TXT_WEEKDAY',ilCalendarUtil::_numericDayToString($i,false));
			$a_tpl->parseCurrentBlock();
		}
		
		include_once('Services/Calendar/classes/class.ilCalendarSchedule.php');
		$this->scheduler = new ilCalendarSchedule($this->seed,ilCalendarSchedule::TYPE_MONTH);
		$this->scheduler->calculate();
		
		$counter = 0;
		foreach(ilCalendarUtil::_buildMonthDayList($this->seed->get(IL_CAL_FKT_DATE,'m'),
			$this->seed->get(IL_CAL_FKT_DATE,'Y'),
			$this->user_settings->getWeekStart())->get() as $date)
		{
			$counter++;
			//$this->showEvents($date);
			
			
			$a_tpl->setCurrentBlock('month_col');
			
			if(count($this->scheduler->getByDay($date,$ilUser->getTimeZone())))
			{
				$a_tpl->setVariable('DAY_CLASS','calminiapp');
				#$a_tpl->setVariable('TD_CLASS','calminiapp');
			}

			include_once('./Services/Calendar/classes/class.ilCalendarUtil.php');			
			if(ilCalendarUtil::_isToday($date))
			{
				$a_tpl->setVariable('TD_CLASS','calminitoday');
			}
			#elseif(ilDateTime::_equals($date,$this->seed,IL_CAL_DAY))
			#{
			#	$a_tpl->setVariable('TD_CLASS','calmininow');
			#}
			elseif(ilDateTime::_equals($date,$this->seed,IL_CAL_MONTH))
			{
				$a_tpl->setVariable('TD_CLASS','calministd');
			}
			elseif(ilDateTime::_before($date,$this->seed,IL_CAL_MONTH))
			{
				$a_tpl->setVariable('TD_CLASS','calminiprev');
			}
			else
			{
				$a_tpl->setVariable('TD_CLASS','calmininext');
			}
			
			$day = $date->get(IL_CAL_FKT_DATE,'j');
			$month = $date->get(IL_CAL_FKT_DATE,'n');
			
			$month_day = $day;
			
			$ilCtrl->clearParametersByClass('ilcalendardaygui');
			$ilCtrl->setParameterByClass('ilcalendardaygui','seed',$date->get(IL_CAL_DATE));
			$a_tpl->setVariable('OPEN_DAY_VIEW', $ilCtrl->getLinkTargetByClass('ilcalendardaygui',''));
			$ilCtrl->clearParametersByClass('ilcalendardaygui');
			
			$a_tpl->setVariable('MONTH_DAY',$month_day);
			$a_tpl->parseCurrentBlock();
			
			if($counter and !($counter % 7))
			{
				$a_tpl->setCurrentBlock('month_row');
				$ilCtrl->clearParametersByClass('ilcalendarweekgui');
				$ilCtrl->setParameterByClass('ilcalendarweekgui','seed',$date->get(IL_CAL_DATE));
				$a_tpl->setVariable('OPEN_WEEK_VIEW', $ilCtrl->getLinkTargetByClass('ilcalendarweekgui',''));
				$ilCtrl->clearParametersByClass('ilcalendarweekgui');
				$a_tpl->setVariable('TD_CLASS','calminiweek');
				$a_tpl->setVariable('WEEK',
					$date->get(IL_CAL_FKT_DATE,'W'));
				$a_tpl->parseCurrentBlock();
			}
		}
		$a_tpl->setCurrentBlock('mini_month');
		$a_tpl->setVariable('TXT_MONTH_OVERVIEW', $lng->txt("cal_month_overview"));
		$a_tpl->setVariable('TXT_MONTH',
			$lng->txt('month_'.$this->seed->get(IL_CAL_FKT_DATE,'m').'_long').
				' '.$this->seed->get(IL_CAL_FKT_DATE,'Y'));
		$myseed = clone($this->seed);
		$ilCtrl->setParameterByClass('ilcalendarmonthgui','seed',$myseed->get(IL_CAL_DATE));
		$a_tpl->setVariable('OPEN_MONTH_VIEW',$ilCtrl->getLinkTargetByClass('ilcalendarmonthgui',''));
		
		$myseed->increment(ilDateTime::MONTH, -1);
		$ilCtrl->setParameter($this->getParentObject(),'seed',$myseed->get(IL_CAL_DATE));
		
		//$a_tpl->setVariable('BL_TYPE', $this->getBlockType());
		//$a_tpl->setVariable('BL_ID', $this->getBlockId());
		
		$a_tpl->setVariable('PREV_MONTH',
			$ilCtrl->getLinkTarget($this->getParentObject(), ""));
			
		$myseed->increment(ilDateTime::MONTH, 2);
		$ilCtrl->setParameter($this->getParentObject(),'seed',$myseed->get(IL_CAL_DATE));
		$a_tpl->setVariable('NEXT_MONTH',
			$ilCtrl->getLinkTarget($this->getParentObject(), ""));

		$ilCtrl->setParameter($this->getParentObject(), 'seed', "");
		$a_tpl->parseCurrentBlock();
	}

	
//
//
//		OLD IMPLEMENTATION
//
//
	
	/**
	 * set presentation mode
	 *
	 * @access public
	 * @param int presentation mode
	 * @return
	 */
/*
	public function setPresentationMode($a_mode)
	{
		$this->mode = $a_mode;
	}
*/
	
	/**
	 * get html
	 *
	 * @access public
	 * @param
	 * @return
	 */
/*
	public function getHTML()
	{
		$this->init();
		return $this->tpl->get();
	}
*/

	/**
	 * init mini calendar
	 *
	 * @access protected
	 * @return
	 */
/*
	protected function init()
	{
		include_once('Services/YUI/classes/class.ilYuiUtil.php');
		ilYuiUtil::initCalendar();
		
		// Navigator
		$this->tpl->setVariable('TXT_CHOOSE_MONTH',$this->lng->txt('yui_cal_choose_month'));
		$this->tpl->setVariable('TXT_CHOOSE_YEAR',$this->lng->txt('yui_cal_choose_year'));
		$this->tpl->setVariable('TXT_SUBMIT','OK');
		$this->tpl->setVariable('TXT_CANCEL',$this->lng->txt('cancel'));
		$this->tpl->setVariable('TXT_INVALID_YEAR',$this->lng->txt('yuical_invalid_year'));
		
		$this->tpl->setVariable('MINICALENDAR','&nbsp;');
		$this->tpl->setVariable('SEED_MY',$this->seed->get(IL_CAL_FKT_DATE,'m/Y','UTC'));
		$this->tpl->setVariable('SEED_MDY',$this->seed->get(IL_CAL_FKT_DATE,'m/d/Y','UTC'));
		$this->tpl->setVariable('MONTHS_LONG',$this->getMonthList());
		$this->tpl->setVariable('WEEKDAYS_SHORT',$this->getWeekdayList());
		$this->tpl->setVariable('WEEKSTART',(int) $this->user_settings->getWeekstart());
		return true;
	}
*/
	
	/**
	 * get month list
	 *
	 * @access private
	 * @param
	 * @return
	 */
/*
	private function getMonthList()
	{
		$this->lng->loadLanguageModule('jscalendar');
		for($i = 1;$i <= 12; $i++)
		{
			if($i < 10)
			{
				$i = '0'.$i;
			}
			$months[] = $this->lng->txt('l_'.$i);
		}
		return '"'.implode('","',$months).'"';
	}
*/
	
	/**
	 * get weekday list
	 *
	 * @access private
	 * @param
	 * @return
	 */
/*
	private function getWeekdayList()
	{
		$this->lng->loadLanguageModule('jscalendar');
		foreach(array('su','mo','tu','we','th','fr','sa') as $day)
		{
			$days[] = $this->lng->txt('s_'.$day); 
		}
		return '"'.implode('","',$days).'"';
	}
*/
}
?>
