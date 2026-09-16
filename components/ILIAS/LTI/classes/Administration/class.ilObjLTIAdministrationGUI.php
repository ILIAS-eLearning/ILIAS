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
    private const CMD_SHOW_GLOBAL_PROVIDERS = "showGlobalProviders";
    private const CMD_SHOW_USER_PROVIDERS = "showUserProviders";
    private const CMD_SHOW_USAGES = "showUsages";

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
                match ($this->ctrl->getCmd()) {
                    self::CMD_SHOW_USER_PROVIDERS => $this->showProviders(false),
                    self::CMD_SHOW_USAGES => $this->showUsages(),
                    default => $this->showProviders(true),
                };
        }
    }

    public function getAdminTabs(): void
    {
        $this->tabs_gui->addTab(
            "lti_consuming",
            $this->lng->txt("lti_consuming_tab"),
            $this->ctrl->getLinkTarget($this, self::CMD_SHOW_GLOBAL_PROVIDERS)
        );

        if ($this->rbac_system->checkAccess("edit_permission", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass(ilPermissionGUI::class, "perm")
            );
        }
    }

    private function showProviders(bool $global): void
    {
        global $DIC;

        $cmd = $global ? self::CMD_SHOW_GLOBAL_PROVIDERS : self::CMD_SHOW_USER_PROVIDERS;
        $this->activateConsumerSubTab($global ? "global_provider" : "user_provider");

        $table = new ilLTIAdministrationConsumerProviderTable(
            $DIC->database(),
            $this->lng,
            $DIC->user(),
            $DIC->ui()->factory(),
            $DIC->ui()->renderer(),
            $DIC->uiService(),
            $this->tpl,
            $this->ctrl,
            $DIC->http()->request(),
            $global,
            $this->checkPermissionBool("write")
        );
        $table->handleAction($this, $cmd);

        $filter = $table->getFilter($this->ctrl->getLinkTarget($this, $cmd));
        $this->tpl->setContent($DIC->ui()->renderer()->render([$filter, $table->getTable($filter)]));
    }

    private function showUsages(): void
    {
        global $DIC;

        $this->activateConsumerSubTab("usage");

        $table = new ilLTIAdministrationConsumerUsageTable(
            $DIC->database(),
            $this->lng,
            $DIC->ui()->factory(),
            $DIC->uiService(),
            $DIC["static_url"]
        );
        $filter = $table->getFilter($this->ctrl->getLinkTarget($this, self::CMD_SHOW_USAGES));
        $this->tpl->setContent($DIC->ui()->renderer()->render([$filter, $table->getTable($filter, $DIC->http()->request())]));
    }

    private function activateConsumerSubTab(string $sub_tab): void
    {
        $this->tabs_gui->activateTab("lti_consuming");
        $sub_tabs = [
            "global_provider" => self::CMD_SHOW_GLOBAL_PROVIDERS,
            "user_provider" => self::CMD_SHOW_USER_PROVIDERS,
            "usage" => self::CMD_SHOW_USAGES,
        ];
        foreach ($sub_tabs as $id => $cmd) {
            $this->tabs_gui->addSubTab($id, $this->lng->txt($id . "_subtab"), $this->ctrl->getLinkTarget($this, $cmd));
        }
        $this->tabs_gui->activateSubTab($sub_tab);
    }
}
