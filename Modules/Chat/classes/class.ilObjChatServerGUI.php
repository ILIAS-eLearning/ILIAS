<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

require_once 'classes/class.ilObjectGUI.php';

class ilObjChatServerGUI extends ilObjectGUI
{	
	private $form_gui = null;
	
	/**
	* Constructor
	* @access public
	*/
	public function __construct($a_data,$a_id,$a_call_by_reference = true, $a_prepare_output = true)
	{

		#define('ILIAS_MODULE','chat');
		$this->type = 'chac';
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule('chat');
	}

	public function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once('./classes/class.ilPermissionGUI.php');
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
		
		// sound activation/deactivation for new chat invitations and messages
		$rg = new ilRadioGroupInputGUI($this->lng->txt('chat_sounds'), 'chat_sound_status');
		$rg->setValue(0);
			$ro = new ilRadioOption($this->lng->txt('chat_sound_status_activate'), 1);
				$chb = new ilCheckboxInputGUI('', 'chat_new_invitation_sound_status');
				$chb->setOptionTitle($this->lng->txt('chat_new_invitation_sound_status'));
				$chb->setChecked(false);
			$ro->addSubItem($chb);
				$chb = new ilCheckBoxInputGUI('','chat_new_message_sound_status');
				$chb->setOptionTitle($this->lng->txt('chat_new_message_sound_status'));
				$chb->setChecked(false);
			$ro->addSubItem($chb);				
		$rg->addOption($ro);
			$ro = new ilRadioOption($this->lng->txt('chat_sound_status_deactivate'), 0);
		$rg->addOption($ro);				
		$this->form_gui->addItem($rg);
		
		// chat message notification in ilias
		$rg = new ilRadioGroupInputGUI($this->lng->txt('chat_message_notify'), 'chat_message_notify_status');
		$rg->setValue(0);
			$ro = new ilRadioOption($this->lng->txt('chat_message_notify_activate'), 1);
		$rg->addOption($ro);
			$ro = new ilRadioOption($this->lng->txt('chat_message_notify_deactivate'), 0);
		$rg->addOption($ro);				
		$this->form_gui->addItem($rg);
		
		// smilies
		$rg = new ilRadioGroupInputGUI($this->lng->txt('chat_smilies_status'), 'chat_smilies_status');
		$rg->setValue(0);
			$ro = new ilRadioOption($this->lng->txt('chat_smilies_activate'), 1);
		$rg->addOption($ro);
			$ro = new ilRadioOption($this->lng->txt('chat_smilies_deactivate'), 0);
		$rg->addOption($ro);

		$this->form_gui->addItem($rg);
		
