<?php

declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilInvalidTestPluginGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilValidTestPluginGUI: ilUIPluginRouterGUI
 */
class ilInvalidTestPluginGUI
{
    public function executeCommand(): string
    {
        return "Goodbye World!";
    }
}
