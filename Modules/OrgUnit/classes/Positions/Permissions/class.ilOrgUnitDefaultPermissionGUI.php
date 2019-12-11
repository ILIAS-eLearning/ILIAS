<?php

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;

/**
 * Class ilOrgUnitDefaultPermissionGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilOrgUnitDefaultPermissionGUI: ilOrgUnitPositionGUI
 */
class ilOrgUnitDefaultPermissionGUI extends BaseCommands
{
    protected function index()
    {
        $this->getParentGui()->addSubTabs();
        $this->getParentGui()->activeSubTab(ilOrgUnitPositionGUI::SUBTAB_PERMISSIONS);
        $ilOrgUnitPermissions = ilOrgUnitPermissionQueries::getAllTemplateSetsForAllActivedContexts($this->getCurrentPositionId());
        $ilOrgUnitDefaultPermissionFormGUI = new ilOrgUnitDefaultPermissionFormGUI($this, $ilOrgUnitPermissions);
        $ilOrgUnitDefaultPermissionFormGUI->fillForm();

        $this->setContent($ilOrgUnitDefaultPermissionFormGUI->getHTML());
    }


    protected function update()
    {
        $this->getParentGui()->addSubTabs();
        $ilOrgUnitPermissions = ilOrgUnitPermissionQueries::getAllTemplateSetsForAllActivedContexts($this->getCurrentPositionId(), true);
        $ilOrgUnitDefaultPermissionFormGUI = new ilOrgUnitDefaultPermissionFormGUI($this, $ilOrgUnitPermissions);
        if ($ilOrgUnitDefaultPermissionFormGUI->saveObject()) {
            ilUtil::sendSuccess($this->txt('msg_success_permission_saved'), true);
            $this->cancel();
        }

        $this->setContent($ilOrgUnitDefaultPermissionFormGUI->getHTML());
    }


    /**
     * @return int
     */
    protected function getCurrentPositionId()
    {
        static $id;
        if (!$id) {
            $id = $this->dic()->http()->request()->getQueryParams()['arid'];
        }

        return (int) $id;
    }

    protected function cancel()
    {
        $this->ctrl()->redirectByClass(ilOrgUnitPositionGUI::class);
    }
}
