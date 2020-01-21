<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");


/**
* Components (Modules, Services, Plugins) Settings.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjComponentSettingsGUI: ilPermissionGUI
*
* @ingroup ServicesComponent
*/
class ilObjComponentSettingsGUI extends ilObjectGUI
{
    private static $ERROR_MESSAGE;
    /**
     * Contructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        $this->type = 'cmps';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('cmps');
    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        global $DIC;
        $rbacsystem = $DIC->rbac()->system();
        $ilErr = $DIC['ilErr'];
        $ilCtrl = $DIC->ctrl();

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt('no_permission'), $ilErr->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            default:

                // configure classes
                $config = false;
                if (substr(strtolower($next_class), strlen($next_class) - 9) == "configgui") {
                    $path = $ilCtrl->lookupClassPath(strtolower($next_class));
                    if ($path != "") {
                        include_once($path);
                        $nc = new $next_class();

                        $pl = ilPluginAdmin::getPluginObject(
                            $_GET["ctype"],
                            $_GET["cname"],
                            $_GET["slot_id"],
                            $_GET["pname"]
                        );

                        $nc->setPluginObject($pl);

                        $ret = $this->ctrl->forwardCommand($nc);
                        $config = true;
                    }
                }

                if (!$config) {
                    if (!$cmd || $cmd == 'view') {
                        $cmd = "listPlugins";
                    }

                    $this->$cmd();
                }
                break;
        }
        return true;
    }

    /**
     * Get tabs
     *
     * @access public
     *
     */
    public function getAdminTabs()
    {
        global $DIC;
        $rbacsystem = $DIC->rbac()->system();
        $lng = $DIC->language();

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "plugins",
                $lng->txt("cmps_plugins"),
                $this->ctrl->getLinkTarget($this, "listPlugins")
            );

