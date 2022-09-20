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
 * Taxonomy Administration Settings
 * @author       Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilObjTaxonomyAdministrationGUI: ilPermissionGUI
 */
class ilObjTaxonomyAdministrationGUI extends ilObjectGUI
{
    protected ilRbacSystem $rbacsystem;

    /**
     * @inheritDoc
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->type = "taxs";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("tax");
    }

    /**
     * Execute command
     */
    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if ($next_class == 'ilpermissiongui') {
            $this->tabs_gui->activateTab('perm_settings');
            $perm_gui = new ilPermissionGUI($this);
            $this->ctrl->forwardCommand($perm_gui);
        } else {
            if (!$cmd || $cmd == 'view') {
                $cmd = "listRepository";
            }
            $this->$cmd();
        }
    }

    /**
     * Get tabs
     */
    public function getAdminTabs(): void
    {
        $rbacsystem = $this->rbacsystem;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("tax_admin_settings_repository"),
                $this->ctrl->getLinkTarget($this, "listRepository")
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }

    /**
     * List taxonomies of repository objects
     */
    public function listRepository(): void
    {
        $this->tabs_gui->activateTab('settings');
        $tbl = new ilTaxonomyAdministrationRepositoryTableGUI($this, "listRepository", $this->object);
        $this->tpl->setContent($tbl->getHTML());
    }
}
