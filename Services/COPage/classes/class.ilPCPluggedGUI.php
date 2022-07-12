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

/**
 * User Interface for plugged page component
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCPluggedGUI extends ilPageContentGUI
{
    protected string $pluginname = "";
    protected ilTabsGUI $tabs;
    protected ?ilPageComponentPlugin $current_plugin = null;
    protected ilComponentRepository $component_repository;
    protected ilComponentFactory $component_factory;
    
    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_plugin_name = "",
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->component_repository = $DIC["component.repository"];
        $this->component_factory = $DIC["component.factory"];
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];

        $this->setPluginName($a_plugin_name);
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function setPluginName(string $a_pluginname) : void
    {
        $this->pluginname = $a_pluginname;
    }

    public function getPluginName() : string
    {
        return $this->pluginname;
    }

    /**
     * @return mixed
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ret = "";
        
        $ilTabs->setBackTarget($lng->txt("pg"), $ilCtrl->getLinkTarget($this, "returnToParent"));
        
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get all plugins and check, whether next class belongs to one
        // of them, then forward
        $plugins = $this->component_repository->getPluginSlotById("pgcp")->getActivePlugins();
        foreach ($plugins as $pl) {
            $pl_name = $pl->getName();
            if ($next_class == strtolower("il" . $pl_name . "plugingui")) {
                $plugin = $this->component_factory->getPlugin($pl->getId());
                $plugin->setPageObj($this->getPage());
                $this->current_plugin = $plugin;
                $this->setPluginName($pl_name);
                $gui_obj = $plugin->getUIClassInstance();
                $gui_obj->setPCGUI($this);
                $ret = $this->ctrl->forwardCommand($gui_obj);
            }
        }

        // get current command
        $cmd = $this->ctrl->getCmd();

        if ($next_class == "" || $next_class == "ilpcpluggedgui") {
            $ret = $this->$cmd();
        }

        return $ret;
    }

    public function insert() : void
    {
        $this->edit(true);
    }

    public function edit(bool $a_insert = false) : void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $html = "";
        
        $this->displayValidationError();
        
        // edit form
        if ($a_insert) {
            $plugin_name = $this->getPluginName();
        } else {
            $plugin_name = $this->content_obj->getPluginName();
        }
        $plugin = $this->component_repository->getPluginByName($plugin_name);
        if ($plugin->isActive()) {
            $plugin_obj = $this->component_factory->getPlugin($plugin->getId());
            $plugin_obj->setPageObj($this->getPage());
            $gui_obj = $plugin_obj->getUIClassInstance();
            $gui_obj->setPCGUI($this);
            if ($a_insert) {
                $gui_obj->setMode(ilPageComponentPlugin::CMD_INSERT);
            } else {
                $gui_obj->setMode(ilPageComponentPlugin::CMD_EDIT);
            }
            $html = $ilCtrl->getHTML($gui_obj);
        }

        if ($html != "") {
            $tpl->setContent($html);
        }
    }

    public function createElement(array $a_properties) : bool
    {
        $this->content_obj = new ilPCPlugged($this->getPage());
        $this->content_obj->create(
            $this->pg_obj,
            $this->hier_id,
            $this->pc_id,
            $this->current_plugin->getPluginName(),
            $this->current_plugin->getVersion()
        );
        $this->content_obj->setProperties($a_properties);
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            return true;
        }
        return false;
    }

    public function updateElement(array $a_properties) : bool
    {
        $this->content_obj->setProperties($a_properties);
        $this->content_obj->setPluginVersion($this->current_plugin->getVersion());
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            return true;
        }
        return false;
    }
    
    public function returnToParent() : void
    {
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }
}
