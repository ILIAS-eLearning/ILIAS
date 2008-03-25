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

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

include_once('./Services/Calendar/classes/class.ilCalendarUserSettings.php');

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
	public function __construct(ilDate $seed)
	{
		global $ilUser,$lng;
		
		$this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($ilUser->getId());
		$this->tpl = new ilTemplate('tpl.minical.html',true,true,'Services/Calendar');
		$this->lng = $lng;
		$this->seed = $seed;
	}
	
	/**
	 * set presentation mode
	 *
	 * @access public
	 * @param int presentation mode
	 * @return
	 */
	public function setPresentationMode($a_mode)
	{
		$this->mode = $a_mode;
	}
	
	/**
	 * get html
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getHTML()
	{
		$this->init();
		return $this->tpl->get();
	}

	/**
	 * init mini calendar
	 *
	 * @access protected
	 * @return
	 */
	protected function init()
	{
		include_once('Services/YUI/classes/class.ilYuiUtil.php');
		ilYuiUtil::initCalendar();
		
		// Navigator
		$this->tpl->setVariable('TXT_CHOOSE_MONTH',$this->lng->txt('yuical_choose_month'));
		$this->tpl->setVariable('TXT_CHOOSE_YEAR',$this->lng->txt('yuical_choose_year'));
		$this->tpl->setVariable('TXT_SUBMIT','OK');
		$this->tpl->setVariable('TXT_CANCEL',$this->lng->txt('cancel'));
		$this->tpl->setVariable('TXT_INVALID_YEAR',$this->lng->txt('yuical_invalid_year'));
		
		$this->tpl->setVariable('MINICALENDAR','&nbsp;');
		$this->tpl->setVariable('SEED_MY',$this->seed->get(IL_CAL_FKT_DATE,'m/Y'));
		$this->tpl->setVariable('SEED_MDY',$this->seed->get(IL_CAL_FKT_DATE,'m/d/Y'));
		$this->tpl->setVariable('MONTHS_LONG',$this->getMonthList());
		$this->tpl->setVariable('WEEKDAYS_SHORT',$this->getWeekdayList());
		$this->tpl->setVariable('WEEKSTART',(int) $this->user_settings->getWeekstart());
		return true;
	}
	
	/**
	 * get month list
	 *
	 * @access private
	 * @param
	 * @return
	 */
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
	
	/**
	 * get weekday list
	 *
	 * @access private
	 * @param
	 * @return
	 */
	private function getWeekdayList()
	{
		$this->lng->loadLanguageModule('jscalendar');
		foreach(array('su','mo','tu','we','th','fr','sa') as $day)
		{
			$days[] = $this->lng->txt('s_'.$day); 
		}
		return '"'.implode('","',$days).'"';
	}
}
?>