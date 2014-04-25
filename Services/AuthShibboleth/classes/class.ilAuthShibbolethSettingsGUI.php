<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ilAuthShibbolethSettingsGUI
 *
 * @author       Stefan Meyer <meyer@leifos.com>
 * @author       Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version      $Id$
 *
 * @ilCtrl_Calls ilAuthShibbolethSettingsGUI:
 * @ingroup      AuthShibboleth
 */
class ilAuthShibbolethSettingsGUI {

	/**
	 * @var ilCtrl
	 */
	private $ctrl;
	/**
	 * @var
	 */
	private $ilias;
	/**
	 * @var ilTabsGUI
	 */
	private $tabs_gui;
	/**
	 * @var ilLanguage
	 */
	private $lng;
	/**
	 * @var HTML_Template_ITX|ilTemplate
	 */
	private $tpl;
	/**
	 * @var int
	 */
	private $ref_id;


	/**
	 *
	 * @param
	 *
	 * @return \ilAuthShibbolethSettingsGUI
	 */
	public function __construct($a_auth_ref_id) {
		global $lng, $ilCtrl, $tpl, $ilTabs, $ilias;
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;
		$this->lng = $lng;
		$this->lng->loadLanguageModule('shib');
		$this->ilias = $ilias;
		$this->tpl = $tpl;
		$this->ref_id = $a_auth_ref_id;
		$this->obj_id = ilObject::_lookupObjId($this->ref_id);
	}


	/**
	 * Execute Command
	 *
	 * @return void
	 */
	public function executeCommand() {
		global $ilAccess, $ilErr, $ilCtrl;
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		if (! $ilAccess->checkAccess('read', '', $this->ref_id)) {
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->WARNING);
		}
		if (! $ilAccess->checkAccess('write', '', $this->ref_id) && $cmd != "settings") {
			ilUtil::sendFailure($this->lng->txt('msg_no_perm_write'), true);
			$ilCtrl->redirect($this, "settings");
		}
		$this->setSubTabs();
		switch ($next_class) {
			default:
				if (! $cmd) {
					$cmd = "settings";
				}
				$this->$cmd();
				break;
		}

