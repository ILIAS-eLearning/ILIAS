<?php

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;

/**
 * Class ilOrgUnitDefaultPermissionGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @ilCtrl_IsCalledBy ilOrgUnitDefaultPermissionGUI: ilOrgUnitPositionGUI
 */
class ilOrgUnitDefaultPermissionGUI extends BaseCommands
{
    private \ilGlobalTemplateInterface $main_tpl;
    private \ilObjectDefinition $objectDefintion;
    private \ILIAS\HTTP\Services $http;
    private \ilCtrlInterface $ctrl;
    private \ilLanguage $lng;

    public function __construct()
    {
        global $DIC;
        
        parent::__construct();
        
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->objectDefintion = $DIC["objDefinition"];
        $this->http = $DIC->http();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
    }

    protected function index() : void
    {
        $this->getParentGui()->addSubTabs();
        $this->getParentGui()->activeSubTab(ilOrgUnitPositionGUI::SUBTAB_PERMISSIONS);
        $ilOrgUnitPermissions = ilOrgUnitPermissionQueries::getAllTemplateSetsForAllActivedContexts($this->getCurrentPositionId());
        $ilOrgUnitDefaultPermissionFormGUI = new ilOrgUnitDefaultPermissionFormGUI(
            $this,
            $ilOrgUnitPermissions,
            $this->objectDefintion
        );
        $ilOrgUnitDefaultPermissionFormGUI->fillForm();

        $this->setContent($ilOrgUnitDefaultPermissionFormGUI->getHTML());
    }

    protected function update(): void
    {
        $this->getParentGui()->addSubTabs();
        $ilOrgUnitPermissions = ilOrgUnitPermissionQueries::getAllTemplateSetsForAllActivedContexts($this->getCurrentPositionId(), true);
        $ilOrgUnitDefaultPermissionFormGUI = new ilOrgUnitDefaultPermissionFormGUI(
            $this,
            $ilOrgUnitPermissions,
            $this->objectDefintion
        );
        if ($ilOrgUnitDefaultPermissionFormGUI->saveObject()) {
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('msg_success_permission_saved'), true);
            $this->cancel();
        }

        $this->setContent($ilOrgUnitDefaultPermissionFormGUI->getHTML());
    }

    protected function getCurrentPositionId(): int
    {
        static $id;
        if (!$id) {
            $id =  $this->http->request()->getQueryParams()['arid'];
        }

        return (int) $id;
    }

    protected function cancel(): void
    {
        $this->ctrl->redirectByClass(ilOrgUnitPositionGUI::class);
    }
}
