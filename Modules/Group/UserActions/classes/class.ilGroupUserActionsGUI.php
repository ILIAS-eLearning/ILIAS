<?php declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Group user actions
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ModulesGroup
 *
 * @ilCtrlStructureCalls(
 *		children={
 *			"ilGroupAddToGroupActionGUI",
 *		}
 * )
 */
class ilGroupUserActionsGUI
{
    protected ilCtrl $ctrl;

    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
    }

    public function executeCommand() : void
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
