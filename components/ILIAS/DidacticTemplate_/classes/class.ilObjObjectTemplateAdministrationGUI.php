<?php

declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Didactic Template administration gui
 * @author            Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_Calls      ilObjObjectTemplateAdministrationGUI: ilPermissionGUI, ilDidacticTemplateSettingsGUI
 * @ilCtrl_IsCalledBy ilObjObjectTemplateAdministrationGUI: ilAdministrationGUI
 * @ingroup           ServicesPortfolio
 */
class ilObjObjectTemplateAdministrationGUI extends ilObjectGUI
{
    public function __construct($a_data, $a_id, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        $this->type = "otpl";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("didactic");
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $this->prepareOutput();
        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ildidactictemplatesettingsgui':
                $this->tabs_gui->activateTab('didactic_adm_tab');
                $did = new ilDidacticTemplateSettingsGUI($this);
                $this->ctrl->forwardCommand($did);
                break;

            default:
                $this->tabs_gui->activateTab('didactic_adm_tab');
                $this->ctrl->redirectByClass('ildidactictemplatesettingsgui');
                break;
        }
    }

    public function getAdminTabs(): void
    {
        if ($this->checkPermissionBool('write')) {
            $this->lng->loadLanguageModule('didactic');
            $this->tabs_gui->addTarget(
                'didactic_adm_tab',
                $this->ctrl->getLinkTargetByClass('ildidactictemplatesettingsgui', 'overview')
            );
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                [],
                'ilpermissiongui'
            );
        }
    }
}
