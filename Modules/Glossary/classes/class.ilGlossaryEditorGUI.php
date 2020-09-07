<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilGlossaryEditorGUI
*
* GUI class for Glossary Editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilGlossaryEditorGUI: ilObjGlossaryGUI
*
* @ingroup ModulesGlossary
*/
class ilGlossaryEditorGUI
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
     * @var ilNavigationHistory
     */
    protected $nav_history;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->error = $DIC["ilErr"];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $ilNavigationHistory = $DIC["ilNavigationHistory"];
        $ilErr = $DIC["ilErr"];
        
        // initialisation stuff
        $this->ctrl = $ilCtrl;
        $lng->loadLanguageModule("content");
        
        // check write permission
        if (!$ilAccess->checkAccess("write", "", $_GET["ref_id"]) &&
            !$ilAccess->checkAccess("edit_content", "", $_GET["ref_id"])) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
        }
        
        $ilNavigationHistory->addItem(
            $_GET["ref_id"],
            "ilias.php?baseClass=ilGlossaryEditorGUI&ref_id=" . $_GET["ref_id"],
            "glo"
        );
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $lng = $this->lng;
        $ilAccess = $this->access;
        
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);
        if ($next_class == "") {
            $this->ctrl->setCmdClass("ilobjglossarygui");
            $this->ctrl->setCmd("");
        }

        switch ($next_class) {
            case 'ilobjglossarygui':
            default:
                require_once "./Modules/Glossary/classes/class.ilObjGlossaryGUI.php";
                $glossary_gui = new ilObjGlossaryGUI("", $_GET["ref_id"], true, false);
                $this->ctrl->forwardCommand($glossary_gui);
                break;
        }
    }
}
