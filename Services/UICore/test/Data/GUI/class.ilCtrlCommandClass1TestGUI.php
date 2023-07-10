<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilCtrlCommandClass1TestGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilCtrlCommandClass1TestGUI: ilCtrlBaseClass1TestGUI, ilCtrlBaseClass2TestGUI
 * @ilCtrl_Calls      ilCtrlCommandClass1TestGUI: ilCtrlBaseClass2TestGUI, ilCtrlCommandClass2TestGUI
 */
class ilCtrlCommandClass1TestGUI implements ilCtrlSecurityInterface
{
    /**
     * @inheritDoc
     */
    public function getUnsafeGetCommands(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getSafePostCommands(): array
    {
        return [];
    }

    public function executeCommand(): string
    {
        return self::class;
    }
}
