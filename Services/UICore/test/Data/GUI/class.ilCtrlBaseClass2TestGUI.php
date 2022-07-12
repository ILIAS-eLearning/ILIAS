<?php declare(strict_types = 1);

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
 * Class ilCtrlBaseClass2TestGUI
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy ilCtrlBaseClass2TestGUI : ilCtrlCommandClass1TestGUI
 * @ilCtrl_Calls      ilCtrlBaseClass2TestGUI : ilCtrlCommandClass1TestGUI
 */
class ilCtrlBaseClass2TestGUI implements ilCtrlBaseClassInterface
{
    public function executeCommand() : string
    {
        return self::class;
    }
}
