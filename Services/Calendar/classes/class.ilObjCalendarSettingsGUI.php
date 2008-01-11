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
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilObjCalendarSettingsGUI: ilPermissionGUI
* @ingroup ServicesCalendar
*/

include_once('./classes/class.ilObjectGUI.php');

class ilObjCalendarSettingsGUI extends ilObjectGUI
{

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		global $lng;
		
		$this->type = 'cals';
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng = $lng;
		$this->lng->loadLanguageModule('dateplaner');
		$this->lng->loadLanguageModule('jscalendar');
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		global $ilErr,$ilAccess;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

		if(!$ilAccess->checkAccess('read','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('no_permission'),$ilErr->WARNING);
		}

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				$this->tabs_gui->setTabActive('settings');
				$this->initCalendarSettings();
				if(!$cmd || $cmd == 'view')
				{
					$cmd = "settings";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Get tabs
	 *
	 * @access public
	 *
	 */
	public function getAdminTabs()
	{
		
		
		global $rbacsystem, $ilAccess;

		if ($ilAccess->checkAccess("read",'',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "settings"),
				array("settings", "view"));
		}

		if ($ilAccess->checkAccess('edit_permission','',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"),
				array(),'ilpermissiongui');
		}
	}

	/**
	* Edit settings.
	*/
	public function settings()
	{
		/*
		include_once('./Services/Calendar/classes/class.ilDateTime.php');
		
		$time = microtime(true);
		try
		{
			for($i = 0;$i < 2;$i++)
			{
				#if($i % 2 or 1)
				if($i %2)
				{
					$date = new ilDateTime('2007-02-29',ilDateTime::FORMAT_DATE,'Asia/Katmandu');
					$unix = $date->getUnixTime();
					echo 'Date: '.date('Y-m-d H:i:s',$unix).'<br />';
				}
				else
				{
					$date = new ilDateTime('2007-02-29 12:00:00',ilDateTime::FORMAT_DATETIME,'Europe/Berlin');
					$unix = $date->getUnixTime();
					echo 'DateTime: '.date('Y-m-d H:i:s',$unix).'<br />';
				}
			}
			echo microtime(true) -$time;
		}
		catch(ilDateTimeException $e)
		{
			var_dump("<pre>",$e->getMessage(),"</pre>");
		}
		*/

		$this->tabs_gui->setTabActive('settings');
		$this->initFormSettings();
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.settings.html','Services/Calendar');
		$this->tpl->setVariable('CAL_SETTINGS',$this->form->getHTML());
		return true;
	}
	
	/**
	 * save settings
	 *
	 * @access protected
	 */
	protected function save()
	{
		$this->settings->setEnabled((int) $_POST['enable']);
		$this->settings->setDefaultWeekStart((int) $_POST['default_week_start']);
		$this->settings->setDefaultTimeZone(ilUtil::stripSlashes($_POST['default_timezone']));
		$this->settings->save();
		
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->settings();
	}

	/**
	 * init calendar settings
	 *
	 * @access protected
	 */
	protected function initCalendarSettings()
	{
		include_once('Services/Calendar/classes/class.ilCalendarSettings.php');
		$this->settings = ilCalendarSettings::_getInstance();
	}
	
	/**
	 * Init settings property form
	 *
	 * @access protected
	 */
	protected function initFormSettings()
	{
		if(is_object($this->form))
		{
			return true;
		}
		include_once('Services/Calendar/classes/class.ilCalendarUtil.php');
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->setTitle($this->lng->txt('cal_global_settings'));
		$this->form->addCommandButton('save',$this->lng->txt('save'));
		$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
		
		$check = new ilCheckboxInputGUI($this->lng->txt('enable_calendar'),'enable');
		$check->setValue(1);
		$check->setChecked($this->settings->isEnabled() ? true : false);
		$this->form->addItem($check);
		
		$server_tz = new ilNonEditableValueGUI($this->lng->txt('cal_server_tz'));
		$server_tz->setValue(ilTimeZone::_getDefaultTimeZone());
		$this->form->addItem($server_tz);
		
		$select = new ilSelectInputGUI($this->lng->txt('cal_def_timezone'),'default_timezone');
		$select->setOptions(ilCalendarUtil::_getShortTimeZoneList());
		$select->setInfo($this->lng->txt('cal_def_timezone_info'));
		$select->setValue($this->settings->getDefaultTimeZone());
		$this->form->addItem($select);
		
		$radio = new ilRadioGroupInputGUI($this->lng->txt('cal_def_week_start'),'default_week_start');
		$radio->setValue($this->settings->getDefaultWeekStart());
	
		$option = new ilRadioOption($this->lng->txt('l_su'),0);
		$radio->addOption($option);
		$option = new ilRadioOption($this->lng->txt('l_mo'),1);
		$radio->addOption($option);
		
		
		$this->form->addItem($radio);
	}
}
?>