<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once("./Services/COPage/classes/class.ilPCPlugged.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
 * Class ilPCPluggedGUI
 *
 * User Interface for plugged page component
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
class ilPCPluggedGUI extends ilPageContentGUI
{
    /**
     * @var ilPluginAdmin
     */
    protected $plugin_admin;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    protected $current_plugin = null;
    
    /**
    * Constructor
    * @access	public
    */
    public function __construct(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_plugin_name = "", $a_pc_id = "")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];

        $this->setPluginName($a_plugin_name);
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    /**
    * Set PluginName.
    *
    * @param	string	$a_pluginname	PluginName
    */
    public function setPluginName($a_pluginname)
    {
        $this->pluginname = $a_pluginname;
    }

    /**
    * Get PluginName.
    *
    * @return	string	PluginName
    */
    public function getPluginName()
    {
        return $this->pluginname;
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilPluginAdmin = $this->plugin_admin;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $ilTabs->setBackTarget($lng->txt("pg"), $ilCtrl->getLinkTarget($this, "returnToParent"));
        
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get all plugins and check, whether next class belongs to one
        // of them, then forward
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(
            IL_COMP_SERVICE,
            "COPage",
            "pgcp"
        );
        foreach ($pl_names as $pl_name) {
            if ($next_class == strtolower("il" . $pl_name . "plugingui")) {
                $plugin = $ilPluginAdmin->getPluginObject(
                    IL_COMP_SERVICE,
                    "COPage",
                    "pgcp",
                    $pl_name
                );
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

    /**
    * Insert new section form.
    */
    public function insert()
    {
        $this->edit(true);
    }

    /**
     * Edit section form.
     */
    public function edit($a_insert = false)
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilPluginAdmin = $this->plugin_admin;
        
        $this->displayValidationError();
        
        // edit form
        if ($a_insert) {
            $plugin_name = $this->getPluginName();
        } else {
            $plugin_name = $this->content_obj->getPluginName();
        }
        if ($ilPluginAdmin->isActive(IL_COMP_SERVICE, "COPage", "pgcp", $plugin_name)) {
            $plugin_obj = $ilPluginAdmin->getPluginObject(
                IL_COMP_SERVICE,
                "COPage",
                "pgcp",
                $plugin_name
            );
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
        
        $tpl->setContent($html);
    }


    /**
     * Create new element
     */
    public function createElement(array $a_properties)
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

    /**
     * Update element
     */
    public function updateElement(array $a_properties)
    {
        $this->content_obj->setProperties($a_properties);
        $this->content_obj->setPluginVersion($this->current_plugin->getVersion());
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            return true;
        }
        return false;
    }
    
    /**
     * Return to parent
     */
    public function returnToParent()
    {
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }
}
