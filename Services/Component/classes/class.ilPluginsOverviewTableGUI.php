<?php
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI class for components listing
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilPluginsOverviewTableGUI extends ilTable2GUI
{
    public const F_PLUGIN_NAME = "plugin_name";
    public const F_PLUGIN_ID = "plugin_id";
    public const F_SLOT_NAME = "slot_name";
    public const F_COMPONENT_NAME = "component_name";
    public const F_PLUGIN_ACTIVE = "plugin_active";
    /**
     * @var array
     */
    protected $filter_data;

    public function __construct(ilObjComponentSettingsGUI $a_parent_obj, array $filter_data, string $a_parent_cmd = "")
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->filter_data = $filter_data;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId("cmpspl");

        $this->addColumn($this->lng->txt("cmps_plugin"), self::F_PLUGIN_NAME);
        $this->addColumn($this->lng->txt("id"), self::F_PLUGIN_ID);
        $this->addColumn($this->lng->txt("cmps_plugin_slot"), self::F_SLOT_NAME);
        $this->addColumn($this->lng->txt("cmps_component"), self::F_COMPONENT_NAME);
        $this->addColumn($this->lng->txt("active"), self::F_PLUGIN_ACTIVE);
        $this->addColumn($this->lng->txt("action"));

        $this->setDefaultOrderField(self::F_PLUGIN_NAME);

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.plugin_overview_row.html",
            "Services/Component"
        );
        $this->getComponents();
        $this->setLimit(10000);
    }

    protected function getComponents() : void
    {
        $plugins = [];
        $modules = $this->getModulesCoreItems();
        $this->addPluginData($plugins, $modules, IL_COMP_MODULE);

        $services = $this->getServicesCoreItems();
        $this->addPluginData($plugins, $services, IL_COMP_SERVICE);

        // apply filters

        $active_filters = array_filter($this->filter_data, static function ($value) : bool {
            return !empty($value);
        });

        $plugins = array_filter($plugins, static function (array $plugin_data) use ($active_filters) : bool {
            $matches_filter = true;
            if (isset($active_filters[self::F_PLUGIN_NAME])) {
                $matches_filter = strpos($plugin_data[self::F_PLUGIN_NAME], $active_filters[self::F_PLUGIN_NAME]) !== false;
            }
            if (isset($active_filters[self::F_PLUGIN_ID])) {
                $matches_filter = strpos($plugin_data[self::F_PLUGIN_ID], $active_filters[self::F_PLUGIN_ID]) !== false;
            }
            if (isset($active_filters[self::F_PLUGIN_ACTIVE])) {
                $v = (int) $active_filters[self::F_PLUGIN_ACTIVE] === 1;
                $matches_filter = $plugin_data[self::F_PLUGIN_ACTIVE] === $v && $matches_filter;
            }
            if (isset($active_filters[self::F_SLOT_NAME])) {
                $matches_filter = in_array($plugin_data[self::F_SLOT_NAME], $active_filters[self::F_SLOT_NAME], true) && $matches_filter;
            }
            if (isset($active_filters[self::F_COMPONENT_NAME])) {
                $matches_filter = in_array($plugin_data['component_type'] . '/' . $plugin_data['component_name'], $active_filters[self::F_COMPONENT_NAME], true) && $matches_filter;
            }

            return $matches_filter;
        });

        $this->setData($plugins);
    }

    protected function getModulesCoreItems() : array
    {
        return ilModule::getAvailableCoreModules();
    }

    protected function getServicesCoreItems() : array
    {
        return ilService::getAvailableCoreServices();
    }

    /**
     * @param array $plugins
     * @param array $core_items
     * @param       $core_type
     */
    protected function addPluginData(array &$plugins, array $core_items, string $core_type)
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
     * @param string       $a_type
     * @param ilPluginSlot $a_slot
     * @param string       $a_slot_subdir
     * @param array        $a_plugin
     * @return array
     * @throws ilPluginException
     */
    protected function gatherPluginData(string $a_type, ilPluginSlot $a_slot, string $a_slot_subdir, array $a_plugin) : array
    {
        if (!$a_plugin["component_type"]) {
            return array();
        }
        $plugin_db_data = ilPlugin::getPluginRecord($a_plugin["component_type"], $a_plugin[self::F_COMPONENT_NAME], $a_plugin["slot_id"], $a_plugin["name"]);

        $config_class = null;
        if (ilPlugin::hasConfigureClass($a_slot->getPluginsDirectory(), $a_plugin, $plugin_db_data)) {
            $config_class = strtolower(ilPlugin::getConfigureClassName($a_plugin));
        }

        return array(
            self::F_SLOT_NAME => $a_slot->getSlotName(),
            "component_type" => $a_type,
            self::F_COMPONENT_NAME => $a_slot_subdir,
            "slot_id" => $a_slot->getSlotId(),
            self::F_PLUGIN_ID => $a_plugin["id"],
            self::F_PLUGIN_NAME => $a_plugin["name"],
            "must_install" => $a_plugin["must_install"],
            self::F_PLUGIN_ACTIVE => $a_plugin["is_active"],
            "activation_possible" => $a_plugin["activation_possible"],
            "needs_update" => $a_plugin["needs_update"],
            "config_class" => $config_class,
            "has_lang" => (bool) sizeof(
                ilPlugin::getAvailableLangFiles(
                    $a_slot->getPluginsDirectory() . "/" . $a_plugin["name"] . "/lang"
                )
            )
        );
    }

    protected function fillRow($a_set) : void
    {
        global $DIC;
        $rbacsystem = $DIC->rbac()->system();

        $this->tpl->setVariable("TXT_SLOT_NAME", $a_set[self::F_SLOT_NAME]);
        $this->tpl->setVariable(
            "TXT_COMP_NAME",
            $a_set["component_type"] . "/" . $a_set[self::F_COMPONENT_NAME]
        );

        if ($a_set[self::F_PLUGIN_ACTIVE]) {
            $this->tpl->setCurrentBlock("active");
            $this->tpl->setVariable("TXT_ACTIVE", $this->lng->txt("yes"));
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock("inactive");
            $this->tpl->setVariable("TXT_INACTIVE", $this->lng->txt("no"));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("TXT_PLUGIN_NAME", $a_set[self::F_PLUGIN_NAME]);
        $this->tpl->setVariable("TXT_PLUGIN_ID", $a_set[self::F_PLUGIN_ID]);

        if ($rbacsystem->checkAccess('write', $_GET['ref_id'])) {
            $actions = $this->getActionMenuEntries($a_set);
            $this->tpl->setVariable("ACTION_SELECTOR", $this->getActionMenu($actions, $a_set[self::F_PLUGIN_ID]));
        }
    }

    /**
     * @param array  $actions
     * @param string $plugin_id
     * @return string
     */
    protected function getActionMenu(array $actions, string $plugin_id) : string
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
     * @param array $a_set
     * @return array
     */
    protected function getActionMenuEntries(array $a_set) : array
    {
        $this->setParameter($a_set);

        $actions = array();
        $this->ctrl->setParameter($this->parent_obj, self::F_PLUGIN_ID, $a_set[self::F_PLUGIN_ID]);
        $this->addCommandToActions($actions, "info", "showPlugin");

        if ($a_set["must_install"]) {
            $this->addCommandToActions($actions, "cmps_install", ilObjComponentSettingsGUI::CMD_INSTALL_PLUGIN);
        } else {
            if ($a_set["config_class"]) {
                $actions[$this->lng->txt("cmps_configure")]
                    = $this->ctrl->getLinkTargetByClass($a_set["config_class"], ilObjComponentSettingsGUI::CMD_CONFIGURE);
            }

            if ($a_set["has_lang"]) {
                $this->addCommandToActions($actions, "cmps_refresh", ilObjComponentSettingsGUI::CMD_REFRESH_LANGUAGES);
            }

            if ($a_set[self::F_PLUGIN_ACTIVE]) {
                $this->addCommandToActions($actions, "cmps_deactivate", ilObjComponentSettingsGUI::CMD_DEACTIVATE_PLUGIN);
            }

            if ($a_set["activation_possible"]) {
                $this->addCommandToActions($actions, "cmps_activate", ilObjComponentSettingsGUI::CMD_ACTIVATE_PLUGIN);
            }

            // update button
            if ($a_set["needs_update"]) {
                $this->addCommandToActions($actions, "cmps_update", ilObjComponentSettingsGUI::CMD_UPDATE_PLUGIN);
            }

            // #17428
            $this->addCommandToActions($actions, "cmps_uninstall", ilObjComponentSettingsGUI::CMD_CONFIRM_UNINSTALL_PLUGIN);
        }

        $this->clearParameter();

        return $actions;
    }

    protected function setParameter(array $a_set) : void
    {
        $this->ctrl->setParameter($this->parent_obj, ilObjComponentSettingsGUI::P_CTYPE, $a_set["component_type"]);
        $this->ctrl->setParameter($this->parent_obj, ilObjComponentSettingsGUI::P_CNAME, $a_set[self::F_COMPONENT_NAME]);
        $this->ctrl->setParameter($this->parent_obj, ilObjComponentSettingsGUI::P_SLOT_ID, $a_set["slot_id"]);
        $this->ctrl->setParameter($this->parent_obj, ilObjComponentSettingsGUI::P_PLUGIN_NAME, $a_set[self::F_PLUGIN_NAME]);
    }

    protected function clearParameter() : void
    {
        $this->ctrl->setParameter($this->parent_obj, ilObjComponentSettingsGUI::P_CTYPE, null);
        $this->ctrl->setParameter($this->parent_obj, ilObjComponentSettingsGUI::P_CNAME, null);
        $this->ctrl->setParameter($this->parent_obj, ilObjComponentSettingsGUI::P_SLOT_ID, null);
        $this->ctrl->setParameter($this->parent_obj, ilObjComponentSettingsGUI::P_PLUGIN_NAME, null);
    }

    protected function addCommandToActions(array &$actions, string $caption, string $command) : void
    {
        $actions[$this->lng->txt($caption)]
            = $this->ctrl->getLinkTarget($this->parent_obj, $command);
    }
}
