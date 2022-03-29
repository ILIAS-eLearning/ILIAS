<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * @ilCtrl_Calls ilObjComponentSettingsGUI: ilPermissionGUI
 */
class ilObjComponentSettingsGUI extends ilObjectGUI
{
    private const TYPE = 'cmps';

    private const TAB_PLUGINS = "plugins";
    private const TAB_PERMISSION = "perm_settings";

    public const CMD_DEFAULT = "listPlugins";
    public const CMD_INSTALL_PLUGIN = "installPlugin";
    public const CMD_CONFIGURE = "configure";
    public const CMD_REFRESH_LANGUAGES = "refreshLanguages";
    public const CMD_ACTIVATE_PLUGIN = "activatePlugin";
    public const CMD_DEACTIVATE_PLUGIN = "deactivatePlugin";
    public const CMD_UPDATE_PLUGIN = "updatePlugin";
    public const CMD_JUMP_TO_PLUGIN_SLOT = "jumpToPluginSlot";
    public const CMD_UNINSTALL_PLUGIN = "uninstallPlugin";
    public const CMD_CONFIRM_UNINSTALL_PLUGIN = "confirmUninstallPlugin";

    public const P_REF_ID = 'ref_id';
    public const P_CTYPE = "ctype";
    public const P_CNAME = "cname";
    public const P_SLOT_ID = "slot_id";
    public const P_PLUGIN_NAME = "pname";
    public const P_PLUGIN_ID = "plugin_id";
    public const P_ADMIN_MODE = 'admin_mode';

    protected ilTabsGUI $tabs;
    protected ilRbacSystem $rbac_system;
    protected ilDBInterface $db;
    protected ilComponentRepository $component_repository;
    protected ilComponentFactory $component_factory;
    protected ilErrorHandling $error;
    protected ILIAS\Refinery\Factory $refinery;
    protected ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper;
    protected Factory $ui;
    protected Renderer $renderer;

