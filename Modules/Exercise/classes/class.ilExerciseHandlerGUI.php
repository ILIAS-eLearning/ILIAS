<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Handles user interface for exercises
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilExerciseHandlerGUI: ilObjExerciseGUI
 */
class ilExerciseHandlerGUI implements ilCtrlBaseClassInterface
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilAccessHandler $access;
    protected ilGlobalTemplateInterface $tpl;
    protected ilNavigationHistory $nav_history;
    protected int $requested_ref_id;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $request = $DIC->exercise()->internal()->gui()->request();
        $this->requested_ref_id = $request->getRefId();

        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tpl = $DIC["tpl"];
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->ctrl = $DIC->ctrl();
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
        if ($ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            $ilNavigationHistory->addItem(
                $this->requested_ref_id,
                "ilias.php?baseClass=ilExerciseHandlerGUI&cmd=showOverview&ref_id=" . $this->requested_ref_id,
                "exc"
            );
        }

        switch ($next_class) {
            case 'ilobjexercisegui':
                $ex_gui = new ilObjExerciseGUI("", $this->requested_ref_id, true);
                $this->ctrl->forwardCommand($ex_gui);
                break;
        }

        $tpl->printToStdout();
    }
}
