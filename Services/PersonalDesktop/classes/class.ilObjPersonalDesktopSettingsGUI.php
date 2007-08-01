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
include_once("./classes/class.ilObjectGUI.php");


/**
* News Settings.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjPersonalDesktopSettingsGUI: ilPermissionGUI
*
* @ingroup ServicesPersonalDesktop
*/
class ilObjPersonalDesktopSettingsGUI extends ilObjectGUI
{
    private static $ERROR_MESSAGE;
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		global $lng;
		
		$this->type = 'pdts';
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$lng->loadLanguageModule("pd");
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		global $rbacsystem,$ilErr,$ilAccess;

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
				if(!$cmd || $cmd == 'view')
				{
					$cmd = "editSettings";
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

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("pd_settings",
				$this->ctrl->getLinkTarget($this, "editSettings"),
				array("editSettings", "view"));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"),
				array(),'ilpermissiongui');
		}
	}

	/**
	* Edit news settings.
	*/
	public function editSettings()
	{
		global $ilCtrl, $lng, $ilSetting;
		
		$pd_set = new ilSetting("pd");
		
		$enable_calendar = $ilSetting->get("enable_calendar");		
		$enable_block_moving = $pd_set->get("enable_block_moving");
		$enable_active_users = $ilSetting->get("block_activated_pdusers");		
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("pd_settings"));
		
		// Enable calendar
		$cb_prop = new ilCheckboxInputGUI($lng->txt("enable_calendar"), "enable_calendar");
		$cb_prop->setValue("1");
		//$cb_prop->setInfo($lng->txt("pd_enable_block_moving_info"));
		$cb_prop->setChecked($enable_calendar);
		$form->addItem($cb_prop);

		// Enable bookmarks
		$cb_prop = new ilCheckboxInputGUI($lng->txt("pd_enable_bookmarks"), "enable_bookmarks");
		$cb_prop->setValue("1");
		$cb_prop->setChecked(($ilSetting->get("disable_bookmarks") ? "0" : "1"));
		$form->addItem($cb_prop);
		
		// Enable notes
		$cb_prop = new ilCheckboxInputGUI($lng->txt("pd_enable_notes"), "enable_notes");
		$cb_prop->setValue("1");
		$cb_prop->setChecked(($ilSetting->get("disable_notes") ? "0" : "1"));
		$form->addItem($cb_prop);
		
		// Enable block moving
		$cb_prop = new ilCheckboxInputGUI($lng->txt("pd_enable_block_moving"),
			"enable_block_moving");
		$cb_prop->setValue("1");
		$cb_prop->setInfo($lng->txt("pd_enable_block_moving_info"));
		$cb_prop->setChecked($enable_block_moving);
		$form->addItem($cb_prop);		
		
		// Enable active users block
		$cb_prop = new ilCheckboxInputGUI($lng->txt("pd_enable_active_users"),
			"block_activated_pdusers");
		$cb_prop->setValue("1");
		$cb_prop->setChecked($enable_active_users);
		
			// maximum inactivity time
			$ti_prop = new ilTextInputGUI($lng->txt("pd_time_before_removal"),
				"time_removal");
			$ti_prop->setValue($pd_set->get("user_activity_time"));
			$ti_prop->setInfo($lng->txt("pd_time_before_removal_info"));
			$ti_prop->setMaxLength(3);
			$ti_prop->setSize(3);
			$cb_prop->addSubItem($ti_prop);
			
			// osi host
			// see http://www.onlinestatus.org
			$ti_prop = new ilTextInputGUI($lng->txt("pd_osi_host"),
				"osi_host");
			$ti_prop->setValue($pd_set->get("osi_host"));
			$ti_prop->setInfo($lng->txt("pd_osi_host_info").
				' <a href="http://www.onlinestatus.org" target="_blank">http://www.onlinestatus.org</a>');
			$cb_prop->addSubItem($ti_prop);
			
		$form->addItem($cb_prop);		
		
		// command buttons
		$form->addCommandButton("saveSettings", $lng->txt("save"));
		$form->addCommandButton("view", $lng->txt("cancel"));

		$this->tpl->setContent($form->getHTML());
	}

	/**
	* Save news and external webfeeds settings
	*/
	public function saveSettings()
	{
		global $ilCtrl, $ilSetting;
		
		$pd_set = new ilSetting("pd");		
		$ilSetting->set("enable_calendar", $_POST["enable_calendar"]);
		$ilSetting->set("disable_bookmarks", (int) ($_POST["enable_bookmarks"] ? 0 : 1));
		$ilSetting->set("disable_notes", (int) ($_POST["enable_notes"] ? 0 : 1));
		
		$ilSetting->set("block_activated_pdusers", $_POST["block_activated_pdusers"]);
		$pd_set->set("enable_block_moving", $_POST["enable_block_moving"]);
		$pd_set->set("user_activity_time", (int) $_POST["time_removal"]);
		$pd_set->set("osi_host", $_POST["osi_host"]);	
		
		ilUtil::sendInfo($this->lng->txt("settings_saved"),true);
		
		$ilCtrl->redirect($this, "view");
	}
}
?>