<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Class ilObjSearchSettingsGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @ilCtrl_Calls ilObjSearchSettingsGUI: ilPermissionGUI
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "classes/class.ilObjectGUI.php";

class ilObjSearchSettingsGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjSearchSettingsGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		$this->type = "seas";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);

		$this->lng->loadLanguageModule('search');
	}

	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if($cmd == "" || $cmd == "view")
				{
					$cmd = "settings";
				}
				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
	}
	
	function cancelObject()
	{
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);
		$this->ctrl->redirect($this, "settings");
	}

	/**
	* Show settings
	* @access	public
	*/
	function settingsObject()
	{
		global $ilAccess,$ilErr;
		
		if(!$ilAccess->checkAccess('read','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}
		$this->tabs_gui->setTabActive('settings');
		$this->initFormSettings();
		$this->tpl->setContent($this->form->getHTML());
		return true;
	}

	
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}

	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "settings"), array("settings","", "view"), "", "");
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
	
	/**
	 * Init settings form 
	 * @return void
	 */
	protected function initFormSettings()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		include_once './Services/Search/classes/class.ilSearchSettings.php';
		
		$settings = new ilSearchSettings();
		
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this,'updateSettings'));
		$this->form->addCommandButton('updateSettings',$this->lng->txt('save'));
		$this->form->setTitle($this->lng->txt('seas_settings'));
		
		// Max hits
		$hits = new ilSelectInputGUI($this->lng->txt('seas_max_hits'),'max_hits');
		$hits->setValue($settings->getMaxHits());
		$hits->setRequired(true);
		for($value = 10; $value <= 100; $value += 10)
		{
			$values[$value] = $value;
		}
		$hits->setOptions($values);
		$hits->setInfo($this->lng->txt('seas_max_hits_info'));
		$this->form->addItem($hits);
		
		// Search type
		$type = new ilRadioGroupInputGUI($this->lng->txt('search_type'),'search_type');
		
		if($settings->enabledLucene()) 
		{
			$type->setValue(ilSearchSettings::LUCENE_SEARCH);
		}
		elseif($settings->enabledIndex()) 
		{
			$type->setValue(ilSearchSettings::INDEX_SEARCH);
		}
		else 
		{
			$type->setValue(ilSearchSettings::LIKE_SEARCH);
		}
		
		$type->setRequired(true);
		$this->form->addItem($type);
		
		$direct = new ilRadioOption($this->lng->txt('search_direct'),ilSearchSettings::LIKE_SEARCH,$this->lng->txt('search_like_info'));
		$type->addOption($direct);
		
		$index = new ilRadioOption($this->lng->txt('search_index'),ilSearchSettings::INDEX_SEARCH,$this->lng->txt('search_full_info'));
		$type->addOption($index);
		
		$lucene = new ilRadioOption($this->lng->txt('search_lucene'),ilSearchSettings::LUCENE_SEARCH,$this->lng->txt('java_server_info'));
		$type->addOption($lucene);
	}
	
	
	/**
	 * Update Settings
	 * @return void
	 */
	protected function updateSettingsObject()
	{
		global $ilAccess,$ilErr;
		
		if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}
		
		include_once './Services/Search/classes/class.ilSearchSettings.php';
		$settings = new ilSearchSettings();
		$settings->setMaxHits((int) $_POST['max_hits']);
		
		switch((int) $_POST['search_type'])
		{
			case ilSearchSettings::LIKE_SEARCH:
				$settings->enableIndex(true);
				$settings->enabledLucene(false);
				break;
			case ilSearchSettings::INDEX_SEARCH:
				$settings->enableIndex(true);
				$settings->enableLucene(false);
				break;
			case ilSearchSettings::LUCENE_SEARCH:
				$settings->enableIndex(false);
				$settings->enableLucene(true);
				break;
		}
		$settings->update();
		
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->settingsObject();
	}
} // END class.ilObjSearchSettingsGUI
?>
