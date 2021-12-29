<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
 * Components (Modules, Services, Plugins) Settings.
 * @author       Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilObjComponentSettingsGUI: ilPermissionGUI
 */
class ilObjComponentSettingsGUI extends ilObjectGUI
{
    private const TYPE = 'cmps';
    public const CMD_DEFAULT = "listPlugins";
    public const CMD_LIST_SLOTS = "listSlots";
    public const TAB_PLUGINS = "plugins";
    public const CMD_INSTALL_PLUGIN = "installPlugin";
    public const CMD_CONFIGURE = "configure";
    public const CMD_REFRESH_LANGUAGES = "refreshLanguages";
    public const CMD_ACTIVATE_PLUGIN = "activatePlugin";
    public const CMD_DEACTIVATE_PLUGIN = "deactivatePlugin";
    public const CMD_UPDATE_PLUGIN = "updatePlugin";
    public const P_REF_ID = 'ref_id';
    public const P_CTYPE = "ctype";
    public const P_CNAME = "cname";
    public const P_SLOT_ID = "slot_id";
    public const P_PLUGIN_NAME = "pname";
    public const P_PLUGIN_ID = "plugin_id";
    public const P_ADMIN_MODE = 'admin_mode';
    public const CMD_SHOW_PLUGIN = "showPlugin";
    public const CMD_JUMP_TO_PLUGIN_SLOT = "jumpToPluginSlot";
    public const CMD_UNINSTALL_PLUGIN = "uninstallPlugin";
    public const CMD_CONFIRM_UNINSTALL_PLUGIN = "confirmUninstallPlugin";
    public const TAB_SLOTS = 'slots';
    /**
     * @var string
     */
    protected $type;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    /**
     * @var ilRbacSystem
     */
    protected $rbac_system;
    /**
     * @var ilDBInterface
     */
    protected $db;

    protected ilComponentRepository $component_repository;
    protected ilComponentFactory $component_factory;

    /**
     * ilObjComponentSettingsGUI constructor.
     * @param      $a_data
     * @param      $a_id
     * @param bool $a_call_by_reference
     * @param bool $a_prepare_output
     */
    public function __construct($a_data, int $a_id, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        global $DIC;
        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->rbac_system = $DIC->rbac()->system();
        $this->db = $DIC->database();
        $this->type = self::TYPE;
        $this->component_repository = $DIC["component.repository"];
        $this->component_factory = $DIC["component.factory"];
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng->loadLanguageModule(self::TYPE);
    }

    protected function getPlugin() : ilPlugin
    {
        return $this->component_factory->getPlugin(
            $this->component_repository->getPluginByName($_GET[self::P_PLUGIN_NAME])->getId()
        );
    }

    protected function getPluginLanguageHandler() : ilPluginLanguage
    {
        return new ilPluginLanguage(
            $this->component_repository->getPluginByName($_GET[self::P_PLUGIN_NAME])
        );
    }

    /**
     * Execute command
     * @access public
     */
    public function executeCommand()
    {
        global $DIC;
        $ilErr = $DIC['ilErr'];

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess('read', $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt('no_permission'), $ilErr->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs->activateTab('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:

                // configure classes
                $config = false;
                if (strtolower(substr($next_class, strlen($next_class) - 9)) === "configgui") {
                    $path = $this->ctrl->lookupClassPath(strtolower($next_class));
                    if ($path != "") {
                        include_once($path);
                        $nc = new $next_class();

                        $pl = $this->getPlugin();

                        $nc->setPluginObject($pl);

                        $this->ctrl->forwardCommand($nc);
                        $config = true;
                    }
                }

                if (!$config) {
                    if (!$cmd || $cmd === 'view') {
                        $cmd = self::CMD_DEFAULT;
                    }

                    $this->$cmd();
                }
                break;
        }
        return true;
    }

    /**
     * Get tabs
     * @access public
     */
    public function getAdminTabs() : void
    {
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                self::TAB_PLUGINS,
                $this->lng->txt("cmps_plugins"),
                $this->ctrl->getLinkTarget($this, self::CMD_DEFAULT)
            );

            if (DEVMODE) {
                $this->tabs_gui->addTab(
                    self::TAB_SLOTS,
                    $this->lng->txt("cmps_slots"),
                    $this->ctrl->getLinkTarget($this, self::CMD_LIST_SLOTS)
                );
            }
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }

