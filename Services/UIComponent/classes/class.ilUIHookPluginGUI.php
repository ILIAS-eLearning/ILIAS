<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUIHookPluginGUI
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 */
class ilUIHookPluginGUI
{

    /**
     * @var ilUserInterfaceHookPlugin
     */
    protected $plugin_object = null;
    public const UNSPECIFIED = '';
    public const KEEP = '';
    public const REPLACE = 'r';
    public const APPEND = 'a';
    public const PREPEND = 'p';


    /**
     * @param ilUserInterfaceHookPlugin $a_val
     */
    final public function setPluginObject($a_val)
    {
        $this->plugin_object = $a_val;
    }


    /**
     * @return ilUserInterfaceHookPlugin
     */
    final public function getPluginObject()
    {
        return $this->plugin_object;
    }


    /**
     * @param string $a_comp component
     * @param string $a_part string that identifies the part of the UI that is handled
     * @param array  $a_par  array of parameters (depend on $a_comp and $a_part)
     *
     * @return array array with entries "mode" => modification mode, "html" => your html
     * @deprecated Note this method is deprecated. There are several issues with hacking into already rendered html
     *             as provided here:
     *             - The generation of html might be performed twice (especially if REPLACE is used).
     *             - There is limited access to data used to generate the original html. If needed this data needs to be gathered again.
     *             - If an element inside the html needs to be changed, some crude string replace magic is needed.
     *
     *
     * Modify HTML output of GUI elements. Modifications modes are:
     * - ilUIHookPluginGUI::KEEP (No modification)
     * - ilUIHookPluginGUI::REPLACE (Replace default HTML with your HTML)
     * - ilUIHookPluginGUI::APPEND (Append your HTML to the default HTML)
     * - ilUIHookPluginGUI::PREPEND (Prepend your HTML to the default HTML)
     *
     */
    public function getHTML($a_comp, $a_part, $a_par = array())
    {
        return array('mode' => self::KEEP, 'html' => '');
    }


    /**
     * @param string $a_comp component
     * @param string $a_part string that identifies the part of the UI that is handled
     * @param array  $a_par  array of parameters (depend on $a_comp and $a_part)
     *
     * @deprecated Note this method is deprecated. User Interface components are migrated towards the UIComponents and
     *             Global Screen which do not make use of the mechanism provided here. Make use of the extension possibilities provided
     *             by Global Screen and UI Components instead.
     *
     * In ILIAS 6.0 still working for working for:
     * - $a_comp="Services/Ini" ; $a_part="init_style"
     * - $a_comp="" ; $a_part="tabs"
     * - $a_comp="" ; $a_part="sub_tabs"
     *
     * Allows to modify user interface objects before they generate their output.
     *
     */
    public function modifyGUI($a_comp, $a_part, $a_par = array())
    {
    }


    /**
     * @param string    default html
     * @param string    response from plugin
     *
     * @return    string    modified html
     * @deprecated Reason, see getHTML
     *
     * Modify HTML based on default html and plugin response
     *
     */
    final public function modifyHTML($a_def_html, $a_resp)
    {
        switch ($a_resp['mode']) {
            case self::REPLACE:
                $a_def_html = $a_resp['html'];
                break;
            case self::APPEND:
                $a_def_html .= $a_resp['html'];
                break;
            case self::PREPEND:
                $a_def_html = $a_resp['html'] . $a_def_html;
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
        return array('target' => false);
    }
}