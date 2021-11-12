<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlCommandClass2TestGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilCtrlCommandClass2TestGUI: ilCtrlCommandClass1TestGUI
 */
class ilCtrlCommandClass2TestGUI
{
    private ilCtrlInterface $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
    }

    public function executeCommand() : string
    {
        $cmd = $this->ctrl->getCmd();
        return $this->{$cmd}();
    }

    private function index() : string
    {
        return "Hello World!";
    }
}