        if ($_GET[self::P_CTYPE] === "Services") {
            $this->tabs_gui->activateTab("services");
        }
    }

    protected function listSlots() : void
    {
        if (!DEVMODE) {
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }

        $this->tabs_gui->activateTab(self::TAB_SLOTS);
        $comp_table = new ilComponentsTableGUI($this, self::CMD_LIST_SLOTS);
        $this->tpl->setContent($comp_table->getHTML());
    }

    protected function listPlugins() : void
    {
        $this->tabs->activateTab(self::TAB_PLUGINS);

        $filters = new ilPluginsOverviewTableFilterGUI($this);
        $table = new ilPluginsOverviewTableGUI($this, $filters->getData(), self::CMD_DEFAULT);

        $this->tpl->setContent($filters->getHTML() . $table->getHTML());
    }

    protected function showPluginSlotInfo() : void
    {
        if (!DEVMODE) {
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }

        $this->tabs->clearTargets();

        $this->tabs->setBackTarget(
            $this->lng->txt("cmps_slots"),
            $this->ctrl->getLinkTarget($this, self::CMD_LIST_SLOTS)
        );

        $comp = ilComponent::getComponentObject($_GET[self::P_CTYPE], $_GET[self::P_CNAME]);

        $form = new ilPropertyFormGUI();

        // component
        $ne = new ilNonEditableValueGUI($this->lng->txt("cmps_component"), "", true);
        $ne->setValue($comp->getComponentType() . "/" . $comp->getName() . " [" . $comp->getId() . "]");
        $form->addItem($ne);

        // plugin slot
        $ne = new ilNonEditableValueGUI($this->lng->txt("cmps_plugin_slot"), "", true);
        $ne->setValue($comp->getPluginSlotName($_GET[self::P_SLOT_ID]) . " [" . $_GET[self::P_SLOT_ID] . "]");
        $form->addItem($ne);

        // main dir
        $ne = new ilNonEditableValueGUI($this->lng->txt("cmps_main_dir"), "", true);
        $ne->setValue($comp->getPluginSlotDirectory($_GET[self::P_SLOT_ID]) . "/&lt;Plugin_Name&gt;");
        $form->addItem($ne);

        // plugin file
        $ne = new ilNonEditableValueGUI($this->lng->txt("cmps_plugin_file"), "", true);
        $ne->setValue("&lt;" . $this->lng->txt("cmps_main_dir") . "&gt;" .
            "/classes/class.il&lt;Plugin_Name&gt;Plugin.php");
        $form->addItem($ne);

        // language files
        $ne = new ilNonEditableValueGUI($this->lng->txt("cmps_lang_files"), "", true);
        $ne->setValue("&lt;" . $this->lng->txt("cmps_main_dir") . "&gt;" .
            "/lang/ilias_&lt;Language ID&gt;.lang");
        $form->addItem($ne);

        // db update
        $ne = new ilNonEditableValueGUI($this->lng->txt("cmps_db_update"), "", true);
        $ne->setValue("&lt;" . $this->lng->txt("cmps_main_dir") . "&gt;" .
            "/sql/dbupdate.php");
        $form->addItem($ne);

        // lang prefix
        $ne = new ilNonEditableValueGUI($this->lng->txt("cmps_plugin_lang_prefixes"), "", true);
        $ne->setValue($comp->getPluginSlotLanguagePrefix($_GET[self::P_SLOT_ID]) . "&lt;Plugin_ID&gt;_");
        $form->addItem($ne);

        // db prefix
        $ne = new ilNonEditableValueGUI($this->lng->txt("cmps_plugin_db_prefixes"), "", true);
        $ne->setValue($comp->getPluginSlotLanguagePrefix($_GET[self::P_SLOT_ID]) . "&lt;Plugin_ID&gt;_");
        $form->addItem($ne);

        $form->setTitle($this->lng->txt("cmps_plugin_slot"));

        // set content and title
        $this->tpl->setContent($form->getHTML());
        $this->tpl->setTitle($comp->getComponentType() . "/" . $comp->getName() . ": " .
            $this->lng->txt("cmps_plugin_slot") . " \"" . $comp->getPluginSlotName($_GET[self::P_SLOT_ID]) . "\"");
        $this->tpl->setDescription("");
    }

    protected function showPlugin() : void
    {
        if (!$_GET[self::P_CTYPE] ||
            !$_GET[self::P_CNAME] ||
            !$_GET[self::P_SLOT_ID] ||
            !$_GET[self::P_PLUGIN_ID]) {
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }

        try {
            $plugin = $this->component_repository
                ->getComponentByTypeAndName(
                    $_GET[self::P_CTYPE],
                    $_GET[self::P_CNAME]
                )
                ->getPluginSlotById(
                    $_GET[self::P_SLOT_ID]
                )
                ->getPluginById(
                    $_GET[self::P_PLUGIN_ID]
                );
        } catch (\InvalidArgumentException $e) {
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }
        $component = $plugin->getComponent();
        $pluginslot = $plugin->getPluginSlot();

        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt("cmps_plugins"),
            $this->ctrl->getLinkTarget($this, self::CMD_DEFAULT)
        );

        $this->ctrl->setParameter($this, self::P_CTYPE, $component->getType());
        $this->ctrl->setParameter($this, self::P_CNAME, $component->getName());
        $this->ctrl->setParameter($this, self::P_SLOT_ID, $pluginslot->getId());
        $this->ctrl->setParameter($this, self::P_PLUGIN_ID, $plugin->getId());
        $this->ctrl->setParameter($this, self::P_PLUGIN_NAME, $plugin->getName());

        $language_handler = new ilPluginLanguage($plugin);

        // dbupdate
        $db_update = new ilPluginDBUpdate(
            $this->db,
            $plugin
        );
        if (!isset($db_update->error)) {
            $db_curr = $plugin->getCurrentDBVersion();
            $db_file = $db_update->getFileVersion();
        }

        // toolbar actions
        if (!$plugin->isInstalled()) {
            $this->toolbar->addButton(
                $this->lng->txt("cmps_install"),
                $this->ctrl->getLinkTarget($this, self::CMD_INSTALL_PLUGIN)
            );
        } else {
            // configure button
            if (ilPlugin::hasConfigureClass($plugin)) {
                $this->toolbar->addButton(
                    $this->lng->txt("cmps_configure"),
                    $this->ctrl->getLinkTargetByClass(strtolower(ilPlugin::getConfigureClassName($plugin)), self::CMD_CONFIGURE)
                );
            }
            // refresh languages button
            if ($language_handler->hasAvailableLangFiles()) {
                $this->toolbar->addButton(
                    $this->lng->txt("cmps_refresh"),
                    $this->ctrl->getLinkTarget($this, self::CMD_REFRESH_LANGUAGES)
                );
            }

            if ($plugin->isActivationPossible() && !$plugin->isActivated()) {
                $this->toolbar->addButton(
                    $this->lng->txt("cmps_activate"),
                    $this->ctrl->getLinkTarget($this, self::CMD_ACTIVATE_PLUGIN)
                );
            }

            // deactivation/refresh languages button
            if ($plugin->isActive()) {
                // deactivate button
                $this->toolbar->addButton(
                    $this->lng->txt("cmps_deactivate"),
                    $this->ctrl->getLinkTarget($this, self::CMD_DEACTIVATE_PLUGIN)
                );
            }

            // update button
            if ($plugin->isUpdateRequired()) {
                $this->toolbar->addButton(
                    $this->lng->txt("cmps_update"),
                    $this->ctrl->getLinkTarget($this, self::CMD_UPDATE_PLUGIN)
                );
            }
        }

        // info
        $resp = array();
        if ($plugin->getResponsible() != '') {
            $responsibles = explode('/', $plugin->getResponsibleMail());
            foreach ($responsibles as $responsible) {
                if (!strlen($responsible = trim($responsible))) {
                    continue;
                }

                $resp[] = $responsible;
            }

            $resp = $plugin->getResponsible() . " (" . implode(" / ", $resp) . ")";
        }

        if ($plugin->isActive()) {
            $status = $this->lng->txt("cmps_active");
        } else {
            $status =
                $this->lng->txt("cmps_inactive") .
                " (" .
                $this->lng->txt($plugin->getReasonForInactivity()) .
                ")";
        }

        $info[""][$this->lng->txt("cmps_name")] = $plugin->getName();
        $info[""][$this->lng->txt("cmps_id")] = $plugin->getId();
        $info[""][$this->lng->txt("cmps_version")] = (string) $plugin->getCurrentVersion();
        if ($resp) {
            $info[""][$this->lng->txt("cmps_responsible")] = $resp;
        }
        $info[""][$this->lng->txt("cmps_ilias_min_version")] = (string) $plugin->getMinimumILIASVersion();
        $info[""][$this->lng->txt("cmps_ilias_max_version")] = (string) $plugin->getMaximumILIASVersion();
        $info[""][$this->lng->txt("cmps_status")] = $status;

        if ($language_handler->hasAvailableLangFiles()) {
            $lang_files = [];
            foreach ($language_handler->getAvailableLangFiles() as $lang) {
                $lang_files[] = $lang["file"];
            }
            $info[""][$this->lng->txt("cmps_languages")] = implode(", ", $lang_files);
        } else {
            $info[""][$this->lng->txt("cmps_languages")] = $this->lng->txt("cmps_no_language_file_available");
        }

        if (!$db_file) {
            $info[$this->lng->txt("cmps_database")][$this->lng->txt("file")] = $this->lng->txt("cmps_no_db_update_file_available");
        } else {
            $info[$this->lng->txt("cmps_database")][$this->lng->txt("file")] = "dbupdate.php";
            $info[$this->lng->txt("cmps_database")][$this->lng->txt("cmps_current_version")] = $db_curr ?? "-";
            $info[$this->lng->txt("cmps_database")][$this->lng->txt("cmps_file_version")] = $db_file;
        }

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("cmps_plugin"));

        foreach ($info as $section => $items) {
            if (trim($section)) {
                $sec = new ilFormSectionHeaderGUI();
                $sec->setTitle($section);
                $form->addItem($sec);
            }
            foreach ($items as $key => $value) {
                $non = new ilNonEditableValueGUI($key);
                $non->setValue($value);
                $form->addItem($non);
            }
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function installPlugin() : void
    {
        $pl = $this->getPlugin();

        $pl->install();
        $this->update($pl);
    }

    protected function activatePlugin() : void
    {
        $pl = $this->getPlugin();

        try {
            $result = $pl->activate();
            if ($result !== true) {
                ilUtil::sendFailure($result, true);
            } else {
                ilUtil::sendSuccess($this->lng->txt("cmps_plugin_activated"), true);
            }
        } catch (ilPluginException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
        }

        $this->ctrl->setParameter($this, self::P_CTYPE, $_GET[self::P_CTYPE]);
        $this->ctrl->setParameter($this, self::P_CNAME, $_GET[self::P_CNAME]);
        $this->ctrl->setParameter($this, self::P_SLOT_ID, $_GET[self::P_SLOT_ID]);

        if ($_GET[self::P_PLUGIN_ID]) {
            $this->ctrl->setParameter($this, self::P_PLUGIN_ID, $_GET[self::P_PLUGIN_ID]);
            $this->ctrl->redirect($this, self::CMD_SHOW_PLUGIN);
        } else {
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }
    }

    protected function updatePlugin() : void
    {
        $pl = $this->getPlugin();
        $this->update($pl);
    }

    protected function update(ilPlugin $plugin) : void
    {
        $result = $plugin->update();

        if ($result !== true) {
            ilUtil::sendFailure($plugin->getMessage(), true);
        } else {
            ilUtil::sendSuccess($plugin->getMessage(), true);
        }

        // reinitialize control class
        $_GET["cmd"] = self::CMD_JUMP_TO_PLUGIN_SLOT;
        $this->ctrl->setParameterByClass(ilAdministrationGUI::class, self::P_ADMIN_MODE, self::ADMIN_MODE_SETTINGS);
        $this->ctrl->setParameterByClass(ilAdministrationGUI::class, self::P_CTYPE, $_GET[self::P_CTYPE]);
        $this->ctrl->setParameterByClass(ilAdministrationGUI::class, self::P_CNAME, $_GET[self::P_CNAME]);
        $this->ctrl->setParameterByClass(ilAdministrationGUI::class, self::P_SLOT_ID, $_GET[self::P_SLOT_ID]);
        $this->ctrl->setParameterByClass(ilAdministrationGUI::class, self::P_PLUGIN_ID, $_GET[self::P_PLUGIN_ID]);
        $this->ctrl->setParameterByClass(ilAdministrationGUI::class, self::P_REF_ID, $_GET[self::P_REF_ID]);
        $this->ctrl->redirectByClass(ilAdministrationGUI::class, self::CMD_JUMP_TO_PLUGIN_SLOT);
    }

    protected function deactivatePlugin() : void
    {
        $pl = $this->getPlugin();
        $result = $pl->deactivate();

        if ($result !== true) {
            ilUtil::sendFailure($result, true);
        } else {
            ilUtil::sendSuccess($this->lng->txt("cmps_plugin_deactivated"), true);
        }

        $this->ctrl->setParameter($this, self::P_CTYPE, $_GET[self::P_CTYPE]);
        $this->ctrl->setParameter($this, self::P_CNAME, $_GET[self::P_CNAME]);
        $this->ctrl->setParameter($this, self::P_SLOT_ID, $_GET[self::P_SLOT_ID]);

        if ($_GET[self::P_PLUGIN_ID]) {
            $this->ctrl->setParameter($this, self::P_PLUGIN_ID, $_GET[self::P_PLUGIN_ID]);
            $this->ctrl->redirect($this, self::CMD_SHOW_PLUGIN);
        } else {
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }
    }

    protected function refreshLanguages() : void
    {
        $this->getPluginLanguageHandler()->updateLanguages();

        $this->ctrl->setParameter($this, self::P_CTYPE, $_GET[self::P_CTYPE]);
        $this->ctrl->setParameter($this, self::P_CNAME, $_GET[self::P_CNAME]);
        $this->ctrl->setParameter($this, self::P_SLOT_ID, $_GET[self::P_SLOT_ID]);

        if ($_GET[self::P_PLUGIN_ID]) {
            $this->ctrl->setParameter($this, self::P_PLUGIN_ID, $_GET[self::P_PLUGIN_ID]);
            $this->ctrl->redirect($this, self::CMD_SHOW_PLUGIN);
        } else {
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }
    }

    protected function confirmUninstallPlugin() : void
    {
        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        $pl = $this->getPlugin();

        $pl_info = $this->component_repository
            ->getComponentByTypeAndName(
                $_GET[self::P_CTYPE],
                $_GET[self::P_CNAME]
            )
            ->getPluginSlotById(
                $_GET[self::P_SLOT_ID]
            )
            ->getPluginByName(
                $_GET[self::P_PLUGIN_NAME]
            );

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

        $this->ctrl->setParameter($this, self::P_CTYPE, $_GET[self::P_CTYPE]);
        $this->ctrl->setParameter($this, self::P_CNAME, $_GET[self::P_CNAME]);
        $this->ctrl->setParameter($this, self::P_SLOT_ID, $_GET[self::P_SLOT_ID]);
        $this->ctrl->setParameter($this, self::P_PLUGIN_NAME, $_GET[self::P_PLUGIN_NAME]);

        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
        $confirmation_gui->setHeaderText($question);
        $confirmation_gui->setCancel($this->lng->txt("cancel"), self::CMD_DEFAULT);
        $confirmation_gui->setConfirm($this->lng->txt("cmps_uninstall"), self::CMD_UNINSTALL_PLUGIN);

        $this->tpl->setContent($confirmation_gui->getHTML());
    }

    protected function uninstallPlugin() : void
    {
        $pl = $this->getPlugin();

        try {
            $result = $pl->uninstall();
            if ($result !== true) {
                ilUtil::sendFailure($result, true);
            } else {
                ilUtil::sendSuccess($this->lng->txt("cmps_plugin_uninstalled"), true);
            }
        } catch (ilPluginException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
        }

        ilGlobalCache::flushAll();
        $ilPluginsOverviewTableGUI = new ilPluginsOverviewTableGUI($this, []);

        $this->ctrl->setParameter($this, self::P_CTYPE, $_GET[self::P_CTYPE]);
        $this->ctrl->setParameter($this, self::P_CNAME, $_GET[self::P_CNAME]);
        $this->ctrl->setParameter($this, self::P_SLOT_ID, $_GET[self::P_SLOT_ID]);
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }
}
