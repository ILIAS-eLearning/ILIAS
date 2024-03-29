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
 ********************************************************************
 */

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Global orgunit settings GUI
 * @author            Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_IsCalledBy ilOrgUnitGlobalSettingsGUI: ilObjOrgUnitGUI
 */
class ilOrgUnitGlobalSettingsGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;

    public function __construct()
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('orgu');
        $this->tpl = $DIC->ui()->mainTemplate();

        if (!ilObjOrgUnitAccess::_checkAccessSettings((int) $_GET['ref_id'])) {
            $main_tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilObjOrgUnitGUI::class);
        }
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd('settings');
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    private function settings(ilPropertyFormGUI $form = null): void
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initSettingsForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    private function initSettingsForm(): ilPropertyFormGUI
    {
        global $DIC;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveSettings'));

        // My Staff
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('orgu_enable_my_staff'));
        $form->addItem($section);

        $item = new ilCheckboxInputGUI($this->lng->txt("orgu_enable_my_staff"), "enable_my_staff");
        $item->setInfo($this->lng->txt("orgu_enable_my_staff_info"));
        $item->setValue("1");
        $item->setChecked(($DIC->settings()->get("enable_my_staff") ? true : false));
        $form->addItem($item);

        // Positions in Modules
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('orgu_global_set_positions'));
        $form->addItem($section);

        $objDefinition = $DIC['objDefinition'];
        $available_types = $objDefinition->getOrgUnitPermissionTypes();
        foreach ($available_types as $object_type) {
            $setting = new ilOrgUnitObjectTypePositionSetting($object_type);
            $is_multi = false;

            if ($objDefinition->isPlugin($object_type)) {
                $label = ilObjectPlugin::lookupTxtById($object_type, 'objs_' . $object_type);
            } else {
                $is_multi = !$objDefinition->isSystemObject($object_type) && $object_type != ilOrgUnitOperationContext::CONTEXT_ETAL;
                $lang_prefix = $is_multi ? 'objs_' : 'obj_';
                $label = $this->lng->txt($lang_prefix . $object_type);
            }

            $type = new ilCheckboxInputGUI(
                $this->lng->txt('orgu_global_set_positions_type_active') . ' ' . $label,
                $object_type . '_active'
            );
            $type->setValue(1);
            $type->setChecked($setting->isActive());
            if ($is_multi) {
                $scope = new ilRadioGroupInputGUI(
                    $this->lng->txt('orgu_global_set_type_changeable'),
                    $object_type . '_changeable'
                );
                $scope->setValue((int) $setting->isChangeableForObject());

                $scope_object = new ilRadioOption(
                    $this->lng->txt('orgu_global_set_type_changeable_object'),
                    1
                );
                $default = new ilCheckboxInputGUI(
                    $this->lng->txt('orgu_global_set_type_default'),
                    $object_type . '_default'
                );
                $default->setInfo($this->lng->txt('orgu_global_set_type_default_info'));
                $default->setValue(ilOrgUnitObjectTypePositionSetting::DEFAULT_ON);
                $default->setChecked($setting->getActivationDefault());

                $scope_object->addSubItem($default);
                $scope->addOption($scope_object);

                $scope_global = new ilRadioOption(
                    $this->lng->txt('orgu_global_set_type_changeable_no'),
                    0
                );
                $scope->addOption($scope_global);

                $type->addSubItem($scope);
            }
            $form->addItem($type);
        }
        $form->addCommandButton('saveSettings', $this->lng->txt('save'));

        return $form;
    }

    private function saveSettings(): void
    {
        global $DIC;
        $objDefinition = $DIC['objDefinition'];
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            // Orgu Permissions / Positions in Modules
            $available_types = $objDefinition->getOrgUnitPermissionTypes();
            foreach ($available_types as $object_type) {
                $obj_setting = new ilOrgUnitObjectTypePositionSetting($object_type);
                $obj_setting->setActive((bool) $form->getInput($object_type . '_active'));
                $obj_setting->setActivationDefault((int) $form->getInput($object_type . '_default'));
                $obj_setting->setChangeableForObject((bool) $form->getInput($object_type
                    . '_changeable'));
                $obj_setting->update();
            }

            // MyStaff
            $DIC->settings()->set("enable_my_staff", (int) $form->getInput('enable_my_staff'));

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'settings');
        } else {
            $form->setValuesByPost();
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'), false);
            $this->settings($form);
        }
    }
}
