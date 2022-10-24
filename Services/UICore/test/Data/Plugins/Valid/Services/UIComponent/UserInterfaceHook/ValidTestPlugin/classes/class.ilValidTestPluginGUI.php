<?php

declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilValidTestPluginGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilValidTestPluginGUI: ilCtrlBaseClass1TestGUI
 */
class ilValidTestPluginGUI
{
    public function executeCommand(): string
    {
        return "Hello World!";
    }
}
