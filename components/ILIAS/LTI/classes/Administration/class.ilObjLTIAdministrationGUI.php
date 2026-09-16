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
 * Administration > LTI.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 *
 * @ilCtrl_isCalledBy ilObjLTIAdministrationGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjLTIAdministrationGUI: ilPermissionGUI
 */
class ilObjLTIAdministrationGUI extends ilObjectGUI
{
    public function __construct(?array $a_data, int $a_id, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        $this->type = "ltis";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng->loadLanguageModule("lti");
    }

    public function executeCommand(): void
    {
        $this->checkPermission("read");
        $this->prepareOutput();

        switch ($this->ctrl->getNextClass($this)) {
            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->activateTab("perm_settings");
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                break;

            default:
                $this->ctrl->redirectByClass(ilPermissionGUI::class, "perm");
        }
    }

    public function getAdminTabs(): void
    {
        if ($this->rbac_system->checkAccess("edit_permission", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass(ilPermissionGUI::class, "perm")
            );
        }
    }
}
