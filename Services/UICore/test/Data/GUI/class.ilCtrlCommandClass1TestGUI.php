<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlCommandClass1TestGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrlStructureCalls(
 *      children={"ilCtrlCommandClass2TestGUI"},
 *      parents={"ilCtrlBaseClass1TestGUI", "ilCtrlBaseClass2TestGUI"}
 * )
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
