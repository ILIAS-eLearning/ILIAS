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

declare(strict_types=1);

class ilMDOERSettingsGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilObjMDSettingsGUI $parent_obj_gui;
    protected ilMDSettingsAccessService $access_service;

    protected ?ilMDSettings $md_settings = null;

    public function __construct(ilObjMDSettingsGUI $parent_obj_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();

        $this->parent_obj_gui = $parent_obj_gui;
        $this->access_service = new ilMDSettingsAccessService(
            $this->parent_obj_gui->getRefId(),
            $DIC->access()
        );

        $this->lng->loadLanguageModule("meta");
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (!$this->access_service->hasCurrentUserReadAccess()) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {
            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = 'showOERSettings';
                }

                $this->$cmd();
                break;
        }
    }

    public function showOERSettings(?ilPropertyFormGUI $form = null): void
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initSettingsForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function saveOERSettings(): void
    {
        if (!$this->access_service->hasCurrentUserWriteAccess()) {
            $this->ctrl->redirect($this, "showOERSettings");
        }
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $this->MDSettings()->activateCopyrightSelection((bool) $form->getInput('active'));
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'showOERSettings');
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'), true);
        $form->setValuesByPost();
        $this->showOERSettings($form);
    }

    protected function initSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        if ($this->access_service->hasCurrentUserWriteAccess()) {
            $form->addCommandButton('saveOERSettings', $this->lng->txt('save'));
        }

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt('md_settings_licence'));
        $form->addItem($header);

        $check = new ilCheckboxInputGUI($this->lng->txt('md_copyright_enabled'), 'active');
        $check->setChecked($this->MDSettings()->isCopyrightSelectionActive());
        $check->setValue('1');
        $check->setInfo($this->lng->txt('md_copyright_enable_info'));
        $form->addItem($check);

        return $form;
    }

    protected function MDSettings(): ilMDSettings
    {
        if (!isset($this->md_settings)) {
            $this->md_settings = ilMDSettings::_getInstance();
        }
        return $this->md_settings;
    }
}
