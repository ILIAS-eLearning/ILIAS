<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Dispatcher to all repository object plugins
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjPluginDispatchGUI:
 */
class ilObjPluginDispatchGUI
{
    protected ilCtrl $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
    }
    
    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;
        
        $next_class = $ilCtrl->getNextClass();
        $cmd_class = $ilCtrl->getCmdClass();

        if ($cmd_class != "ilobjplugindispatchgui" && $cmd_class != "") {
            $class_path = $ilCtrl->lookupClassPath($next_class);
            include_once($class_path);
            $class_name = $ilCtrl->getClassForClasspath($class_path);
            //echo "-".$class_name."-".$class_path."-";
            $this->gui_obj = new $class_name($_GET["ref_id"]);
            $ilCtrl->forwardCommand($this->gui_obj);
        } else {
            $this->processCommand($ilCtrl->getCmd());
        }
    }
    
    public function processCommand(string $a_cmd) : void
    {
        switch ($a_cmd) {
            case "forward":
                $this->forward();
                break;
        }
    }
    
    /**
     * Forward command to plugin
     */
    public function forward() : void
    {
        $ilCtrl = $this->ctrl;
        
        $type = ilObject::_lookupType($_GET["ref_id"], true);
        if ($type != "") {
            $plugin = ilObjectPlugin::getPluginObjectByType($type);
            if ($plugin) {
                $gui_cn = "ilObj" . $plugin->getPluginName() . "GUI";
                $ilCtrl->setParameterByClass($gui_cn, "ref_id", $_GET["ref_id"]);
                $ilCtrl->redirectByClass($gui_cn, $_GET["forwardCmd"]);
            }
        }
    }
}
