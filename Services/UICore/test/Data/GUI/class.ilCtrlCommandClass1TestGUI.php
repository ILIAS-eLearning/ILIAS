<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlCommandClass1TestGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilCtrlCommandClass1TestGUI: ilCtrlBaseClass1TestGUI, ilCtrlBaseClass2TestGUI
 * @ilCtrl_Calls      ilCtrlCommandClass1TestGUI: ilCtrlCommandClass2TestGUI
 */
class ilCtrlCommandClass1TestGUI implements ilCtrlSecurityInterface
{
    /**
     * @inheritDoc
     */
    public function getUnsafeGetCommands() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getSafePostCommands() : array
    {
        return [];
    }

    public function executeCommand() : string
    {
        return self::class;
    }
}