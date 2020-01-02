<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Top level GUI class for media pools.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_Calls ilMediaPoolPresentationGUI: ilObjMediaPoolGUI
 *
 * @ingroup ModulesMediaPool
 */
class ilMediaPoolPresentationGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilNavigationHistory
     */
    protected $nav_history;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjectDefinition
     */
    protected $objDefinition;

    /**
    * Constructor
    * @access	public
    */
    public function __construct()
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $objDefinition = $DIC["objDefinition"];
        $ilCtrl = $DIC->ctrl();
        
        $lng->loadLanguageModule("content");

        $this->ctrl = $ilCtrl;

        // initiate variables
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->objDefinition = $objDefinition;
    }

    /**
     * execute command
     * @return mixed
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $ilNavigationHistory = $this->nav_history;

        $next_class = $this->ctrl->getNextClass($this);

        // add entry to navigation history
        if ($ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $ilNavigationHistory->addItem(
                $_GET["ref_id"],
                "ilias.php?baseClass=ilMediaPoolPresentationGUI&ref_id=" . $_GET["ref_id"],
                "mep"
            );
        }

        switch ($next_class) {
            case "ilobjmediapoolgui":
                require_once("./Modules/MediaPool/classes/class.ilObjMediaPoolGUI.php");
                $mep_gui = new ilObjMediaPoolGUI($_GET["ref_id"]);
                $ilCtrl->forwardCommand($mep_gui);
                break;

            default:
                $this->ctrl->setCmdClass("ilobjmediapoolgui");
                return $this->executeCommand();
                break;
        }
    }
}
