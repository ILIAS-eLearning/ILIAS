<?php

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
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * Base class of the commands of the LTI view, which belong to no object.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 *
 * @ilCtrl_Calls ilLTIRouterGUI: ilLTIViewGUI
 */
class ilLTIRouterGUI implements ilCtrlBaseClassInterface
{
    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        global $DIC;

        if ($DIC->ctrl()->getNextClass($this) === strtolower(ilLTIViewGUI::class)) {
            $DIC->ctrl()->forwardCommand(ilLTIViewGUI::getInstance());
        }
    }
}
