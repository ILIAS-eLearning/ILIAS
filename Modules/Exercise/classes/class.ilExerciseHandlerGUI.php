<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Handles user interface for exercises
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilExerciseHandlerGUI: ilObjExerciseGUI
*
* @ingroup ModulesExercise
*/
class ilExerciseHandlerGUI
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

    /**
     * @var ilNavigationHistory
     */
    protected $nav_history;

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
        
        //$ilNavigationHistory->addItem($_GET["ref_id"],
        //	"ilias.php?baseClass=ilGlossaryEditorGUI&ref_id=".$_GET["ref_id"]);
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $lng = $this->lng;
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilNavigationHistory = $this->nav_history;
        
        $cmd = $this->ctrl->getCmd();
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
                require_once "./Modules/Exercise/classes/class.ilObjExerciseGUI.php";
                $ex_gui = new ilObjExerciseGUI("", (int) $_GET["ref_id"], true, false);
                $this->ctrl->forwardCommand($ex_gui);
                break;
        }

        $tpl->show();
    }
}
