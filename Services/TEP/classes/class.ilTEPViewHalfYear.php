<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/TEP/classes/class.ilTEPViewGridBased.php";

/**
 * TEP month view class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 */
class ilTEPViewHalfYear extends ilTEPViewGridBased
{	
	protected $month_data; // [array]
	
	const DAY_HEIGHT = 24;
	const COL_WIDTH = 195;
	
	// 
	// request
	// 
	
	public function normalizeSeed(ilDate $a_value)
	{
		$month = (int)$a_value->get(IL_CAL_FKT_DATE, 'm');
		$year = (int)$a_value->get(IL_CAL_FKT_DATE, 'Y');
		$month = ($month < 7)
			? 1
			: 7;		
		return new ilDate(mktime(12, 0, 0, $month, 1, $year), IL_CAL_UNIX);			
	}
	
	public function getPeriod()
	{
		$seed = $this->getSeed();
		$month = (int)$seed->get(IL_CAL_FKT_DATE, 'm')+5;
		$year = (int)$seed->get(IL_CAL_FKT_DATE, 'Y');
		$end = mktime(12, 0, 1, $month+1, 0, $year);
		$end = new ilDate($end, IL_CAL_UNIX);		
		return array($seed, $end);
	}	
	
	public function getCurrentNavigationOption()
	{
		$seed = $this->getSeed();
		$month = (int)$seed->get(IL_CAL_FKT_DATE, 'm');
		$month = ($month < 7)
			? "0101"
			: "0701";
		return $seed->get(IL_CAL_FKT_DATE, 'Y').$month;
	}
	
	protected function isMultiTutor()
	{
		return false;
	}
	
	protected function hasNoTutorColumn()
	{
		return false;
	}
	
	
	//
	// presentation
	// 
	
	protected function getNumberOfColumns()
	{
		return 6;
	}
	
	protected function prepareDataForPresentation()
	{		
		parent::prepareDataForPresentation();
		
		$period = $this->getPeriod();
		$curr_month = (int)$period[0]->get(IL_CAL_FKT_DATE, 'm');
		$curr_year = (int)$period[0]->get(IL_CAL_FKT_DATE, 'Y');
				
		$this->month_data = array();
		for($month = $curr_month; $month < $curr_month+6; $month++)
		{			
			foreach(array_keys($this->entries) as $user_id)
			{				
				$from = date("Y-m-d", mktime(0, 0, 1, $month, 1, $curr_year));
				$to = date("Y-m-d", mktime(0, 0, 1, $month+1, 0, $curr_year));
				
				$this->month_data[$month][$user_id] = $this->layoutEvents($this->entries[$user_id], $from, $to);
			}
		}
		
		$this->entries = null;
	}
		
	public function getNavigationOptions()
	{					
		require_once "Services/Calendar/classes/class.ilCalendarUtil.php";
		
		$min_year = 2014;

		$opts = array();
		for($loop = $min_year; $loop < $min_year+6; $loop++)
		{
		   $opts[$loop."0101"] = ilCalendarUtil::_numericMonthToString(1, true).
			   "-".ilCalendarUtil::_numericMonthToString(6, true)." ".$loop;

		   $opts[$loop."0701"] = ilCalendarUtil::_numericMonthToString(7, true).
			   "-".ilCalendarUtil::_numericMonthToString(12, true)." ".$loop;
		}

		return $opts;
	}
			
	/**
	 * Render column headers
	 * 
	 * @param ilTemplate $a_tpl
	 */
	protected function renderColumnHeaders(ilTemplate $a_tpl)
	{				
		require_once "Services/Calendar/classes/class.ilCalendarUtil.php";
		
		$user_id = array_shift($this->getTutors());
				
		$period = $this->getPeriod();
		$curr_month = (int)$period[0]->get(IL_CAL_FKT_DATE, 'm');
		$curr_year = (int)$period[0]->get(IL_CAL_FKT_DATE, 'Y');
								
		$a_tpl->setCurrentBlock("col_head_bl");
		for($month = $curr_month; $month < $curr_month+6; $month++)
		{
			$col_id = $user_id."_".date("Ym", mktime(0, 0, 1, $month, 1, $curr_year));

			$a_tpl->setVariable("COL_ID", $col_id);
			$a_tpl->setVariable("COL_HEAD", ilCalendarUtil::_numericMonthToString($month, true));
			$a_tpl->setVariable("COL_WIDTH", self::COL_WIDTH);
			$a_tpl->parseCurrentBlock();
		}
	}
	
	protected function renderContent(ilTemplate $a_tpl)
	{		
		$period = $this->getPeriod();
		$curr_month = (int)$period[0]->get(IL_CAL_FKT_DATE, 'm');
		$curr_year = (int)$period[0]->get(IL_CAL_FKT_DATE, 'Y');
		
		$user_id = array_shift($this->getTutors());
		
		require_once "Services/TEP/classes/class.ilTEPHolidays.php";
	
		for($day = 1; $day <= 31; $day++)
		{
			for($month = $curr_month; $month < $curr_month+6; $month++)
			{								
				$this->entries = $this->month_data[$month];
				
				$is_holiday = (ilTEPHolidays::isBankHoliday("de", $curr_year, $month, $day) || 
						ilTEPHolidays::isWeekend($curr_year, $month, $day));
					
				if(checkdate($month, $day, $curr_year))
				{															
					$this->renderDayForUser($a_tpl, $user_id, $curr_year, $month, $day, $is_holiday);			
				}
				else
				{
					$this->renderDayForUser($a_tpl, 0, $curr_year, $month, $day, $is_holiday, true);		
				}
			}			
			
			$a_tpl->setCurrentBlock("row_bl");
			$a_tpl->setVariable("DAY", $day);
			$a_tpl->parseCurrentBlock();
		}		
	}
}

