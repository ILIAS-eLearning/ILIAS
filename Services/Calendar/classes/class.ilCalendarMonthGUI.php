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

include_once('Services/Calendar/classes/class.ilDate.php');
include_once('Services/Calendar/classes/class.ilCalendarHeaderNavigationGUI.php');


/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_IsCalledBy ilCalendarMonthGUI:
* @ingroup ServicesCalendar 
*/


class ilCalendarMonthGUI
{
	protected $seed = null;

	protected $lng;
	protected $ctrl;
	protected $tabs_gui;
	protected $tpl;
	
	protected $timezone = 'UTC';

	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct(ilDate $seed_date)
	{
		global $ilCtrl, $lng, $ilUser,$ilTabs,$tpl;
		
		$this->seed = $seed_date;

		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;
		$this->tabs_gui->setSubTabActive('app_month');
		
		$this->timezone = $ilUser->getUserTimeZone();
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 * 
	 */
	public function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		switch($next_class)
		{
			
			default:
				$cmd = $this->ctrl->getCmd("show");
				$this->$cmd();
				break;
		}
		return true;
	}
	
	/**
	 * fill data section
	 *
	 * @access public
	 * 
	 */
	public function show()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.month_view.html','Services/Calendar');
		
		$navigation = new ilCalendarHeaderNavigationGUI($this,$this->seed,ilDateTime::MONTH);
		$this->tpl->setVariable('NAVIGATION',$navigation->getHTML());
		
		for($i = 1;$i < 8;$i++)
		{
			$this->tpl->setCurrentBlock('month_header_col');
			$this->tpl->setVariable('TXT_WEEKDAY',ilCalendarUtil::_numericDayToString($i,true));
			$this->tpl->parseCurrentBlock();
		}
		
		include_once('Services/Calendar/classes/class.ilCalendarSchedule.php');
		$this->scheduler = new ilCalendarSchedule(new ilDate('2008-02-22',IL_CAL_DATE),
			new ilDate('2008-04-06',IL_CAL_DATE));
		$this->scheduler->calculate();
		
		$counter = 0;
		foreach(ilCalendarUtil::_buildMonthDayList($this->seed->get(IL_CAL_FKT_DATE,'m'),$this->seed->get(IL_CAL_FKT_DATE,'Y'))->get() as $date)
		{
			$counter++;
			
			$this->showEvents($date);
			
			$this->tpl->setCurrentBlock('month_col');
			
			if(ilDateTime::_equals($date,$this->seed,IL_CAL_DAY))
			{
				$this->tpl->setVariable('TD_CLASS','calnow');
			}
			elseif(ilDateTime::_equals($date,$this->seed,IL_CAL_MONTH))
			{
				$this->tpl->setVariable('TD_CLASS','calstd');
			}
			elseif(ilDateTime::_before($date,$this->seed,IL_CAL_MONTH))
			{
				$this->tpl->setVariable('TD_CLASS','calprev');
			}
			else
			{
				$this->tpl->setVariable('TD_CLASS','calnext');
			}
			
			$day = $date->get(IL_CAL_FKT_DATE,'j');
			$month = $date->get(IL_CAL_FKT_DATE,'n');
			
			if($day == 1)
			{
				$month_day = '1 '.ilCalendarUtil::_numericMonthToString($month,false);
			}
			else
			{
				$month_day = $day;
			}
			
			$this->tpl->setVariable('MONTH_DAY',$month_day);
			$this->tpl->setVariable('NEW_SRC',ilUtil::getImagePath('new.gif','calendar'));
			$this->tpl->setVariable('OPEN_SRC',ilUtil::getImagePath('open.gif','calendar'));
			$this->tpl->parseCurrentBlock();
			
			
			if($counter and !($counter % 7))
			{
				$this->tpl->setCurrentBlock('month_row');
				$this->tpl->parseCurrentBlock();
			}
		}
	}
	
	/**
	 * 
	 * Show events
	 *
	 * @access protected
	 */
	protected function showEvents(ilDate $date)
	{
		foreach($this->scheduler->getByDay($date,$this->timezone) as $item)
		{
			$this->tpl->setCurrentBlock('il_event');
			
			if($item['event']->isFullDay())
			{
				$title = $item['event']->getTitle();
			}
			else
			{
				$title = $item['event']->getStart()->get(IL_CAL_FKT_DATE,'H:i',$this->timezone);
				$title .= (' '.$item['event']->getTitle());
			}
			$this->tpl->setVariable('EVENT_TITLE',$title);
			$this->tpl->parseCurrentBlock();
		}
	}
	
}


?>