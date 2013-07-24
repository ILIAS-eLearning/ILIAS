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
		$this->lng->loadLanguageModule('cmps');
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
	
	public function getAdminTabs() 
	{
		global $rbacsystem;
		
		$this->tabs_gui->addTab("settings",
			$this->lng->txt("settings"),
			$this->ctrl->getLinkTarget($this, "view"));
		
		$this->tabs_gui->addTab("icons",
			$this->lng->txt("rep_custom_icons"),
			$this->ctrl->getLinkTarget($this, "customIcons"));
		
		$this->tabs_gui->addTab("modules",
			$this->lng->txt("cmps_modules"),
			$this->ctrl->getLinkTarget($this, "listModules"));			
		
		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTab("perm_settings",
				$this->lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"));
		}
	}
	
	public function view(ilPropertyFormGUI $a_form = null)
	{		
		$this->tabs_gui->activateTab("settings");
		
		if(!$a_form)
		{
			$a_form = $this->initSettingsForm();
		}
		
		$this->tpl->setContent($a_form->getHTML());
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
		
		
		include_once "Services/Administration/classes/class.ilAdministrationSettingsFormHandler.php";
		ilAdministrationSettingsFormHandler::addFieldsToForm(
			ilAdministrationSettingsFormHandler::FORM_REPOSITORY, 
			$form,
			$this
		);		
		
		
		// object lists
		
		$lists = new ilFormSectionHeaderGUI();
		$lists->setTitle($this->lng->txt("rep_object_lists"));
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
		global $ilSetting;
	
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
	
	public function customIcons(ilPropertyFormGUI $a_form = null)
	{		
		$this->tabs_gui->activateTab("icons");
		
		if(!$a_form)
		{
			$a_form = $this->initCustomIconsForm();
		}
		
		$this->tpl->setContent($a_form->getHTML());
	}
	
	protected function initCustomIconsForm()
	{
		global $ilSetting;
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		include_once "Services/Form/classes/class.ilCombinationInputGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt("rep_custom_icons"));
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveCustomIcons'));			
				
		$cb = new ilCheckboxInputGUI($this->lng->txt("enable_custom_icons"), "custom_icons");
		$cb->setInfo($this->lng->txt("enable_custom_icons_info"));
		$cb->setChecked($ilSetting->get("custom_icons"));
		$form->addItem($cb);
		
		
		$size_big = new ilCombinationInputGUI($this->lng->txt("custom_icon_size_big"));
		$form->addItem($size_big);
		
		$width = new ilNumberInputGUI("", "custom_icon_big_width");
		$width->setSize(3);
		$width->setValue($ilSetting->get("custom_icon_big_width"));
		$size_big->addCombinationItem("bgw", $width, $this->lng->txt("width"));
		
		$height = new ilNumberInputGUI("", "custom_icon_big_height");
		$height->setSize(3);
		$height->setValue($ilSetting->get("custom_icon_big_height"));
		$size_big->addCombinationItem("bgh", $height, $this->lng->txt("height"));
		
		
		$size_small = new ilCombinationInputGUI($this->lng->txt("custom_icon_size_standard"));
		$form->addItem($size_small);
		
		$width = new ilNumberInputGUI("", "custom_icon_small_width");
		$width->setSize(3);
		$width->setValue($ilSetting->get("custom_icon_small_width"));
		$size_small->addCombinationItem("smw", $width, $this->lng->txt("width"));
		
		$height = new ilNumberInputGUI("", "custom_icon_small_height");
		$height->setSize(3);
		$height->setValue($ilSetting->get("custom_icon_small_height"));
		$size_small->addCombinationItem("smh", $height, $this->lng->txt("height"));
		
		
		$size_tiny = new ilCombinationInputGUI($this->lng->txt("custom_icon_size_tiny"));
		$form->addItem($size_tiny);
		
		$width = new ilNumberInputGUI("", "custom_icon_tiny_width");
		$width->setSize(3);
		$width->setValue($ilSetting->get("custom_icon_tiny_width"));
		$size_tiny->addCombinationItem("tnw", $width, $this->lng->txt("width"));
		
		$height = new ilNumberInputGUI("", "custom_icon_tiny_height");
		$height->setSize(3);
		$height->setValue($ilSetting->get("custom_icon_tiny_height"));
		$size_tiny->addCombinationItem("tnh", $height, $this->lng->txt("height"));
		
		
		$form->addCommandButton('saveCustomIcons', $this->lng->txt('save'));
		
		return $form;
	}
	
	public function saveCustomIcons()
	{
		global $ilSetting;
	
		$form = $this->initSettingsForm();
		if ($form->checkInput())
		{
			$ilSetting->set("custom_icons", (int)$form->getInput("custom_icons"));
			$ilSetting->set("custom_icon_big_width", (int)$form->getInput("custom_icon_big_width"));
			$ilSetting->set("custom_icon_big_height", (int)$form->getInput("custom_icon_big_height"));
			$ilSetting->set("custom_icon_small_width", (int)$form->getInput("custom_icon_small_width"));
			$ilSetting->set("custom_icon_small_height", (int)$form->getInput("custom_icon_small_height"));
			$ilSetting->set("custom_icon_tiny_width", (int)$form->getInput("custom_icon_tiny_width"));
			$ilSetting->set("custom_icon_tiny_height", (int)$form->getInput("custom_icon_tiny_height"));
			
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			$this->ctrl->redirect($this, "customIcons");
		}
		
		$form->setValuesByPost();
		$this->customIcons($form);	
	}
	
	protected function setModuleSubTabs($a_active)
	{
		$this->tabs_gui->activateTab('modules');
		
		$this->tabs_gui->addSubTab("list_mods",
			$this->lng->txt("list"),
			$this->ctrl->getLinkTarget($this, "listModules"));
		
		$this->tabs_gui->addSubTab("new_item_groups",
			$this->lng->txt("rep_new_item_groups"),
			$this->ctrl->getLinkTarget($this, "listNewItemGroups"));
		
		$this->tabs_gui->activateSubTab($a_active);
	}		
	
	protected function listModules()
	{		
		$this->setModuleSubTabs("list_mods");
				
		include_once("./Services/Repository/classes/class.ilModulesTableGUI.php");
		$comp_table = new ilModulesTableGUI($this, "listModules");
				
		$this->tpl->setContent($comp_table->getHTML());
	}
	
	protected function saveModules()
	{
		global $ilSetting, $ilCtrl, $lng;

		// disable creation
		if (is_array($_POST["obj_pos"]))
		{
			foreach($_POST["obj_pos"] as $k => $v)
			{
				$ilSetting->set("obj_dis_creation_".$k, !(int)$_POST["obj_enbl_creation"][$k]);
			}
		}
		
		// add new position
		$double = $ex_pos = array();
		if (is_array($_POST["obj_pos"]))
		{
			reset($_POST["obj_pos"]);
			foreach($_POST["obj_pos"] as $k => $v)
			{
				if (in_array($v, $ex_pos))
				{
					$double[$v] = $v;
				}
				$ex_pos[] = $v;
				$ilSetting->set("obj_add_new_pos_".$k, $v);
			}
		}
		
		if (count($double) == 0)
		{
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		}
		else
		{			
			ilUtil::sendInfo($lng->txt("cmps_duplicate_positions")." ".implode($double, ", "), true);
		}		
		$ilCtrl->redirect($this, "listModules");		
	}
	
	protected function listNewItemGroups()
	{
		global $ilToolbar;
		
		$this->setModuleSubTabs("new_item_groups");
		
		$ilToolbar->addButton($this->lng->txt("rep_new_item_group_add"), 
			$this->ctrl->getLinkTarget($this, "addNewItemGroup"));
		
		include_once("./Services/Repository/classes/class.ilNewItemGroupTableGUI.php");
		$grp_table = new ilNewItemGroupTableGUI($this, "listNewItemGroups");
				
		$this->tpl->setContent($grp_table->getHTML());
	}
	
	protected function initNewItemGroupForm($a_grp_id = false)
	{
		$this->setModuleSubTabs("new_item_groups");
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		
		$this->lng->loadLanguageModule("meta");
		$def_lng = $this->lng->getDefaultLanguage();
	
		$title = new ilTextInputGUI($this->lng->txt("title"), "title_".$def_lng);
		$title->setInfo($this->lng->txt("meta_l_".$def_lng).
			" (".$this->lng->txt("default_language").")");
		$title->setRequired(true);
		$form->addItem($title);
		
		foreach($this->lng->getInstalledLanguages() as $lang_id)
		{
			if($lang_id != $def_lng)
			{
				$title = new ilTextInputGUI($this->lng->txt("translation"), "title_".$lang_id);
				$title->setInfo($this->lng->txt("meta_l_".$lang_id));
				$form->addItem($title);		
			}
		}
										
		if(!$a_grp_id)
		{
			$form->setTitle($this->lng->txt("rep_new_item_group_add"));
			$form->setFormAction($this->ctrl->getFormAction($this, "saveNewItemGroup"));
			
			$form->addCommandButton("saveNewItemGroup", $this->lng->txt("save"));			
		}
		else
		{
			$form->setTitle($this->lng->txt("rep_new_item_group_edit"));
			$form->setFormAction($this->ctrl->getFormAction($this, "updateNewItemGroup"));
			
			include_once("Services/Repository/classes/class.ilObjRepositorySettings.php");
			$grp = ilObjRepositorySettings::getNewItemGroups();
			$grp = $grp[$a_grp_id];
			
			foreach($grp["titles"] as $id => $value)
			{
				$field = $form->getItemByPostVar("title_".$id);
				if($field)
				{
					$field->setValue($value);
				}
			}
			
			$form->addCommandButton("updateNewItemGroup", $this->lng->txt("save"));			
		}
		$form->addCommandButton("listNewItemGroups", $this->lng->txt("cancel"));			
		
		return $form;
	}
	
	protected function addNewItemGroup(ilPropertyFormGUI $a_form = null)
	{
		if(!$a_form)
		{
			$a_form = $this->initNewItemGroupForm();
		}
		
		$this->tpl->setContent($a_form->getHTML());
	}
	
	protected function saveNewItemGroup()
	{
		$form = $this->initNewItemGroupForm();
		if($form->checkInput())
		{
			$titles = array();
			foreach($this->lng->getInstalledLanguages() as $lang_id)
			{
				$titles[$lang_id] = $form->getInput("title_".$lang_id);
			}
			
			include_once("Services/Repository/classes/class.ilObjRepositorySettings.php");
			if(ilObjRepositorySettings::addNewItemGroup($titles))
			{
				ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
				$this->ctrl->redirect($this, "listNewItemGroups");
			}
		}
		
		$form->setValuesByPost();
		$this->addNewItemGroup($form);
	}
	
	protected function editNewItemGroup(ilPropertyFormGUI $a_form = null)
	{		
		$grp_id = (int)$_GET["grp_id"];
		if(!$grp_id)
		{
			$this->ctrl->redirect($this, "listNewItemGroups");
		}
		
		if(!$a_form)
		{
			$this->ctrl->setParameter($this, "grp_id", $grp_id);
			$a_form = $this->initNewItemGroupForm($grp_id);
		}
		
		$this->tpl->setContent($a_form->getHTML());	
	}
	
	protected function updateNewItemGroup()
	{
		$grp_id = (int)$_GET["grp_id"];
		if(!$grp_id)
		{
			$this->ctrl->redirect($this, "listNewItemGroups");
		}
		
		$this->ctrl->setParameter($this, "grp_id", $grp_id);
		
		$form = $this->initNewItemGroupForm($grp_id);
		if($form->checkInput())
		{
			$titles = array();
			foreach($this->lng->getInstalledLanguages() as $lang_id)
			{
				$titles[$lang_id] = $form->getInput("title_".$lang_id);
			}
			
			include_once("Services/Repository/classes/class.ilObjRepositorySettings.php");
			if(ilObjRepositorySettings::updateNewItemGroup($grp_id, $titles))
			{
				ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
				$this->ctrl->redirect($this, "listNewItemGroups");
			}			
		}
		
		$form->setValuesByPost();
		$this->addNewItemGroup($form);
	}
	
	protected function saveNewItemGroupOrder()
	{
		if(is_array($_POST["grp_order"]))	
		{
			include_once("Services/Repository/classes/class.ilObjRepositorySettings.php");
			ilObjRepositorySettings::updateNewItemGroupOrder($_POST["grp_order"]);
			ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		}
		$this->ctrl->redirect($this, "listNewItemGroups");
	}
	
	public function addToExternalSettingsForm($a_form_id)
	{				
		switch($a_form_id)
		{			
			case ilAdministrationSettingsFormHandler::FORM_FILES_QUOTA:
				
				require_once 'Services/WebDAV/classes/class.ilObjDiskQuotaSettings.php';
				$disk_quota_obj = ilObjDiskQuotaSettings::getInstance();
				
				$fields = array('repository_disk_quota' => array($disk_quota_obj->isDiskQuotaEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL));
				
				if((bool)$disk_quota_obj->isDiskQuotaEnabled())
				{
					$fields['~enable_disk_quota_reminder_mail'] = array($disk_quota_obj->isDiskQuotaReminderMailEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL);
					$fields['~enable_disk_quota_summary_mail'] = array($disk_quota_obj->isDiskQuotaSummaryMailEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL);
					
					if((bool)$disk_quota_obj->isDiskQuotaSummaryMailEnabled())
					{
						$fields['~~disk_quota_summary_rctp'] = $disk_quota_obj->getSummaryRecipients();
					}
				}
				
				return array(array("view", $fields));
				
			case ilAdministrationSettingsFormHandler::FORM_LP:
				
				require_once 'Services/Tracking/classes/class.ilChangeEvent.php';		
				$fields = array('trac_repository_changes' => array(ilChangeEvent::_isActive(), ilAdministrationSettingsFormHandler::VALUE_BOOL));
												
				return array(array("view", $fields));			
		}
	}
}

?>