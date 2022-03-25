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
    public function executeCommand() : string
    {
        return self::class;
    }
}
