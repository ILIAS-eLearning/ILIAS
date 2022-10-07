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

use ILIAS\Repository\PluginSlot\PluginSlotGUIRequest;

/**
 * Dispatcher to all repository object plugins
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjPluginDispatchGUI:
 */
class ilObjPluginDispatchGUI implements ilCtrlBaseClassInterface
{
    protected ilCtrl $ctrl;
    protected PluginSlotGUIRequest $request;
    protected object $gui_obj;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->request = $DIC->repository()
            ->internal()
            ->gui()
            ->pluginSlot()
            ->request();

        $this->ctrl = $DIC->ctrl();
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass();
        $cmd_class = $ilCtrl->getCmdClass();

        if ($cmd_class !== "ilobjplugindispatchgui" && $cmd_class !== "" && $cmd_class !== null) {
            $class_path = $ilCtrl->lookupClassPath($next_class);
            include_once($class_path);
            $class_name = $ilCtrl->getClassForClasspath($class_path);
            $this->gui_obj = new $class_name($this->request->getRefId());
            $ilCtrl->forwardCommand($this->gui_obj);
        } else {
            $this->processCommand($ilCtrl->getCmd());
        }
    }

    public function processCommand(string $a_cmd): void
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
    public function forward(): void
    {
        $ilCtrl = $this->ctrl;

        $type = ilObject::_lookupType($this->request->getRefId(), true);
        if ($type !== "") {
            $plugin = ilObjectPlugin::getPluginObjectByType($type);
            if ($plugin) {
                $gui_cn = "ilObj" . $plugin->getPluginName() . "GUI";
                $ilCtrl->setParameterByClass($gui_cn, "ref_id", $this->request->getRefId());
                $ilCtrl->redirectByClass($gui_cn, $this->request->getForwardCmd());
            }
        }
    }
}
