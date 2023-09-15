<?php

declare(strict_types=1);


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
 * Export settings gui
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilMemberExportSettingsGUI
{
    protected const TYPE_PRINT_VIEW_SETTINGS = 'print_view';
    protected const TYPE_EXPORT_SETTINGS = 'member_export';
    protected const TYPE_PRINT_VIEW_MEMBERS = 'prv_members';

    private string $parent_type = '';
    private int $parent_obj_id = 0;
    private \ILIAS\HTTP\Services $http;
    private \ILIAS\Refinery\Factory $refinery;

    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilRbacSystem $rbacsystem;

    /**
     * Constructor
     */
    public function __construct(string $a_parent_type, int $a_parent_obj_id = 0)
    {
        global $DIC;

        $this->parent_type = $a_parent_type;
        $this->parent_obj_id = $a_parent_obj_id;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('mem');
        $this->rbacsystem = $DIC->rbac()->system();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    private function getLang(): ilLanguage
    {
        return $this->lng;
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd('printViewSettings');

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    protected function printViewSettings(?ilPropertyFormGUI $form = null): void
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initForm(self::TYPE_PRINT_VIEW_SETTINGS);
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function initForm(string $a_type): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->getLang()->txt('mem_' . $a_type . '_form'));

        // profile fields
        $fields['name'] = $this->lng->txt('name');
        $fields['login'] = $this->lng->txt('login');
        $fields['email'] = $this->lng->txt('email');

        $field_info = ilExportFieldsInfo::_getInstanceByType($this->parent_type);
        $field_info->sortExportFields();

        foreach ($field_info->getExportableFields() as $field) {
            switch ($field) {
                case 'username':
                case 'firstname':
                case 'lastname':
                case 'email':
                    continue 2;
            }

            // Check if default enabled
            $fields[$field] = $this->lng->txt($field);
        }

        // udf
        $udf = ilUserDefinedFields::_getInstance();
        $exportable = array();
        if ($this->parent_type === 'crs') {
            $exportable = $udf->getCourseExportableFields();
        } elseif ($this->parent_type === 'grp') {
            $exportable = $udf->getGroupExportableFields();
        }
        foreach ($exportable as $field_id => $udf_data) {
            $fields['udf_' . $field_id] = $udf_data['field_name'];
        }

        $ufields = new ilCheckboxGroupInputGUI($this->lng->txt('user_detail'), 'preset');
        foreach ($fields as $id => $name) {
            $ufields->addOption(new ilCheckboxOption($name, $id));
        }
        $form->addItem($ufields);

        $privacy = ilPrivacySettings::getInstance();
        if ($this->parent_type === 'crs') {
            if ($privacy->enabledCourseAccessTimes()) {
                $ufields->addOption(new ilCheckboxOption($this->lng->txt('last_access'), 'access'));
            }
        }
        if ($this->parent_type === 'grp') {
            if ($privacy->enabledGroupAccessTimes()) {
                $ufields->addOption(new ilCheckboxOption($this->lng->txt('last_access'), 'access'));
            }
        }
        $ufields->addOption(new ilCheckboxOption($this->lng->txt('crs_status'), 'status'));
        $ufields->addOption(new ilCheckboxOption($this->lng->txt('crs_passed'), 'passed'));

        $blank = new ilTextInputGUI($this->lng->txt('event_blank_columns'), 'blank');
        $blank->setMulti(true);
        $form->addItem($blank);

        $roles = new ilCheckboxGroupInputGUI($this->lng->txt('event_user_selection'), 'selection_of_users');

        $roles->addOption(new ilCheckboxOption($this->lng->txt('event_tbl_admin'), 'role_adm'));
        if ($this->parent_type === 'crs') {
            $roles->addOption(new ilCheckboxOption($this->lng->txt('event_tbl_tutor'), 'role_tut'));
        }
        $roles->addOption(new ilCheckboxOption($this->lng->txt('event_tbl_member'), 'role_mem'));

        if (!$this->parent_obj_id) {
            $subscriber = new ilCheckboxOption($this->lng->txt('event_user_selection_include_requests'), 'subscr');
            $roles->addOption($subscriber);

            $waiting_list = new ilCheckboxOption($this->lng->txt('event_user_selection_include_waiting_list'), 'wlist');
            $roles->addOption($waiting_list);
        }
        $form->addItem($roles);

        switch ($a_type) {
            case self::TYPE_PRINT_VIEW_SETTINGS:

                $ref_id = 0;
                if ($this->http->wrapper()->query()->has('ref_id')) {
                    $ref_id = $this->http->wrapper()->query()->retrieve(
                        'ref_id',
                        $this->refinery->kindlyTo()->int()
                    );
                }

                if ($this->rbacsystem->checkAccess('write', $ref_id)) {
                    $form->addCommandButton('savePrintViewSettings', $this->getLang()->txt('save'));
                }
                break;
        }

        $identifier = $this->parent_type . 's_pview';
        if ($this->parent_obj_id) {
            $identifier_for_object = $identifier . '_' . $this->parent_obj_id;
        } else {
            $identifier_for_object = $identifier . '_0';
        }

        $settings = new ilUserFormSettings($identifier_for_object, -1);
        if (!$settings->hasStoredEntry()) {
            // use default settings
            $settings = new ilUserFormSettings($identifier, -1);
        }
        $settings->exportToForm($form);

        return $form;
    }

    /**
     * Save print view settings
     */
    protected function savePrintViewSettings(): void
    {
        $form = $this->initForm(self::TYPE_PRINT_VIEW_SETTINGS);
        if ($form->checkInput()) {
            $form->setValuesByPost();

            ilUserFormSettings::deleteAllForPrefix('crs_memlist');
            ilUserFormSettings::deleteAllForPrefix('grp_memlist');

            $identifier = $this->parent_type . 's_pview';
            if ($this->parent_obj_id) {
                $identifier .= '_' . $this->parent_obj_id;
            }

            $settings = new ilUserFormSettings($identifier, -1);
            $settings->importFromForm($form);
            $settings->store();

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'printViewSettings');
        }
    }
}
