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
 * Group user actions
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup components\ILIASGroup
 *
 * @ilCtrl_Calls ilGroupUserActionsGUI: ilGroupAddToGroupActionGUI
 */
class ilGroupUserActionsGUI
{
    protected ilCtrl $ctrl;

    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        switch ($next_class) {
            case "ilgroupaddtogroupactiongui":
                $gui = new ilGroupAddToGroupActionGUI();
                $ctrl->forwardCommand($gui);
                break;
        }
    }
}
