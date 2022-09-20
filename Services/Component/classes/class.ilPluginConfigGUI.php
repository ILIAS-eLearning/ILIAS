<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Parent class for all plugin config gui classes
 *
 * You can provide a configuration screen in the ILIAS administration if you implement the
 * class class.il<plugin_name>ConfigGUI.php which should extend ilPluginConfigGUI.
 *
 * The access the configuration class open the ILIAS Administration > Plugins > Actions (of your Plugin) > Configure
 *
 * IMPORTANT: Note, that for the configure action to be displayed in your plugins actions dropdown, you need to reload
 * the plugins control structure. You can force your plugin to do so, by updating the plugins version in plugin.php
 * and select Update in the plugins actions in the table in the plugin administration.
 *
 * @author Alex Killing <alex.killing>
 * @version $Id$
 * @ingroup ServicesComponent
 */
abstract class ilPluginConfigGUI
{
    protected ?ilPlugin $plugin_object = null;

    final public function setPluginObject(ilPlugin $a_val): void
    {
        $this->plugin_object = $a_val;
    }

    final public function getPluginObject(): ?ilPlugin
    {
        return $this->plugin_object;
    }

    /**
     * Execute command
     *
     * @param
     * @return
     */
    public function executeCommand(): void
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();
        $lng = $DIC->language();
        $tpl = $DIC['tpl'];
        $request_wrapper = $DIC->http()->wrapper()->query();
        $string_trafo = $DIC["refinery"]->kindlyTo()->string();

        $ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "ctype", $request_wrapper->retrieve("ctype", $string_trafo));
        $ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "cname", $request_wrapper->retrieve("cname", $string_trafo));
        $ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "slot_id", $request_wrapper->retrieve("slot_id", $string_trafo));
        $ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "plugin_id", $request_wrapper->retrieve("plugin_id", $string_trafo));
        $ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "pname", $request_wrapper->retrieve("pname", $string_trafo));

        $tpl->setTitle($lng->txt("cmps_plugin") . ": " . $request_wrapper->retrieve("pname", $string_trafo));
        $tpl->setDescription("");

        $ilTabs->clearTargets();

        if ($request_wrapper->retrieve("plugin_id", $string_trafo)) {
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

    abstract public function performCommand(string $cmd): void;
}
