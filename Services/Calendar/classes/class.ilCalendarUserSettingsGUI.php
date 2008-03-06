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
* @author Stefan Meyer <smeyer@databay.de>
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
		$this->user_settings = new ilCalendarUserSettings($this->user);
		
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
	 * save settings
	 *
	 * @access public
	 * @return
	 */
	public function save()
	{
		$this->user_settings->setTimeZone($_POST['timezone']);
		$this->user_settings->save();	
		
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->show();
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
		
		$radio = new ilRadioGroupInputGUI($this->lng->txt('cal_week_start'),'week_start');
		$radio->setValue($this->settings->getDefaultWeekStart());
	
		$option = new ilRadioOption($this->lng->txt('l_su'),0);
		$radio->addOption($option);
		$option = new ilRadioOption($this->lng->txt('l_mo'),1);
		$radio->addOption($option);
		$this->form->addItem($radio);
		
	}
}

?>