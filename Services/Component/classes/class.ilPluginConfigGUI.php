<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Parent class for all plugin config gui classes
 *
 * @author Alex Killing <alex.killing>
 * @version $Id$
 * @ingroup ServicesComponent
 */
abstract class ilPluginConfigGUI
{
    protected $plugin_object = null;
    
    /**
     * Set plugin object
     *
     * @param	object	plugin object
     */
    final public function setPluginObject($a_val)
    {
        $this->plugin_object = $a_val;
    }

    /**
     * Get plugin object
     *
     * @return ilPlugin	 object
     */
    final public function getPluginObject()
    {
        return $this->plugin_object;
    }

    /**
     * Execute command
     *
     * @param
     * @return
     */
    public function executeCommand()
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();
        $lng = $DIC->language();
        $tpl = $DIC['tpl'];

        $ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "ctype", $_GET["ctype"]);
        $ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "cname", $_GET["cname"]);
        $ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "slot_id", $_GET["slot_id"]);
        $ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "plugin_id", $_GET["plugin_id"]);
        $ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "pname", $_GET["pname"]);

        $tpl->setTitle($lng->txt("cmps_plugin") . ": " . $_GET["pname"]);
        $tpl->setDescription("");

        $ilTabs->clearTargets();
        
        if ($_GET["plugin_id"]) {
            $ilTabs->setBackTarget(
                $lng->txt("cmps_plugin"),
                $ilCtrl->getLinkTargetByClass("ilobjcomponentsettingsgui", "showPlugin")
            );
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("cmps_plugins"),
                $ilCtrl->getLinkTargetByClass("ilobjcomponentsettingsgui", "listPlugins")
            );
        }

        $this->performCommand($ilCtrl->getCmd("configure"));
    }

    abstract public function performCommand($cmd);
}
