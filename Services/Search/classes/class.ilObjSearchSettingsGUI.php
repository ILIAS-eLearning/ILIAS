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

	/**
	* Show settings
	* @access	public
	*/
	function settingsObject()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->object->initSettingsObject();

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.seas_settings.html','Services/Search');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("COLSPAN",2);
		$this->tpl->setVariable("TXT_SEAS_TITLE",$this->lng->txt('seas_settings'));

		// Max hits
		$this->tpl->setVariable("TXT_MAX_HITS",$this->lng->txt('seas_max_hits'));
		$this->tpl->setVariable("TXT_MAX_HITS_INFO",$this->lng->txt('seas_max_hits_info'));
		for($i = 10; $i <= 100; $i += 10)
		{
			$max_hits[$i] = $i;
		}
		$this->tpl->setVariable('SELECT_MAX_HITS',ilUtil::formSelect($this->object->settings_obj->getMaxHits(),
																	 'max_hits',
																	 $max_hits,false,true));

		$this->tpl->setVariable("CMD_SUBMIT",'saveSettings');
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt('save'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));


		return true;
	}

	/**
	* Save settings
	* @access	public
	*/
	function saveSettingsObject()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->object->initSettingsObject();
		$this->object->settings_obj->setMaxHits((int) $_POST['max_hits']);
		$this->object->settings_obj->update();

		sendInfo($this->lng->txt('settings_saved'));
		$this->settingsObject();

		return true;
	}
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		// tabs are defined manually here. The autogeneration via objects.xml will be deprecated in future
		// for usage examples see ilObjGroupGUI or ilObjSystemFolderGUI
	}
} // END class.ilObjSearchSettings
?>
