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

use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\UI\Component\Component;

/**
 * Administration > LTI: platforms that launch ILIAS and their released objects, providers of external
 * tools and their usages. The screens serve both LTI 1.1 and LTI Advantage.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 *
 * @ilCtrl_isCalledBy ilObjLTIAdministrationGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjLTIAdministrationGUI: ilPermissionGUI
 */
class ilObjLTIAdministrationGUI extends ilObjectGUI
{
    private const string CMD_LIST_PLATFORMS = "listConsumers";
    private const string CMD_CREATE_PLATFORM = "createConsumer";
    private const string CMD_EDIT_PLATFORM = "editConsumer";
    private const string CMD_SAVE_PLATFORM = "saveConsumer";
    private const string CMD_CREATE_USER_ROLE = "createLtiUserRole";
    private const string CMD_SHOW_RELEASED_OBJECTS = "releasedObjects";
    private const string CMD_SHOW_GLOBAL_PROVIDERS = "showGlobalProviders";
    private const string CMD_SHOW_USER_PROVIDERS = "showUserProviders";
    private const string CMD_SHOW_USAGES = "showUsages";
    private const string CMD_CREATE_PROVIDER = "createProvider";
    private const string CMD_EDIT_PROVIDER = "editProvider";
    private const string CMD_SAVE_PROVIDER = "saveProvider";
    private const string VERSION_PARAM = "version";
    private const string VERSION_1P1 = "1p1";
    private const string VERSION_ADVANTAGE = "advantage";
    private const string LTI_USER_ROLE = "il_lti_global_role";
    private const string LTI_1P1_DEPRECATION_URL = "https://www.1edtech.org/lti-security-announcement-and-deprecation-schedule";

    public function __construct(?array $a_data, int $a_id, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        $this->type = "ltis";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng->loadLanguageModule("lti");
    }