    public function __construct($data, int $id, bool $call_by_reference = true, bool $prepare_output = true)
    {
        parent::__construct($data, $id, $call_by_reference, $prepare_output);

        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->rbac_system = $DIC->rbac()->system();
        $this->db = $DIC->database();
        $this->type = self::TYPE;
        $this->component_repository = $DIC["component.repository"];
        $this->component_factory = $DIC["component.factory"];
        $this->error = $DIC["ilErr"];
        $this->refinery = $DIC->refinery();
        $this->request_wrapper = $DIC->http()->wrapper()->query();
        $this->ui = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();

        $this->lng->loadLanguageModule(self::TYPE);
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess('read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        switch (true) {
            case $next_class === 'ilpermissiongui':
                $this->tabs->activateTab(self::TAB_PERMISSION);
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            case preg_match("/configgui$/i", $next_class):
                $this->forwardConfigGUI($next_class);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_INSTALL_PLUGIN:
                        $this->installPlugin();
                        break;
                    case self::CMD_REFRESH_LANGUAGES:
                        $this->refreshLanguages();
                        break;
                    case self::CMD_ACTIVATE_PLUGIN:
                        $this->activatePlugin();
                        break;
                    case self::CMD_DEACTIVATE_PLUGIN:
                        $this->deactivatePlugin();
                        break;
                    case self::CMD_UPDATE_PLUGIN:
                        $this->updatePlugin();
                        break;
                    case self::CMD_CONFIRM_UNINSTALL_PLUGIN:
                        $this->confirmUninstallPlugin();
                        break;
                    case self::CMD_UNINSTALL_PLUGIN:
                        $this->uninstallPlugin();
                        break;
                    default:
                        $this->listPlugins();
                }
        }
    }

    protected function forwardConfigGUI(string $name) : void
    {
        if (!class_exists($name)) {
            throw new Exception("class $name not found!");
        }

        $plugin = $this->getPlugin();
        $gui = new $name();
        $gui->setPluginObject($plugin);

        $this->ctrl->forwardCommand($gui);
    }

    protected function installPlugin() : void
    {
        $pl = $this->getPlugin();

        $pl->install();
        $this->update($pl);
    }

    protected function update(ilPlugin $plugin) : void
    {
        try {
            $plugin->update();
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("cmps_plugin_updated"), true);
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage("failure", $e->getMessage(), true);
        }

        $this->ctrl->redirectByClass(ilAdministrationGUI::class, self::CMD_JUMP_TO_PLUGIN_SLOT);
    }

    protected function refreshLanguages() : void
    {
        try {
            $plugin_name = $this->request_wrapper->retrieve(self::P_PLUGIN_NAME, $this->refinery->kindlyTo()->string());
            $plugin = $this->component_repository->getPluginByName($plugin_name);
            $language_handler = new ilPluginLanguage($plugin);
            $language_handler->updateLanguages();
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("cmps_refresh_lng"), true);
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage("failure", $e->getMessage(), true);
        }
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    protected function activatePlugin() : void
    {
        $pl = $this->getPlugin();

        try {
            $pl->activate();
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("cmps_plugin_activated"), true);
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage("failure", $e->getMessage(), true);
        }

        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    protected function deactivatePlugin() : void
    {
        $pl = $this->getPlugin();

        try {
            $pl->deactivate();
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("cmps_plugin_deactivated"), true);
        } catch (InvalidArgumentException $e) {
            $this->tpl->setOnScreenMessage("failure", $e->getMessage(), true);
        }

        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    protected function updatePlugin() : void
    {
        $pl = $this->getPlugin();
        $this->update($pl);
    }

    protected function confirmUninstallPlugin() : void
    {
        $pl = $this->getPlugin();

        $plugin_name = $this->request_wrapper->retrieve(self::P_PLUGIN_NAME, $this->refinery->kindlyTo()->string());

        $pl_info = $this->component_repository->getPluginByName($plugin_name);

        if ($pl_info->isActivated() || $pl_info->isActivationPossible()) {
            $question = sprintf(
                $this->lng->txt("cmps_uninstall_confirm"),
                $pl->getPluginName()
            );
        } else {
            $question = sprintf(
                $this->lng->txt("cmps_uninstall_inactive_confirm"),
                $pl->getPluginName(),
                $pl_info->getReasonForInactivity()
            );
        }

        $this->ctrl->setParameter($this, self::P_PLUGIN_NAME, $pl_info->getName());
        $buttons = array(
            $this->ui->button()->standard(
                $this->lng->txt('confirm'),
                $this->ctrl->getLinkTarget($this, self::CMD_UNINSTALL_PLUGIN)
            ),
            $this->ui->button()->standard(
                $this->lng->txt('cancel'),
                $this->ctrl->getLinkTarget($this, 'showQuestionList')
            )
        );

        $this->tpl->setContent($this->renderer->render($this->ui->messageBox()->confirmation($question)->withButtons($buttons)));
    }

    protected function uninstallPlugin() : void
    {
        $pl = $this->getPlugin();

        try {
            $pl->uninstall();
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("cmps_plugin_deinstalled"), true);
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage("failure", $e->getMessage(), true);
        }

        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    protected function getPlugin() : ilPlugin
    {
        $plugin_name = $this->request_wrapper->retrieve(self::P_PLUGIN_NAME, $this->refinery->kindlyTo()->string());
        return $this->component_factory->getPlugin(
            $this->component_repository->getPluginByName($plugin_name)->getId()
        );
    }

    protected function listPlugins() : void
    {
        $this->tabs->activateTab(self::TAB_PLUGINS);

        $filters = new ilPluginsOverviewTableFilterGUI($this);

        $plugins = [];
        foreach ($this->component_repository->getPlugins() as $plugin) {
            $plugins[] = $plugin;
        }

        $table = new ilPluginsOverviewTable(
            $this,
            $this->ctrl,
            $this->ui,
            $this->renderer,
            $this->lng,
            $filters->getData()
        );

        $table = $table->withData($plugins)->getTable();

        $this->tpl->setContent($filters->getHTML() . $table);
    }

    public function getAdminTabs() : void
    {
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                self::TAB_PLUGINS,
                $this->lng->txt("cmps_plugins"),
                $this->ctrl->getLinkTarget($this, self::CMD_DEFAULT)
            );
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }

        if (
            $this->request_wrapper->has(self::P_CTYPE) &&
            $this->request_wrapper->retrieve(self::P_CTYPE, $this->refinery->kindlyTo()->string()) === "Services"
        ) {
            $this->tabs_gui->activateTab("services");
        }
    }
}
