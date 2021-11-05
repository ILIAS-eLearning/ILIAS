<?php

/**
 * Class ilCtrlBaseClassTestGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilCtrlBaseClassTestGUI: ilCtrlCommandClass1TestGUI
 */
final class ilCtrlBaseClassTestGUI implements ilCtrlBaseClassInterface
{
    private ilCtrlInterface $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
    }

    public function executeCommand() : string
    {
        switch ($this->ctrl->getNextClass($this)) {
            case strtolower(ilCtrlCommandClass1TestGUI::class):
                return $this->ctrl->forwardCommand(new ilCtrlCommandClass1TestGUI());

            case strtolower(ilCtrlCommandClass2TestGUI::class):
                return $this->ctrl->forwardCommand(new ilCtrlCommandClass2TestGUI());

            default:
                $cmd = $this->ctrl->getCmd();
                return $this->{$cmd}();
        }
    }

    private function index() : string
    {
        return "Hello World!";
    }
}