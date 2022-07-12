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
    public function executeCommand() : string
    {
        return self::class;
    }

    public function getHTML(array $args = null) : string
    {
        if (!empty($args)) {
            return $args[array_key_first($args)];
        }

        return 'foo';
    }
}
