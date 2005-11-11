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
* Class ilLPListOfSettingsGUI
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilLPListOfSettingsGUI:
*
* @package ilias-tracking
*
*/

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
include_once './Services/Tracking/classes/class.ilLPObjSettings.php';

class ilLPListOfSettingsGUI extends ilLearningProgressBaseGUI
{
	function ilLPListOfSettingsGUI($a_mode,$a_ref_id)
	{
		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id);

		$this->obj_settings = new ilLPObjSettings($this->getObjId());
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		switch($this->ctrl->getNextClass())
		{
			default:
				$cmd = $this->__getDefaultCommand();
				$this->$cmd();

		}
		return true;
	}

	function show()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_obj_settings.html','Services/Tracking');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormaction($this));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_trac.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('tracking_settings'));
		$this->tpl->setVariable("TXT_TRACKING_SETTINGS", $this->lng->txt("tracking_settings"));

		$this->tpl->setVariable("TXT_ACTIVATE_TRACKING", $this->lng->txt("trac_activated"));
		$this->tpl->setVariable("ACTIVATED_IMG_OK",$activated = ilObjUserTracking::_enabledTracking()
								? ilUtil::getImagePath('icon_ok.gif') 
								: ilUtil::getImagePath('icon_not_ok.gif'));
		$this->tpl->setVariable("ACTIVATED_STATUS",$activated ? $this->lng->txt('yes') : $this->lng->txt('no'));

		$this->tpl->setVariable("TXT_USER_RELATED_DATA", $this->lng->txt("trac_anonymized"));
		$this->tpl->setVariable("ANONYMIZED_IMG_OK",$anonymized = ilObjUserTracking::_enabledUserRelatedData()
								? ilUtil::getImagePath('icon_ok.gif') 
								: ilUtil::getImagePath('icon_not_ok.gif'));
		$this->tpl->setVariable("ANONYMIZED_STATUS",$anonymized ? $this->lng->txt('yes') : $this->lng->txt('no'));

		$this->tpl->setVariable("TXT_VALID_REQUEST",$this->lng->txt('trac_valid_request'));
		$this->tpl->setVariable("INFO_VALID_REQUEST",$this->lng->txt('info_valid_request'));
		$this->tpl->setVariable("SECONDS",$this->lng->txt('seconds'));
		$this->tpl->setVariable("VAL_SECONDS",ilObjUserTracking::_getValidTimeSpan());

		// Mode selector
		$this->tpl->setVariable("TXT_MODE",$this->lng->txt('trac_modus'));

		$this->tpl->setVariable("MODE",ilUtil::formSelect($this->obj_settings->getMode(),
														  'modus',
														  $this->obj_settings->getValidModes(),
														  false,true));

		$this->tpl->setVariable("TXT_SAVE",$this->lng->txt('save'));

		
	}

	function saveSettings()
	{
		$this->obj_settings->setMode($_POST['modus']);
		$this->obj_settings->update();

		sendInfo($this->lng->txt('trac_settings_saved'));
		$this->show();
	}
}
?>