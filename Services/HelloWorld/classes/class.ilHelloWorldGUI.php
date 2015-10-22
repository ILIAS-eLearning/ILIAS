<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Hello World GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilHelloWorldGUI: ilTheTimeGUI
 */
class ilHelloWorldGUI
{
    /**
     * Constructor
     */
    function __construct()
    {
        global $tpl;

        // get the standard template
        $tpl->getStandardTemplate();
    }

    /**
     * Execute command
     *
     */
    function executeCommand()
    {

        global $ilCtrl, $tpl;

        // determine next class in the call structure
        $next_class = $ilCtrl->getNextClass($this);

        switch($next_class)
        {
            // this would be the way to call a sub-GUI class
           case "ilthetimegui":
               require_once 'Services/HelloWorld/classes/class.ilTheTimeGUI.php';
                $bar_gui = new ilTheTimeGUI();
                $ret = $ilCtrl->forwardCommand($bar_gui);
                break;

            // process command, if current class is responsible to do so
            default:

                // determin the current command (take "view" as default)
                $cmd = $ilCtrl->getCmd("view");
                if (in_array($cmd, array("view", "goodbye")))
                {
                    $this->$cmd();
                }
                break;
        }

        $tpl->show();
    }

    /**
     * View hello world...
     *
     */
    function view()
    {
        global $tpl, $ilCtrl;
        $helloTemplate = new ilTemplate('tpl.hello.html', true, true, 'Services/HelloWorld');

        $helloTemplate->setCurrentBlock('helloBlock');
        $helloTemplate->setVariable('HELLO', 'hello world.');
        $helloTemplate->setVariable('LINK', $ilCtrl->getLinkTarget($this, 'goodbye'));
        $helloTemplate->parseCurrentBlock();

        $tpl->setContent($helloTemplate->get());
    }

    function goodbye() {
        global $tpl, $ilCtrl;
        $helloTemplate = new ilTemplate('tpl.hello.html', true, true, 'Services/HelloWorld');

        $helloTemplate->setCurrentBlock('helloBlock');
        $helloTemplate->setVariable('HELLO', 'goodbye cruel world.');
        $helloTemplate->setVariable('LINK', $ilCtrl->getLinkTargetByClass('ilTheTimeGUI', 'view'));
        $helloTemplate->parseCurrentBlock();

        $tpl->setContent($helloTemplate->get());
    }

}

?>