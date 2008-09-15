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
* Class ilObjChatServerGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* $Id:class.ilObjChatServerGUI.php 12853 2006-12-15 13:36:31 +0000 (Fr, 15 Dez 2006) smeyer $
*
* @ilCtrl_Calls ilObjChatServerGUI: ilPermissionGUI
*
* @extends ilObjectGUI
*/

require_once "classes/class.ilObjectGUI.php";

class ilObjChatServerGUI extends ilObjectGUI
{	
	private $form_gui = null;
	
	/**
	* Constructor
	* @access public
	*/
	function ilObjChatServerGUI($a_data,$a_id,$a_call_by_reference = true, $a_prepare_output = true)
	{

		#define("ILIAS_MODULE","chat");
		$this->type = "chac";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule("chat");
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
				if(!$cmd)
				{
					$cmd = 'view';
				}
				$cmd .= 'Object';
				$this->$cmd();

				break;
		}
		return true;
	}
	
	public function initForm($a_mode)
	{		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'update'));
		
		if($this->object->server_conf->isAlive() or $this->object->server_conf->getActiveStatus())
		{
			// activation/deactivation of chat module
			$sec_cd = new ilFormSectionHeaderGUI();
			$sec_cd->setTitle($this->lng->txt('chat_ilias'));
			$this->form_gui->addItem($sec_cd);
			
			$sel = new ilSelectInputGUI($this->lng->txt('chat_status'), 'chat_active');
			$options = array(
				1 => $this->lng->txt('chat_active'),
				0 => $this->lng->txt('chat_inactive')
			);
			$sel->setOptions($options);
			$sel->setValue(1);
			$this->form_gui->addItem($sel);			
		}
		else if(!$this->object->server_conf->isAlive() && $this->ctrl->getCmd() != 'update')
		{
			ilUtil::sendInfo($this->lng->txt('chat_cannot_connect_to_server'));
		}
		
		// chat server settings
		$sec_l = new ilFormSectionHeaderGUI();
		$sec_l->setTitle($this->lng->txt('chat_server_settings'));
		$this->form_gui->addItem($sec_l);
		
		// sever internal ip
		$inp = new ilTextInputGUI($this->lng->txt('chat_server_internal_ip'), 'chat_internal_ip');
		$inp->setRequired(true);
		$inp->setSize(40);
		$inp->setMaxLength(128);
		$this->form_gui->addItem($inp);
		
		// server address
		$inp = new ilTextInputGUI($this->lng->txt('chat_server_external_ip'), 'chat_external_ip');
		$inp->setRequired(true);
		$inp->setSize(40);
		$inp->setMaxLength(128);
		$this->form_gui->addItem($inp);
		
		// server port
		$inp = new ilTextInputGUI($this->lng->txt('chat_server_port'), 'chat_port');
		$inp->setRequired(true);
		$inp->setSize(5);
		$inp->setMaxLength(5);
		$this->form_gui->addItem($inp);
		
		// ssl
		$chb = new ilCheckboxInputGUI($this->lng->txt('chat_server_ssl_settings'), 'chat_ssl_status');
		$chb->setOptionTitle($this->lng->txt('chat_server_ssl_active'));
		$chb->setChecked(false);
		$inp = new ilTextInputGUI($this->lng->txt('chat_server_ssl_port'), 'chat_ssl_port');
		$inp->setSize(5);
		$inp->setMaxLength(5);
		$chb->addSubItem($inp);
		$this->form_gui->addItem($chb);	
		
		// moderator password
		$inp = new ilPasswordInputGUI($this->lng->txt('chat_moderator_password'), 'chat_moderator');
		$inp->setRequired(true);
		$inp->setSize(9);
		$inp->setMaxLength(16);
		$this->form_gui->addItem($inp);
		
		// logfile path
		$inp = new ilTextInputGUI($this->lng->txt('chat_server_logfile'), 'chat_logfile');
		$inp->setSize(40);
		$inp->setMaxLength(256);
		$this->form_gui->addItem($inp);

		// log level
		$sel = new ilSelectInputGUI($this->lng->txt('chat_server_loglevel'), 'chat_loglevel');
		$options = array(
			1 => $this->lng->txt('chat_level_fatal'),
			2 => $this->lng->txt('chat_level_error'),
			3 => $this->lng->txt('chat_level_info'),
			5 => $this->lng->txt('chat_level_debug'),
			6 => $this->lng->txt('chat_level_all')
		);
		$sel->setOptions($options);
		$sel->setValue(1);
		$this->form_gui->addItem($sel);
		
		// allowed hosts
		$inp = new ilTextInputGUI($this->lng->txt('chat_server_allowed'), 'chat_allowed');
		$inp->setInfo($this->lng->txt('chat_server_allowed_b'));
		$inp->setRequired(true);
		$inp->setSize(40);
		$inp->setMaxLength(256);
		$this->form_gui->addItem($inp);			
		
		// chat general settings
		$sec_cd = new ilFormSectionHeaderGUI();
		$sec_cd->setTitle($this->lng->txt('chat_general_settings'));
		$this->form_gui->addItem($sec_cd);
		
		// sound activation/deactivation for new chat invitations
		$rg = new ilRadioGroupInputGUI($this->lng->txt('chat_sounds'), 'chat_sound_status');
		$rg->setValue(0);
			$ro = new ilRadioOption($this->lng->txt('chat_sound_status_activate'), 1);
				$chb = new ilCheckboxInputGUI('', 'chat_new_invitation_sound_status');
				$chb->setOptionTitle($this->lng->txt('chat_new_invitation_sound_status'));
				$chb->setChecked(false);
			$ro->addSubItem($chb);				
		$rg->addOption($ro);
			$ro = new ilRadioOption($this->lng->txt('chat_sound_status_deactivate'), 0);
		$rg->addOption($ro);				
		$this->form_gui->addItem($rg);
		
		$this->form_gui->addCommandButton('update', $this->lng->txt('save'));
		$this->form_gui->addCommandButton('cancel', $this->lng->txt('cancel'));
	}
	
	public function getValues()
	{
		global $ilSetting;
		
		$data = array();		
		$data['chat_internal_ip'] = $this->object->server_conf->getInternalIp();
		$data['chat_external_ip'] = $this->object->server_conf->getExternalIp();
		$data['chat_port'] = $this->object->server_conf->getPort();
		$data['chat_ssl_status'] = $this->object->server_conf->getSSLStatus();
		$data['chat_ssl_port'] = $this->object->server_conf->getSSLPort();
		$data['chat_moderator'] = $this->object->server_conf->getModeratorPassword();
		$data['chat_logfile'] = $this->object->server_conf->getLogfile();
		$data['chat_loglevel'] = $this->object->server_conf->getLogLevel();		
		$data['chat_allowed'] = $this->object->server_conf->getAllowedHosts();
		$data['chat_active'] = $this->object->server_conf->getActiveStatus();
		$data['chat_new_invitation_sound_status'] = (bool)$ilSetting->get('chat_new_invitation_sound_status');
		$data['chat_sound_status'] = (int)$ilSetting->get('chat_sound_status');

		$this->form_gui->setValuesByArray($data);
	}

	public function editObject()
	{
		global $rbacsystem;
	
		$this->tabs_gui->setTabActive('edit_properties');

		if(!$rbacsystem->checkAccess('read', $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilias->error_obj->MESSAGE);
		}
		
		$this->initForm('edit');
		$this->getValues();
		$this->tpl->setContent($this->form_gui->getHTML());
	}

	public function updateObject()
	{
		global $rbacsystem, $ilSetting;

		if(!$rbacsystem->checkAccess('write', $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->MESSAGE);
		}
		
		$this->initForm('edit');
		
		if(!$this->form_gui->checkInput())
		{			
			$this->tabs_gui->setTabActive('edit_properties');					
			
			$this->form_gui->setValuesByPost();
			
			$this->tpl->setContent($this->form_gui->getHtml());
			return;
		}
		else	
		{
			$this->object->server_conf->setInternalIp(ilUtil::stripSlashes($_POST['chat_internal_ip']));
	        $this->object->server_conf->setExternalIp(ilUtil::stripSlashes($_POST['chat_external_ip']));
			$this->object->server_conf->setPort(ilUtil::stripSlashes($_POST['chat_port']));
			$this->object->server_conf->setSSLStatus((int)$_POST['chat_ssl_status'] ? 1 : 0);
			$this->object->server_conf->setSSLPort(ilUtil::stripSlashes($_POST['chat_ssl_port']));
			$this->object->server_conf->setModeratorPassword(ilUtil::stripSlashes($_POST['chat_moderator']));
			$this->object->server_conf->setLogfile(ilUtil::stripSlashes($_POST['chat_logfile']));
			$this->object->server_conf->setLogLevel(ilUtil::stripSlashes($_POST['chat_loglevel']));
			$this->object->server_conf->setAllowedHosts(ilUtil::stripSlashes($_POST['chat_allowed']));
			
			$this->object->server_conf->validate();			
			
			$ilSetting->set('chat_sound_status', (int)$_POST['chat_sound_status']);
			$ilSetting->set('chat_new_invitation_sound_status', (int)$_POST['chat_new_invitation_sound_status']);
			
			$this->object->server_conf->setActiveStatus((bool)$_POST['chat_active']);
			$this->object->server_conf->updateStatus();
			
			if(!$this->object->server_conf->update())
			{
				ilUtil::sendInfo($this->object->server_conf->getErrorMessage());
				return $this->editObject();
			}
		}
		
		ilUtil::sendInfo($this->lng->txt('chat_settings_saved'), true);
		return $this->editObject();
	}

	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getAdminTabs(&$tabs_gui)
	{
		global $rbacsystem,$rbacreview;

		$this->ctrl->setParameter($this,"ref_id",$this->object->getRefId());

		if($rbacsystem->checkAccess('read',$this->object->getRefId()))
		{
			$force_active = ($_GET["cmd"] == "" || $_GET["cmd"] == "view")
				? true
				: false;
			$tabs_gui->addTarget("chat_rooms",
				$this->ctrl->getLinkTarget($this, "view"), array("view", ""), get_class($this),
				"", $force_active);
		}
		if($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$force_active = ($_GET["cmd"] == "edit")
				? true
				: false;
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this),
				"", $force_active);
		}
		if($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
} // END class.ilObjChatServerGUI
?>