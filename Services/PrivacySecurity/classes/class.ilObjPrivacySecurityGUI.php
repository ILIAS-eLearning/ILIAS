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
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		$this->prepareOutput();

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
		$this->tabs_gui->setTabActive('show_privacy');
	 	$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.show_privacy.html','Services/PrivacySecurity');
	}
}
?>