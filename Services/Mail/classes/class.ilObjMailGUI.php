<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjMailGUI
* for admin panel
*
* @author Stefan Meyer <meyer@leifos.com> 
* $Id$
* 
* @ilCtrl_Calls ilObjMailGUI: ilPermissionGUI
* 
* @extends ilObjectGUI
*/
class ilObjMailGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	public function __construct($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = 'mail';
		parent::__construct($a_data,$a_id,$a_call_by_reference, false);
		
		$this->lng->loadLanguageModule('mail');
	}

	public function viewObject()
	{
		global $ilAccess;
		
		if(!$ilAccess->checkAccess('write,read', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->WARNING);
		}
		
		$this->initForm();
		$this->setDefaultValues();
		$this->tpl->setContent($this->form->getHTML());
	}
	
	private function initForm()
	{		
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$this->form = new ilPropertyFormGUI();
		
		$this->form->setFormAction($this->ctrl->getFormAction($this, 'save'));
		$this->form->setTitle($this->lng->txt('general_settings'));
		
		// Subject prefix
		$pre = new ilTextInputGUI($this->lng->txt('mail_subject_prefix'),'mail_subject_prefix');
		$pre->setSize(12);
		$pre->setMaxLength(32);
		$pre->setInfo($this->lng->txt('mail_subject_prefix_info'));
		$this->form->addItem($pre);
		
		// incoming type
		include_once 'Services/Mail/classes/class.ilMailOptions.php';
		$options = array(
			IL_MAIL_LOCAL => $this->lng->txt('mail_incoming_local'), 
			IL_MAIL_EMAIL => $this->lng->txt('mail_incoming_smtp'),
			IL_MAIL_BOTH => $this->lng->txt('mail_incoming_both')
		);	
		$si = new ilSelectInputGUI($this->lng->txt('mail_incoming'), 'mail_incoming_mail');
		$si->setOptions($options);		
		$this->ctrl->setParameterByClass('ilobjuserfoldergui', 'ref_id', USER_FOLDER_ID);
		$si->setInfo(sprintf($this->lng->txt('mail_settings_incoming_type_see_also'), $this->ctrl->getLinkTargetByClass('ilobjuserfoldergui', 'settings')));
		$this->ctrl->clearParametersByClass('ilobjuserfoldergui');
		$this->form->addItem($si);

		$send_html = new ilCheckboxInputGUI($this->lng->txt('mail_send_html'), 'mail_send_html');
		$send_html->setInfo($this->lng->txt('mail_send_html_info'));
		$send_html->setValue(1);
		$this->form->addItem($send_html);
		
		// noreply address
		$ti = new ilTextInputGUI($this->lng->txt('mail_external_sender_noreply'), 'mail_external_sender_noreply');
		$ti->setInfo($this->lng->txt('info_mail_external_sender_noreply'));
		$ti->setMaxLength(255);
		$this->form->addItem($ti);

		$system_sender_name = new ilTextInputGUI($this->lng->txt('mail_system_sender_name'), 'mail_system_sender_name');
		$system_sender_name->setInfo($this->lng->txt('mail_system_sender_name_info'));
		$system_sender_name->setMaxLength(255);
		$this->form->addItem($system_sender_name);

		$cb = new ilCheckboxInputGUI($this->lng->txt('mail_use_pear_mail'), 'pear_mail_enable');
		$cb->setInfo($this->lng->txt('mail_use_pear_mail_info'));
		$cb->setValue(1);
		$this->form->addItem($cb);
		
		// prevent smtp mails
		$cb = new ilCheckboxInputGUI($this->lng->txt('mail_prevent_smtp_globally'), 'prevent_smtp_globally');
		$cb->setValue(1);
		$this->form->addItem($cb);

		$cron_mail = new ilSelectInputGUI($this->lng->txt('cron_mail_notification'), 'mail_notification');
		$cron_options = array(
			0 => $this->lng->txt('cron_mail_notification_never'),
			1 => $this->lng->txt('cron_mail_notification_cron')
		);

		$cron_mail->setOptions($cron_options);
		$cron_mail->setInfo($this->lng->txt('cron_mail_notification_desc'));
		$this->form->addItem($cron_mail);

		// section header
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($this->lng->txt('mail').' ('.$this->lng->txt('internal_system').')');
		$this->form->addItem($sh);
		
		// max attachment size
		$ti = new ilNumberInputGUI($this->lng->txt('mail_maxsize_attach'), 'mail_maxsize_attach');
		$ti->setSuffix($this->lng->txt('kb'));
		$ti->setInfo($this->lng->txt('mail_max_size_attachments_total'));
		$ti->setMaxLength(10);
		$ti->setSize(10);
		$this->form->addItem($ti);

		// Course/Group member notification
		$mn = new ilFormSectionHeaderGUI();
		$mn->setTitle($this->lng->txt('mail_member_notification'));
		$this->form->addItem($mn);

		include_once "Services/Administration/classes/class.ilAdministrationSettingsFormHandler.php";
		ilAdministrationSettingsFormHandler::addFieldsToForm(
			ilAdministrationSettingsFormHandler::FORM_MAIL, 
			$this->form,
			$this
		);
		
		$this->form->addCommandButton('save', $this->lng->txt('save'));
	}
	
	private function setDefaultValues()
	{
		$settings = $this->ilias->getAllSettings();
		$this->form->setValuesByArray(array(
			'mail_subject_prefix' => $settings['mail_subject_prefix'] ? $settings['mail_subject_prefix'] : '[ILIAS]',
			'mail_incoming_mail' => (int)$settings['mail_incoming_mail'],
			'mail_send_html' => (int)$settings['mail_send_html'],
			'pear_mail_enable' => $settings['pear_mail_enable'] ? true : false,
			'mail_external_sender_noreply' => $settings['mail_external_sender_noreply'],
			'prevent_smtp_globally' => ($settings['prevent_smtp_globally'] == '1') ? true : false,
			'mail_maxsize_attach' => $settings['mail_maxsize_attach'],
			'mail_notification' => $settings['mail_notification'],			
			'mail_system_sender_name' => $settings['mail_system_sender_name']
		));
	}
	
	public function saveObject()
	{
		global $rbacsystem, $ilSetting;
		
		if(!$rbacsystem->checkAccess('write', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->WARNING);
		}

		$this->initForm();		
		if($this->form->checkInput())
		{
			$this->ilias->setSetting('mail_send_html',$this->form->getInput('mail_send_html'));
			$this->ilias->setSetting('mail_subject_prefix',$this->form->getInput('mail_subject_prefix'));
			$this->ilias->setSetting('mail_incoming_mail', (int)$this->form->getInput('mail_incoming_mail'));
			$this->ilias->setSetting('mail_maxsize_attach', $this->form->getInput('mail_maxsize_attach'));
			$this->ilias->setSetting('pear_mail_enable', (int)$this->form->getInput('pear_mail_enable'));
			$this->ilias->setSetting('mail_external_sender_noreply', $this->form->getInput('mail_external_sender_noreply'));
			$this->ilias->setSetting('prevent_smtp_globally', (int)$this->form->getInput('prevent_smtp_globally'));
			$this->ilias->setSetting('mail_notification', (int)$this->form->getInput('mail_notification'));			
			$ilSetting->set('mail_system_sender_name', $this->form->getInput('mail_system_sender_name'));

			ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		}		
		$this->form->setValuesByPost();		
		
		$this->tpl->setContent($this->form->getHTML());
	}

	function importObject()
	{
		global $rbacsystem,$lng;

		if (!$rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_import.html", "Services/Mail");

		// GET ALREADY CREATED UPLOADED XML FILE
		$this->__initFileObject();
		if($this->file_obj->findXMLFile())
		{
			$this->tpl->setVariable("TXT_IMPORTED_FILE",$lng->txt("checked_files"));
			$this->tpl->setVariable("XML_FILE",basename($this->file_obj->getXMLFile()));

			$this->tpl->setVariable("BTN_IMPORT",$this->lng->txt("import"));
		}

		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_IMPORT_MAIL",$this->lng->txt("table_mail_import"));
		$this->tpl->setVariable("TXT_IMPORT_FILE",$this->lng->txt("mail_import_file"));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN_UPLOAD",$this->lng->txt("upload"));

		return true;
	}

	function performImportObject()
	{
		global $rbacsystem,$lng;

		if (!$rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
		}
		$this->__initFileObject();
		$this->file_obj->findXMLFile();
		$this->__initParserObject($this->file_obj->getXMLFile(),"import");
		$this->parser_obj->startParsing();
		$number = $this->parser_obj->getCountImported();
		ilUtil::sendInfo($lng->txt("import_finished")." ".$number,true);
		
		$this->ctrl->redirect($this, "import");
	}
	
	

	function uploadObject()
	{
		global $rbacsystem,$lng;

		if (!$rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
		}
		
		$this->__initFileObject();
		if(!$this->file_obj->storeUploadedFile($_FILES["importFile"]))	// STEP 1 save file in ...import/mail
		{
			$this->message = $lng->txt("import_file_not_valid"); 
			$this->file_obj->unlinkLast();
		}
		else if(!$this->file_obj->unzip())
		{
			$this->message = $lng->txt("cannot_unzip_file");					// STEP 2 unzip uplaoded file
			$this->file_obj->unlinkLast();
		}
		else if(!$this->file_obj->findXMLFile())						// STEP 3 getXMLFile
		{
			$this->message = $lng->txt("cannot_find_xml");
			$this->file_obj->unlinkLast();
		}
		else if(!$this->__initParserObject($this->file_obj->getXMLFile(),"check"))
		{
			$this->message = $lng->txt("error_parser");				// STEP 4 init sax parser
		}
		else if(!$this->parser_obj->startParsing())
		{
			$this->message = $lng->txt("users_not_imported").":<br/>"; // STEP 5 start parsing
			$this->message .= $this->parser_obj->getNotAssignableUsers();
		}
		// FINALLY CHECK ERROR
		if(!$this->message)
		{
			$this->message = $lng->txt("uploaded_and_checked");
		}
		ilUtil::sendInfo($this->message,true);
		
		$this->ctrl->redirect($this, "import");
	}

	// PRIVATE
	function __initFileObject()
	{
		include_once "./Services/Mail/classes/class.ilFileDataImportMail.php";

		$this->file_obj =& new ilFileDataImportMail();

		return true;
	}
	function __initParserObject($a_xml,$a_mode)
	{
		include_once "Services/Mail/classes/class.ilMailImportParser.php";

		if(!$a_xml)
		{
			return false;
		}

		$this->parser_obj =& new ilMailImportParser($a_xml,$a_mode);
		
		return true;
	}
	
	function &executeCommand()
	{
		/**
		 * @var $rbacsystem ilRbacSystem
		 */
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilmailtemplategui':
				if(!$rbacsystem->checkAccess('write', $this->object->getRefId()))
				{
					$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->WARNING);
				}

				require_once 'Services/Mail/classes/class.ilMailTemplateGUI.php';
				$this->ctrl->forwardCommand(new ilMailTemplateGUI());
				break;

			default:
				if(!$cmd)
				{
					$cmd = "view";
				}
				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
	}
	
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}
	
	/**
	 * @param ilTabsGUI  $tabs_gui
	*/
	public function getTabs(ilTabsGUI $tabs_gui)
	{
		/**
		 * @var $rbacsystem ilRbacSystem
		 */
		global $rbacsystem;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "view"), array("view", 'save', ""), "", "");
		}

		if($rbacsystem->checkAccess('write', $this->object->getRefId()))
		{
			$tabs_gui->addTarget('mail_templates', $this->ctrl->getLinkTargetByClass('ilmailtemplategui', 'showTemplates'), '', 'ilmailtemplategui');
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}

	/**
	 * goto target group
	 */
	public static function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng, $rbacsystem;

		require_once 'Services/Mail/classes/class.ilMail.php';
		$mail = new ilMail($_SESSION["AccountId"]);
		if($rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId()))
		{
			ilUtil::redirect("ilias.php?baseClass=ilMailGUI");
			exit;
		}
		else
		{
			if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
			{
				$_GET["cmd"] = "frameset";
				$_GET["target"] = "";
				$_GET["ref_id"] = ROOT_FOLDER_ID;
				$_GET["baseClass"] = "ilRepositoryGUI";
				ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
					ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
				include("ilias.php");
				exit;
			}
		}
		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}

} // END class.ilObjMailGUI
?>
