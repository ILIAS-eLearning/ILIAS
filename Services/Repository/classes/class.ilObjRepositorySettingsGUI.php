<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
 * Repository settings.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjRepositorySettingsGUI: ilPermissionGUI
 *
 * @ingroup ServicesRepository
 */
class ilObjRepositorySettingsGUI extends ilObjectGUI
{
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{		
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->type = 'reps';
		$this->lng->loadLanguageModule('rep');
	}
	
	public function executeCommand()
	{
		global $ilErr, $ilAccess;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

		if(!$ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('no_permission'), $ilErr->WARNING);
		}

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				$this->$cmd();				
				break;
		}
		return true;
	}	
	
	public function getAdminTabs(&$tabs_gui) 
	{
		$tabs_gui->addTab("settings",
			$this->lng->txt("settings"),
			$this->ctrl->getLinkTarget($this, "settings"));
		
	}
	
	public function view(ilPropertyFormGUI $a_form = null)
	{
		global $ilTabs, $tpl;
		
		$ilTabs->activateTab("settings");
		
		if(!$a_form)
		{
			$a_form = $this->initSettingsForm();
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	protected function initSettingsForm()
	{				
		global $ilSetting;
		
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt("settings"));
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveSettings'));					
		
		// default repository view
		$options = array(
			"flat" => $this->lng->txt("flatview"),
			"tree" => $this->lng->txt("treeview")
			);
		$si = new ilSelectInputGUI($this->lng->txt("def_repository_view"), "default_rep_view");
		$si->setOptions($options);
		$si->setInfo($this->lng->txt(""));
		if ($ilSetting->get("default_repository_view") == "tree")
		{
			$si->setValue("tree");
		}
		else
		{
			$si->setValue("flat");
		}
		$form->addItem($si);

		//
		$options = array(
			"" => $this->lng->txt("adm_rep_tree_only_container"),
			"tree" => $this->lng->txt("adm_all_resource_types")
			);

		// repository tree
		$radg = new ilRadioGroupInputGUI($this->lng->txt("adm_rep_tree_presentation"), "tree_pres");
		$radg->setValue($ilSetting->get("repository_tree_pres"));
		$op1 = new ilRadioOption($this->lng->txt("adm_rep_tree_only_cntr"), "",
			$this->lng->txt("adm_rep_tree_only_cntr_info"));
		$radg->addOption($op1);

		$op2 = new ilRadioOption($this->lng->txt("adm_rep_tree_all_types"), "all_types",
			$this->lng->txt("adm_rep_tree_all_types_info"));

			// limit tree in courses and groups
			$cb = new ilCheckboxInputGUI($this->lng->txt("adm_rep_tree_limit_grp_crs"), "rep_tree_limit_grp_crs");
			$cb->setChecked($ilSetting->get("rep_tree_limit_grp_crs"));
			$cb->setInfo($this->lng->txt("adm_rep_tree_limit_grp_crs_info"));
			$op2->addSubItem($cb);

		$radg->addOption($op2);

		$form->addItem($radg);	
	
		/* OBSOLETE
		// synchronize repository tree with main view
		$cb = new ilCheckboxInputGUI($this->lng->txt("adm_synchronize_rep_tree"), "rep_tree_synchronize");
		$cb->setInfo($this->lng->txt("adm_synchronize_rep_tree_info"));
		$cb->setChecked($ilSetting->get("rep_tree_synchronize"));
		$form->addItem($cb);
		*/
		
		/* DISABLED
		// repository access check
		$options = array(
			0 => "0",
			10 => "10",
			30 => "30",
			60 => "60",
			120 => "120"
			);
		$si = new ilSelectInputGUI($this->lng->txt("adm_repository_cache_time"), "rep_cache");
		$si->setOptions($options);
		$si->setValue($ilSetting->get("rep_cache"));
		$si->setInfo($this->lng->txt("adm_repository_cache_time_info")." ".
			$this->lng->txt("adm_repository_cache_time_info2"));
		$form->addItem($si);
		*/
	
		// trash
		$cb = new ilCheckboxInputGUI($this->lng->txt("enable_trash"), "enable_trash");
		$cb->setInfo($this->lng->txt("enable_trash_info"));
		if ($ilSetting->get("enable_trash"))
		{
			$cb->setChecked(true);
		}
		$form->addItem($cb);
	
		// change event
		require_once 'Services/Tracking/classes/class.ilChangeEvent.php';
		$this->lng->loadLanguageModule("trac");
		$event = new ilCheckboxInputGUI($this->lng->txt('trac_repository_changes'), 'change_event_tracking');
		$event->setChecked(ilChangeEvent::_isActive());		
		$form->addItem($event);
		
		$lists = new ilFormSectionHeaderGUI();
		$lists->setTitle($this->lng->txt("object_lists"));
		$form->addItem($lists);		
			
		$sdesc = new ilCheckboxInputGUI($this->lng->txt("adm_rep_shorten_description"), "rep_shorten_description");
		$sdesc->setInfo($this->lng->txt("adm_rep_shorten_description_info"));
		$sdesc->setChecked($ilSetting->get("rep_shorten_description"));
		$form->addItem($sdesc);
		
		$sdesclen = new ilTextInputGUI($this->lng->txt("adm_rep_shorten_description_length"), "rep_shorten_description_length");
		$sdesclen->setValue($ilSetting->get("rep_shorten_description_length"));
		$sdesclen->setSize(3);
		$sdesc->addSubItem($sdesclen);
			
		// load action commands asynchronously 
		$cb = new ilCheckboxInputGUI($this->lng->txt("adm_item_cmd_asynch"), "item_cmd_asynch");
		$cb->setInfo($this->lng->txt("adm_item_cmd_asynch_info"));
		$cb->setChecked($ilSetting->get("item_cmd_asynch"));
		$form->addItem($cb);
		
		// notes/comments/tagging
		$pl = new ilCheckboxInputGUI($this->lng->txt('adm_show_comments_tagging_in_lists'),'comments_tagging_in_lists');
		$pl->setValue(1);
		$pl->setChecked($ilSetting->get('comments_tagging_in_lists'));
		$form->addItem($pl);

		
		$form->addCommandButton('saveSettings', $this->lng->txt('save'));
		
		return $form;
	}
	
	public function saveSettings()
	{
		global $ilSetting, $rbacsystem;
	
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$form = $this->initSettingsForm();
		if ($form->checkInput())
		{
			$ilSetting->set("default_repository_view", $_POST["default_rep_view"]);
						
			$ilSetting->set("repository_tree_pres", $_POST["tree_pres"]);			 
			if ($_POST["tree_pres"] == "")
			{
				$_POST["rep_tree_limit_grp_crs"] = "";
			}
			if ($_POST["rep_tree_limit_grp_crs"] && !$ilSetting->get("rep_tree_limit_grp_crs"))
			{
				$_POST["rep_tree_synchronize"] = true;
			}
			else if (!$_POST["rep_tree_synchronize"] && $ilSetting->get("rep_tree_synchronize"))
			{
				$_POST["rep_tree_limit_grp_crs"] = false;
			}
			$ilSetting->set("rep_tree_limit_grp_crs", $_POST["rep_tree_limit_grp_crs"]);
						
			// $ilSetting->set('rep_cache',(int) $_POST['rep_cache']);
			// $ilSetting->set("rep_tree_synchronize", $_POST["rep_tree_synchronize"]);	
			
			$ilSetting->set("enable_trash", $_POST["enable_trash"]);	
			 
			$ilSetting->set("rep_shorten_description", $form->getInput('rep_shorten_description'));
			$ilSetting->set("rep_shorten_description_length", (int)$form->getInput('rep_shorten_description_length'));										
			$ilSetting->set('item_cmd_asynch',(int) $_POST['item_cmd_asynch']);			
     		$ilSetting->set('comments_tagging_in_lists',(int) $_POST['comments_tagging_in_lists']);	
						
			require_once 'Services/Tracking/classes/class.ilChangeEvent.php';			
			if ($form->getInput('change_event_tracking'))
			{
				ilChangeEvent::_activate();
			}
			else
			{
				ilChangeEvent::_deactivate();
			}			
						
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			$this->ctrl->redirect($this, "view");
		}
		
		$form->setValuesByPost();
		$this->view($form);
	}
}

?>