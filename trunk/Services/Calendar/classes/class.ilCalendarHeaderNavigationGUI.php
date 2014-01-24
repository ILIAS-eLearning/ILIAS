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

include_once('Services/Calendar/classes/class.ilCalendarUtil.php');

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesCalendar
*  
*/

class ilCalendarHeaderNavigationGUI
{
	protected $cmdClass = null;
	protected $cmd = null;
	protected $seed = null;
	protected $increment = '';
	
	protected $html;
	protected $lng;
	protected $tpl;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param object ilDate seed for navigation
	 * @parame string type MONTH WEEK DAY 
	 * 
	 */
	public function __construct($cmdClass,ilDate $seed,$a_increment,$cmd = '')
	{
		global $lng,$ilCtrl;
		
		$this->lng = $lng;
		
		$this->ctrl = $ilCtrl;
		$this->cmdClass = $cmdClass;
		$this->seed = clone $seed;
		$this->increment = $a_increment;
		$this->cmd = $cmd;
	}
	
	/**
	 * getHTML
	 *
	 * @access public
	 * 
	 */
	public function getHTML()
	{
		global $lng;
		
	 	$this->tpl = new ilTemplate('tpl.navigation_header.html',true,true,'Services/Calendar');
		
		$this->incrementDate(-2);
		$num = 0;
		do
		{
			switch($this->increment)
			{
				case ilDateTime::DAY:
					$this->tpl->setVariable('NAV_NAME_'.++$num,ilCalendarUtil::_numericDayToString($this->seed->get(IL_CAL_FKT_DATE,'w')));
					break;
				
				case ilDateTime::WEEK:
					$this->tpl->setVariable('NAV_NAME_'.++$num,$this->lng->txt('week').' '.$this->seed->get(IL_CAL_FKT_DATE,'W'));
					break;
					
				case ilDateTime::MONTH:
					if($num == 2)
					{
						$this->tpl->setVariable('NAV_NAME_'.++$num,
							$this->lng->txt('month_'.$this->seed->get(IL_CAL_FKT_DATE,'m').'_long').
							' '.$this->seed->get(IL_CAL_FKT_DATE,'Y'));
					}
					else
					{
						$this->tpl->setVariable('NAV_NAME_'.++$num,$this->lng->txt('month_'.$this->seed->get(IL_CAL_FKT_DATE,'m').'_long'));
					}
					break;
			}
			$this->ctrl->setParameterByClass(get_class($this->cmdClass),'seed',$this->seed->get(IL_CAL_DATE));
			$this->tpl->setVariable('NAV_LINK_'.$num,$this->ctrl->getLinkTarget($this->cmdClass,$this->cmd));
			// $this->ctrl->clearParametersByClass(get_class($this->cmdClass));
			$this->ctrl->setParameterByClass(get_class($this->cmdClass),'seed','');

			$this->incrementDate(1);

		} while($num < 6);

		// header
		switch ($this->increment)
		{
			case ilDateTime::DAY:
				$this->tpl->setVariable('TXT_SELECT_TITLE', $lng->txt("cal_day_selection"));
				$this->tpl->setVariable('TXT_VIEW_HEAD', $lng->txt("cal_day_overview"));
				break;
				
			case ilDateTime::WEEK:
				$this->tpl->setVariable('TXT_SELECT_TITLE', $lng->txt("cal_week_selection"));
				$this->tpl->setVariable('TXT_VIEW_HEAD', $lng->txt("cal_week_overview"));
				break;
				
			case ilDateTime::MONTH:
				$this->tpl->setVariable('TXT_SELECT_TITLE', $lng->txt("cal_month_selection"));
				$this->tpl->setVariable('TXT_VIEW_HEAD', $lng->txt("cal_month_overview"));
				break;
		}
	 	$this->tpl->setVariable('TXT_SELECTED', $lng->txt("stat_selected"));
		
	 	return $this->tpl->get();
	}

	protected function incrementDate($a_count)
	{
		switch($this->increment)
		{
			case ilDateTime::MONTH:

				$day = $this->seed->get(IL_CAL_FKT_DATE,'j');
				if($day > 28)
				{
					$this->seed->increment(IL_CAL_DAY, (31 - $day) * -1);
				}
			default:
				$this->seed->increment($this->increment,$a_count);
				break;
		}
	}
}

?>