<?php

declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlBaseClass1TestGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilCtrlBaseClass1TestGUI: ilCtrlCommandClass1TestGUI
 */
class ilCtrlBaseClass1TestGUI implements ilCtrlBaseClassInterface
{
    public function executeCommand(): string
    {
        return self::class;
    }
}