		return true;
	}


	public function settings() {
		global $rbacreview;
		$this->tabs_gui->setSubTabActive('shib_settings');
		// set already saved data or default value for port
		$settings = $this->ilias->getAllSettings();
		// Compose role list
		$role_list = $rbacreview->getRolesByFilter(2);
		$role = array();
		if (! isset($settings["shib_user_default_role"])) {
			$settings["shib_user_default_role"] = 4;
		}
		if (! isset($settings["shib_idp_list"]) || $settings["shib_idp_list"] == '') {
			$settings["shib_idp_list"] = "urn:mace:organization1:providerID, Example Organization 1\nurn:mace:organization2:providerID, Example Organization 2, /Shibboleth.sso/WAYF/SWITCHaai";
		}
		if (! isset($settings["shib_login_button"]) || $settings["shib_login_button"] == '') {
			$settings["shib_login_button"] = "templates/default/images/shib_login_button.png";
		}
		if (! isset($settings["shib_hos_type"]) || $settings["shib_hos_type"] == '') {
			$settings["shib_hos_type"] = 'internal_wayf';
		}
		foreach ($role_list as $data) {
			$role[$data["obj_id"]] = $data["title"];
		}
		// Set text field content
		$shib_settings = array(
			'shib_login',
			'shib_title',
			'shib_firstname',
			'shib_lastname',
			'shib_email',
			'shib_gender',
			'shib_institution',
			'shib_department',
			'shib_zipcode',
			'shib_city',
			'shib_country',
			'shib_street',
			'shib_phone_office',
			'shib_phone_home',
			'shib_phone_mobile',
			'shib_language',
			'shib_matriculation',
		);
		//set PropertyFormGUI
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$propertys = new ilPropertyFormGUI();
		$propertys->setTitle($this->lng->txt("shib"));
		$propertys->setFormAction($this->ctrl->getFormAction($this, "save"));
		$propertys->addCommandButton("save", $this->lng->txt("save"));
		$propertys->addCommandButton("settings", $this->lng->txt("cancel"));
		//set enable shibboleth support
		$enable = new ilCheckboxInputGUI();
		$enable->setTitle($this->lng->txt("shib_active"));
		$read_me_link = "./Services/AuthShibboleth/README.SHIBBOLETH.txt";
		$info = "<a href='" . $read_me_link . "' target='_blank'>" . $this->lng->txt("auth_shib_instructions") . "</a>";
		$enable->setInfo($info);
		$enable->setPostVar("shib[active]");
		$enable->setChecked($settings["shib_active"]);
		//set allow local authentication
		$local = new ilCheckboxInputGUI();
		$local->setTitle($this->lng->txt("auth_allow_local"));
		$local->setPostVar("shib[auth_allow_local]");
		$local->setChecked($settings['shib_auth_allow_local']);
		//set user default role
		$defaultrole = new ilSelectInputGUI();
		$defaultrole->setTitle($this->lng->txt("shib_user_default_role"));
		$defaultrole->setPostVar("shib[user_default_role]");
		$defaultrole->setOptions($role);
		$defaultrole->setRequired(true);
		$defaultrole->setValue($settings["shib_user_default_role"]);
		//set name of federation
		$name = new ilTextInputGUI();
		$name->setTitle($this->lng->txt("shib_federation_name"));
		$name->setPostVar("shib[federation_name]");
		$name->setSize(40);
		$name->setMaxLength(50);
		$name->setRequired(true);
		$name->setValue(stripslashes($settings["shib_federation_name"]));
		//set Organize selection group
		include_once("./Services/Form/classes/class.ilRadioGroupInputGUI.php");
		include_once("./Services/Form/classes/class.ilRadioOption.php");
		$organize = new ilRadioGroupInputGUI();
		$organize->setTitle($this->lng->txt("shib_login_type"));
		$organize->setPostVar("shib[hos_type]");
		$organize->setRequired(true);
		$organize->setValue($settings["shib_hos_type"]);
		//set 1. option internalwayf
		$internalwayf = new ilRadioOption();
		$internalwayf->setTitle($this->lng->txt("shib_login_internal_wayf"));
		$internalwayf->setValue("internal_wayf");
		//set 1. option internalwayf textbox idplist
		$idplist = new ilTextAreaInputGUI();
		$idplist->setInfo($this->lng->txt("shib_idp_list"));
		$idplist->setPostVar("shib[idp_list]");
		$idplist->setRows(3);
		$idplist->setCols(50);
		$idplist->setValue($settings["shib_idp_list"]);
		//set 2. Option externalwayf
		$externalwayf = new ilRadioOption();
		$externalwayf->setTitle($this->lng->txt("shib_login_external_wayf"));
		$externalwayf->setValue("external_wayf");
		//set 2. Option externalwayf textfield path to login button image
		$loginbutton = new ilTextInputGUI();
		$loginbutton->setInfo($this->lng->txt("shib_login_button"));
		$loginbutton->setPostVar("shib[login_button]");
		$loginbutton->setSize(50);
		$loginbutton->setMaxLength(255);
		$loginbutton->setValue($settings["shib_login_button"]);
		//set 3. Option embeddedwayf
		$embeddedwayf = new ilRadioOption();
		$embeddedwayf->setTitle($this->lng->txt("shib_login_embedded_wayf"));
		$embeddedwayf->setInfo($this->lng->txt("shib_login_embedded_wayf_description"));
		$embeddedwayf->setValue("embedded_wayf");
		//set login instructions
		$logininstruction = new ilTextAreaInputGUI();
		$logininstruction->setTitle($this->lng->txt("auth_login_instructions"));
		$logininstruction->setPostVar("shib[login_instructions]");
		$logininstruction->setRows(3);
		$logininstruction->setCols(50);
		$logininstruction->setValue(stripslashes($settings["shib_login_instructions"]));
		//set path to data manipulation API
		$dataconv = new ilTextInputGUI();
		$dataconv->setTitle($this->lng->txt("shib_data_conv"));
		$dataconv->setPostVar("shib[data_conv]");
		$dataconv->setSize(80);
		$dataconv->setMaxLength(512);
		$dataconv->setValue($settings["shib_data_conv"]);
		//field mappings
		$fields = array();
		foreach ($shib_settings as $setting) {
			$field = ereg_replace('shib_', '', $setting);
			$textinput = new ilTextInputGUI();
			$textinput->setTitle($this->lng->txt($setting));
			$textinput->setPostVar("shib[" . $field . "]");
			$textinput->setValue($settings[$setting]);
			$textinput->setSize(40);
			$textinput->setMaxLength(50);
			$checkinput = new ilCheckboxInputGUI("");
			$checkinput->setOptionTitle($this->lng->txt("shib_update"));
			$checkinput->setPostVar("shib[update_" . $field . "]");
			$checkinput->setChecked($settings["shib_update_" . $field]);
			if ($setting == 'shib_login' || $setting == 'shib_firstname'
				|| $setting == 'shib_lastname'
				|| $setting == 'shib_email'
			) {
				$textinput->setRequired(true);
			}
			$fields[$setting] = array( "text" => $textinput, "check" => $checkinput );
		}
		$propertys->addItem($enable);
		$propertys->addItem($local);
		$propertys->addItem($defaultrole);
		$propertys->addItem($name);
		$internalwayf->addSubItem($idplist);
		$organize->addOption($internalwayf);
		$externalwayf->addSubItem($loginbutton);
		$organize->addOption($externalwayf);
		$organize->addOption($embeddedwayf);
		$propertys->addItem($organize);
		$propertys->addItem($logininstruction);
		$propertys->addItem($dataconv);
		foreach ($shib_settings as $setting) {
			$propertys->addItem($fields[$setting]["text"]);
			if ($setting != "shib_login") {
				$propertys->addItem($fields[$setting]["check"]);
			}
		}
		$this->tpl->setContent($propertys->getHTML());
	}


	public function save() {
		global $ilUser;
		// validate required data
		if (! $_POST["shib"]["login"]
			or ! $_POST["shib"]["hos_type"]
			or ! $_POST["shib"]["firstname"]
			or ! $_POST["shib"]["lastname"]
			or ! $_POST["shib"]["email"]
			or ! $_POST["shib"]["user_default_role"]
			or ! $_POST["shib"]["federation_name"]
		) {
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"), $this->ilias->error_obj->MESSAGE);
		}
		// validate api
		if ($_POST["shib"]["data_conv"]
			and $_POST["shib"]["data_conv"] != ''
			and ! is_readable($_POST["shib"]["data_conv"])
		) {
			$this->ilias->raiseError($this->lng->txt("shib_data_conv_warning"), $this->ilias->error_obj->MESSAGE);
		}
		// all ok. save settings
		$shib_settings = array(
			'shib_login',
			'shib_title',
			'shib_firstname',
			'shib_lastname',
			'shib_email',
			'shib_gender',
			'shib_institution',
			'shib_department',
			'shib_zipcode',
			'shib_city',
			'shib_country',
			'shib_street',
			'shib_phone_office',
			'shib_phone_home',
			'shib_phone_mobile',
			'shib_language',
			'shib_matriculation'
		);
		foreach ($shib_settings as $setting) {
			$field = ereg_replace('shib_', '', $setting);
			if ($_POST["shib"]["update_" . $field] != "1") {
				$_POST["shib"]["update_" . $field] = "0";
			}
			$this->ilias->setSetting($setting, trim($_POST["shib"][$field]));
			$this->ilias->setSetting("shib_update_" . $field, $_POST["shib"]["update_" . $field]);
		}
		if ($_POST["shib"]["active"] != "1") {
			$this->ilias->setSetting("shib_active", "0");
		} else {
			$this->ilias->setSetting("shib_active", "1");
		}
		$this->ilias->setSetting("shib_user_default_role", $_POST["shib"]["user_default_role"]);
		$this->ilias->setSetting("shib_hos_type", $_POST["shib"]["hos_type"]);
		$this->ilias->setSetting("shib_federation_name", $_POST["shib"]["federation_name"]);
		$this->ilias->setSetting("shib_idp_list", $_POST["shib"]["idp_list"]);
		$this->ilias->setSetting("shib_login_instructions", $_POST["shib"]["login_instructions"]);
		$this->ilias->setSetting("shib_login_button", $_POST["shib"]["login_button"]);
		$this->ilias->setSetting("shib_data_conv", $_POST["shib"]["data_conv"]);
		$this->ilias->setSetting("shib_auth_allow_local", ($_POST['shib']['auth_allow_local'] == '1') ? '1' : '0');
		ilUtil::sendSuccess($this->lng->txt("shib_settings_saved"), true);
		$this->ctrl->redirect($this, 'settings');
	}


	protected function roleAssignment() {
		$this->tabs_gui->setSubTabActive('shib_role_assignment');
		$this->initFormRoleAssignment('default');
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shib_role_assignment.html', 'Services/AuthShibboleth');
		$this->tpl->setVariable('NEW_RULE_TABLE', $this->form->getHTML());
		if (strlen($html = $this->parseRulesTable())) {
			$this->tpl->setVariable('RULE_TABLE', $html);
		}

		return true;
	}


	protected function parseRulesTable() {
		include_once('./Services/AuthShibboleth/classes/class.ilShibbolethRoleAssignmentRules.php');
		if (ilShibbolethRoleAssignmentRules::getCountRules() == 0) {
			return '';
		}
		include_once('./Services/AuthShibboleth/classes/class.ilShibbolethRoleAssignmentTableGUI.php');
		$rules_table = new ilShibbolethRoleAssignmentTableGUI($this, 'roleAssignment');
		$rules_table->setTitle($this->lng->txt('shib_rules_tables'));
		$rules_table->parse(ilShibbolethRoleAssignmentRules::getAllRules());
		$rules_table->addMultiCommand("confirmDeleteRules", $this->lng->txt("delete"));
		$rules_table->setSelectAllCheckbox("rule_id");

		return $rules_table->getHTML();
	}


	/**
	 * Confirm delete rules
	 *
	 * @access public
	 *
	 * @param
	 *
	 */
	protected function confirmDeleteRules() {
		if (! is_array($_POST['rule_ids'])) {
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->roleAssignment();

			return false;
		}
		$this->tabs_gui->setSubTabActive('shib_role_assignment');
		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();
		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteRules"));
		$c_gui->setHeaderText($this->lng->txt("shib_confirm_del_role_ass"));
		$c_gui->setCancel($this->lng->txt("cancel"), "roleAssignment");
		$c_gui->setConfirm($this->lng->txt("confirm"), "deleteRules");
		// add items to delete
		include_once('Services/AuthShibboleth/classes/class.ilShibbolethRoleAssignmentRule.php');
		foreach ($_POST["rule_ids"] as $rule_id) {
			$rule = new ilShibbolethRoleAssignmentRule($rule_id);
			$info = ilObject::_lookupTitle($rule->getRoleId());
			$info .= " (";
			$info .= $rule->conditionToString();
			$info .= ')';
			$c_gui->addItem('rule_ids[]', $rule_id, $info);
		}
		$this->tpl->setContent($c_gui->getHTML());
	}


	/**
	 * delete role assignment rule
	 *
	 * @access public
	 *
	 */
	protected function deleteRules() {
		if (! is_array($_POST['rule_ids'])) {
			ilUtil::sendFailure($this->lng->txt('select_once'));
			$this->roleAssignment();

			return false;
		}
		include_once('Services/AuthShibboleth/classes/class.ilShibbolethRoleAssignmentRule.php');
		foreach ($_POST["rule_ids"] as $rule_id) {
			$rule = new ilShibbolethRoleAssignmentRule($rule_id);
			$rule->delete();
		}
		ilUtil::sendSuccess($this->lng->txt('shib_deleted_rule'));
		$this->roleAssignment();

		return true;
	}


	protected function initFormRoleAssignment($a_mode = 'default') {
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this, 'cancel'));
		$this->form->setTitle($this->lng->txt('shib_role_ass_table'));
		if ($a_mode == 'default') {
			$this->form->setTitle($this->lng->txt('shib_role_ass_table'));
			$this->form->addCommandButton('addRoleAssignmentRule', $this->lng->txt('shib_new_rule'));
			$this->form->addCommandButton('settings', $this->lng->txt('cancel'));
		} else {
			$this->form->setTitle($this->lng->txt('shib_update_role_ass_table'));
			$this->form->addCommandButton('updateRoleAssignmentRule', $this->lng->txt('save'));
			$this->form->addCommandButton('roleAssignment', $this->lng->txt('cancel'));
		}
		// Role selection
		$role = new ilRadioGroupInputGUI($this->lng->txt('shib_role_name'), 'role_name');
		$role->setRequired(true);
		$global = new ilRadioOption($this->lng->txt('shib_global_role'), 0);
		$role->addOption($global);
		$role_select = new ilSelectInputGUI('', 'role_id');
		$role_select->setOptions($this->prepareRoleSelect());
		$global->addSubItem($role_select);
		$local = new ilRadioOption($this->lng->txt('shib_local_role'), 1);
		$role->addOption($local);
		include_once './Services/Form/classes/class.ilRoleAutoCompleteInputGUI.php';
		$role_search = new ilRoleAutoCompleteInputGUI('', 'role_search', $this, 'addRoleAutoCompleteObject');
		$role_search->setSize(40);
		$local->addSubItem($role_search);
		include_once './Services/AccessControl/classes/class.ilRoleAutoComplete.php';
		$role->setInfo($this->lng->txt('shib_role_name_info'));
		$this->form->addItem($role);
		// Update options
		$update = new ilNonEditableValueGUI($this->lng->txt('shib_update_roles'), 'update_roles');
		$update->setValue($this->lng->txt('shib_check_role_assignment'));
		$add = new ilCheckboxInputGUI('', 'add_missing');
		$add->setOptionTitle($this->lng->txt('shib_add_missing'));
		$add->setValue(1);
		$update->addSubItem($add);
		$remove = new ilCheckboxInputGUI('', 'remove_deprecated');
		$remove->setOptionTitle($this->lng->txt('shib_remove_deprecated'));
		$remove->setValue(1);
		$update->addSubItem($remove);
		$this->form->addItem($update);
		// Assignment type
		$kind = new ilRadioGroupInputGUI($this->lng->txt('shib_assignment_type'), 'kind');
		$kind->setValue(1);
		$kind->setRequired(true);
		$attr = new ilRadioOption($this->lng->txt('shib_attribute'), 1);
		$attr->setInfo($this->lng->txt('shib_attr_info'));
		$name = new ilTextInputGUI($this->lng->txt('shib_attribute_name'), 'attr_name');
		$name->setSize(32);
		$attr->addSubItem($name);
		$value = new ilTextInputGUI($this->lng->txt('shib_attribute_value'), 'attr_value');
		$value->setSize(32);
		$attr->addSubItem($value);
		$kind->addOption($attr);
		$pl_active = (bool)$this->hasActiveRoleAssignmentPlugins();
		$pl = new ilRadioOption($this->lng->txt('shib_plugin'), 2);
		$pl->setInfo($this->lng->txt('shib_plugin_info'));
		$pl->setDisabled(! $pl_active);
		$id = new ilNumberInputGUI($this->lng->txt('shib_plugin_id'), 'plugin_id');
		$id->setDisabled(! $pl_active);
		$id->setSize(3);
		$id->setMaxLength(3);
		$id->setMaxValue(999);
		$id->setMinValue(1);
		$pl->addSubItem($id);
		$kind->addOption($pl);
		$this->form->addItem($kind);
	}


	/**
	 * Add Member for autoComplete
	 */
	function addRoleAutoCompleteObject() {
		include_once("./Services/Form/classes/class.ilRoleAutoCompleteInputGUI.php");
		ilRoleAutoCompleteInputGUI::echoAutoCompleteList();
	}


	protected function addRoleAssignmentRule() {
		global $ilAccess, $ilErr;
		if (! $ilAccess->checkAccess('write', '', $this->ref_id)) {
			ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
			$this->roleAssignment();

			return false;
		}
		$this->initFormRoleAssignment();
		if (! $this->form->checkInput() or ($err = $this->checkInput())) {
			if ($err) {
				ilUtil::sendFailure($this->lng->txt($err));
			}
			$this->tabs_gui->setSubTabActive('shib_role_assignment');
			$this->form->setValuesByPost();
			$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shib_role_assignment.html', 'Services/AuthShibboleth');
			$this->tpl->setVariable('NEW_RULE_TABLE', $this->form->getHTML());
			if (strlen($html = $this->parseRulesTable())) {
				$this->tpl->setVariable('RULE_TABLE', $html);
			}

			return true;
		}
		// Redirects if required
		$this->showLocalRoleSelection();
		$this->rule->add();
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->roleAssignment();

		return true;
	}


	/**
	 * Edit Role Assignment
	 *
	 * @return
	 */
	protected function editRoleAssignment() {
		$this->ctrl->setParameter($this, 'rule_id', (int)$_GET['rule_id']);
		$this->tabs_gui->setSubTabActive('shib_role_assignment');
		$this->initFormRoleAssignment('update');
		$this->getRuleValues();
		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shib_role_assignment.html', 'Services/AuthShibboleth');
		$this->tpl->setVariable('NEW_RULE_TABLE', $this->form->getHTML());

		return true;
	}


	protected function updateRoleAssignmentRule() {
		global $ilAccess, $ilErr;
		if (! $ilAccess->checkAccess('write', '', $this->ref_id)) {
			ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
			$this->roleAssignment();

			return false;
		}
		$this->initFormRoleAssignment();
		if (! $this->form->checkInput() or ($err = $this->checkInput((int)$_REQUEST['rule_id']))) {
			if ($err) {
				ilUtil::sendFailure($this->lng->txt($err));
			}
			$this->tabs_gui->setSubTabActive('shib_role_assignment');
			$this->form->setValuesByPost();
			$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shib_role_assignment.html', 'Services/AuthShibboleth');
			$this->tpl->setVariable('NEW_RULE_TABLE', $this->form->getHTML());

			return true;
		}
		$this->showLocalRoleSelection('update');
		$this->rule->update();
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->roleAssignment();

		return true;
	}


	private function loadRule($a_rule_id = 0) {
		include_once('./Services/AuthShibboleth/classes/class.ilShibbolethRoleAssignmentRule.php');
		$this->rule = new ilShibbolethRoleAssignmentRule($a_rule_id);
		if ($this->form->getInput('role_name') == 0) {
			$this->rule->setRoleId($this->form->getInput('role_id'));
		} elseif ($this->form->getInput('role_search')) {
			// Search role
			include_once './Services/Search/classes/class.ilQueryParser.php';
			$parser = new ilQueryParser($this->form->getInput('role_search'));
			// TODO: Handle minWordLength
			$parser->setMinWordLength(1, true);
			$parser->setCombination(QP_COMBINATION_AND);
			$parser->parse();
			include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
			$object_search = new ilLikeObjectSearch($parser);
			$object_search->setFilter(array( 'role' ));
			$res = $object_search->performSearch();
			$entries = $res->getEntries();
			if (count($entries) == 1) {
				$role = current($entries);
				$this->rule->setRoleId($role['obj_id']);
			} elseif (count($entries) > 1) {
				$this->rule->setRoleId(- 1);
			}
		}
		$this->rule->setName($this->form->getInput('attr_name'));
		$this->rule->setValue($this->form->getInput('attr_value'));
		$this->rule->enableAddOnUpdate($this->form->getInput('add_missing'));
		$this->rule->enableRemoveOnUpdate($this->form->getInput('remove_deprecated'));
		$this->rule->enablePlugin($this->form->getInput('kind') == 2);
		$this->rule->setPluginId($this->form->getInput('plugin_id'));

		return $this->rule;
	}


	private function getRuleValues() {
		global $rbacreview;
		include_once './Services/AuthShibboleth/classes/class.ilShibbolethRoleAssignmentRule.php';
		$rule = new ilShibbolethRoleAssignmentRule((int)$_GET['rule_id']);
		$role = $rule->getRoleId();
		if ($rbacreview->isGlobalRole($role)) {
			$values['role_name'] = 0;
			$values['role_id'] = $role;
		} else {
			$values['role_name'] = 1;
			$values['role_search'] = ilObject::_lookupTitle($role);
		}
		$values['add_missing'] = (int)$rule->isAddOnUpdateEnabled();
		$values['remove_deprecated'] = (int)$rule->isRemoveOnUpdateEnabled();
		$values['attr_name'] = $rule->getName();
		$values['attr_value'] = $rule->getValue();
		if (! $rule->isPluginActive()) {
			$values['kind'] = 1;
		} else {
			$values['kind'] = 2;
			$values['plugin_id'] = $rule->getPluginId();
		}
		$this->form->setValuesByArray($values);
	}


	private function checkInput($a_rule_id = 0) {
		$this->loadRule($a_rule_id);

		return $this->rule->validate();
	}


	private function showLocalRoleSelection() {
		if ($this->rule->getRoleId() > 0) {
			return false;
		}
		$_SESSION['shib_role_ass']['rule_id'] = $_REQUEST['rule_id'] ? $_REQUEST['rule_id'] : 0;
		$_SESSION['shib_role_ass']['search'] = $this->form->getInput('role_search');
		$_SESSION['shib_role_ass']['add_on_update'] = $this->rule->isAddOnUpdateEnabled();
		$_SESSION['shib_role_ass']['remove_on_update'] = $this->rule->isRemoveOnUpdateEnabled();
		$_SESSION['shib_role_ass']['name'] = $this->rule->getName();
		$_SESSION['shib_role_ass']['value'] = $this->rule->getValue();
		$_SESSION['shib_role_ass']['plugin'] = $this->rule->isPluginActive();
		$_SESSION['shib_role_ass']['plugin_id'] = $this->rule->getPluginId();
		$this->ctrl->redirect($this, 'chooseRole');
	}


	protected function chooseRole() {
		$this->tabs_gui->setSubTabActive('shib_role_assignment');
		include_once './Services/Search/classes/class.ilQueryParser.php';
		$parser = new ilQueryParser($_SESSION['shib_role_ass']['search']);
		$parser->setMinWordLength(1, true);
		$parser->setCombination(QP_COMBINATION_AND);
		$parser->parse();
		include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
		$object_search = new ilLikeObjectSearch($parser);
		$object_search->setFilter(array( 'role' ));
		$res = $object_search->performSearch();
		$entries = $res->getEntries();
		include_once './Services/AccessControl/classes/class.ilRoleSelectionTableGUI.php';
		$table = new ilRoleSelectionTableGUI($this, 'chooseRole');
		$table->setTitle($this->lng->txt('shib_role_selection'));
		$table->addMultiCommand('saveRoleSelection', $this->lng->txt('shib_choose_role'));
		$table->addCommandButton('roleAssignment', $this->lng->txt('cancel'));
		$table->parse($entries);
		$this->tpl->setContent($table->getHTML());

		return true;
	}


	protected function saveRoleSelection() {
		$rule = new ilShibbolethRoleAssignmentRule($_SESSION['shib_role_ass']['rule_id']);
		$rule->setRoleId((int)$_POST['role_id']);
		$rule->setName($_SESSION['shib_role_ass']['name']);
		$rule->setValue($_SESSION['shib_role_ass']['value']);
		$rule->enablePlugin($_SESSION['shib_role_ass']['plugin']);
		$rule->setPluginId($_SESSION['shib_role_ass']['plugin_id']);
		$rule->enableAddOnUpdate($_SESSION['shib_role_ass']['add_on_update']);
		$rule->enableRemoveOnUpdate($_SESSION['shib_role_ass']['remove_on_update']);
		if ($rule->getRuleId()) {
			$rule->update();
		} else {
			$rule->add();
		}
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		unset($_SESSION['shib_role_ass']);
		$this->roleAssignment();
	}


	/**
	 * Check if plugin is active
	 *
	 * @return
	 */
	private function hasActiveRoleAssignmentPlugins() {
		global $ilPluginAdmin;

		return count($ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, 'AuthShibboleth', 'shibhk'));
	}


	private function prepareRoleSelect($a_as_select = true) {
		global $rbacreview, $ilObjDataCache;
		$global_roles = ilUtil::_sortIds($rbacreview->getGlobalRoles(), 'object_data', 'title', 'obj_id');
		$select[0] = $this->lng->txt('links_select_one');
		foreach ($global_roles as $role_id) {
			$select[$role_id] = ilObject::_lookupTitle($role_id);
		}

		return $select;
	}


	protected function setSubTabs() {
		global $ilSetting;
		include_once './Services/AuthShibboleth/classes/class.ilShibbolethRoleAssignmentRules.php';
		if ($ilSetting->get('shib_active') == 0 and ilShibbolethRoleAssignmentRules::getCountRules() == 0) {
			return false;
		}
		// DONE: show sub tabs if there is any role assignment rule
		$this->tabs_gui->addSubTabTarget('shib_settings', $this->ctrl->getLinkTarget($this, 'settings'));
		$this->tabs_gui->addSubTabTarget('shib_role_assignment', $this->ctrl->getLinkTarget($this, 'roleAssignment'));

		return true;
	}
}

?>