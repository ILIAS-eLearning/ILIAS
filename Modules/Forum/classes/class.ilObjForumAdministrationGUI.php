<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
* Forum Administration Settings.
*
* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id:$
*
* @ilCtrl_Calls ilObjForumAdministrationGUI: ilPermissionGUI
* @ilCtrl_IsCalledBy ilObjForumAdministrationGUI: ilAdministrationGUI
*
* @ingroup ModulesForum
*/
class ilObjForumAdministrationGUI extends ilObjectGUI
{
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = 'frma';
		parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule('forum');
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

/*		if(!$ilAccess->checkAccess('read','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('no_permission'),$ilErr->WARNING);
		}
*/
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
			$this->tabs_gui->addTarget("settings",
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
	* Edit settings.
	*/
	public function editSettings()
	{
		global $ilTabs;
		
		$this->tabs_gui->setTabActive('forum_edit_settings');
		//$this->addSubTabs();
		$ilTabs->activateSubTab("settings");		
		$this->initFormSettings();
		return true;
	}

	/**
	* Save settings
	*/
	public function saveSettings()
	{
		global $ilCtrl, $ilSetting;
		
		$this->checkPermission("write");

		$frma_set = new ilSetting("frma");
		$frma_set->set("forum_overview", ilUtil::stripSlashes($_POST["forum_overview"]));
		
		if(isset($_POST['anonymous_fora']))
			$ilSetting->set('enable_anonymous_fora', 1);
		else $ilSetting->set('enable_anonymous_fora', 0);

		if(isset($_POST['fora_statistics']))
			$ilSetting->set('enable_fora_statistics', 1);
		else $ilSetting->set('enable_fora_statistics', 0);

		$ilSetting->set('forum_notification', ilUtil::stripSlashes($_POST["forum_notification"]));

		ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);
		$ilCtrl->redirect($this, "view");
	}

	/**
	* Save settings
	*/
	public function cancel()
	{
		global $ilCtrl;
		
		$ilCtrl->redirect($this, "view");
	}
		
	/**
	 * Init settings property form
	 *
	 * @access protected
	 */
	protected function initFormSettings()
	{
	    global $lng, $ilSetting;

		$this->tabs_gui->setTabActive('settings');
		$frma_set = new ilSetting("frma");
		
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('settings'));
		$form->addCommandButton('saveSettings',$this->lng->txt('save'));
		$form->addCommandButton('cancel',$this->lng->txt('cancel'));

		// forum overview

		$frm_radio = new ilRadioGroupInputGUI($this->lng->txt('show_topics_overview'), 'forum_overview');
		$frm_radio->addOption(new ilRadioOption($this->lng->txt('new').', '.$this->lng->txt('is_read').', '.$this->lng->txt('unread'), '0'));
		$frm_radio->addOption(new ilRadioOption($this->lng->txt('is_read').', '.$this->lng->txt('unread'), '1'));
		$frm_radio->setValue($frma_set->get('forum_overview'));
		$frm_radio->setInfo($this->lng->txt('topics_overview_info'));
		$form->addItem($frm_radio);

		$this->fora_statistics = (bool) $ilSetting->get('enable_fora_statistics',false);
		$this->anonymous_fora = (bool) $ilSetting->get('enable_anonymous_fora',false);
		$check = new ilCheckboxInputGui($this->lng->txt('enable_fora_statistics'), 'fora_statistics');
		$check->setInfo($this->lng->txt('enable_fora_statistics_desc'));
		$check->setChecked($this->fora_statistics);
		$form->addItem($check);

		$check = new ilCheckboxInputGui($this->lng->txt('enable_anonymous_fora'), 'anonymous_fora');
		$check->setInfo($this->lng->txt('enable_anonymous_fora_desc'));
		$check->setChecked($this->anonymous_fora);
		$form->addItem($check);

		$frm_sel =  new ilSelectInputGUI($this->lng->txt('cron_forum_notification'), 'forum_notification');
		$notification_options = array(
			0 => $this->lng->txt('cron_forum_notification_never'),
			1 => $this->lng->txt('cron_forum_notification_directly'),
			2 => $this->lng->txt('cron_forum_notification_cron'));
		
		$frm_sel->setOptions($notification_options);
		$frm_sel->setValue($ilSetting->get('forum_notification'));
		$frm_sel->setInfo($this->lng->txt('cron_forum_notification_desc'));
		$form->addItem($frm_sel);

		$this->tpl->setContent($form->getHTML());
	}
}
?>