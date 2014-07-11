<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/TEP/classes/class.ilTEPViewGridBased.php";

/**
 * TEP month view class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 */
class ilTEPViewMonth extends ilTEPViewGridBased
{		
	const DAY_HEIGHT = 24; // You also need to set this in global css file for div.il_tep_content tr, div.il_tep_event_wrapper
						   // div.il_tep_content td.il_tep_content_days and div.il_tep_content td.il_tep_content_cell
	const COL_WIDTH = 100; // This is space for events plus 20px for action-item
	
	// 
	// request
	// 
	
	public function normalizeSeed(ilDate $a_value)
	{
		$day = (int)$a_value->get(IL_CAL_FKT_DATE, 'd');
		if($day > 1)
		{
			$a_value->increment(IL_CAL_DAY, -($day-1));
		}	
		return $a_value;
	}
	
	public function getPeriod()
	{
		$seed = $this->getSeed();
		$month = (int)$seed->get(IL_CAL_FKT_DATE, 'm');
		$year = (int)$seed->get(IL_CAL_FKT_DATE, 'Y');
		$end = mktime(12, 0, 0, $month+1, 0, $year);
		$end = new ilDate($end, IL_CAL_UNIX);		
		return array($seed, $end);
	}	
	
	public function getCurrentNavigationOption()
	{
		return $this->getSeed()->get(IL_CAL_FKT_DATE, 'Ymd');
	}
	
	protected function isMultiTutor()
	{
		return true;
	}
	
	protected function hasNoTutorColumn()
	{
		return true;
	}
	
	
	//
	// presentation
	// 
	
	/**
	 * Is "no tutors" column currently active?
	 * 
	 * @return bool
	 */
	protected function displayNoTutorsColumn()
	{
		return (is_array($this->filter) && $this->filter["notut"]);
	}
	
	protected function getNumberOfColumns()
	{
		$num_cols = sizeof($this->getTutors());
		if($this->displayNoTutorsColumn()) 
		{
			$num_cols++;
		}
		return $num_cols;
	}
	
	protected function prepareDataForPresentation()
	{		
		parent::prepareDataForPresentation();
		
		$period = $this->getPeriod();
		$from = $period[0]->get(IL_CAL_DATE);		
		$to = $period[1]->get(IL_CAL_DATE);		
		
		foreach($this->entries as $tutor_id => $entries)
		{			
			$this->entries[$tutor_id] = $this->layoutEvents($entries, $from, $to);
		}
	}
	
	public function getNavigationOptions()
	{					
		require_once "Services/Calendar/classes/class.ilCalendarUtil.php";
		
		$min_year = 2014;

		$opts = array();
		for($loop = $min_year; $loop < $min_year+6; $loop++)
		{
			for($i = 1; $i < 13; $i++)
			{
				$date = date("Ymd", mktime(0, 0, 1, $i, 1, $loop));
			    $opts[$date] = ilCalendarUtil::_numericMonthToString($i, true)." ".$loop;
			}
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
		global $lng;
		
		$seed_id = $this->getSeed()->get(IL_CAL_FKT_DATE, 'Ym');
				
		if($this->displayNoTutorsColumn())
		{
			$a_tpl->setCurrentBlock("col_head_bl");
			$a_tpl->setVariable("COL_ID", "0_".$seed_id);
			$a_tpl->setVariable("COL_HEAD", $lng->txt("tep_column_no_tutor"));
			$a_tpl->setVariable("COL_WIDTH", self::COL_WIDTH);
			$a_tpl->parseCurrentBlock();

			$a_tpl->setCurrentBlock("col_foot_bl");
			$a_tpl->setVariable("COL_FOOT", $lng->txt("tep_column_no_tutor"));
			$a_tpl->setVariable("COL_WIDTH", self::COL_WIDTH);
			$a_tpl->parseCurrentBlock();
		}
		
		
		foreach(ilTEP::getUserNames($this->getTutors(), true) as $tid => $tname)
		{
			$col_id = $tid."_".$seed_id;

			$a_tpl->setCurrentBlock("col_head_bl");
			$a_tpl->setVariable("COL_ID", $col_id);
			$a_tpl->setVariable("COL_HEAD", $tname);
			$a_tpl->setVariable("COL_WIDTH", self::COL_WIDTH);
			$a_tpl->parseCurrentBlock();
			
			$a_tpl->setCurrentBlock("col_foot_bl");
			$a_tpl->setVariable("COL_FOOT", $tname);
			$a_tpl->setVariable("COL_WIDTH", self::COL_WIDTH);
			$a_tpl->parseCurrentBlock();
		}		
	}
	
	protected function renderContent(ilTemplate $a_tpl)
	{	
		$period = $this->getPeriod();
		$year = (int)$period[0]->get(IL_CAL_FKT_DATE, "Y");
		$month = (int)$period[0]->get(IL_CAL_FKT_DATE, "m");	
		$last_day = (int)$period[1]->get(IL_CAL_FKT_DATE, "d");
		
		require_once "Services/TEP/classes/class.ilTEPHolidays.php";
		
		for($day = 1; $day <= $last_day; $day++)
		{									
			$is_holiday = (ilTEPHolidays::isBankHoliday("de", $year, $month, $day) || 
							ilTEPHolidays::isWeekend($year, $month, $day));
		
			if($this->displayNoTutorsColumn())							
			{
				$this->renderDayForUser($a_tpl, 0, $year, $month, $day, $is_holiday);				
			}

			foreach($this->getTutors() as $tid)
			{							
				$this->renderDayForUser($a_tpl, $tid, $year, $month, $day, $is_holiday);					
			}					

			$a_tpl->setCurrentBlock("row_bl");
			$a_tpl->setVariable("DAY", $day);
			$a_tpl->parseCurrentBlock();
		}
	}
}

