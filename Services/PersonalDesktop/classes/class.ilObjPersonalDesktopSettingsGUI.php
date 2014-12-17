<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");
include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');

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
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
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
			
			$this->tabs_gui->addTarget("pd_personal_workspace",
				$this->ctrl->getLinkTarget($this, "editWsp"),
				array("editWsp"));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"),
				array(),'ilpermissiongui');
		}
	}

	/**
	* Edit personal desktop settings.
	*/
	public function editSettings()
	{
		global $ilCtrl, $lng, $ilSetting;
		
		$pd_set = new ilSetting("pd");
		
		$enable_calendar = ilCalendarSettings::_getInstance()->isEnabled();
		#$enable_calendar = $ilSetting->get("enable_calendar");		
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
		
		// Enable contacts
		$cb_prop = new ilCheckboxInputGUI($lng->txt("pd_enable_contacts"), "enable_contacts");
		$cb_prop->setValue("1");
		$cb_prop->setChecked(($ilSetting->get("disable_contacts") ? "0" : "1"));

			$cb_prop_requires_mail = new ilCheckboxInputGUI($lng->txt('pd_enable_contacts_requires_mail'), 'enable_contacts_require_mail');
			$cb_prop_requires_mail->setValue("1");
			$cb_prop_requires_mail->setChecked(($ilSetting->get("disable_contacts_require_mail") ? "0" : "1"));
			$cb_prop->addSubItem($cb_prop_requires_mail);

		$form->addItem($cb_prop);
		
		// Enable notes
		$cb_prop = new ilCheckboxInputGUI($lng->txt("pd_enable_notes"), "enable_notes");
		$cb_prop->setValue("1");
		$cb_prop->setChecked(($ilSetting->get("disable_notes") ? "0" : "1"));
		$form->addItem($cb_prop);
		
		// Enable notes
		$cb_prop = new ilCheckboxInputGUI($lng->txt("pd_enable_comments"), "enable_comments");
		$cb_prop->setValue("1");
		$cb_prop->setChecked(($ilSetting->get("disable_comments") ? "0" : "1"));
		$form->addItem($cb_prop);
		
		$comm_del_user =  new ilCheckboxInputGUI($lng->txt("pd_enable_comments_del_user"), "comm_del_user");
		$comm_del_user->setChecked($ilSetting->get("comments_del_user", 0));
		$cb_prop->addSubItem($comm_del_user);		
		
		$comm_del_tutor =  new ilCheckboxInputGUI($lng->txt("pd_enable_comments_del_tutor"), "comm_del_tutor");
		$comm_del_tutor->setChecked($ilSetting->get("comments_del_tutor", 1));
		$cb_prop->addSubItem($comm_del_tutor);		
		
		// Enable Chatviewer
		$cb_prop = new ilCheckboxInputGUI($lng->txt("pd_enable_chatviewer"), "block_activated_chatviewer");
		$cb_prop->setValue("1");
		$cb_prop->setChecked(($ilSetting->get("block_activated_chatviewer")));
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
			$ti_prop = new ilNumberInputGUI($lng->txt("pd_time_before_removal"),
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
		
		// Enable 'My Offers' (default personal items)
		$cb_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_my_offers'), 'enable_my_offers');
		$cb_prop->setValue('1');
		$cb_prop->setInfo($lng->txt('pd_enable_my_offers_info'));
		$cb_prop->setChecked(($ilSetting->get('disable_my_offers') ? '0' : '1'));
		$form->addItem($cb_prop);
		
		// Enable 'My Memberships'
		$cb_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_my_memberships'), 'enable_my_memberships');
		$cb_prop->setValue('1');
		$cb_prop->setInfo($lng->txt('pd_enable_my_memberships_info'));
		$cb_prop->setChecked(($ilSetting->get('disable_my_memberships') ? '0' : '1'));
		$form->addItem($cb_prop);
		
		if($ilSetting->get('disable_my_offers') == 0 &&
		   $ilSetting->get('disable_my_memberships') == 0)
		{
			// Default view of personal items
			$sb_prop = new ilSelectInputGUI($lng->txt('pd_personal_items_default_view'), 'personal_items_default_view');
			$sb_prop->setInfo($lng->txt('pd_personal_items_default_view_info'));
			$option = array();
			$option[0] = $lng->txt('pd_my_offers');
			$option[1] = $lng->txt('my_courses_groups');
			$sb_prop->setOptions($option);
			$sb_prop->setValue((int)$ilSetting->get('personal_items_default_view'));
			$form->addItem($sb_prop);
		}
		
		// command buttons
		$form->addCommandButton("saveSettings", $lng->txt("save"));
		$form->addCommandButton("view", $lng->txt("cancel"));

		$this->tpl->setContent($form->getHTML());
	}

	/**
	* Save personal desktop settings
	*/
	public function saveSettings()
	{
		global $ilCtrl, $ilSetting;
		
		$pd_set = new ilSetting("pd");
		
		ilCalendarSettings::_getInstance()->setEnabled( $_POST["enable_calendar"]);
		ilCalendarSettings::_getInstance()->save();
			
		#$ilSetting->set("enable_calendar", $_POST["enable_calendar"]);
		$ilSetting->set("disable_bookmarks", (int) ($_POST["enable_bookmarks"] ? 0 : 1));

		$ilSetting->set("disable_contacts", (int) ($_POST["enable_contacts"] ? 0 : 1));
		$ilSetting->set("disable_contacts_require_mail", (int) ($_POST["enable_contacts_require_mail"] ? 0 : 1));

		$ilSetting->set("disable_notes", (int) ($_POST["enable_notes"] ? 0 : 1));
		$ilSetting->set("disable_comments", (int) ($_POST["enable_comments"] ? 0 : 1));
	
		$ilSetting->set("comments_del_user", (int) ($_POST["comm_del_user"] ? 1 : 0));
		$ilSetting->set("comments_del_tutor", (int) ($_POST["comm_del_tutor"] ? 1 : 0));			
		
		$ilSetting->set("block_activated_chatviewer", (int) ($_POST["block_activated_chatviewer"]));		
		
		$ilSetting->set("block_activated_pdusers", $_POST["block_activated_pdusers"]);
		$pd_set->set("enable_block_moving", $_POST["enable_block_moving"]);
		$pd_set->set("user_activity_time", (int) $_POST["time_removal"]);
		$pd_set->set("osi_host", $_POST["osi_host"]);
		
		// Validate personal desktop view
		if(!(int)$_POST['enable_my_offers'] && !(int)$_POST['enable_my_memberships'])
		{
			ilUtil::sendFailure($this->lng->txt('pd_view_select_at_least_one'), true);
			$ilCtrl->redirect($this, 'view');
		}
		
		// Enable 'My Offers' (default personal items)
		$ilSetting->set('disable_my_offers', (int)($_POST['enable_my_offers'] ? 0 : 1));
		
		// Enable 'My Memberships'
		$ilSetting->set('disable_my_memberships', (int)($_POST['enable_my_memberships'] ? 0 : 1));
		
		if((int)$_POST['enable_my_offers'] && !(int)$_POST['enable_my_memberships'])
			$_POST['personal_items_default_view'] = 0;
		else if(!(int)$_POST['enable_my_offers'] && (int)$_POST['enable_my_memberships'])
			$_POST['personal_items_default_view'] = 1;
		else if(!isset($_POST['personal_items_default_view']))
			$_POST['personal_items_default_view'] = $ilSetting->get('personal_items_default_view');
		
		// Default view of personal items
		$ilSetting->set('personal_items_default_view', (int)$_POST['personal_items_default_view']);
	
		ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);		
		$ilCtrl->redirect($this, "view");
	}
	
	/**
	* Edit personal workspace settings.
	*/
	public function editWsp()
	{
		global $ilCtrl, $lng, $ilSetting;
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "saveWsp"));
		$form->setTitle($lng->txt("pd_personal_workspace"));
		
		// Enable 'Personal Workspace'
		$wsp_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_personal_workspace'), 'wsp');
		$wsp_prop->setValue('1');
		$wsp_prop->setChecked(($ilSetting->get('disable_personal_workspace') ? '0' : '1'));
		$form->addItem($wsp_prop);
		
		// Enable 'Blogs'
		$blog_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_wsp_blogs'), 'blog');
		$blog_prop->setValue('1');
		$blog_prop->setChecked(($ilSetting->get('disable_wsp_blogs') ? '0' : '1'));
		$wsp_prop->addSubItem($blog_prop);
		
		// Enable 'Files'
		$file_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_wsp_files'), 'file');
		$file_prop->setValue('1');
		$file_prop->setChecked(($ilSetting->get('disable_wsp_files') ? '0' : '1'));
		$wsp_prop->addSubItem($file_prop);
		
		// Enable 'Certificates'
		$cert_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_wsp_certificates'), 'cert');
		$cert_prop->setValue('1');
		$cert_prop->setChecked(($ilSetting->get('disable_wsp_certificates') ? '0' : '1'));
		$wsp_prop->addSubItem($cert_prop);
		
		// Enable 'Links'
		$link_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_wsp_links'), 'link');
		$link_prop->setValue('1');
		$link_prop->setChecked(($ilSetting->get('disable_wsp_links') ? '0' : '1'));
		$wsp_prop->addSubItem($link_prop);			
		
		/*
		// Enable 'Portfolios'
		$lng->loadLanguageModule('user');
		$prtf_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_prtf'), 'prtf');
		$prtf_prop->setValue('1');
		$prtf_prop->setInfo($lng->txt('user_portfolios_desc'));
		$prtf_prop->setChecked(($ilSetting->get('user_portfolios') ? '1' : '0'));
		$form->addItem($prtf_prop);
		*/
		
		// Load the disk quota settings object
		require_once 'Services/WebDAV/classes/class.ilObjDiskQuotaSettings.php';
		$disk_quota_obj = ilObjDiskQuotaSettings::getInstance();
		
		// Enable disk quota
		$lng->loadLanguageModule("file");
		$cb_prop = new ilCheckboxInputGUI($lng->txt("personal_workspace_disk_quota"), "enable_personal_workspace_disk_quota");
		$cb_prop->setValue('1');
		$cb_prop->setChecked($disk_quota_obj->isPersonalWorkspaceDiskQuotaEnabled());
		$cb_prop->setInfo($lng->txt('enable_personal_workspace_disk_quota_info'));
		$form->addItem($cb_prop);
				
		require_once 'Services/Administration/classes/class.ilAdministrationSettingsFormHandler.php';
		ilAdministrationSettingsFormHandler::addFieldsToForm(
			ilAdministrationSettingsFormHandler::FORM_WSP,
			$form,
			$this
		);
		
		// command buttons
		$form->addCommandButton("saveWsp", $lng->txt("save"));
		$form->addCommandButton("editWsp", $lng->txt("cancel"));

		$this->tpl->setContent($form->getHTML());
	}
	
	/**
	 * Save personal desktop settings	 
	 */
	public function saveWsp()
	{
		global $ilCtrl, $ilSetting;
		
		// without personal workspace we have to disable to sub-items
		if(!$_POST["wsp"])
		{
			$_POST["blog"] = 0;
			$_POST["file"] = 0;
			$_POST["cert"] = 0;
			$_POST["link"] = 0;
		}
		
		$ilSetting->set('disable_personal_workspace', (int)($_POST['wsp'] ? 0 : 1));
		$ilSetting->set('disable_wsp_blogs', (int)($_POST['blog'] ? 0 : 1));
		$ilSetting->set('disable_wsp_files', (int)($_POST['file'] ? 0 : 1));
		$ilSetting->set('disable_wsp_certificates', (int)($_POST['cert'] ? 0 : 1));
		$ilSetting->set('disable_wsp_links', (int)($_POST['link'] ? 0 : 1));
		// $ilSetting->set('user_portfolios', (int)($_POST['prtf'] ? 1 : 0));
		
		// Load the disk quota settings object
		require_once 'Services/WebDAV/classes/class.ilObjDiskQuotaSettings.php';
		$disk_quota_obj = ilObjDiskQuotaSettings::getInstance();		
		$disk_quota_obj->setPersonalWorkspaceDiskQuotaEnabled($_POST['enable_personal_workspace_disk_quota'] == '1');
		$disk_quota_obj->update();
		
		ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);		
		$ilCtrl->redirect($this, "editWsp");
	}
	
	public function addToExternalSettingsForm($a_form_id)
	{				
		switch($a_form_id)
		{			
			case ilAdministrationSettingsFormHandler::FORM_FILES_QUOTA:
				
				require_once 'Services/WebDAV/classes/class.ilObjDiskQuotaSettings.php';
				$disk_quota_obj = ilObjDiskQuotaSettings::getInstance();
				
				$fields = array('personal_workspace_disk_quota' => array($disk_quota_obj->isPersonalWorkspaceDiskQuotaEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL));
				
				return array(array("editWsp", $fields));			
		}
	}
}

?>
