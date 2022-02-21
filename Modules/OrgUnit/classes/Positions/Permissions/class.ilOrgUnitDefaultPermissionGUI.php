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
    private \ilGlobalTemplateInterface $main_tpl;
    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }
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
            $this->main_tpl->setOnScreenMessage('success', $this->txt('msg_success_permission_saved'), true);
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
