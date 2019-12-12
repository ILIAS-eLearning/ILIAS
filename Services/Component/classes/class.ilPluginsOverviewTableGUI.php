<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");
include_once("Services/Component/classes/class.ilComponent.php");

/**
 * TableGUI class for components listing
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Stefan Hecken <stefan.hecken@concepts-and-training.de>
 *
 * @version $Id$
 *
 * @ingroup ServicesComponent
 */
class ilPluginsOverviewTableGUI extends ilTable2GUI
{
    private $mode;


    public function __construct($a_parent_obj, $a_parent_cmd = "")
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId("cmpspl");

        $this->addColumn($this->lng->txt("cmps_plugin"), "plugin_name");
        $this->addColumn($this->lng->txt("id"), "plugin_id");
        $this->addColumn($this->lng->txt("cmps_plugin_slot"), "slot_name");
        $this->addColumn($this->lng->txt("cmps_component"), "component_name");
        $this->addColumn($this->lng->txt("active"), "plugin_active");
        $this->addColumn($this->lng->txt("action"));

        $this->setDefaultOrderField("plugin_name");

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.plugin_overview_row.html",
            "Services/Component"
        );
        $this->getComponents();
        $this->setLimit(10000);

        include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
    }


    /**
     * Get pages for list.
     */
    public function getComponents()
    {
        $plugins = array();
        $modules = $this->getModulesCoreItems();
        $this->addPluginData($plugins, $modules, IL_COMP_MODULE);

        $services = $this->getServicesCoreItems();
        $this->addPluginData($plugins, $services, IL_COMP_SERVICE);

        $this->setData($plugins);
    }


    /**
     * Get all available modules
     *
     * @return string[]
     */
    protected function getModulesCoreItems()
    {
        include_once("./Services/Component/classes/class.ilModule.php");

        return ilModule::getAvailableCoreModules();
    }


    /**
     * Get all available services
     *
     * @return string[]
     */
    protected function getServicesCoreItems()
    {
        include_once("./Services/Component/classes/class.ilService.php");

        return ilService::getAvailableCoreServices();
    }


    /**
     * Get plugin informations
     *
     * @param string[]    &$plugins
     * @param string[]     $core_items
     * @param sring        $core_type
     *
     * @return string[]
     */
    protected function addPluginData(array &$plugins, array $core_items, $core_type)
    {
        foreach ($core_items as $core_item) {
            $plugin_slots = ilComponent::lookupPluginSlots($core_type, $core_item["subdir"]);
            foreach ($plugin_slots as $plugin_slot) {
                $slot = new ilPluginSlot($core_type, $core_item["subdir"], $plugin_slot["id"]);
                foreach ($slot->getPluginsInformation() as $plugin) {
                    if ($core_type && $slot && $core_item["subdir"] && is_array($plugin) && count($plugin) > 0) {
                        $plugins[] = $this->gatherPluginData($core_type, $slot, $core_item["subdir"], $plugin);
                    }
                }
            }
        }
    }


    /**
     * Process plugin data for table row
     *
     * @param string       $a_type
     * @param ilPluginSlot $a_slot
     * @param string       $a_slot_subdir
     * @param array        $a_plugin
     *
     * @return array
     */
    protected function gatherPluginData($a_type, ilPluginSlot $a_slot, $a_slot_subdir, array $a_plugin)
    {
        if (!$a_plugin["component_type"]) {
            return array();
        }
        $plugin_db_data = ilPlugin::getPluginRecord($a_plugin["component_type"], $a_plugin["component_name"], $a_plugin["slot_id"], $a_plugin["name"]);

        $config_class = null;
        if (ilPlugin::hasConfigureClass($a_slot->getPluginsDirectory(), $a_plugin, $plugin_db_data)
            && $this->ctrl->checkTargetClass(ilPlugin::getConfigureClassName($a_plugin))
        ) {
            $config_class = strtolower(ilPlugin::getConfigureClassName($a_plugin));
        }

        return array("slot_name"           => $a_slot->getSlotName(),
                     "component_type"      => $a_type,
                     "component_name"      => $a_slot_subdir,
                     "slot_id"             => $a_slot->getSlotId(),
                     "plugin_id"           => $a_plugin["id"],
                     "plugin_name"         => $a_plugin["name"],
                     "must_install"        => $a_plugin["must_install"],
                     "plugin_active"       => $a_plugin["is_active"],
                     "activation_possible" => $a_plugin["activation_possible"],
                     "needs_update"        => $a_plugin["needs_update"],
                     "config_class"        => $config_class,
                     "has_lang"            => (bool) sizeof(
                         ilPlugin::getAvailableLangFiles(
                             $a_slot->getPluginsDirectory() . "/" . $a_plugin["name"] . "/lang"
                         )
                     ));
    }


    /**
     * Standard Version of Fill Row. Most likely to
     * be overwritten by derived class.
     */
    protected function fillRow($a_set)
    {
        global $DIC;
        $rbacsystem = $DIC->rbac()->system();

        $this->tpl->setVariable("TXT_SLOT_NAME", $a_set["slot_name"]);
        $this->tpl->setVariable(
            "TXT_COMP_NAME",
            $a_set["component_type"] . "/" . $a_set["component_name"]
        );

        if ($a_set["plugin_active"]) {
            $this->tpl->setCurrentBlock("active");
            $this->tpl->setVariable("TXT_ACTIVE", $this->lng->txt("yes"));
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock("inactive");
            $this->tpl->setVariable("TXT_INACTIVE", $this->lng->txt("no"));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("TXT_PLUGIN_NAME", $a_set["plugin_name"]);
        $this->tpl->setVariable("TXT_PLUGIN_ID", $a_set["plugin_id"]);

        if ($rbacsystem->checkAccess('write', $_GET['ref_id'])) {
            $actions = $this->getActionMenuEntries($a_set);
            $this->tpl->setVariable("ACTION_SELECTOR", $this->getActionMenu($actions, $a_set["plugin_id"]));
        }
    }


    /**
     * Get action menu for each row
     *
     * @param string[] $actions
     * @param int      $plugin_id
     *
     * @return ilAdvancedSelectionListGUI
     */
    protected function getActionMenu(array $actions, $plugin_id)
    {
        $alist = new ilAdvancedSelectionListGUI();
        $alist->setId($plugin_id);
        $alist->setListTitle($this->lng->txt("actions"));

        foreach ($actions as $caption => $cmd) {
            $alist->addItem($caption, "", $cmd);
        }

        return $alist->getHTML();
    }


    /**
     * Get entries for action menu
     *
     * @param string[] $a_set
     *
     * @return string[]
     */
    protected function getActionMenuEntries(array $a_set)
    {
        $this->setParameter($a_set);

        $actions = array();
        $this->ctrl->setParameter($this->parent_obj, "plugin_id", $a_set["plugin_id"]);
        $this->addCommandToActions($actions, "info", "showPlugin");
        $this->ctrl->setParameter($this->parent_obj, "plugin_id", null);

        if ($a_set["must_install"]) {
            $this->addCommandToActions($actions, "cmps_install", "installPlugin");
        } else {
            if ($a_set["config_class"]) {
                $actions[$this->lng->txt("cmps_configure")]
                    = $this->ctrl->getLinkTargetByClass($a_set["config_class"], "configure");
            }

            if ($a_set["has_lang"]) {
                $this->addCommandToActions($actions, "cmps_refresh", "refreshLanguages");
            }

            if ($a_set["plugin_active"]) {
                $this->addCommandToActions($actions, "cmps_deactivate", "deactivatePlugin");
            }

            if ($a_set["activation_possible"]) {
                $this->addCommandToActions($actions, "cmps_activate", "activatePlugin");
            }

            // update button
            if ($a_set["needs_update"]) {
                $this->addCommandToActions($actions, "cmps_update", "updatePlugin");
            }

            // #17428
            $this->addCommandToActions($actions, "cmps_uninstall", "confirmUninstallPlugin");
        }

        $this->clearParameter();

        return $actions;
    }


    /**
     * Set parameter for plugin
     *
     * @param string[] $a_set
     *
     * @return void
     */
    protected function setParameter(array $a_set)
    {
        $this->ctrl->setParameter($this->parent_obj, "ctype", $a_set["component_type"]);
        $this->ctrl->setParameter($this->parent_obj, "cname", $a_set["component_name"]);
        $this->ctrl->setParameter($this->parent_obj, "slot_id", $a_set["slot_id"]);
        $this->ctrl->setParameter($this->parent_obj, "pname", $a_set["plugin_name"]);
    }


    /**
     * Clear parameter
     *
     * @return void
     */
    protected function clearParameter()
    {
        $this->ctrl->setParameter($this->parent_obj, "ctype", null);
        $this->ctrl->setParameter($this->parent_obj, "cname", null);
        $this->ctrl->setParameter($this->parent_obj, "slot_id", null);
        $this->ctrl->setParameter($this->parent_obj, "pname", null);
    }


    /**
     * Add command to actions
     *
     * @param string[]    &$actions
     * @param string       $caption not translated lang var
     * @param string       $command
     *
     * @return void
     */
    protected function addCommandToActions(array &$actions, $caption, $command)
    {
        $actions[$this->lng->txt($caption)]
            = $this->ctrl->getLinkTarget($this->parent_obj, $command);
    }
}
