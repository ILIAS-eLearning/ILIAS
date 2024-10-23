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

        if (
            !$this->access_service->hasCurrentUserVisibleAccess() ||
            !$this->access_service->hasCurrentUserReadAccess()
        ) {
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
            $this->MDSettings()->activateOAIPMH((bool) $form->getInput('oai_active'));
            $this->MDSettings()->saveOAIRepositoryName((string) $form->getInput('oai_repository_name'));
            $this->MDSettings()->saveOAIIdentifierPrefix((string) $form->getInput('oai_identifier_prefix'));
            $this->MDSettings()->saveOAIContactMail((string) $form->getInput('oai_contact_mail'));
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
        $form->setTitle($this->lng->txt('md_copyright_settings'));

        if ($this->access_service->hasCurrentUserWriteAccess()) {
            $form->addCommandButton('saveOERSettings', $this->lng->txt('save'));
        }

        $check = new ilCheckboxInputGUI($this->lng->txt('md_copyright_enabled'), 'active');
        $check->setChecked($this->MDSettings()->isCopyrightSelectionActive());
        $check->setValue('1');
        $check->setInfo($this->lng->txt('md_copyright_enable_info'));
        $form->addItem($check);

        ilAdministrationSettingsFormHandler::addFieldsToForm(
            $this->getAdministrationFormId(),
            $form,
            $this->parent_obj_gui
        );

        $oai_check = new ilCheckboxInputGUI($this->lng->txt('md_oai_pmh_enabled'), 'oai_active');
        $oai_check->setChecked($this->MDSettings()->isOAIPMHActive());
        $oai_check->setValue('1');
        $oai_check->setInfo($this->lng->txt('md_oai_pmh_enabled_info'));
        $form->addItem($oai_check);

        $oai_repo_name = new ilTextInputGUI($this->lng->txt('md_oai_repository_name'), 'oai_repository_name');
        $oai_repo_name->setValue($this->MDSettings()->getOAIRepositoryName());
        $oai_repo_name->setInfo($this->lng->txt('md_oai_repository_name_info'));
        $oai_repo_name->setRequired(true);
        $oai_check->addSubItem($oai_repo_name);

        $oai_id_prefix = new ilTextInputGUI($this->lng->txt('md_oai_identifier_prefix'), 'oai_identifier_prefix');
        $oai_id_prefix->setValue($this->MDSettings()->getOAIIdentifierPrefix());
        $oai_id_prefix->setInfo($this->lng->txt('md_oai_identifier_prefix_info'));
        $oai_id_prefix->setRequired(true);
        $oai_check->addSubItem($oai_id_prefix);

        $oai_contact_mail = new ilTextInputGUI($this->lng->txt('md_oai_contact_mail'), 'oai_contact_mail');
        $oai_contact_mail->setValue($this->MDSettings()->getOAIContactMail());
        $oai_contact_mail->setRequired(true);
        $oai_check->addSubItem($oai_contact_mail);

        return $form;
    }

    protected function MDSettings(): ilMDSettings
    {
        if (!isset($this->md_settings)) {
            $this->md_settings = ilMDSettings::_getInstance();
        }
        return $this->md_settings;
    }

    protected function getAdministrationFormId(): int
    {
        return ilAdministrationSettingsFormHandler::FORM_META_COPYRIGHT;
    }
}
