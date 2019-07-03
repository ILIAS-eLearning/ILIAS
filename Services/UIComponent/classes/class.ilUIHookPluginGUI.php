<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\GlobalScreen\Scope\Layout\ModifierServices;
use ILIAS\GlobalScreen\Scope\Layout\Provider\FinalModificationProvider;

/**
 * User interface hook class
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ingroup ServicesUIComponent
 */
class ilUIHookPluginGUI // implements FinalModificationProvider
{

    /**
     * @var ilPlugin
     */
    protected $plugin_object = null;
    const UNSPECIFIED = "";
    const KEEP = "";
    const REPLACE = "r";
    const APPEND = "a";
    const PREPEND = "p";


    /**
     * @param ilPlugin $a_val
     */
    final function setPluginObject(ilPlugin $a_val)
    {
        $this->plugin_object = $a_val;
    }


    /**
     * @return ilPlugin
     */
    final function getPluginObject() : ilPlugin
    {
        return $this->plugin_object;
    }


    /**
     *
     * @param       $a_comp
     * @param       $a_part
     * @param array $a_par
     *
     * @return array
     * @deprecated
     *
     */
    public function getHTML($a_comp, $a_part, $a_par = array())
    {
        return array("mode" => ilUIHookPluginGUI::KEEP, "html" => "");
    }


    /**
     * @param       $a_comp
     * @param       $a_part
     * @param array $a_par
     *
     * @deprecated
     *
     */
    public function modifyGUI($a_comp, $a_part, $a_par = array())
    {
    }


    /**
     * @inheritDoc
     */
    public function modifyGlobalLayout(ModifierServices $modifier_services) : void
    {
        // TODO: Implement modifyGlobalLayout() method.
    }


    /**
     * @inheritDoc
     */
    public final function getProviderNameForPresentation() : string
    {
        return $this->getPluginObject()->getPluginName();
    }


    /**
     * @inheritDoc
     */
    public final function getFullyQualifiedClassName() : string
    {
        return get_class($this);
    }


    /**
     * Modify HTML based on default html and plugin response
     *
     * @param string    default html
     * @param string    resonse from plugin
     *
     * @return    string    modified html
     */
    public final function modifyHTML($a_def_html, $a_resp)
    {
        switch ($a_resp["mode"]) {
            case ilUIHookPluginGUI::REPLACE:
                $a_def_html = $a_resp["html"];
                break;
            case ilUIHookPluginGUI::APPEND:
                $a_def_html .= $a_resp["html"];
                break;
            case ilUIHookPluginGUI::PREPEND:
                $a_def_html = $a_resp["html"] . $a_def_html;
                break;
        }

        return $a_def_html;
    }


    /**
     * Goto script hook
     *
     * Can be used to interfere with the goto script behaviour
     */

    public function gotoHook()
    {
    }


    /**
     * Goto script hook
     *
     * Can be used to interfere with the goto script behaviour
     */
    public function checkGotoHook($a_target)
    {
        return array("target" => false);
    }
}