    /**
     * @throws ilCtrlException
     */
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
                    self::CMD_CREATE_PLATFORM, self::CMD_EDIT_PLATFORM => $this->showPlatformForm(),
                    self::CMD_SAVE_PLATFORM => $this->savePlatform(),
                    self::CMD_CREATE_USER_ROLE => $this->createLtiUserRole(),
                    self::CMD_SHOW_RELEASED_OBJECTS => $this->showReleasedObjects(),
                    self::CMD_SHOW_GLOBAL_PROVIDERS => $this->showProviders(true),
                    self::CMD_SHOW_USER_PROVIDERS => $this->showProviders(false),
                    self::CMD_SHOW_USAGES => $this->showUsages(),
                    self::CMD_CREATE_PROVIDER, self::CMD_EDIT_PROVIDER => $this->showProviderForm(),
                    self::CMD_SAVE_PROVIDER => $this->saveProvider(),
                    default => $this->showPlatforms(),
                };
        }
    }

    /**
     * @throws ilCtrlException
     */
    public function getAdminTabs(): void
    {
        $this->tabs_gui->addTab(
            "lti_providing",
            $this->lng->txt("lti_providing_tab"),
            $this->ctrl->getLinkTarget($this, self::CMD_LIST_PLATFORMS)
        );
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

    /**
     * @throws ilCtrlException
     */
    private function showPlatforms(): void
    {
        global $DIC;

        $this->activateProviderSubTab("consumers");

        $table = new ilLTIAdministrationProviderPlatformTable(
            $DIC->database(),
            $this->lng,
            $DIC->ui()->factory(),
            $DIC->ui()->renderer(),
            $this->tpl,
            $this->ctrl,
            $this->request,
            $this->checkPermissionBool("write")
        );
        $table->handleAction($this, self::CMD_LIST_PLATFORMS, self::CMD_EDIT_PLATFORM);

        $content = [];
        if ($this->checkPermissionBool("write")) {
            $this->toolbar->addComponent($this->getCreateDropdown("lti_create_consumer", self::CMD_CREATE_PLATFORM));
            if (ilObject::_getIdsForTitle(self::LTI_USER_ROLE, "role") === []) {
                $factory = $DIC->ui()->factory();
                $content[] = $factory->messageBox()->info($this->lng->txt("lti_user_role_info"))->withButtons([
                    $factory->button()->standard(
                        $this->lng->txt("lti_create_lti_user_role"),
                        $this->ctrl->getLinkTarget($this, self::CMD_CREATE_USER_ROLE)
                    ),
                ]);
            }
        }
        $content[] = $table->getTable();

        $this->tpl->setContent($DIC->ui()->renderer()->render($content));
    }

    /**
     * @throws ilCtrlException
     */
    private function showPlatformForm(?Form $form = null): void
    {
        global $DIC;

        $this->checkPermission("write");
        $this->activateProviderSubTab("consumers");

        $platform_form = $this->getPlatformForm();
        $form ??= $platform_form->getForm($this->getPlatformFormAction());
        $this->tpl->setContent($DIC->ui()->renderer()->render($this->withDeprecationInfo($form, $platform_form->isAdvantage())));
    }

    /**
     * @throws ilCtrlException
     */
    private function savePlatform(): void
    {
        $this->checkPermission("write");

        $form = $this->getPlatformForm()->save($this->getPlatformFormAction(), $this->request);
        if ($form !== null) {
            $this->showPlatformForm($form);
            return;
        }

        $this->tpl->setOnScreenMessage(
            "success",
            $this->lng->txt($this->getPlatformId() > 0 ? "lti_consumer_updated" : "lti_consumer_created"),
            true
        );
        $this->ctrl->clearParameterByClass(self::class, "cid");
        $this->ctrl->clearParameterByClass(self::class, self::VERSION_PARAM);
        $this->ctrl->redirect($this, self::CMD_LIST_PLATFORMS);
    }

    private function getPlatformForm(): ilLTIAdministrationProviderPlatformForm
    {
        global $DIC;

        $platform_id = $this->getIntParameter("cid");
        $version = $platform_id > 0
            ? ilLTIAdministrationProviderPlatformForm::lookupVersion($DIC->database(), $platform_id)
            : ($this->isAdvantageRequested()
                ? ilLTIAdministrationProviderPlatformForm::VERSION_ADVANTAGE
                : ilLTIAdministrationProviderPlatformForm::VERSION_1P1);

        return new ilLTIAdministrationProviderPlatformForm(
            $DIC->database(),
            $this->lng,
            $DIC->ui()->factory(),
            $this->obj_definition,
            $this->rbac_review,
            $platform_id,
            $version
        );
    }

    /**
     * @throws ilCtrlException
     */
    private function getPlatformFormAction(): string
    {
        $this->keepFormParameters("cid");

        return $this->ctrl->getFormAction($this, self::CMD_SAVE_PLATFORM);
    }

    private function getPlatformId(): int
    {
        return $this->getIntParameter("cid");
    }

    /**
     * @throws ilCtrlException
     */
    private function showProviderForm(?Form $form = null): void
    {
        global $DIC;

        $this->checkPermission("write");
        $provider_id = $this->getIntParameter("provider_id");
        $global = $provider_id === 0 || ($DIC->database()->fetchAssoc($DIC->database()->query(
            "SELECT global FROM lti_ext_provider WHERE id = " . $DIC->database()->quote($provider_id, "integer")
        ))["global"] ?? true);
        $this->activateConsumerSubTab($global ? "global_provider" : "user_provider");

        $provider_form = $this->getProviderForm();
        $form ??= $provider_form->getForm($this->getProviderFormAction());
        $this->tpl->setContent($DIC->ui()->renderer()->render($this->withDeprecationInfo($form, $provider_form->isAdvantage())));
    }

    /**
     * @throws ilCtrlException
     */
    private function saveProvider(): void
    {
        $this->checkPermission("write");

        $form = $this->getProviderForm()->save($this->getProviderFormAction(), $this->request);
        if ($form !== null) {
            $this->showProviderForm($form);
            return;
        }

        $this->tpl->setOnScreenMessage("success", $this->lng->txt("settings_saved"), true);
        $this->ctrl->clearParameterByClass(self::class, "provider_id");
        $this->ctrl->clearParameterByClass(self::class, self::VERSION_PARAM);
        $this->ctrl->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDERS);
    }

    private function getProviderForm(): ilLTIAdministrationConsumerProviderForm
    {
        global $DIC;

        $provider_id = $this->getIntParameter("provider_id");
        $version = $provider_id > 0
            ? ilLTIAdministrationConsumerProviderForm::lookupVersion($DIC->database(), $provider_id)
            : ($this->isAdvantageRequested()
                ? ilLTIAdministrationConsumerProviderForm::VERSION_ADVANTAGE
                : ilLTIAdministrationConsumerProviderForm::VERSION_1P1);

        return new ilLTIAdministrationConsumerProviderForm(
            $DIC->database(),
            $this->lng,
            $DIC->ui()->factory(),
            $this->refinery,
            $DIC->user(),
            $provider_id,
            $version
        );
    }

    /**
     * @throws ilCtrlException
     */
    private function getProviderFormAction(): string
    {
        $this->keepFormParameters("provider_id");

        return $this->ctrl->getFormAction($this, self::CMD_SAVE_PROVIDER);
    }

    /**
     * Dropdown to create an LTI Advantage or an LTI 1.1 entry, with LTI 1.1 marked as deprecated.
     *
     * @throws ilCtrlException
     */
    private function getCreateDropdown(string $label, string $cmd): Component
    {
        global $DIC;

        $factory = $DIC->ui()->factory();
        $items = [];
        foreach ([self::VERSION_ADVANTAGE => "lti_version_advantage", self::VERSION_1P1 => "lti_version_1p1_deprecated"] as $version => $txt) {
            $this->ctrl->setParameter($this, self::VERSION_PARAM, $version);
            $items[] = $factory->button()->shy($this->lng->txt($txt), $this->ctrl->getLinkTarget($this, $cmd));
        }
        $this->ctrl->clearParameterByClass(self::class, self::VERSION_PARAM);

        return $factory->dropdown()->standard($items)->withLabel($this->lng->txt($label));
    }

    /**
     * @param Form $form
     * @param bool $advantage
     * @return array
     */
    private function withDeprecationInfo(Form $form, bool $advantage): array
    {
        global $DIC;

        if ($advantage) {
            return [$form];
        }

        $factory = $DIC->ui()->factory();
        $link = $factory->link()->standard($this->lng->txt("lti_1p1_deprecated_link"), self::LTI_1P1_DEPRECATION_URL)
            ->withOpenInNewViewport(true);

        return [
            $factory->messageBox()->confirmation($this->lng->txt("lti_1p1_deprecated_info"))->withLinks([$link]),
            $form,
        ];
    }

    /**
     * Keeps the id of the edited entry, or the requested version of a new one, in the form action.
     * @throws ilCtrlException
     */
    private function keepFormParameters(string $id_parameter): void
    {
        $id = $this->getIntParameter($id_parameter);
        if ($id > 0) {
            $this->ctrl->setParameter($this, $id_parameter, $id);
            return;
        }
        $this->ctrl->setParameter($this, self::VERSION_PARAM, $this->isAdvantageRequested() ? self::VERSION_ADVANTAGE : self::VERSION_1P1);
    }

    private function isAdvantageRequested(): bool
    {
        return $this->request_wrapper->retrieve(
            self::VERSION_PARAM,
            $this->refinery->byTrying([$this->refinery->kindlyTo()->string(), $this->refinery->always("")])
        ) === self::VERSION_ADVANTAGE;
    }

    private function getIntParameter(string $name): int
    {
        return $this->request_wrapper->retrieve(
            $name,
            $this->refinery->byTrying([$this->refinery->kindlyTo()->int(), $this->refinery->always(0)])
        );
    }

    /**
     * Creates the recommended global role for LTI users.
     * @throws ilCtrlException
     */
    private function createLtiUserRole(): void
    {
        $this->checkPermission("write");

        $role = new ilObjRole();
        $role->setTitle(self::LTI_USER_ROLE);
        $role->setDescription("This global role should only contain the permission 'read' for repository and categories. Do not rename this role.");
        $role->create();
        $this->rbac_admin->assignRoleToFolder($role->getId(), ROLE_FOLDER_ID);
        $this->rbac_admin->setProtected(ROLE_FOLDER_ID, $role->getId(), "y");
        $this->rbac_admin->setRolePermission($role->getId(), "root", [3], ROLE_FOLDER_ID);
        $this->rbac_admin->setRolePermission($role->getId(), "cat", [3], ROLE_FOLDER_ID);
        $this->rbac_admin->grantPermission($role->getId(), [3], ROOT_FOLDER_ID);
        $role->changeExistingObjects(ROOT_FOLDER_ID, ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES, ["cat"]);

        $this->tpl->setOnScreenMessage("success", $this->lng->txt("lti_user_role_created"), true);
        $this->ctrl->redirect($this, self::CMD_LIST_PLATFORMS);
    }

    /**
     * @throws ilCtrlException
     */
    private function showReleasedObjects(): void
    {
        global $DIC;

        $this->activateProviderSubTab("releasedObjects");

        $table = new ilLTIAdministrationProviderReleasedObjectTable(
            $DIC->database(),
            $this->lng,
            $DIC->ui()->factory(),
            $DIC["static_url"]
        );
        $this->tpl->setContent($DIC->ui()->renderer()->render($table->getTable($this->request)));
    }

    /**
     * @throws ilCtrlException
     */
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
            $this->request,
            $global,
            $this->checkPermissionBool("write")
        );
        $table->handleAction($this, $cmd, self::CMD_EDIT_PROVIDER);

        if ($global && $this->checkPermissionBool("write")) {
            $this->toolbar->addComponent($this->getCreateDropdown("lti_add_global_provider", self::CMD_CREATE_PROVIDER));
        }

        $filter = $table->getFilter($this->ctrl->getLinkTarget($this, $cmd));
        $this->tpl->setContent($DIC->ui()->renderer()->render([$filter, $table->getTable($filter)]));
    }

    /**
     * @throws ilCtrlException
     */
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
        $this->tpl->setContent($DIC->ui()->renderer()->render([$filter, $table->getTable($filter, $this->request)]));
    }

    /**
     * @throws ilCtrlException
     */
    private function activateProviderSubTab(string $sub_tab): void
    {
        $this->tabs_gui->activateTab("lti_providing");
        $this->tabs_gui->addSubTab("consumers", $this->lng->txt("consumers"), $this->ctrl->getLinkTarget($this, self::CMD_LIST_PLATFORMS));
        $this->tabs_gui->addSubTab(
            "releasedObjects",
            $this->lng->txt("lti_released_objects"),
            $this->ctrl->getLinkTarget($this, self::CMD_SHOW_RELEASED_OBJECTS)
        );
        $this->tabs_gui->activateSubTab($sub_tab);
    }

    /**
     * @throws ilCtrlException
     */
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
