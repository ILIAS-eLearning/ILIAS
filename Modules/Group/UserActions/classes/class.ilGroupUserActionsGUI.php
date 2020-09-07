<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Group user actions
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ModulesGroup
 *
 * @ilCtrl_Calls ilGroupUserActionsGUI: ilGroupAddToGroupActionGUI
 */
class ilGroupUserActionsGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * Constructor
     *
     * @param
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        switch ($next_class) {
            case "ilgroupaddtogroupactiongui":
                include_once("./Modules/Group/UserActions/classes/class.ilGroupAddToGroupActionGUI.php");
                $gui = new ilGroupAddToGroupActionGUI();
                $ctrl->forwardCommand($gui);
                break;

            default:
                /*if (in_array($cmd, array("show")))
                {
                    $this->$cmd();
                }*/
        }
    }
}
