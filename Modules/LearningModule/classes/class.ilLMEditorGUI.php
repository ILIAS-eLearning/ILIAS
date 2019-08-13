<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * GUI class for learning module editor
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_Calls ilLMEditorGUI: ilObjLearningModuleGUI
 *
 * @ingroup ModulesIliasLearningModule
 */
class ilLMEditorGUI
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
     * @var ilNavigationHistory
     */
    protected $nav_history;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilHelpGUI
     */
    protected $help;

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
     * @var int
     */
    protected $ref_id;

    /**
     * @var ilObjLearningModule
     */
    protected $lm_obj;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var int
     */
    protected $obj_id;

    /**
    * Constructor
    * @access	public
    */
    public function __construct()
    {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->error = $DIC["ilErr"];
        $this->help = $DIC["ilHelp"];
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $objDefinition = $DIC["objDefinition"];
        $ilCtrl = $DIC->ctrl();
        $rbacsystem = $DIC->rbac()->system();
        $ilNavigationHistory = $DIC["ilNavigationHistory"];
        $ilErr = $DIC["ilErr"];
        
        $lng->loadLanguageModule("content");
        $lng->loadLanguageModule("lm");

        // check write permission
        if (!$rbacsystem->checkAccess("write", $_GET["ref_id"])) {
            $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $this->ctrl = $ilCtrl;

        $this->ctrl->saveParameter($this, array("ref_id", "transl"));

        // initiate variables
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->objDefinition = $objDefinition;
        $this->ref_id = $_GET["ref_id"];
        $this->obj_id = $_GET["obj_id"];

        $this->lm_obj = ilObjectFactory::getInstanceByRefId($this->ref_id);
        $this->tree = new ilTree($this->lm_obj->getId());
        $this->tree->setTableNames('lm_tree', 'lm_data');
        $this->tree->setTreeTablePK("lm_id");


        $ilNavigationHistory->addItem(
            $_GET["ref_id"],
            "ilias.php?baseClass=ilLMEditorGUI&ref_id=" . $_GET["ref_id"],
            "lm"
        );

        $this->checkRequestParameters();
    }
    
    /**
     * Check request parameters
     * @throws ilCtrlException
     * @throws ilException
     */
    protected function checkRequestParameters()
    {
        $forwards_to_role = $this->ctrl->checkCurrentPathForClass("ilobjrolegui");

        if (!$forwards_to_role && $this->obj_id > 0 && ilLMObject::_lookupContObjID($this->obj_id) != $this->lm_obj->getId()) {
            throw new ilException("Object ID does not match learning module.");
        }
        if ($_REQUEST["active_node"] > 0 && ilLMObject::_lookupContObjID((int) $_REQUEST["active_node"]) != $this->lm_obj->getId()) {
            throw new ilException("Active node does not match learning module.");
        }
    }
    

    /**
     * @throws ilCtrlException
     * @throws ilException
     */
    public function executeCommand()
    {
        global $DIC;

        /** @var ilLocatorGUI $loc */
        $loc = $DIC["ilLocator"];
        $loc->addRepositoryItems((int) $_GET["ref_id"]);

        if ($_GET["to_page"]== 1) {
            $this->ctrl->setParameterByClass("illmpageobjectgui", "obj_id", $_GET["obj_id"]);
            $this->ctrl->redirectByClass(array("ilobjlearningmodulegui", "illmpageobjectgui"), "edit");
        }
        
        $this->showTree();

        $next_class = $this->ctrl->getNextClass($this);

        if ($next_class == "" && ($cmd != "explorer")
            && ($cmd != "showImageMap")) {
            $next_class = "ilobjlearningmodulegui";
        }

        // show footer
        $show_footer = ($cmd == "explorer")
            ? false
            : true;
            
        switch ($next_class) {
            case "ilobjlearningmodulegui":
                include_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
                include_once("./Modules/LearningModule/classes/class.ilObjLearningModuleGUI.php");
                $this->main_header($this->lm_obj->getType());
                $lm_gui = new ilObjLearningModuleGUI("", $_GET["ref_id"], true, false);

                $ret = $this->ctrl->forwardCommand($lm_gui);
                if (strcmp($cmd, "explorer") != 0) {
                    // don't call the locator in the explorer frame
                    // this prevents a lot of log errors
                    // Helmut Schottmüller, 2006-07-21
                    $this->displayLocator();
                }
                // (horrible) workaround for preventing template engine
                // from hiding paragraph text that is enclosed
                // in curly brackets (e.g. "{a}", see ilPageObjectGUI::showPage())
                $output =  $this->tpl->get("DEFAULT", true, true, $show_footer, true);
                $output = str_replace("&#123;", "{", $output);
                $output = str_replace("&#125;", "}", $output);
                header('Content-type: text/html; charset=UTF-8');
                echo $output;
                break;

            default:
                $ret = $this->$cmd();
                break;
        }
    }

    /**
     * Show tree
     */
    public function showTree()
    {
        $tpl = $this->tpl;

        include_once("./Modules/LearningModule/classes/class.ilLMEditorExplorerGUI.php");
        $exp = new ilLMEditorExplorerGUI($this, "showTree", $this->lm_obj);
        if (!$exp->handleCommand()) {
            $tpl->setLeftNavContent($exp->getHTML());
        }
    }
    
    /**
     * output main header (title and locator)
     */
    public function main_header()
    {
        $this->tpl->getStandardTemplate();

        // content style
        $this->tpl->setCurrentBlock("ContentStyle");
        $this->tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath($this->lm_obj->getStyleSheetId())
        );
        $this->tpl->parseCurrentBlock();

        // syntax style
        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $this->tpl->parseCurrentBlock();
    }


    /**
     * Display locator
     */
    public function displayLocator()
    {
        $this->tpl->setLocator();
    }
}
