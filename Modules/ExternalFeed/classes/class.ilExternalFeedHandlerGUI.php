<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Handles user interface for external feeds
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilExternalFeedHandlerGUI: ilObjExternalFeedGUI
 */
class ilExternalFeedHandlerGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tpl = $DIC["tpl"];
        $ilCtrl = $DIC->ctrl();

        // initialisation stuff
        $this->ctrl = $ilCtrl;
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $lng = $this->lng;
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);
        if ($next_class == "") {
            $this->ctrl->setCmdClass("ilobjexternalfeedgui");
            $next_class = $this->ctrl->getNextClass($this);
        }

        switch ($next_class) {
            case 'ilobjexternalfeedgui':
                $ef_gui = new ilObjExternalFeedGUI("", (int) $_GET["ref_id"], true, false);
                $this->ctrl->forwardCommand($mc_gui);
                break;
        }

        $tpl->printToStdout();
    }
}
