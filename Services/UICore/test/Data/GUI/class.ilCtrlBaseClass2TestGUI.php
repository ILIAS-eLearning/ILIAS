<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlBaseClass2TestGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilCtrlBaseClass2TestGUI: ilCtrlCommandClass1TestGUI
 */
class ilCtrlBaseClass2TestGUI implements ilCtrlBaseClassInterface
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