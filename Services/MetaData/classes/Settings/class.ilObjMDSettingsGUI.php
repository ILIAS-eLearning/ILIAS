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

/**
 * @author       Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls ilObjMDSettingsGUI: ilPermissionGUI, ilAdvancedMDSettingsGUI,
 * @ilCtrl_Calls ilObjMDSettingsGUI: ilMDCopyrightSelectionGUI
 */
class ilObjMDSettingsGUI extends ilObjectGUI
{
    protected ?ilMDSettings $md_settings = null;
    protected ilMDSettingsAccessService $access_service;

    public function __construct(
        $data,
        int $id = 0,
        bool $call_by_reference = true,
        bool $prepare_output = true
    ) {
        parent::__construct($data, $id, $call_by_reference, $prepare_output);

        $this->access_service = new ilMDSettingsAccessService(
            $this->object->getRefId(),
            $this->access
        );

        $this->type = 'mds';
        $this->lng->loadLanguageModule("meta");
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (
            !$this->access_service->hasCurrentUserVisibleAccess() ||
            !$this->access_service->hasCurrentUserReadAccess()
        ) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {
            case strtolower(ilMDCopyrightSelectionGUI::class):
                $gui = new ilMDCopyrightSelectionGUI($this);
                $ret = $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ilAdvancedMDSettingsGUI::class):
                $this->tabs_gui->setTabActive('md_advanced');
                $adv_md = new ilAdvancedMDSettingsGUI(
                    ilAdvancedMDSettingsGUI::CONTEXT_ADMINISTRATION,
                    $this->ref_id
                );
                $ret = $this->ctrl->forwardCommand($adv_md);
                break;

            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->setTabActive('perm_settings');

                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = "showGeneralSettings";
                }

                $this->$cmd();
                break;
        }
    }

    protected function getType(): string
    {
        return $this->type;
    }

    protected function getParentObjType(): string
    {
        return 'meta';
    }

    public function getAdminTabs(): void
    {
        if (
            $this->access_service->hasCurrentUserVisibleAccess() &&
            $this->access_service->hasCurrentUserReadAccess()
        ) {
            $this->tabs_gui->addTab(
                'md_general_settings',
                $this->lng->txt('md_general_settings'),
                $this->ctrl->getLinkTarget($this, 'showGeneralSettings')
            );

            $this->tabs_gui->addTab(
                'md_copyright',
                $this->lng->txt('md_copyright'),
                $this->ctrl->getLinkTargetByClass(
                    ilMDCopyrightSelectionGUI::class,
                    'showCopyrightSettings'
                )
            );

            $this->tabs_gui->addTab(
                'md_advanced',
                $this->lng->txt('md_advanced'),
                $this->ctrl->getLinkTargetByClass(ilAdvancedMDSettingsGUI::class, '')
            );
        }

        if ($this->access_service->hasCurrentUserPermissionsAccess()) {
            $this->tabs_gui->addTab(
                'perm_settings',
                $this->lng->txt('perm_settings'),
                $this->ctrl->getLinkTargetByClass(ilPermissionGUI::class, 'perm')
            );
        }
    }

    /**
     * TODO: get rid of this and the customizable delimiter for authors/keywords
     *  with the new MD editor
     */
    public function showGeneralSettings(?ilPropertyFormGUI $form = null): void
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initGeneralSettingsForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function initGeneralSettingsForm(string $a_mode = "edit"): ilPropertyFormGUI
    {
        $this->tabs_gui->setTabActive('md_general_settings');
        $form = new ilPropertyFormGUI();
        $ti = new ilTextInputGUI($this->lng->txt("md_delimiter"), "delimiter");
        $ti->setInfo($this->lng->txt("md_delimiter_info"));
        $ti->setMaxLength(1);
        $ti->setSize(1);
        $ti->setValue($this->MDSettings()->getDelimiter());
        $form->addItem($ti);

        if ($this->access_service->hasCurrentUserWriteAccess()) {
            $form->addCommandButton("saveGeneralSettings", $this->lng->txt("save"));
        }
        $form->setTitle($this->lng->txt("md_general_settings"));
        $form->setFormAction($this->ctrl->getFormAction($this));
        return $form;
    }

    public function saveGeneralSettings(): void
    {
        if (!$this->access_service->hasCurrentUserWriteAccess()) {
            $this->ctrl->redirect($this, "showGeneralSettings");
        }
        $form = $this->initGeneralSettingsForm();
        if ($form->checkInput()) {
            $delim = $form->getInput('delimiter');
            $delim = (
                trim($delim) === '' ?
                ',' :
                trim($delim)
            );
            $this->MDSettings()->setDelimiter($delim);
            $this->MDSettings()->save();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, "showGeneralSettings");
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'), true);
        $form->setValuesByPost();
        $this->showGeneralSettings($form);
    }

    protected function MDSettings(): ilMDSettings
    {
        if (!isset($this->md_settings)) {
            $this->md_settings = ilMDSettings::_getInstance();
        }
        return $this->md_settings;
    }
}
