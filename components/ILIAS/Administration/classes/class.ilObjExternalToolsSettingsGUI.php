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
 * Class ilObjExternalToolsSettingsGUI
 *
 * @author Sascha Hofmann <saschahofmann@gmx.de>
 * @ilCtrl_Calls ilObjExternalToolsSettingsGUI: ilPermissionGUI
 */
class ilObjExternalToolsSettingsGUI extends ilObjectGUI
{
    public const EDIT_WOPI = "editWopi";
    public ilRbacSystem $rbacsystem;
    public ilRbacReview $rbacreview;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
        $this->rbacreview = $DIC->rbac()->review();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $lng = $DIC->language();

        $this->type = "extt";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $lng->loadLanguageModule("delic");
        $lng->loadLanguageModule("maps");
        $lng->loadLanguageModule("mathjax");
        $lng->loadLanguageModule("wopi");
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    protected function getTabs(): void
    {
        $rbacsystem = $this->rbacsystem;

        $this->ctrl->setParameter($this, "ref_id", $this->object->getRefId());

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'wopi_settings',
                $this->ctrl->getLinkTargetByClass(ilWOPIAdministrationGUI::class)
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        switch ($next_class) {
            case strtolower(ilWOPIAdministrationGUI::class):
                $this->ctrl->forwardCommand(new ilWOPIAdministrationGUI());
                break;

            case 'ilpermissiongui':
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                $this->tabs_gui->setTabActive('perm_settings');
                break;
            default:
                $this->ctrl->redirectToURL($this->ctrl->getLinkTargetByClass(ilWOPIAdministrationGUI::class));
                break;
        }
    }
}
