<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * GUI class for learning module editor
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilHTLMEditorGUI: ilObjFileBasedLMGUI
 */
class ilHTLMEditorGUI implements ilCtrlBaseClassInterface
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilNavigationHistory
     */
    protected $nav_history;

    public $tpl;
    public $lng;
    public $objDefinition;
    public $ref_id;

    /**
    * Constructor
    * @access	public
    */
    public function __construct()
    {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->error = $DIC["ilErr"];
        $this->access = $DIC->access();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $objDefinition = $DIC["objDefinition"];
        $ilCtrl = $DIC->ctrl();
        $rbacsystem = $DIC->rbac()->system();
        $ilErr = $DIC["ilErr"];
        
        $lng->loadLanguageModule("content");

        // check write permission
        if (!$rbacsystem->checkAccess("write", $_GET["ref_id"])) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $this->ctrl = $ilCtrl;

        //$this->ctrl->saveParameter($this, array("ref_id", "obj_id"));
        $this->ctrl->saveParameter($this, array("ref_id"));

        // initiate variables
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->objDefinition = $objDefinition;
        $this->ref_id = $_GET["ref_id"];
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $ilNavigationHistory = $this->nav_history;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("");

        // add entry to navigation history
        if ($ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $ilCtrl->setParameterByClass("ilobjfilebasedlmgui", "ref_id", $_GET["ref_id"]);
            $ilNavigationHistory->addItem(
                $_GET["ref_id"],
                $ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjfilebasedlmgui"), "infoScreen"),
                "htlm"
            );
        }

        switch ($next_class) {
            case "ilobjfilebasedlmgui":
                $fblm_gui = new ilObjFileBasedLMGUI("", $_GET["ref_id"], true, false);
                $ilCtrl->forwardCommand($fblm_gui);
                $tpl->printToStdout();
                break;

            default:
                $this->ctrl->setCmdClass("ilobjfilebasedlmgui");
                $this->ctrl->setCmd("");
                return $this->executeCommand();
                break;
        }
    }
}
