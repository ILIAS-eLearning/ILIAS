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

include_once('Services/Calendar/classes/class.ilCalendarUserSettings.php');
include_once('Services/Calendar/classes/class.ilCalendarSettings.php');

/** 
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilCalendarUserSettingsGUI:
* @ingroup ServicesCalendar 
*/
class ilCalendarUserSettingsGUI
{
	protected $tpl;
	protected $lng;
	protected $user;
	protected $settings;
	

	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct()
	{
	 	global $ilUser,$tpl,$lng,$ilCtrl;

		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->lng->loadLanguageModule('dateplaner');
		$this->lng->loadLanguageModule('jscalendar');
		
		$this->ctrl = $ilCtrl;
		
		$this->user = $ilUser;
		$this->settings = ilCalendarSettings::_getInstance();
		$this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());
		
	}
	
	
	/**
	 * Execute command
	 *
	 * @access public
	 * 
	 */
	public function executeCommand()
	{
		global $ilUser, $ilSetting;


		$next_class = $this->ctrl->getNextClass();

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
	 * show settings
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function show()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.user_settings.html','Services/Calendar');
		
		$this->initSettingsForm();
		$this->tpl->setVariable('CAL_SETTINGS',$this->form->getHTML());
	}
	
	/**
	 * cancel editing
	 *
	 * @access public
	 */
	public function cancel()
	{
		$this->ctrl->returnToParent($this);
	}
	
	/**
	 * save settings
	 *
	 * @access public
	 * @return
	 */
	public function save()
	{
		
		$this->user_settings->setTimeZone($_POST['timezone']);
		$this->user_settings->setExportTimeZoneType((int) $_POST['export_tz']);
		$this->user_settings->setWeekStart((int) $_POST['weekstart']);
		$this->user_settings->setDateFormat((int) $_POST['date_format']);
		$this->user_settings->setTimeFormat((int) $_POST['time_format']);
		$this->user_settings->setDayStart((int) $_POST['dst']);
		$this->user_settings->setDayEnd((int) $_POST['den']);

		if(((int) $_POST['den']) < (int) $_POST['dst'])
		{
			ilUtil::sendFailure($this->lng->txt('cal_dstart_dend_warn'));
			$this->show();
			return false;
		}

		$this->user_settings->save();
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->returnToParent($this);
	}
	
	/**
	 * show settings table
	 *
	 * @access public
	 * @return
	 */
	public function initSettingsForm()
	{
		if(is_object($this->form))
		{
			return true;
		}
		include_once('Services/Calendar/classes/class.ilCalendarUtil.php');
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this,'save'));
		$this->form->setTitle($this->lng->txt('cal_user_settings'));
		$this->form->addCommandButton('save',$this->lng->txt('save'));
		$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
		
		$select = new ilSelectInputGUI($this->lng->txt('cal_user_timezone'),'timezone');
		$select->setOptions(ilCalendarUtil::_getShortTimeZoneList());
		$select->setInfo($this->lng->txt('cal_timezone_info'));
		$select->setValue($this->user_settings->getTimeZone());
		$this->form->addItem($select);
		
		$export_type = new ilRadioGroupInputGUI($this->lng->txt('cal_export_timezone'),'export_tz');
		$export_type->setValue($this->user_settings->getExportTimeZoneType());
		
		$export_tz = new ilRadioOption($this->lng->txt('cal_export_timezone_tz'), ilCalendarUserSettings::CAL_EXPORT_TZ_TZ);
		$export_type->addOption($export_tz);
		$export_utc = new ilRadioOption($this->lng->txt('cal_export_timezone_utc'), ilCalendarUserSettings::CAL_EXPORT_TZ_UTC);
		$export_type->addOption($export_utc);
		$this->form->addItem($export_type);

		$year = date("Y");
		$select = new ilSelectInputGUI($this->lng->txt('cal_user_date_format'),'date_format');
		$select->setOptions(array(
			ilCalendarSettings::DATE_FORMAT_DMY => '31.10.'.$year,
			ilCalendarSettings::DATE_FORMAT_YMD => $year."-10-31",
			ilCalendarSettings::DATE_FORMAT_MDY => "10/31/".$year));
		$select->setInfo($this->lng->txt('cal_date_format_info'));
		$select->setValue($this->user_settings->getDateFormat());
		$this->form->addItem($select);
		
		$select = new ilSelectInputGUI($this->lng->txt('cal_user_time_format'),'time_format');
		$select->setOptions(array(
			ilCalendarSettings::TIME_FORMAT_24 => '13:00',
			ilCalendarSettings::TIME_FORMAT_12 => '1:00pm'));
		$select->setInfo($this->lng->txt('cal_time_format_info'));
		$select->setValue($this->user_settings->getTimeFormat());
		$this->form->addItem($select);
		
		// Week/Month View
		$week_month = new ilFormSectionHeaderGUI();
		$week_month->setTitle($this->lng->txt('cal_week_month_view'));
		$this->form->addItem($week_month);
		
		$radio = new ilRadioGroupInputGUI($this->lng->txt('cal_week_start'),'weekstart');
		$radio->setValue($this->user_settings->getWeekStart());
	
		$option = new ilRadioOption($this->lng->txt('l_su'),0);
		$radio->addOption($option);
		$option = new ilRadioOption($this->lng->txt('l_mo'),1);
		$radio->addOption($option);
		$this->form->addItem($radio);
		
		// Day/Week View
		$week_month = new ilFormSectionHeaderGUI();
		$week_month->setTitle($this->lng->txt('cal_day_week_view'));
		$this->form->addItem($week_month);
		
		$day_start = new ilSelectInputGUI($this->lng->txt('cal_day_start'),'dst');
		$day_start->setOptions(
			ilCalendarUtil::getHourSelection($this->user_settings->getTimeFormat())
		);
		$day_start->setValue($this->user_settings->getDayStart());
		$this->form->addItem($day_start);
		
		$day_end = new ilSelectInputGUI($this->lng->txt('cal_day_end'),'den');
		$day_end->setOptions(
			ilCalendarUtil::getHourSelection($this->user_settings->getTimeFormat())
		);
		$day_end->setValue($this->user_settings->getDayEnd());
		$this->form->addItem($day_end);
	}
}

?>