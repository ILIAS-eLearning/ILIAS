<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilCustomUserFieldsGUI
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilCustomUserFieldsGUI:
 */
class ilCustomUserFieldsGUI
{
    protected \ILIAS\User\StandardGUIRequest $request;
    protected int $ref_id = 0;
    protected bool $confirm_change = false;
    protected int $field_id = 0;
    /**
     * @var array[]
     */
    protected array $field_definition = [];
    protected ilClaimingPermissionHelper $permissions;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        int $ref_id,
        int $requested_field_id
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $this->ref_id = $ref_id;

        $lng->loadLanguageModule("user");
        $lng->loadLanguageModule("administration");

        $this->field_id = $requested_field_id;
        $ilCtrl->saveParameter($this, "field_id", $this->field_id);

        if ($this->field_id) {
            $user_field_definitions = ilUserDefinedFields::_getInstance();
            $this->field_definition = $user_field_definitions->getDefinition($this->field_id);
        }

        $this->permissions = ilUDFPermissionHelper::getInstance(
            $DIC->user()->getId(),
            $ref_id
        );
        $this->request = new \ILIAS\User\StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }

    protected function getPermissions(): ilClaimingPermissionHelper
    {
        return $this->permissions;
    }

    public function executeCommand(): void
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
    }

    public function listUserDefinedFields(): void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilCtrl = $DIC['ilCtrl'];

        if ($this->getPermissions()->hasPermission(
            ilUDFPermissionHelper::CONTEXT_UDF,
            (string) $this->ref_id,
            ilUDFPermissionHelper::ACTION_UDF_CREATE_FIELD
        )) {
            $ilToolbar->addButton(
                $lng->txt("add_user_defined_field"),
                $ilCtrl->getLinkTarget($this, "addField")
            );
        }

        $tab = new ilCustomUserFieldSettingsTableGUI($this, "listUserDefinedFields", $this->getPermissions());
        if ($this->confirm_change) {
            $tab->setConfirmChange();
        }
        $tpl->setContent($tab->getHTML());
    }

    public function addField(ilPropertyFormGUI $a_form = null): void
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
     * @return array<string,string>
     */
    public function getAccessOptions(): array
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

    /**
     * @return array<string,string>
     */
    public static function getAccessPermissions(): array
    {
        return array("visible" => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_PERSONAL,
            "changeable" => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_CHANGEABLE_PERSONAL,
            "searchable" => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_SEARCHABLE,
            "required" => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_REQUIRED,
            "export" => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_EXPORT,
            "course_export" => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_COURSES,
            'group_export' => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_GROUPS,
            "visib_reg" => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_REGISTRATION,
            'visib_lua' => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_VISIBLE_LOCAL,
            'changeable_lua' => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_CHANGEABLE_LOCAL,
            'certificate' => ilUDFPermissionHelper::SUBACTION_FIELD_ACCESS_CERTIFICATE
        );
    }

    protected function initFieldDefinition(): array // Missing array type.
    {
        global $DIC;

        $lng = $DIC['lng'];

        if (ilMemberAgreement::_hasAgreements()) {
            $lng->loadLanguageModule("ps");
            $this->main_tpl->setOnScreenMessage('info', $lng->txt("ps_warning_modify"));
        }

        $perms = array();
        if ($this->field_definition) {
            $perms = $this->permissions->hasPermissions(
                ilUDFPermissionHelper::CONTEXT_FIELD,
                (string) $this->field_definition["field_id"],
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

    protected function initForm(string $a_mode = 'create'): ilPropertyFormGUI
    {
        global $ilCtrl, $lng;

        $perms = [];
        $se_mu = null;
        $perm_map = [];
        $udf_type = null;

        if (ilMemberAgreement::_hasAgreements()) {
            $lng->loadLanguageModule("ps");
            $this->main_tpl->setOnScreenMessage('info', $lng->txt("ps_warning_modify"));
        }

        if ($this->field_definition) {
            $perms = $this->initFieldDefinition();
            $perm_map = self::getAccessPermissions();
        }

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
        } else {
            $name->setValue($this->field_definition["field_name"]);
            $radg->setValue($this->field_definition["field_type"]);
            $radg->setDisabled(true);
            $acc->setValue($acc_values);

            switch ($this->field_definition["field_type"]) {
                case UDF_TYPE_SELECT:
                    $values = $this->field_definition["field_values"];
                    if (!is_array($values) || $values === []) {
                        $values = [''];
                    }
                    $se_mu->setValue($values);
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
        }
        $form->addCommandButton("listUserDefinedFields", $lng->txt("cancel"));
        return $form;
    }

    protected function validateForm(
        ilPropertyFormGUI $form,
        ilUserDefinedFields $user_field_definitions,
        array &$access,
        array $a_field_permissions = null
    ): bool {
        global $DIC;

        $lng = $DIC['lng'];
        $perm_map = [];

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
                $this->main_tpl->setOnScreenMessage('failure', $lng->txt("form_input_not_valid"));
            }
            return $valid;
        }

        return false;
    }

    public function create(): void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $user_field_definitions = ilUserDefinedFields::_getInstance();
        $user_field_definitions->setFieldType(
            $this->request->getFieldType()
        );

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
                $plugin = ilCustomUserFieldsHelper::getInstance()->getPluginForType($user_field_definitions->getFieldType());
                if ($plugin instanceof ilUDFDefinitionPlugin) {
                    $plugin->updateDefinitionFromForm($form, $new_id);
                }
            }
            if ($access['course_export']) {
                ilMemberAgreement::_reset();
            }

            $this->main_tpl->setOnScreenMessage('success', $lng->txt('udf_added_field'), true);
            $ilCtrl->redirect($this);
        }

        $form->setValuesByPost();
        $this->addField($form);
    }

    public function edit(ilPropertyFormGUI $a_form = null): void
    {
        global $DIC;

        $tpl = $DIC['tpl'];

        if (!$a_form) {
            $a_form = $this->initForm("edit");
        }

        $tpl->setContent($a_form->getHTML());
    }

    public function update(): void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $perms = [];

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
                (string) $this->field_id,
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
                $plugin = ilCustomUserFieldsHelper::getInstance()->getPluginForType($user_field_definitions->getFieldType());
                if ($plugin instanceof ilUDFDefinitionPlugin) {
                    $plugin->updateDefinitionFromForm($form, $this->field_id);
                }
            }

            if ($access['course_export']) {
                ilMemberAgreement::_reset();
            }

            $this->main_tpl->setOnScreenMessage('success', $lng->txt('settings_saved'), true);
            $ilCtrl->redirect($this);
        }

        $form->setValuesByPost();
        $this->edit($form);
    }

    public function askDeleteField(): bool
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];

        $fields = $this->request->getFields();
        if (count($fields) == 0) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("select_one"));
            $this->listUserDefinedFields();
            return false;
        }

        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
        $confirmation_gui->setHeaderText($lng->txt("udf_delete_sure"));
        $confirmation_gui->setCancel($lng->txt("cancel"), "listUserDefinedFields");
        $confirmation_gui->setConfirm($lng->txt("delete"), "deleteField");

        $user_field_definitions = ilUserDefinedFields::_getInstance();
        foreach ($fields as $id) {
            $definition = $user_field_definitions->getDefinition($id);
            $confirmation_gui->addItem("fields[]", $id, $definition["field_name"]);
        }

        $tpl->setContent($confirmation_gui->getHTML());

        return true;
    }

    public function deleteField(): void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $user_field_definitions = ilUserDefinedFields::_getInstance();
        $fields = $this->request->getFields();

        // all fields have to be deletable
        $fail = array();
        foreach ($fields as $id) {
            if (!$this->getPermissions()->hasPermission(
                ilUDFPermissionHelper::CONTEXT_FIELD,
                (string) $id,
                ilUDFPermissionHelper::ACTION_FIELD_DELETE
            )) {
                $field = $user_field_definitions->getDefinition($id);
                $fail[] = $field["field_name"];
            }
        }
        if ($fail) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt('msg_no_perm_delete') . " " . implode(", ", $fail), true);
            $ilCtrl->redirect($this, "listUserDefinedFields");
        }

        foreach ($fields as $id) {
            $user_field_definitions->delete($id);
        }

        $this->main_tpl->setOnScreenMessage('success', $lng->txt('udf_field_deleted'), true);
        $ilCtrl->redirect($this);
    }

    /**
     * Update custom fields properties (from table gui)
     */
    public function updateFields(string $action = ""): void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $user_field_definitions = ilUserDefinedFields::_getInstance();
        $a_fields = $user_field_definitions->getDefinitions();

        $checked = $this->request->getChecked();

        $perm_map = self::getAccessPermissions();

        foreach ($a_fields as $field_id => $definition) {
            $perms = $this->permissions->hasPermissions(
                ilUDFPermissionHelper::CONTEXT_FIELD,
                (string) $field_id,
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
                    $checked[$prop . '_' . $field_id] = $definition[$prop];
                }
            }
        }

        foreach ($a_fields as $field_id => $definition) {
            if (isset($checked['required_' . $field_id]) && (int) $checked['required_' . $field_id] &&
                (!isset($checked['visib_reg_' . $field_id]) || !(int) $checked['visib_reg_' . $field_id])) {
                $this->confirm_change = true;

                $this->main_tpl->setOnScreenMessage('failure', $lng->txt('invalid_visible_required_options_selected'));
                $this->listUserDefinedFields();
                return;
            }
        }

        foreach ($a_fields as $field_id => $definition) {
            $user_field_definitions->setFieldName($definition['field_name']);
            $user_field_definitions->setFieldType($definition['field_type']);
            $user_field_definitions->setFieldValues($definition['field_values']);
            $user_field_definitions->enableVisible((bool) ($checked['visible_' . $field_id] ?? false));
            $user_field_definitions->enableChangeable((bool) ($checked['changeable_' . $field_id] ?? false));
            $user_field_definitions->enableRequired((bool) ($checked['required_' . $field_id] ?? false));
            $user_field_definitions->enableSearchable((bool) ($checked['searchable_' . $field_id] ?? false));
            $user_field_definitions->enableExport((bool) ($checked['export_' . $field_id] ?? false));
            $user_field_definitions->enableCourseExport((bool) ($checked['course_export_' . $field_id] ?? false));
            $user_field_definitions->enableVisibleLocalUserAdministration((bool) ($checked['visib_lua_' . $field_id] ?? false));
            $user_field_definitions->enableChangeableLocalUserAdministration((bool) ($checked['changeable_lua_' . $field_id] ?? false));
            $user_field_definitions->enableGroupExport((bool) ($checked['group_export_' . $field_id] ?? false));
            $user_field_definitions->enableVisibleRegistration((bool) ($checked['visib_reg_' . $field_id] ?? false));
            $user_field_definitions->enableCertificate((bool) ($checked['certificate_' . $field_id] ?? false));

            $user_field_definitions->update($field_id);
        }

        $this->main_tpl->setOnScreenMessage('success', $lng->txt('settings_saved'), true);
        $ilCtrl->redirect($this);
    }
}
