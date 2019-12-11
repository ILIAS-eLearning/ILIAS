<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/User/classes/class.ilUserDefinedFields.php';
include_once './Services/User/classes/class.ilUDFPermissionHelper.php';

/**
* Class ilCustomUserFieldsGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjUserFolderGUI.php 30361 2011-08-25 11:05:41Z jluetzen $
*
* @ilCtrl_Calls ilCustomUserFieldsGUI:
*
* @ingroup ServicesUser
*/
class ilCustomUserFieldsGUI
{
    protected $confirm_change; // [bool]
    protected $field_id; // [int]
    protected $field_definition; // [array]
    protected $permissions; // [ilUDFPermissionHelper]
    
    public function __construct()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $lng->loadLanguageModule("user");
        $lng->loadLanguageModule("administration");
        
        $this->field_id = $_REQUEST["field_id"];
        $ilCtrl->saveParameter($this, "field_id", $this->field_id);
        
        if ($this->field_id) {
            $user_field_definitions = ilUserDefinedFields::_getInstance();
            $this->field_definition = $user_field_definitions->getDefinition($this->field_id);
        }
        
        $this->permissions = ilUDFPermissionHelper::getInstance();
    }
    
    protected function getPermissions()
    {
        return $this->permissions;
    }
    
    public function executeCommand()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();
        
        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = "listUserDefinedFields";
                }
                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * List all custom user fields
     */
    public function listUserDefinedFields()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilCtrl = $DIC['ilCtrl'];
                
        if ($this->getPermissions()->hasPermission(
            ilUDFPermissionHelper::CONTEXT_UDF,
            (int) $_GET["ref_id"],
            ilUDFPermissionHelper::ACTION_UDF_CREATE_FIELD
        )) {
            $ilToolbar->addButton(
                $lng->txt("add_user_defined_field"),
                $ilCtrl->getLinkTarget($this, "addField")
            );
        }
        
        include_once("./Services/User/classes/class.ilCustomUserFieldSettingsTableGUI.php");
        $tab = new ilCustomUserFieldSettingsTableGUI($this, "listUserDefinedFields", $this->getPermissions());
        if ($this->confirm_change) {
            $tab->setConfirmChange();
        }
        $tpl->setContent($tab->getHTML());
    }
    
    /**
     * Add field
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function addField($a_form = null)
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        
        if (!$a_form) {
            $a_form = $this->initForm('create');
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Get all access options, order is kept in forms
     *
     * @return array
     */
    public function getAccessOptions()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $opts = array();
        $opts["visible"] = $lng->txt("user_visible_in_profile");
        $opts["visib_reg"] = $lng->txt("visible_registration");
        $opts["visib_lua"] = $lng->txt("usr_settings_visib_lua");
        $opts["course_export"] = $lng->txt("course_export");
        $opts["group_export"] = $lng->txt("group_export");
        $opts["changeable"] = $lng->txt("changeable");
        $opts["changeable_lua"] = $lng->txt("usr_settings_changeable_lua");
        $opts["required"] = $lng->txt("required_field");
        $opts["export"] = $lng->txt("export");
        $opts["searchable"] = $lng->txt("header_searchable");
        $opts["certificate"] = $lng->txt("certificate");
        return $opts;
    }
    
    public static function getAccessPermissions()
    {
        return array("visible" => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_PERSONAL,
            "changeable" => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_CHANGEABLE_PERSONAL,
            "searchable" => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_SEARCHABLE,
            "required"  => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_REQUIRED,
            "export" => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_EXPORT,
            "course_export" => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_COURSES,
            'group_export' => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_GROUPS,
            "visib_reg" => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_REGISTRATION,
            'visib_lua' => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_LOCAL,
            'changeable_lua' => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_CHANGEABLE_LOCAL,
            'certificate' => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_CERTIFICATE
        );
    }
    
    
    /**
     * init field definition
     * @return array
     */
    protected function initFieldDefinition()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
                
        include_once("Services/Membership/classes/class.ilMemberAgreement.php");
        if (ilMemberAgreement::_hasAgreements()) {
            $lng->loadLanguageModule("ps");
            ilUtil::sendInfo($lng->txt("ps_warning_modify"));
        }
        
        $perms = array();
        if ($this->field_definition) {
            $perms = $this->permissions->hasPermissions(
                ilUDFPermissionHelper::CONTEXT_FIELD,
                $this->field_definition["field_id"],
                array(
                    array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_PROPERTY,
                        ilUDFPermissionHelper::SUBACTION_FIELD_TITLE)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_PROPERTY,
                        ilUDFPermissionHelper::SUBACTION_FIELD_PROPERTIES)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_PERSONAL)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_REGISTRATION)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_LOCAL)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_COURSES)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_GROUPS)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_CHANGEABLE_PERSONAL)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_CHANGEABLE_LOCAL)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_REQUIRED)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_EXPORT)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_SEARCHABLE)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_CERTIFICATE)
            )
            );
        }
        return $perms;
    }
    
    protected function initForm($a_mode = 'create')
    {
        global $ilCtrl, $lng;
        
        include_once("Services/Membership/classes/class.ilMemberAgreement.php");
        if (ilMemberAgreement::_hasAgreements()) {
            $lng->loadLanguageModule("ps");
            ilUtil::sendInfo($lng->txt("ps_warning_modify"));
        }

        if ($this->field_definition) {
            $perms = $this->initFieldDefinition();
            $perm_map = self::getAccessPermissions();
        }
        
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        
        $name = new ilTextInputGUI($lng->txt("field_name"), "name");
        $name->setRequired(true);
        $form->addItem($name);
        
        if ($perms && !$perms[ilUDFPermissionHelper::ACTION_FIELD_EDIT_PROPERTY][ilUDFPermissionHelper::SUBACTION_FIELD_TITLE]) {
            $name->setDisabled(true);
        }
        
        // type
        $radg = new ilRadioGroupInputGUI($lng->txt("field_type"), "field_type");
        $radg->setRequired(true);
        include_once './Services/User/classes/class.ilCustomUserFieldsHelper.php';
        foreach (ilCustomUserFieldsHelper::getInstance()->getUDFTypes() as $udf_type => $udf_name) {
            $op = new ilRadioOption($udf_name, $udf_type);
            $radg->addOption($op);
            
            switch ($udf_type) {
                case UDF_TYPE_TEXT:
                case UDF_TYPE_WYSIWYG:
                    // do nothing
                    break;
                case UDF_TYPE_SELECT:
                    // select values
                    $se_mu = new ilTextWizardInputGUI($lng->txt("value"), "selvalue");
                    $se_mu->setRequired(true);
                    $se_mu->setSize(32);
                    $se_mu->setMaxLength(128);
                    $se_mu->setValues(array(''));
                    $op->addSubItem($se_mu);
                    break;
                
                default:
                    $plugin = ilCustomUserFieldsHelper::getInstance()->getPluginForType($udf_type);
                    if ($plugin instanceof ilUDFDefinitionPlugin) {
                        $plugin->addDefinitionTypeOptionsToRadioOption($op, $this->field_id);
                    }
                    break;
            }
        }

        $form->addItem($radg);
        
        if ($perms && !$perms[ilUDFPermissionHelper::ACTION_FIELD_EDIT_PROPERTY][ilUDFPermissionHelper::SUBACTION_FIELD_PROPERTIES]) {
            $se_mu->setDisabled(true);
            $se_mu->setRequired(false);
        }
        
        
        // access
        $acc = new ilCheckboxGroupInputGUI($lng->txt("access"), "access");
            
        $acc_values = array();
        foreach ($this->getAccessOptions() as $id => $caption) {
            $opt = new ilCheckboxOption($caption, $id);
            $acc->addOption($opt);
                
            if ($this->field_definition && $this->field_definition[$id]) {
                $acc_values[] = $id;
            }
                
            if ($perms && !$perms[ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS][$perm_map[$id]]) {
                $opt->setDisabled(true);
            }
        }
        
        $form->addItem($acc);
                
        
        if ($a_mode == 'create') {
            $radg->setValue(UDF_TYPE_TEXT);
            $form->setTitle($lng->txt('add_new_user_defined_field'));
            $form->addCommandButton("create", $lng->txt("save"));
            $form->addCommandButton("listUserDefinedFields", $lng->txt("cancel"));
        } else {
            $name->setValue($this->field_definition["field_name"]);
            $radg->setValue($this->field_definition["field_type"]);
            $radg->setDisabled(true);
            $acc->setValue($acc_values);
            
            switch ($this->field_definition["field_type"]) {
                case UDF_TYPE_SELECT:
                    $se_mu->setValue($this->field_definition["field_values"]);
                    $form->setTitle($lng->txt("udf_update_select_field"));
                    break;
                
                case UDF_TYPE_TEXT:
                    $form->setTitle($lng->txt("udf_update_text_field"));
                    break;
                
                case UDF_TYPE_WYSIWYG:
                    $form->setTitle($lng->txt("udf_update_wysiwyg_field"));
                    break;
                
                default:
                    $plugin = ilCustomUserFieldsHelper::getInstance()->getPluginForType($udf_type);
                    if ($plugin instanceof ilUDFDefinitionPlugin) {
                        $form->setTitle($plugin->getDefinitionUpdateFormTitle());
                    }
                    break;
            }
            $form->addCommandButton("update", $lng->txt("save"));
            $form->addCommandButton("listUserDefinedFields", $lng->txt("cancel"));
        }
        return $form;
    }
    
    /**
     * Validate field form
     *
     * @param ilPropertyFormGUI $form
     * @param ilUserDefinedFields $user_field_definitions
     * @param array $access
     * @param array $a_field_permissions
     * @return bool
     */
    protected function validateForm($form, $user_field_definitions, array &$access, array $a_field_permissions = null)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        if ($form->checkInput()) {
            $valid = true;
                            
            $incoming = (array) $form->getInput("access");
                        
            if ($a_field_permissions) {
                $perm_map = self::getAccessPermissions();
            }
                                                                        
            $access = array();
            foreach (array_keys($this->getAccessOptions()) as $id) {
                $access[$id] = in_array($id, $incoming);
                
                // disabled fields
                if ($a_field_permissions && !$a_field_permissions[ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS][$perm_map[$id]]) {
                    $access[$id] = $this->field_definition[$id];
                }
            }
            
            if ($access['required'] && !$access['visib_reg']) {
                $this->confirm_change = true;
                $form->getItemByPostVar("access")->setAlert($lng->txt('udf_required_requires_visib_reg'));
                $valid = false;
            }

            if (!$this->field_id && $user_field_definitions->nameExists($form->getInput("name"))) {
                $form->getItemByPostVar("name")->setAlert($lng->txt('udf_name_already_exists'));
                $valid = false;
            }
    
            if ($form->getInput("field_type") == UDF_TYPE_SELECT &&
                (!$a_field_permissions || $a_field_permissions[ilUDFPermissionHelper::ACTION_FIELD_EDIT_PROPERTY][ilUDFPermissionHelper::SUBACTION_FIELD_PROPERTIES])) {
                $user_field_definitions->setFieldValues($form->getInput("selvalue"));
                if ($error = $user_field_definitions->validateValues()) {
                    switch ($error) {
                        case UDF_DUPLICATE_VALUES:
                            $form->getItemByPostVar("selvalue")->setAlert($lng->txt('udf_duplicate_entries'));
                            $valid = false;
                            break;
                    }
                }
            }
            
            if (!$valid) {
                ilUtil::sendFailure($lng->txt("form_input_not_valid"));
            }
            return $valid;
        }
        
        return false;
    }
        
    public function create()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $user_field_definitions = ilUserDefinedFields::_getInstance();
        $user_field_definitions->setFieldType($_POST["field_type"]);
        
        $access = array();
        $form = $this->initForm('create');
        if ($this->validateForm($form, $user_field_definitions, $access)) {
            $user_field_definitions->setFieldName($form->getInput("name"));
            $user_field_definitions->enableVisible($access['visible']);
            $user_field_definitions->enableVisibleRegistration((int) $access['visib_reg']);
            $user_field_definitions->enableVisibleLocalUserAdministration($access['visib_lua']);
            $user_field_definitions->enableCourseExport($access['course_export']);
            $user_field_definitions->enableGroupExport($access['group_export']);
            $user_field_definitions->enableChangeable($access['changeable']);
            $user_field_definitions->enableChangeableLocalUserAdministration($access['changeable_lua']);
            $user_field_definitions->enableRequired($access['required']);
            $user_field_definitions->enableExport($access['export']);
            $user_field_definitions->enableSearchable($access['searchable']);
            $user_field_definitions->enableCertificate($access['certificate']);
            $new_id = $user_field_definitions->add();
            
            if ($user_field_definitions->isPluginType()) {
                include_once './Services/User/classes/class.ilCustomUserFieldsHelper.php';
                $plugin = ilCustomUserFieldsHelper::getInstance()->getPluginForType($user_field_definitions->getFieldType());
                if ($plugin instanceof ilUDFDefinitionPlugin) {
                    $plugin->updateDefinitionFromForm($form, $new_id);
                }
            }
            if ($access['course_export']) {
                include_once('Services/Membership/classes/class.ilMemberAgreement.php');
                ilMemberAgreement::_reset();
            }

            ilUtil::sendSuccess($lng->txt('udf_added_field'), true);
            $ilCtrl->redirect($this);
        }
        
        $form->setValuesByPost();
        $this->addField($form);
    }
        
    /**
     * Edit field
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function edit($a_form = null)
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        
        if (!$a_form) {
            $a_form = $this->initForm("edit");
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    public function update()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $user_field_definitions = ilUserDefinedFields::_getInstance();
        $user_field_definitions->setFieldType($this->field_definition["field_type"]);
        
        // gather old select options
        $old_options = null;
        if ($this->field_id) {
            $old_values = $user_field_definitions->getDefinition($this->field_id);
            if ($old_values["field_type"] == UDF_TYPE_SELECT) {
                $old_options = $old_values["field_values"];
            }
            
            $perms = $this->permissions->hasPermissions(
                ilUDFPermissionHelper::CONTEXT_FIELD,
                $this->field_id,
                array(
                    array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_PROPERTY,
                        ilUDFPermissionHelper::SUBACTION_FIELD_TITLE)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_PROPERTY,
                        ilUDFPermissionHelper::SUBACTION_FIELD_PROPERTIES)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_PERSONAL)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_REGISTRATION)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_LOCAL)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_COURSES)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_GROUPS)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_CHANGEABLE_PERSONAL)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_CHANGEABLE_LOCAL)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_REQUIRED)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_EXPORT)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_SEARCHABLE)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_CERTIFICATE)
            )
            );
        }
        
        $access = array();
        $form = $this->initForm("edit");
        if ($this->validateForm($form, $user_field_definitions, $access, $perms) && $this->field_id) {
            // field values are set in validateForm()...
            
            if (!$perms || $perms[ilUDFPermissionHelper::ACTION_FIELD_EDIT_PROPERTY][ilUDFPermissionHelper::SUBACTION_FIELD_PROPERTIES]) {
                // diff old select options against new to handle deleted values properly
                if (is_array($old_options)) {
                    foreach ($old_options as $old_option) {
                        if (!in_array($old_option, $user_field_definitions->getFieldValues())) {
                            ilUserDefinedData::deleteFieldValue($this->field_id, $old_option);
                        }
                    }
                }
            }
            // disabled fields
            elseif (is_array($old_options)) {
                $user_field_definitions->setFieldValues($old_options);
            }
                        
            if (!$perms || $perms[ilUDFPermissionHelper::ACTION_FIELD_EDIT_PROPERTY][ilUDFPermissionHelper::SUBACTION_FIELD_TITLE]) {
                $user_field_definitions->setFieldName($form->getInput("name"));
            } else {
                $user_field_definitions->setFieldName($this->field_definition["field_name"]);
            }
        
            $user_field_definitions->enableVisible($access['visible']);
            $user_field_definitions->enableVisibleRegistration((int) $access['visib_reg']);
            $user_field_definitions->enableVisibleLocalUserAdministration($access['visib_lua']);
            $user_field_definitions->enableCourseExport($access['course_export']);
            $user_field_definitions->enableGroupExport($access['group_export']);
            $user_field_definitions->enableChangeable($access['changeable']);
            $user_field_definitions->enableChangeableLocalUserAdministration($access['changeable_lua']);
            $user_field_definitions->enableRequired($access['required']);
            $user_field_definitions->enableExport($access['export']);
            $user_field_definitions->enableSearchable($access['searchable']);
            $user_field_definitions->enableCertificate($access['certificate']);
            $user_field_definitions->update($this->field_id);
            
            if ($user_field_definitions->isPluginType()) {
                include_once './Services/User/classes/class.ilCustomUserFieldsHelper.php';
                $plugin = ilCustomUserFieldsHelper::getInstance()->getPluginForType($user_field_definitions->getFieldType());
                if ($plugin instanceof ilUDFDefinitionPlugin) {
                    $plugin->updateDefinitionFromForm($form, $this->field_id);
                }
            }

            if ($access['course_export']) {
                include_once('Services/Membership/classes/class.ilMemberAgreement.php');
                ilMemberAgreement::_reset();
            }

            ilUtil::sendSuccess($lng->txt('settings_saved'), true);
            $ilCtrl->redirect($this);
        }
        
        $form->setValuesByPost();
        $this->edit($form);
    }
        
    public function askDeleteField()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        
        if (!$_POST["fields"]) {
            ilUtil::sendFailure($lng->txt("select_one"));
            return $this->listUserDefinedFields();
        }
    
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
        $confirmation_gui->setHeaderText($lng->txt("udf_delete_sure"));
        $confirmation_gui->setCancel($lng->txt("cancel"), "listUserDefinedFields");
        $confirmation_gui->setConfirm($lng->txt("delete"), "deleteField");
        
        $user_field_definitions = ilUserDefinedFields::_getInstance();
        foreach ($_POST["fields"] as $id) {
            $definition = $user_field_definitions->getDefinition($id);
            $confirmation_gui->addItem("fields[]", $id, $definition["field_name"]);
        }

        $tpl->setContent($confirmation_gui->getHTML());

        return true;
    }
        
    public function deleteField()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $user_field_definitions = ilUserDefinedFields::_getInstance();
        
        // all fields have to be deletable
        $fail = array();
        foreach ($_POST["fields"] as $id) {
            if (!$this->getPermissions()->hasPermission(
                ilUDFPermissionHelper::CONTEXT_FIELD,
                $id,
                ilUDFPermissionHelper::ACTION_FIELD_DELETE
            )) {
                $field = $user_field_definitions->getDefinition($id);
                $fail[] = $field["field_name"];
            }
        }
        if ($fail) {
            ilUtil::sendFailure($lng->txt('msg_no_perm_delete') . " " . implode(", ", $fail), true);
            $ilCtrl->redirect($this, "listUserDefinedFields");
        }
        
        foreach ($_POST["fields"] as $id) {
            $user_field_definitions->delete($id);
        }

        ilUtil::sendSuccess($lng->txt('udf_field_deleted'), true);
        $ilCtrl->redirect($this);
    }

    /**
     * Update custom fields properties (from table gui)
     */
    public function updateFields($action = "")
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $user_field_definitions = ilUserDefinedFields::_getInstance();
        $a_fields = $user_field_definitions->getDefinitions();
        
        $perm_map =	self::getAccessPermissions();
        
        foreach ($a_fields as $field_id => $definition) {
            $perms = $this->permissions->hasPermissions(
                ilUDFPermissionHelper::CONTEXT_FIELD,
                $field_id,
                array(
                    array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_PERSONAL)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_REGISTRATION)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_LOCAL)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_COURSES)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_GROUPS)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_CHANGEABLE_PERSONAL)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_CHANGEABLE_LOCAL)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_REQUIRED)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_EXPORT)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_SEARCHABLE)
                    ,array(ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS,
                        ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_CERTIFICATE)
            )
            );
            
            // disabled field
            foreach ($perm_map as $prop => $perm) {
                if (!$perms[ilUDFPermissionHelper::ACTION_FIELD_EDIT_ACCESS][$perm]) {
                    $_POST['chb'][$prop . '_' . $field_id] = $definition[$prop];
                }
            }
        }
        
        foreach ($a_fields as $field_id => $definition) {
            if (isset($_POST['chb']['required_' . $field_id]) && (int) $_POST['chb']['required_' . $field_id] &&
                (!isset($_POST['chb']['visib_reg_' . $field_id]) || !(int) $_POST['chb']['visib_reg_' . $field_id])) {
                $this->confirm_change = true;
    
                ilUtil::sendFailure($lng->txt('invalid_visible_required_options_selected'));
                $this->listUserDefinedFields();
                return false;
            }
        }
        
        foreach ($a_fields as $field_id => $definition) {
            $user_field_definitions->setFieldName($definition['field_name']);
            $user_field_definitions->setFieldType($definition['field_type']);
            $user_field_definitions->setFieldValues($definition['field_values']);
            $user_field_definitions->enableVisible((int) $_POST['chb']['visible_' . $field_id]);
            $user_field_definitions->enableChangeable((int) $_POST['chb']['changeable_' . $field_id]);
            $user_field_definitions->enableRequired((int) $_POST['chb']['required_' . $field_id]);
            $user_field_definitions->enableSearchable((int) $_POST['chb']['searchable_' . $field_id]);
            $user_field_definitions->enableExport((int) $_POST['chb']['export_' . $field_id]);
            $user_field_definitions->enableCourseExport((int) $_POST['chb']['course_export_' . $field_id]);
            $user_field_definitions->enableVisibleLocalUserAdministration((int) $_POST['chb']['visib_lua_' . $field_id]);
            $user_field_definitions->enableChangeableLocalUserAdministration((int) $_POST['chb']['changeable_lua_' . $field_id]);
            $user_field_definitions->enableGroupExport((int) $_POST['chb']['group_export_' . $field_id]);
            $user_field_definitions->enableVisibleRegistration((int) $_POST['chb']['visib_reg_' . $field_id]);
            $user_field_definitions->enableCertificate((int) $_POST['chb']['certificate_' . $field_id]);

            $user_field_definitions->update($field_id);
        }

        ilUtil::sendSuccess($lng->txt('settings_saved'), true);
        $ilCtrl->redirect($this);
    }
}
