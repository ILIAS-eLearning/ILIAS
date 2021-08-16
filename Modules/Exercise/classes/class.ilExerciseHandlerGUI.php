<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Handles user interface for exercises
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilExerciseHandlerGUI: ilObjExerciseGUI
 */
class ilExerciseHandlerGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilAccessHandler $access;
    protected ilGlobalTemplateInterface $tpl;
    protected ilNavigationHistory $nav_history;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tpl = $DIC["tpl"];
        $this->nav_history = $DIC["ilNavigationHistory"];
        $ilCtrl = $DIC->ctrl();

        // initialisation stuff
        $this->ctrl = $ilCtrl;
    }

    /**
     * @throws ilCtrlException
     * @throws ilExerciseException
     */
    public function executeCommand() : void
    {
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilNavigationHistory = $this->nav_history;
        
        $next_class = $this->ctrl->getNextClass($this);
        if ($next_class == "") {
            $this->ctrl->setCmdClass("ilobjexercisegui");
            $next_class = $this->ctrl->getNextClass($this);
        }

        // add entry to navigation history
        if ($ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $ilNavigationHistory->addItem(
                $_GET["ref_id"],
                "ilias.php?baseClass=ilExerciseHandlerGUI&cmd=showOverview&ref_id=" . $_GET["ref_id"],
                "exc"
            );
        }

        switch ($next_class) {
            case 'ilobjexercisegui':
                $ex_gui = new ilObjExerciseGUI("", (int) $_GET["ref_id"], true);
                $this->ctrl->forwardCommand($ex_gui);
                break;
        }

        $tpl->printToStdout();
    }
}
