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
include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');

/** 
* @defgroup ServicesPrivacySecurity Services/PrivacySecurity
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilObjPrivacySecurityGUI: ilPermissionGUI
* 
* @ingroup ServicesPrivacySecurity 
*/
class ilObjPrivacySecurityGUI extends ilObjectGUI
{
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = 'ps';
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
		
		$this->lng->loadLanguageModule('ps');
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
					$cmd = "showPrivacy";
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
		global $rbacsystem;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("show_privacy",
				$this->ctrl->getLinkTarget($this, "showPrivacy"),
				'showPrivacy');
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"),
				array(),'ilpermissiongui');
		}
	}
	
	/**
	 * Show Privacy settings
	 *
	 * @access public
	 */
	public function showPrivacy()
	{
		$privacy = ilPrivacySettings::_getInstance();
		
		$this->tabs_gui->setTabActive('show_privacy');
	 	$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.show_privacy.html','Services/PrivacySecurity');
	 	
	 	$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
	 	$this->tpl->setVariable('TXT_PRIVACY_PROTECTION',$this->lng->txt('ps_privacy_protection'));
	 	$this->tpl->setVariable('TXT_PROFILE_EXPORT',$this->lng->txt('ps_profile_export'));
	 	$this->tpl->setVariable('TXT_EXPORT_COURSE',$this->lng->txt('ps_export_course'));
	 	$this->tpl->setVariable('TXT_EXPORT_CONFIRM',$this->lng->txt('ps_export_confirm'));
	 	
	 	// Check export
	 	$this->tpl->setVariable('CHECK_EXPORT_COURSE',ilUtil::formCheckbox($privacy->enabledExport() ? 1 : 0,'export_course',1));
	 	$this->tpl->setVariable('CHECK_EXPORT_CONFIRM',ilUtil::formCheckbox($privacy->confirmationRequired() ? 1 : 0,'export_confirm',1));
	 	
	 	$this->tpl->setVariable('TXT_SAVE',$this->lng->txt('save'));
	}
	
	/**
	 * Save privacy settings
	 *
	 * @access public
	 * 
	 */
	public function save()
	{
		global $ilErr,$ilAccess;
		
		if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('no_permission'),$ilErr->WARNING);
		}
		
		$privacy = ilPrivacySettings::_getInstance();
		$privacy->enableExport((int) $_POST['export_course']);
		$privacy->setConfirmationRequired((int) $_POST['export_confirm']);
		$privacy->save();
		
		sendInfo($this->lng->txt('settings_saved'));
		$this->showPrivacy();
	}
}
?>