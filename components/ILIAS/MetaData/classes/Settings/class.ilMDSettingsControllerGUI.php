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

class ilMDSettingsControllerGUI
{
    protected const OER_SETTINGS_TAB = 'md_oer_settings';
    protected const COPYRIGHT_CONFIG_TAB = 'md_copyright_config';
    protected const VOCABULARIES_TAB = 'md_vocabularies';

    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs_gui;
    protected ilMDSettingsAccessService $access_service;
    protected ilObjMDSettingsGUI $parent_obj_gui;

    public function __construct(ilObjMDSettingsGUI $parent_obj_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tabs_gui = $DIC->tabs();
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

        $this->setTabs();

        if (
            !$this->access_service->hasCurrentUserVisibleAccess() ||
            !$this->access_service->hasCurrentUserReadAccess()
        ) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {
            case strtolower(ilMDCopyrightConfigurationGUI::class):
                $this->tabs_gui->activateSubTab(self::COPYRIGHT_CONFIG_TAB);
                $gui = new ilMDCopyrightConfigurationGUI($this->parent_obj_gui);
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ilMDOERSettingsGUI::class):
                $this->tabs_gui->activateSubTab(self::OER_SETTINGS_TAB);
                $gui = new ilMDOERSettingsGUI($this->parent_obj_gui);
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ilMDVocabulariesGUI::class):
                $this->tabs_gui->activateSubTab(self::VOCABULARIES_TAB);
                $gui = new ilMDVocabulariesGUI($this->parent_obj_gui);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $this->tabs_gui->activateSubTab(self::OER_SETTINGS_TAB);
                $this->ctrl->redirectByClass(
                    ilMDOERSettingsGUI::class,
                    'showOERSettings'
                );
                break;
        }
    }

    protected function setTabs(): void
    {
        if (
            !$this->access_service->hasCurrentUserVisibleAccess() ||
            !$this->access_service->hasCurrentUserReadAccess()
        ) {
            return;
        }

        $this->tabs_gui->addSubTab(
            self::OER_SETTINGS_TAB,
            $this->lng->txt('settings'),
            $this->ctrl->getLinkTargetByClass(
                ilMDOERSettingsGUI::class,
                'showOERSettings'
            )
        );

        $this->tabs_gui->addSubTab(
            self::COPYRIGHT_CONFIG_TAB,
            $this->lng->txt('md_copyright_selection'),
            $this->ctrl->getLinkTargetByClass(
                ilMDCopyrightConfigurationGUI::class,
                'showCopyrightSelection'
            )
        );

        $this->tabs_gui->addSubTab(
            self::VOCABULARIES_TAB,
            $this->lng->txt('md_vocabularies_config'),
            $this->ctrl->getLinkTargetByClass(
                ilMDVocabulariesGUI::class,
                'showVocabularies'
            )
        );
    }
}