            if (DEVMODE) {
                $this->tabs_gui->addTab(
                    "slots",
                    $lng->txt("cmps_slots"),
                    $this->ctrl->getLinkTarget($this, "listSlots")
                );
            }
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }

        if ($_GET["ctype"] == "Services") {
            $this->tabs_gui->activateTab("services");
        }
    }

    /**
    * List Services
    */
    public function listSlots()
    {
        if (!DEVMODE) {
            $this->ctrl->redirect($this, "listPlugins");
        }

        $this->tabs_gui->activateTab('slots');

        include_once("./Services/Component/classes/class.ilComponentsTableGUI.php");
        $comp_table = new ilComponentsTableGUI($this, "listSlots");

        $this->tpl->setContent($comp_table->getHTML());
    }
    
    /**
     * List plugins
     *
     * @param
     * @return
     */
    protected function listPlugins()
    {
        global $DIC;
        $tpl = $DIC->ui()->mainTemplate();
        $ilTabs = $DIC->tabs();

        $ilTabs->activateTab("plugins");
        include_once("./Services/Component/classes/class.ilPluginsOverviewTableGUI.php");
        $table = new ilPluginsOverviewTableGUI($this, "listPlugins");
        $tpl->setContent($table->getHTML());
    }

    /**
     * Show information about a plugin slot.
     */
    protected function showPluginSlotInfo()
    {
        global $DIC;
        $tpl = $DIC->ui()->mainTemplate();
        $lng = $DIC->language();
        $ilTabs = $DIC->tabs();
        $ilCtrl = $DIC->ctrl();

        if (!DEVMODE) {
            $ilCtrl->redirect($this, "listPlugins");
        }

        $ilTabs->clearTargets();

        $ilTabs->setBackTarget(
            $lng->txt("cmps_slots"),
            $ilCtrl->getLinkTarget($this, "listSlots")
        );

        include_once("./Services/Component/classes/class.ilComponent.php");
        $comp = ilComponent::getComponentObject($_GET["ctype"], $_GET["cname"]);

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        // component
        $ne = new ilNonEditableValueGUI($lng->txt("cmps_component"), "", true);
        $ne->setValue($comp->getComponentType() . "/" . $comp->getName() . " [" . $comp->getId() . "]");
        $form->addItem($ne);

        // plugin slot
        $ne = new ilNonEditableValueGUI($lng->txt("cmps_plugin_slot"), "", true);
        $ne->setValue($comp->getPluginSlotName($_GET["slot_id"]) . " [" . $_GET["slot_id"] . "]");
        $form->addItem($ne);

        // main dir
        $ne = new ilNonEditableValueGUI($lng->txt("cmps_main_dir"), "", true);
        $ne->setValue($comp->getPluginSlotDirectory($_GET["slot_id"]) . "/&lt;Plugin_Name&gt;");
        $form->addItem($ne);

        // plugin file
        $ne = new ilNonEditableValueGUI($lng->txt("cmps_plugin_file"), "", true);
        $ne->setValue("&lt;" . $lng->txt("cmps_main_dir") . "&gt;" .
            "/classes/class.il&lt;Plugin_Name&gt;Plugin.php");
        $form->addItem($ne);

        // language files
        $ne = new ilNonEditableValueGUI($lng->txt("cmps_lang_files"), "", true);
        $ne->setValue("&lt;" . $lng->txt("cmps_main_dir") . "&gt;" .
            "/lang/ilias_&lt;Language ID&gt;.lang");
        $form->addItem($ne);

        // db update
        $ne = new ilNonEditableValueGUI($lng->txt("cmps_db_update"), "", true);
        $ne->setValue("&lt;" . $lng->txt("cmps_main_dir") . "&gt;" .
            "/sql/dbupdate.php");
        $form->addItem($ne);

        // lang prefix
        $ne = new ilNonEditableValueGUI($lng->txt("cmps_plugin_lang_prefixes"), "", true);
        $ne->setValue($comp->getPluginSlotLanguagePrefix($_GET["slot_id"]) . "&lt;Plugin_ID&gt;_");
        $form->addItem($ne);

        // db prefix
        $ne = new ilNonEditableValueGUI($lng->txt("cmps_plugin_db_prefixes"), "", true);
        $ne->setValue($comp->getPluginSlotLanguagePrefix($_GET["slot_id"]) . "&lt;Plugin_ID&gt;_");
        $form->addItem($ne);

        $form->setTitle($lng->txt("cmps_plugin_slot"));

        // set content and title
        $tpl->setContent($form->getHTML());
        $tpl->setTitle($comp->getComponentType() . "/" . $comp->getName() . ": " .
            $lng->txt("cmps_plugin_slot") . " \"" . $comp->getPluginSlotName($_GET["slot_id"]) . "\"");
        $tpl->setDescription("");
    }

    protected function showPlugin()
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();
        $lng = $DIC->language();
        $tpl = $DIC->ui()->mainTemplate();
        $ilDB = $DIC->database();
        $ilToolbar = $DIC->toolbar();

        if (!$_GET["ctype"] ||
            !$_GET["cname"] ||
            !$_GET["slot_id"] ||
            !$_GET["plugin_id"]) {
            $ilCtrl->redirect($this, "listPlugins");
        }

        include_once("./Services/Component/classes/class.ilPluginSlot.php");
        $slot = new ilPluginSlot($_GET["ctype"], $_GET["cname"], $_GET["slot_id"]);

        $plugin = null;
        foreach ($slot->getPluginsInformation() as $item) {
            if ($item["id"] == $_GET["plugin_id"]) {
                $plugin = $item;
                break;
            }
        }
        if (!$plugin) {
            $ilCtrl->redirect($this, "listPlugins");
        }

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("cmps_plugins"),
            $ilCtrl->getLinkTarget($this, "listPlugins")
        );

        $ilCtrl->setParameter($this, "ctype", $_GET["ctype"]);
        $ilCtrl->setParameter($this, "cname", $_GET["cname"]);
        $ilCtrl->setParameter($this, "slot_id", $_GET["slot_id"]);
        $ilCtrl->setParameter($this, "plugin_id", $_GET["plugin_id"]);
        $ilCtrl->setParameter($this, "pname", $plugin["name"]);

        $langs = ilPlugin::getAvailableLangFiles($slot->getPluginsDirectory() . "/" .
            $plugin["name"] . "/lang");

        // dbupdate
        $file = ilPlugin::getDBUpdateScriptName(
            $_GET["ctype"],
            $_GET["cname"],
            ilPluginSlot::lookupSlotName($_GET["ctype"], $_GET["cname"], $_GET["slot_id"]),
            $plugin["name"]
        );
        $db_curr = $db_file = null;
        if (@is_file($file)) {
            include_once("./Services/Component/classes/class.ilPluginDBUpdate.php");
            $dbupdate = new ilPluginDBUpdate(
                $_GET["ctype"],
                $_GET["cname"],
                $_GET["slot_id"],
                $plugin["name"],
                $ilDB,
                true,
                ""
            );

            $db_curr = $dbupdate->getCurrentVersion();
            $db_file = $dbupdate->getFileVersion();
        }

        $plugin_db_data = ilPlugin::getPluginRecord($plugin["component_type"], $plugin["component_name"], $plugin["slot_id"], $plugin["name"]);

        // toolbar actions
        if ($plugin["must_install"]) {
            $ilToolbar->addButton(
                $lng->txt("cmps_install"),
                $ilCtrl->getLinkTarget($this, "installPlugin")
            );
        } else {
            // configure button
            if (ilPlugin::hasConfigureClass($slot->getPluginsDirectory(), $plugin, $plugin_db_data) &&
                $ilCtrl->checkTargetClass(ilPlugin::getConfigureClassName($plugin))) {
                $ilToolbar->addButton(
                    $lng->txt("cmps_configure"),
                    $ilCtrl->getLinkTargetByClass(strtolower(ilPlugin::getConfigureClassName($plugin)), "configure")
                );
            }
            // refresh languages button
            if (count($langs) > 0) {
                $ilToolbar->addButton(
                    $lng->txt("cmps_refresh"),
                    $ilCtrl->getLinkTarget($this, "refreshLanguages")
                );
            }

            if ($plugin["activation_possible"]) {
                $ilToolbar->addButton(
                    $lng->txt("cmps_activate"),
                    $ilCtrl->getLinkTarget($this, "activatePlugin")
                );
            }

            // deactivation/refresh languages button
            if ($plugin["is_active"]) {
                // deactivate button
                $ilToolbar->addButton(
                    $lng->txt("cmps_deactivate"),
                    $ilCtrl->getLinkTarget($this, "deactivatePlugin")
                );
            }

            // update button
            if ($plugin["needs_update"]) {
                $ilToolbar->addButton(
                    $lng->txt("cmps_update"),
                    $ilCtrl->getLinkTarget($this, "updatePlugin")
                );
            }
        }

        // info
        $resp = array();
        if (strlen($plugin["responsible"])) {
            $responsibles = explode('/', $plugin["responsible_mail"]);
            foreach ($responsibles as $responsible) {
                if (!strlen($responsible = trim($responsible))) {
                    continue;
                }

                $resp[] = $responsible;
            }

            $resp = $plugin["responsible"] . " (" . implode(" / ", $resp) . ")";
        }

        if ($plugin["is_active"]) {
            $status = $lng->txt("cmps_active");
        } else {
            $r = ($plugin["inactive_reason"] != "")
                ? " (" . $plugin["inactive_reason"] . ")"
                : "";

            $status = $lng->txt("cmps_inactive") . $r;
        }

        $info[""][$lng->txt("cmps_name")] = $plugin["name"];
        $info[""][$lng->txt("cmps_id")] = $plugin["id"];
        $info[""][$lng->txt("cmps_version")] = $plugin["version"];
        if ($resp) {
            $info[""][$lng->txt("cmps_responsible")] = $resp;
        }
        $info[""][$lng->txt("cmps_ilias_min_version")] = $plugin["ilias_min_version"];
        $info[""][$lng->txt("cmps_ilias_max_version")] = $plugin["ilias_max_version"];
        $info[""][$lng->txt("cmps_status")] = $status;

        if (sizeof($langs)) {
            $lang_files = array();
            foreach ($langs as $lang) {
                $lang_files[] = $lang["file"];
            }
            $info[""][$lng->txt("cmps_languages")] = implode(", ", $lang_files);
        } else {
            $info[""][$lng->txt("cmps_languages")] = $lng->txt("cmps_no_language_file_available");
        }

        $info[$lng->txt("cmps_basic_files")]["plugin.php"] = $plugin["plugin_php_file_status"] ?
            $lng->txt("cmps_available") :
            $lng->txt("cmps_missing");
        $info[$lng->txt("cmps_basic_files")][$lng->txt("cmps_class_file")] = ($plugin["class_file_status"] ?
                $lng->txt("cmps_available") :
                $lng->txt("cmps_missing")) .
            " (" . $plugin["class_file"] . ")";

        if (!$db_file) {
            $info[$lng->txt("cmps_database")][$lng->txt("file")] = $lng->txt("cmps_no_db_update_file_available");
        } else {
            $info[$lng->txt("cmps_database")][$lng->txt("file")] = "dbupdate.php";
            $info[$lng->txt("cmps_database")][$lng->txt("cmps_current_version")] = $db_curr;
            $info[$lng->txt("cmps_database")][$lng->txt("cmps_file_version")] = $db_file;
        }

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTitle($lng->txt("cmps_plugin"));

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

        $tpl->setContent($form->getHTML());
    }

    /**
     * Install a plugin
     *
     * @return void
     */
    protected function installPlugin()
    {
        include_once("./Services/Component/classes/class.ilPlugin.php");
        $pl = ilPlugin::getPluginObject(
            $_GET["ctype"],
            $_GET["cname"],
            $_GET["slot_id"],
            $_GET["pname"]
        );

        $pl->install();
        $this->update($pl);
    }

    /**
    * Activate a plugin.
    */
    protected function activatePlugin()
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        include_once("./Services/Component/classes/class.ilPlugin.php");
        $pl = ilPlugin::getPluginObject(
            $_GET["ctype"],
            $_GET["cname"],
            $_GET["slot_id"],
            $_GET["pname"]
        );

        try {
            $result = $pl->activate();
            if ($result !== true) {
                ilUtil::sendFailure($result, true);
            } else {
                ilUtil::sendSuccess($lng->txt("cmps_plugin_activated"), true);
            }
        } catch (ilPluginException $e) {
            ilUtil::sendFailure($e->getMessage, true);
        }

        $ilCtrl->setParameter($this, "ctype", $_GET["ctype"]);
        $ilCtrl->setParameter($this, "cname", $_GET["cname"]);
        $ilCtrl->setParameter($this, "slot_id", $_GET["slot_id"]);

        if ($_GET["plugin_id"]) {
            $ilCtrl->setParameter($this, "plugin_id", $_GET["plugin_id"]);
            $ilCtrl->redirect($this, "showPlugin");
        } else {
            $ilCtrl->redirect($this, "listPlugins");
        }
    }

    /**
    * Update a plugin.
    */
    protected function updatePlugin()
    {
        include_once("./Services/Component/classes/class.ilPlugin.php");
        $pl = ilPlugin::getPluginObject(
            $_GET["ctype"],
            $_GET["cname"],
            $_GET["slot_id"],
            $_GET["pname"]
        );

        $this->update($pl);
    }

    /**
     * Execute update and ctrl reload
     *
     * @param ilPlugin 	$plugin
     *
     * @return void
     */
    protected function update(ilPlugin $plugin)
    {
        $result = $plugin->update();

        if ($result !== true) {
            ilUtil::sendFailure($plugin->message, true);
        } else {
            ilUtil::sendSuccess($plugin->message, true);
        }

        // reinitialize control class
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $ilCtrl->initBaseClass("iladministrationgui");
        $_GET["cmd"] = "jumpToPluginSlot";
        $ilCtrl->setParameterByClass("iladministrationgui", "ctype", $_GET["ctype"]);
        $ilCtrl->setParameterByClass("iladministrationgui", "cname", $_GET["cname"]);
        $ilCtrl->setParameterByClass("iladministrationgui", "slot_id", $_GET["slot_id"]);
        $ilCtrl->setParameterByClass("iladministrationgui", "plugin_id", $_GET["plugin_id"]);
        ilUtil::redirect("ilias.php?admin_mode=settings&baseClass=ilAdministrationGUI" .
            "&cmd=jumpToPluginSlot&ref_id=" . $_GET["ref_id"] . "&ctype=" . $_GET["ctype"] .
            "&cname=" . $_GET["cname"] . "&slot_id=" . $_GET["slot_id"] . "&plugin_id=" . $_GET["plugin_id"]);
    }

    /**
    * Deactivate a plugin.
    */
    protected function deactivatePlugin()
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        include_once("./Services/Component/classes/class.ilPlugin.php");
        $pl = ilPlugin::getPluginObject(
            $_GET["ctype"],
            $_GET["cname"],
            $_GET["slot_id"],
            $_GET["pname"]
        );

        $result = $pl->deactivate();

        if ($result !== true) {
            ilUtil::sendFailure($result, true);
        } else {
            ilUtil::sendSuccess($lng->txt("cmps_plugin_deactivated"), true);
        }

        $ilCtrl->setParameter($this, "ctype", $_GET["ctype"]);
        $ilCtrl->setParameter($this, "cname", $_GET["cname"]);
        $ilCtrl->setParameter($this, "slot_id", $_GET["slot_id"]);

        if ($_GET["plugin_id"]) {
            $ilCtrl->setParameter($this, "plugin_id", $_GET["plugin_id"]);
            $ilCtrl->redirect($this, "showPlugin");
        } else {
            $ilCtrl->redirect($this, "listPlugins");
        }
    }

    /**
    * Refresh Languages
    */
    protected function refreshLanguages()
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();

        include_once("./Services/Component/classes/class.ilPlugin.php");
        $pl = ilPlugin::getPluginObject(
            $_GET["ctype"],
            $_GET["cname"],
            $_GET["slot_id"],
            $_GET["pname"]
        );

        $result = $pl->updateLanguages();

        if ($result !== true) {
            ilUtil::sendFailure($result, true);
        }

        $ilCtrl->setParameter($this, "ctype", $_GET["ctype"]);
        $ilCtrl->setParameter($this, "cname", $_GET["cname"]);
        $ilCtrl->setParameter($this, "slot_id", $_GET["slot_id"]);

        if ($_GET["plugin_id"]) {
            $ilCtrl->setParameter($this, "plugin_id", $_GET["plugin_id"]);
            $ilCtrl->redirect($this, "showPlugin");
        } else {
            $ilCtrl->redirect($this, "listPlugins");
        }
    }

    protected function confirmUninstallPlugin()
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $tpl = $DIC->ui()->mainTemplate();
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        include_once("./Services/Component/classes/class.ilPlugin.php");
        $pl = ilPlugin::getPluginObject(
            $_GET["ctype"],
            $_GET["cname"],
            $_GET["slot_id"],
            $_GET["pname"]
        );

        $pl_meta = $ilPluginAdmin->getAllData(
            $_GET["ctype"],
            $_GET["cname"],
            $_GET["slot_id"],
            $_GET["pname"]
        );

        $activation = ((bool) $pl_meta["activation_possible"] || (bool) $pl_meta["is_active"]); // #18827
        $reason = $pl_meta["inactive_reason"];

        $question = $activation
            ? sprintf($this->lng->txt("cmps_uninstall_confirm"), $pl->getPluginName())
            : sprintf($this->lng->txt("cmps_uninstall_inactive_confirm"), $pl->getPluginName(), $reason);

        $ilCtrl->setParameter($this, "ctype", $_GET["ctype"]);
        $ilCtrl->setParameter($this, "cname", $_GET["cname"]);
        $ilCtrl->setParameter($this, "slot_id", $_GET["slot_id"]);
        $ilCtrl->setParameter($this, "pname", $_GET["pname"]);

        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $confirmation_gui = new ilConfirmationGUI();
        $confirmation_gui->setFormAction($ilCtrl->getFormAction($this));
        $confirmation_gui->setHeaderText($question);
        $confirmation_gui->setCancel($this->lng->txt("cancel"), "listPlugins");
        $confirmation_gui->setConfirm($this->lng->txt("cmps_uninstall"), "uninstallPlugin");

        $tpl->setContent($confirmation_gui->getHTML());
    }

    protected function uninstallPlugin()
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();

        include_once("./Services/Component/classes/class.ilPlugin.php");
        $pl = ilPlugin::getPluginObject(
            $_GET["ctype"],
            $_GET["cname"],
            $_GET["slot_id"],
            $_GET["pname"]
        );

        try {
            $result = $pl->uninstall();
            if ($result !== true) {
                ilUtil::sendFailure($result, true);
            } else {
                ilUtil::sendSuccess($this->lng->txt("cmps_plugin_uninstalled"), true);
            }
        } catch (ilPluginException $e) {
            ilUtil::sendFailure($e->getMessage, true);
        }

        ilGlobalCache::flushAll();
        $ilPluginsOverviewTableGUI = new ilPluginsOverviewTableGUI($this);

        $ilCtrl->setParameter($this, "ctype", $_GET["ctype"]);
        $ilCtrl->setParameter($this, "cname", $_GET["cname"]);
        $ilCtrl->setParameter($this, "slot_id", $_GET["slot_id"]);
        $ilCtrl->redirect($this, "listPlugins");
    }
}
