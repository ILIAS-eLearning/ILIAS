<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
* Learning Resources Settings.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjLearningResourcesSettingsGUI: ilPermissionGUI
* @ilCtrl_Calls ilObjLearningResourcesSettingsGUI: ilLicenseOverviewGUI
* @ilCtrl_IsCalledBy ilObjLearningResourcesSettingsGUI: ilAdministrationGUI
*
* @ingroup ModulesLearningModule
*/
class ilObjLearningResourcesSettingsGUI extends ilObjectGUI
{
    private static $ERROR_MESSAGE;
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = 'lrss';
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule('content');
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
			case 'illicenseoverviewgui':
				include_once("./Services/License/classes/class.ilLicenseOverviewGUI.php");
				$license_gui =& new ilLicenseOverviewGUI($this,LIC_MODE_ADMINISTRATION);
				$ret =& $this->ctrl->forwardCommand($license_gui);
				break;

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
		global $rbacsystem, $ilAccess, $ilSetting;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("cont_edit_lrs_settings",
				$this->ctrl->getLinkTarget($this, "editSettings"),
				array("editSettings", "view"));

			include_once("Services/License/classes/class.ilLicenseAccess.php");
			if (ilLicenseAccess::_isEnabled())
			{
				$this->tabs_gui->addTarget("licenses",
					$this->ctrl->getLinkTargetByClass('illicenseoverviewgui', ''),
				"", "illicenseoverviewgui");
			}
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"),
				array(),'ilpermissiongui');
		}
	}

	/**
	* Edit learning resources settings.
	*/
	public function editSettings()
	{
		global $ilCtrl, $lng, $ilSetting;

		$lm_set = new ilSetting("lm");
		$lic_set = new ilSetting("license");
		$lng->loadLanguageModule("license");
		$lng->loadLanguageModule("scormdebug");

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("cont_lrs_settings"));
		
		// Page History
		$cb_prop = new ilCheckboxInputGUI($lng->txt("cont_enable_page_history"),
			"page_history");
		$cb_prop->setInfo($lng->txt("cont_enable_page_history_info"));
		$cb_prop->setChecked($lm_set->get("page_history", 1));
		$form->addItem($cb_prop);
		
		// Time scheduled page activation
		$cb_prop = new ilCheckboxInputGUI($lng->txt("cont_enable_time_scheduled_page_activation"),
			"time_scheduled_page_activation");
		$cb_prop->setInfo($lng->txt("cont_enable_time_scheduled_page_activation_info"));
		$cb_prop->setChecked($lm_set->get("time_scheduled_page_activation"));
		$form->addItem($cb_prop);

		// Activate replace media object function
		$cb_prop = new ilCheckboxInputGUI($lng->txt("cont_replace_mob_feature"),
			"replace_mob_feature");
		$cb_prop->setInfo($lng->txt("cont_replace_mob_feature_info"));
		$cb_prop->setChecked($lm_set->get("replace_mob_feature"));
		$form->addItem($cb_prop);

		// Activate HTML export IDs
		$cb_prop = new ilCheckboxInputGUI($lng->txt("cont_html_export_ids"),
			"html_export_ids");
		$cb_prop->setInfo($lng->txt("cont_html_export_ids_info"));
		$cb_prop->setChecked($lm_set->get("html_export_ids"));
		$form->addItem($cb_prop);

		// Upload dir for learning resources
		$tx_prop = new ilTextInputGUI($lng->txt("cont_upload_dir"),
			"cont_upload_dir");
		$tx_prop->setInfo($lng->txt("cont_upload_dir_info"));
		$tx_prop->setValue($lm_set->get("cont_upload_dir"));
		$form->addItem($tx_prop);


		// license activation
		$cb_prop = new ilCheckboxInputGUI($lng->txt("license_counter"),
			"license_counter");
		$cb_prop->setInfo($lng->txt("license_counter_info"));
		$cb_prop->setChecked($lic_set->get("license_counter"));
		$form->addItem($cb_prop);
		
		// license warning
		$tx_prop = new ilTextInputGUI($lng->txt("license_warning"),
			"license_warning");
		$tx_prop->setSize(5);
		$tx_prop->setInfo($lng->txt("license_warning_info"));
		$tx_prop->setValue($lic_set->get("license_warning"));
		$form->addItem($tx_prop);
		
		// scormDebugger activation
		$cb_prop = new ilCheckboxInputGUI($lng->txt("scormdebug_global_activate"),"scormdebug_global_activate");
		$cb_prop->setInfo($lng->txt("scormdebug_global_activate_info"));
		$cb_prop->setChecked($lm_set->get("scormdebug_global_activate"));
		$form->addItem($cb_prop);

		// scormDebugger disableRTECaching
		$cb_prop = new ilCheckboxInputGUI($lng->txt("scormdebug_disable_cache"),
			"scormdebug_disable_cache");
		$cb_prop->setInfo($lng->txt("scormdebug_disable_cache_info"));
		$cb_prop->setChecked($lm_set->get("scormdebug_disable_cache"));
		$form->addItem($cb_prop);

		// command buttons
		$form->addCommandButton("saveSettings", $lng->txt("save"));
		$form->addCommandButton("view", $lng->txt("cancel"));

		$this->tpl->setContent($form->getHTML());
	}

	/**
	* Save learning resources settings
	*/
	public function saveSettings()
	{
		global $ilCtrl, $ilSetting;
		
		$lm_set = new ilSetting("lm");
		$lm_set->set("time_scheduled_page_activation",
			ilUtil::stripSlashes($_POST["time_scheduled_page_activation"]));
		$lm_set->set("page_history",
			(int) ilUtil::stripSlashes($_POST["page_history"]));
		$lm_set->set("replace_mob_feature",
			ilUtil::stripSlashes($_POST["replace_mob_feature"]));
		$lm_set->set("html_export_ids",
			ilUtil::stripSlashes($_POST["html_export_ids"]));
		$lm_set->set("cont_upload_dir",
			ilUtil::stripSlashes($_POST["cont_upload_dir"]));
		$lm_set->setScormDebug("scormdebug_global_activate",
			ilUtil::stripSlashes($_POST["scormdebug_global_activate"]));
		$lm_set->set("scormdebug_disable_cache",
			ilUtil::stripSlashes($_POST["scormdebug_disable_cache"]));
		$lic_set = new ilSetting("license");
		$lic_set->set("license_counter",
			ilUtil::stripSlashes($_POST["license_counter"]));
		$lic_set->set("license_warning",
			ilUtil::stripSlashes($_POST["license_warning"]));

		ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);

		$ilCtrl->redirect($this, "view");
	}
}
?>
