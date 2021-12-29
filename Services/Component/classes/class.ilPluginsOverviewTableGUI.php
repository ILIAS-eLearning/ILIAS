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

    protected ilComponentRepository $component_repository;

    public function __construct(ilObjComponentSettingsGUI $a_parent_obj, array $filter_data, string $a_parent_cmd = "")
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->filter_data = $filter_data;
        $this->component_repository = $DIC["component.repository"];

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId("cmpspl");

        $this->addColumn($this->lng->txt("cmps_plugin"), self::F_PLUGIN_NAME);
        $this->addColumn($this->lng->txt("id"), self::F_PLUGIN_ID);
        $this->addColumn($this->lng->txt("cmps_plugin_slot"), self::F_SLOT_NAME);
        $this->addColumn($this->lng->txt("cmps_component"), self::F_COMPONENT_NAME);
        $this->addColumn($this->lng->txt("active"), self::F_PLUGIN_ACTIVE);
        $this->addColumn($this->lng->txt("action"));

        $this->setExternalSorting(true);

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.plugin_overview_row.html",
            "Services/Component"
        );
        $this->setPluginData();
        $this->setLimit(10000);
    }

    protected function setPluginData() : void
    {
        $plugins = [];

        foreach ($this->component_repository->getPlugins() as $id => $plugin) {
            $plugins[] = $plugin;
        }

        // TODO: restore sortation and filters.

        // use this instead of setData to circumvent checks in DEVMODE that require
        // rows to be arrays.
        $this->row_data = $plugins;
        return;
    }

    protected function fillRow(array $a_set) : void
    {
        global $DIC;
        $rbacsystem = $DIC->rbac()->system();

        $this->tpl->setVariable("TXT_SLOT_NAME", $a_set->getPluginSlot()->getName());
        $this->tpl->setVariable("TXT_COMP_NAME", $a_set->getComponent()->getQualifiedName());

        if ($a_set->isActive()) {
            $this->tpl->setCurrentBlock("active");
            $this->tpl->setVariable("TXT_ACTIVE", $this->lng->txt("yes"));
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock("inactive");
            $this->tpl->setVariable("TXT_INACTIVE", $this->lng->txt("no"));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("TXT_PLUGIN_NAME", $a_set->getName());
        $this->tpl->setVariable("TXT_PLUGIN_ID", $a_set->getId());

        if ($rbacsystem->checkAccess('write', $_GET['ref_id'])) {
            $actions = $this->getActionMenuEntries($a_set);
            $this->tpl->setVariable("ACTION_SELECTOR", $this->getActionMenu($actions, $a_set->getId()));
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
    protected function getActionMenuEntries(ilPluginInfo $plugin) : array
    {
        global $DIC;
        $this->setParameter($plugin);

        $language_handler = new ilPluginLanguage($plugin);

        $actions = array();
        $this->ctrl->setParameter($this->parent_obj, self::F_PLUGIN_ID, $plugin->getId());
        $this->addCommandToActions($actions, "info", "showPlugin");

        if (!$plugin->isInstalled()) {
            $this->addCommandToActions($actions, "cmps_install", ilObjComponentSettingsGUI::CMD_INSTALL_PLUGIN);
        } else {
            if (ilPlugin::hasConfigureClass($plugin)) {
                $actions[$this->lng->txt("cmps_configure")]
                    = $this->ctrl->getLinkTargetByClass($a_set["config_class"], ilObjComponentSettingsGUI::CMD_CONFIGURE);
            }

            if ($language_handler->hasAvailableLangFiles()) {
                $this->addCommandToActions($actions, "cmps_refresh", ilObjComponentSettingsGUI::CMD_REFRESH_LANGUAGES);
            }

            if ($plugin->isActive()) {
                $this->addCommandToActions($actions, "cmps_deactivate", ilObjComponentSettingsGUI::CMD_DEACTIVATE_PLUGIN);
            }

            if ($plugin->isActivationPossible() && !$plugin->isActive()) {
                $this->addCommandToActions($actions, "cmps_activate", ilObjComponentSettingsGUI::CMD_ACTIVATE_PLUGIN);
            }

            // update button
            if ($plugin->isUpdateRequired()) {
                $this->addCommandToActions($actions, "cmps_update", ilObjComponentSettingsGUI::CMD_UPDATE_PLUGIN);
            }

            // #17428
            $this->addCommandToActions($actions, "cmps_uninstall", ilObjComponentSettingsGUI::CMD_CONFIRM_UNINSTALL_PLUGIN);
        }

        $this->clearParameter();

        return $actions;
    }

    protected function setParameter(ilPluginInfo $plugin) : void
    {
        $this->ctrl->setParameter($this->parent_obj, ilObjComponentSettingsGUI::P_CTYPE, $plugin->getComponent()->getType());
        $this->ctrl->setParameter($this->parent_obj, ilObjComponentSettingsGUI::P_CNAME, $plugin->getComponent()->getName());
        $this->ctrl->setParameter($this->parent_obj, ilObjComponentSettingsGUI::P_SLOT_ID, $plugin->getPluginSlot()->getId());
        $this->ctrl->setParameter($this->parent_obj, ilObjComponentSettingsGUI::P_PLUGIN_NAME, $plugin->getName());
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
