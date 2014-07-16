<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
* Media Cast Settings.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjTaggingSettingsGUI: ilPermissionGUI
* @ilCtrl_IsCalledBy ilObjTaggingSettingsGUI: ilAdministrationGUI
*
* @ingroup ServicesTagging
*/
class ilObjTaggingSettingsGUI extends ilObjectGUI
{
    private static $ERROR_MESSAGE;
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = 'tags';
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule('tagging');
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
			$this->tabs_gui->addTarget("tagging_edit_settings",
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
	 * Add subtabs
	 */
	function addSubTabs()
	{
		global $ilTabs, $lng, $ilCtrl, $ilAccess;
		
		$tags_set = new ilSetting("tags");
		if ($tags_set->get("enable"))
		{
			$ilTabs->addSubTab("settings", $lng->txt("settings"),
				$ilCtrl->getLinkTarget($this, "editSettings"));

			if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
			{
				$ilTabs->addSubTab("forbidden_tags", $lng->txt("tagging_forbidden_tags"),
					$ilCtrl->getLinkTarget($this, "editForbiddenTags"));
	
				$ilTabs->addSubTab("users", $lng->txt("users"),
					$ilCtrl->getLinkTarget($this, "showUsers"));
			}
		}
	}
	
	
	/**
	* Edit mediacast settings.
	*/
	public function editSettings()
	{
		global $ilTabs;
		
		$this->tabs_gui->setTabActive('tagging_edit_settings');
		$this->addSubTabs();
		$ilTabs->activateSubTab("settings");		
		$this->initFormSettings();
		return true;
	}

	/**
	* Save mediacast settings
	*/
	public function saveSettings()
	{
		global $ilCtrl, $ilSetting;
		
		$this->checkPermission("write");

		$tags_set = new ilSetting("tags");
		$tags_set->set("enable", ilUtil::stripSlashes($_POST["enable_tagging"]));
		$tags_set->set("enable_all_users", ilUtil::stripSlashes($_POST["enable_all_users"]));
		$ilSetting->set("block_activated_pdtag", $_POST["enable_tagging"]);

		ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);
		$ilCtrl->redirect($this, "view");
	}

	/**
	* Save mediacast settings
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
	    global $lng;
		
		$tags_set = new ilSetting("tags");
		
		
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('tagging_settings'));
		$form->addCommandButton('saveSettings',$this->lng->txt('save'));
		$form->addCommandButton('cancel',$this->lng->txt('cancel'));

		// enable tagging
		$cb_prop = new ilCheckboxInputGUI($lng->txt("tagging_enable_tagging"),
			"enable_tagging");
		$cb_prop->setValue("1");
		$cb_prop->setChecked($tags_set->get("enable"));
		
			// enable all users info
			$cb_prop2 = new ilCheckboxInputGUI($lng->txt("tagging_enable_all_users"),
				"enable_all_users");
			$cb_prop2->setInfo($lng->txt("tagging_enable_all_users_info"));
			$cb_prop2->setChecked($tags_set->get("enable_all_users"));
			$cb_prop->addSubItem($cb_prop2);

		$form->addItem($cb_prop);
				
		include_once "Services/Administration/classes/class.ilAdministrationSettingsFormHandler.php";
		ilAdministrationSettingsFormHandler::addFieldsToForm(
			ilAdministrationSettingsFormHandler::FORM_TAGGING, 
			$form,
			$this
		);		
		
		$this->tpl->setContent($form->getHTML());
	}
	
	//
	//
	// FORBIDDEN TAGS
	//
	//
	
	/**
	 * Edit forbidden tags
	 */
	function editForbiddenTags()
	{
		global $ilTabs, $tpl;
		
		$this->addSubTabs();
		$ilTabs->activateSubTab("forbidden_tags");
		$ilTabs->activateTab("tagging_edit_settings");
		$this->initForbiddenTagsForm();
		
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Init forbidden tags form.
	 */
	public function initForbiddenTagsForm()
	{
		global $lng, $ilCtrl;
	
		$tags_set = new ilSetting("tags");
		$forbidden = $tags_set->get("forbidden_tags");

		if ($forbidden != "")
		{
			$tags_array = unserialize($forbidden);
			$forb_str = implode($tags_array, " ");
		}
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		// tags
		$ta = new ilTextAreaInputGUI($this->lng->txt("tagging_tags"), "forbidden_tags");
		$ta->setCols(50);
		$ta->setRows(10);
		$ta->setValue($forb_str);
		$this->form->addItem($ta);
	
		$this->form->addCommandButton("saveForbiddenTags", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("tagging_forbidden_tags"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	 
	}
	
	/**
	* Save forbidden tags
	*/
	public function saveForbiddenTags()
	{
		global $tpl, $lng, $ilCtrl;

		$this->initForbiddenTagsForm();
		$this->form->checkInput();
		
		$this->checkPermission("write");
		
		$tags = str_replace(",", " ", $_POST["forbidden_tags"]);
		$tags = explode(" ", $tags);
		$tags_array = array();
		foreach ($tags as $t)
		{
			$t = strtolower(trim($t));
			if ($t != "")
			{
				$tags_array[$t] = $t;
			}
		}
		
		asort($tags_array);
		
		$tags_set = new ilSetting("tags");
		
		$tags_set->set("forbidden_tags", serialize($tags_array));
		
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "editForbiddenTags");
	}
	
	//
	//
	// USER INFO
	//
	//

	/**
	 * Show users
	 */
	function showUsers($a_search = false)
	{
		global $ilTabs, $tpl, $ilToolbar, $lng, $ilCtrl;
		
		$this->checkPermission("write");
		
		$this->addSubTabs();
		$ilTabs->activateTab("tagging_edit_settings");
		$ilTabs->activateSubTab("users");

		$tag = ($_POST["tag"] != "")
			? ilUtil::stripSlashes($_POST["tag"])
			: $_GET["tag"];
		
		// tag input
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($lng->txt("tagging_tag"), "tag");
		$ti->setSize(15);
		$ti->setValue($tag);
		$ilToolbar->addInputItem($ti, true);
		
		$ilToolbar->addFormButton($lng->txt("tagging_search_users"), "searchUsersForTag");
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this, "searchUsersForTag"));
		
		if ($a_search)
		{
			include_once("./Services/Tagging/classes/class.ilUserForTagTableGUI.php");
			$ilCtrl->setParameter($this, "tag", $tag);
			$table = new ilUserForTagTableGUI($this, "searchUsersForTag",
				$tag);
			$tpl->setContent($table->getHTML());
		}
	}
	
	
	/**
	 * Search users for tag
	 */
	function searchUsersForTag()
	{
		$this->showUsers(true);
	}
}
?>