		$this->form_gui->addCommandButton('update', $this->lng->txt('save'));
		$this->form_gui->addCommandButton('cancel', $this->lng->txt('cancel'));
	}
	
	/*
	public function initialDBObject() {
		//include_once 'Modules/Chat/classes/class.ilChatSmilies.php';
		//ilChatSmilies::initial();
	}
	*/
	
	public function uploadSmileyObject() {
		global $rbacsystem, $ilSetting, $ilCtrl;

		if(!$rbacsystem->checkAccess('write', $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->MESSAGE);
		}
		
		$this->initSmiliesForm();
		
		include_once "Modules/Chat/classes/class.ilChatSmilies.php";
		
		$keywords = ilChatSmilies::_prepareKeywords(ilUtil::stripSlashes($_REQUEST["chat_smiley_keywords"]));
		$keywordscheck = count($keywords) > 0;
		
		if(!$this->form_gui->checkInput())
		{			
			$this->tabs_gui->setTabActive('edit_smilies');					
			$this->form_gui->setValuesByPost();
			$this->tpl->setContent($this->form_gui->getHtml());
			return;
		}
		$pathinfo = pathinfo($_FILES["chat_image_path"]["name"]);
		$target_file = md5(time() + $pathinfo['basename']).".".$pathinfo['extension'];
		
		move_uploaded_file($_FILES["chat_image_path"]["tmp_name"], ilChatSmilies::_getSmiliesBasePath() . $target_file);
		ilChatSmilies::_storeSmiley(join("\n", $keywords), $target_file);
		//$this->editSmiliesObject();
		$ilCtrl->redirect($this, "editSmilies");
	}
	
	public function deleteMultipleObject() {
		global $rbacsystem, $ilSetting, $lng, $ilCtrl;
		$this->tabs_gui->setTabActive('edit_smilies');
		if(!$rbacsystem->checkAccess('write', $this->ref_id)) {
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->MESSAGE);
		}
		
		$items = $_REQUEST["smiley_id"];
		include_once 'Modules/Chat/classes/class.ilChatSmilies.php';
		
		$smilies = ilChatSmilies::_getSmiliesById($items); 
		$tpl = new ilTemplate("tpl.chat_smilies_delete_multiple_confirm.html", true, true, "Modules/Chat");
		$tpl->setVariable("SMILIES_DELETE_INTRO", $lng->txt('chat_smilies_delete_multiple_intro'));
		$tpl->setVariable("TXT_SUBMIT", $lng->txt('submit'));
		$tpl->setVariable("TXT_CANCEL", $lng->txt('cancel'));
		$tpl->setVariable("SMILIES_IDS", join(",", $items));
		$tpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this,'update'));
		
		$i = 0;
		
		foreach($smilies as $s) {
			$tpl->setCurrentBlock("smilies_list");
			$tpl->setVariable("SMILEY_PATH", $s["smiley_fullpath"]);
			$tpl->setVariable("SMILEY_KEYWORDS", $s["smiley_keywords"]);
			$tpl->setVariable("ROW_CNT", ($i++ % 2) + 1);
			$tpl->parseCurrentBlock();
		}
		$this->tpl->setContent($tpl->get());
		
	}
	
	public function confirmedDeleteMultipleObject() {
		global $rbacsystem, $ilSetting, $lng, $ilCtrl;

		if(!$rbacsystem->checkAccess('write', $this->ref_id)) {
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->MESSAGE);
		}
		
		$ids = $_REQUEST["sel_ids"];
		$parts = explode(",", $ids);
		
		include_once "Modules/Chat/classes/class.ilChatSmilies.php";
		ilChatSmilies::_deleteMultipleSmilies($parts);
		//$this->editSmiliesObject();
		$ilCtrl->redirect($this, "editSmilies");
	}
	
	public function initSmiliesForm()
	{		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form_gui = new ilPropertyFormGUI();
		$table_nav = $_REQUEST["_table_nav"] ? "&_table_nav=".$_REQUEST["_table_nav"] : "";
		$this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'update'). $table_nav);
				
		// chat server settings
		$sec_l = new ilFormSectionHeaderGUI();
		$sec_l->setTitle($this->lng->txt('chat_add_smiley'));
		$this->form_gui->addItem($sec_l);

		$inp = new ilImageFileInputGUI($this->lng->txt('chat_image_path'), 'chat_image_path');
		$inp->setRequired(true);
		$this->form_gui->addItem($inp);
		
		$inp = new ilTextAreaInputGUI($this->lng->txt('chat_smiley_keywords'), 'chat_smiley_keywords');
		$inp->setRequired(true);
		$inp->setUseRte(false);
		$inp->setInfo($this->lng->txt('chat_smiley_keywords_one_per_line_note'));
		$this->form_gui->addItem($inp);
		
		//$this->form_gui->addCommandButton('initialDB', $this->lng->txt('setup'));
		$this->form_gui->addCommandButton('uploadSmiley', $this->lng->txt('chat_upload_smiley'));
		//$this->form_gui->addCommandButton('cancel', $this->lng->txt('cancel'));
	}

	public function initSmiliesEditForm()
	{		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form_gui = new ilPropertyFormGUI();
		
		$table_nav = $_REQUEST["_table_nav"] ? "&_table_nav=".$_REQUEST["_table_nav"] : "";

		$this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'update') . $table_nav);
				
		// chat server settings
		$sec_l = new ilFormSectionHeaderGUI();
		$sec_l->setTitle($this->lng->txt('chat_edit_smiley'));
		$this->form_gui->addItem($sec_l);

		include_once "Modules/Chat/classes/class.ilChatSmiliesCurrentSmileyFormElement.php";
		
		$inp = new ilChatSmiliesCurrentSmileyFormElement($this->lng->txt('chat_current_smiley_image_path'), 'chat_current_smiley_image_path');
		$this->form_gui->addItem($inp);
		
		$inp = new ilImageFileInputGUI($this->lng->txt('chat_image_path'), 'chat_image_path');
		$inp->setRequired(false);
		$inp->setInfo($this->lng->txt('chat_smiley_image_only_if_changed'));
		$this->form_gui->addItem($inp);
		
		$inp = new ilTextAreaInputGUI($this->lng->txt('chat_smiley_keywords'), 'chat_smiley_keywords');
		$inp->setUseRte(false);
		$inp->setRequired(true);
		$inp->setInfo($this->lng->txt('chat_smiley_keywords_one_per_line_note'));
		$this->form_gui->addItem($inp);
		
		$inp = new ilHiddenInputGUI('chat_smiley_id');
		$this->form_gui->addItem($inp);
		
		$this->form_gui->addCommandButton('editSmilies', $this->lng->txt('cancel'));
		$this->form_gui->addCommandButton('updateSmilies', $this->lng->txt('submit'));
		//$this->form_gui->addCommandButton('cancel', $this->lng->txt('cancel'));
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
		$data['chat_new_message_sound_status'] = (bool)$ilSetting->get('chat_new_message_sound_status');
		$data['chat_sound_status'] = (int)$ilSetting->get('chat_sound_status');
		$data['chat_message_notify_status'] = (int) $ilSetting->get('chat_message_notify_status');
		$data['chat_smilies_status'] = (int)$ilSetting->get('chat_smilies_status'); 

		$this->form_gui->setValuesByArray($data);
	}

	public function editObject()
	{
		global $rbacsystem;
	
		$this->tabs_gui->setTabActive('edit_properties');

		if(!$rbacsystem->checkAccess('read', $this->ref_id)){
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilias->error_obj->MESSAGE);
		}
		
		$this->initForm('edit');
		$this->getValues();
		$this->tpl->setContent($this->form_gui->getHTML());
	}

	public function showEditSmileyEntryFormObject() {
		global $rbacsystem;
		$this->tabs_gui->setTabActive('edit_smilies');

		if(!$rbacsystem->checkAccess('read', $this->ref_id)) {
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilias->error_obj->MESSAGE);
		}
		
		$this->initSmiliesEditForm();
		include_once "Modules/Chat/classes/class.ilChatSmilies.php";
		$smiley = ilChatSmilies::_getSmiley($_REQUEST["smiley_id"]);
		$form_data = array(
			"chat_smiley_id" => $smiley["smiley_id"],
			"chat_smiley_keywords" => $smiley["smiley_keywords"],
			"chat_current_smiley_image_path" => $smiley["smiley_fullpath"],
		);
		$this->form_gui->setValuesByArray($form_data);
		
		$tpl = new ilTemplate("tpl.chat_edit_smilies.html", true, true, "Modules/Chat");
		$tpl->setVariable("SMILEY_FORM", $this->form_gui->getHTML());
		
		$this->tpl->setContent($tpl->get());
	}
	
	public function showDeleteSmileyFormObject() {
		global $rbacsystem, $lng, $ilCtrl;
		$this->tabs_gui->setTabActive('edit_smilies');

		if(!$rbacsystem->checkAccess('write', $this->ref_id)) {
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->MESSAGE);
		}
		
		$tpl = new ilTemplate("tpl.chat_smiley_confirm_delete.html", true, true, "Modules/Chat");
		$tpl->setVariable("TXT_CONFIRM_DELETE_SMILEY", $lng->txt('chat_confirm_delete_smiley'));
		$tpl->setVariable("TXT_CONFIRM_DELETE", $lng->txt('chat_confirm_delete_smiley'));
		$tpl->setVariable("TXT_CANCEL_DELETE", $lng->txt('cancel'));
		$tpl->setVariable("SMILEY_ID", $_REQUEST["smiley_id"]);
		//$tpl->setVariable("TABLE_NAV", $_REQUEST["_table_nav"]);
		
		include_once 'Modules/Chat/classes/class.ilChatSmilies.php'; 
		$smiley = ilChatSmilies::_getSmiley($_REQUEST["smiley_id"]);
		
		$tpl->setVariable("SMILEY_PATH", $smiley["smiley_fullpath"]);
		$tpl->setVariable("SMILEY_KEYWORDS", $smiley["smiley_keywords"]);
		
		$table_nav = $_REQUEST["_table_nav"] ? "&_table_nav=".$_REQUEST["_table_nav"] : "";
		
		$tpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this). $table_nav);
		
		$tpl->parseCurrentBlock();
		$this->tpl->setContent($tpl->get());
	}
	
	public function deleteSmileyObject() {
		global $rbacsystem, $ilCtrl;
		
		if(!$rbacsystem->checkAccess('write', $this->ref_id)) {
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->MESSAGE);
		}
		include_once 'Modules/Chat/classes/class.ilChatSmilies.php';
		ilChatSmilies::_deleteSmiley($_REQUEST["chat_smiley_id"]);
		
		//$this->editSmiliesObject();
		$ilCtrl->redirect($this, "editSmilies");
	}
	
	public function editSmiliesObject()
	{
		global $rbacsystem;
		
		$this->tabs_gui->setTabActive('edit_smilies');
		
		if(!$rbacsystem->checkAccess('read', $this->ref_id)) {
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilias->error_obj->MESSAGE);
		}
		include_once 'Modules/Chat/classes/class.ilChatSmilies.php';
		ilChatSmilies::_checkSetup();
		
		$this->initSmiliesForm('edit');
		include_once "Modules/Chat/classes/class.ilChatSmiliesGUI.php";
		$table = ilChatSmiliesGUI::_getExistingSmiliesTable($this);
		
		$tpl = new ilTemplate("tpl.chat_edit_smilies.html", true, true, "Modules/Chat");
		$tpl->setVariable("SMILEY_TABLE", $table);
		$tpl->setVariable("SMILEY_FORM", $this->form_gui->getHTML());
		
		$this->tpl->setContent($tpl->get());
	
	}
	
	public function updateSmiliesObject() {
		global $rbacsystem, $ilCtrl;
		if(!$rbacsystem->checkAccess('write', $this->ref_id)) {
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->MESSAGE);
		}
		$this->initSmiliesEditForm();

		include_once "Modules/Chat/classes/class.ilChatSmilies.php";
		
		$keywords = ilChatSmilies::_prepareKeywords(ilUtil::stripSlashes($_REQUEST["chat_smiley_keywords"]));
		$keywordscheck = count($keywords) > 0;
		
		if(!$this->form_gui->checkInput() || !$keywordscheck) {			
			$this->tabs_gui->setTabActive('edit_properties');					
			$this->form_gui->setValuesByPost();
			$this->tpl->setContent($this->form_gui->getHtml());
			return;
		}
		else {
			$data = array();
			$data["smiley_keywords"] = join("\n", $keywords);
			$data["smiley_id"] = $_REQUEST["chat_smiley_id"];
			
			if ($_FILES["chat_image_path"]) {
				move_uploaded_file($_FILES["chat_image_path"]["tmp_name"], ilChatSmilies::_getSmiliesBasePath() . $_FILES["chat_image_path"]["name"]);
				$data["smiley_path"] = $_FILES["chat_image_path"]["name"];
			}
			
			ilChatSmilies::_updateSmiley($data);
		}
		//$this->editSmiliesObject();
		$ilCtrl->redirect($this, "editSmilies");
	}
	
	public function updateObject()
	{
		global $rbacsystem, $ilSetting;

		if(!$rbacsystem->checkAccess('write', $this->ref_id)) {
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->MESSAGE);
		}
		
		$this->initForm('edit');
		
		if(!$this->form_gui->checkInput())	{			
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
		
			$ilSetting->set('chat_new_message_sound_status', (int)$_POST['chat_new_message_sound_status']);
			$ilSetting->set('chat_message_notify_status', (int)$_POST['chat_message_notify_status']);
			//echo (int)$_POST['chat_message_notify_status']; exit;
			$ilSetting->set('chat_smilies_status', (int)$_POST['chat_smilies_status']);
			
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

		$this->ctrl->setParameter($this,'ref_id',$this->object->getRefId());

		if($rbacsystem->checkAccess('read',$this->object->getRefId()))
		{
			$force_active = ($_GET['cmd'] == '' || $_GET['cmd'] == 'view')
				? true
				: false;
			$tabs_gui->addTarget('chat_rooms',
				$this->ctrl->getLinkTarget($this, 'view'), array('view', ''), get_class($this),
				'', $force_active);
		}
		if($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$force_active_edit = ($_GET["cmd"] == "edit") ? true	: false;
			$force_active_smilies = ($_GET["cmd"] == "edit") ? true	: false;
			$tabs_gui->addTarget("edit_properties",	$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this),"", $force_active_edit);
			$tabs_gui->addTarget("edit_smilies",	$this->ctrl->getLinkTarget($this, "editSmilies"), "edit_smilies", get_class($this),"", $force_active_smilies);
		}
		if($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget('perm_settings',
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), 'perm'), array('perm','info','owner'), 'ilpermissiongui');
		}
	}
} // END class.ilObjChatServerGUI
?>