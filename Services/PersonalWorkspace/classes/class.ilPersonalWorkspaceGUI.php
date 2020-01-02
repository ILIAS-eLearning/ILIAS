<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* GUI class for personal workspace
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilPersonalDesktopGUI.php 26976 2010-12-16 13:24:38Z akill $
*
* @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjWorkspaceRootFolderGUI, ilObjWorkspaceFolderGUI
* @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjectCopyGUI, ilObjFileGUI, ilObjBlogGUI
* @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjTestVerificationGUI, ilObjExerciseVerificationGUI
* @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjLinkResourceGUI, ilObjCourseVerificationGUI
* @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjSCORMVerificationGUI
*
* @ingroup ServicesPersonalWorkspace
*/
class ilPersonalWorkspaceGUI
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
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilMainMenuGUI
     */
    protected $main_menu;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilLocatorGUI
     */
    protected $locator;

    protected $tree; // [ilTree]
    protected $node_id; // [int]
    
    /**
     * constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->help = $DIC["ilHelp"];
        $this->obj_definition = $DIC["objDefinition"];
        $this->tpl = $DIC["tpl"];
        $this->main_menu = $DIC["ilMainMenu"];
        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->locator = $DIC["ilLocator"];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilHelp = $DIC["ilHelp"];

        $lng->loadLanguageModule("wsp");

        $this->initTree();

        $ilCtrl->saveParameter($this, "wsp_id");

        $this->node_id = (int) $_REQUEST["wsp_id"];
        if (!$this->node_id) {
            $this->node_id = $this->tree->getRootId();
        }
    }
    
    /**
     * execute command
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $objDefinition = $this->obj_definition;
        $tpl = $this->tpl;
        $ilMainMenu = $this->main_menu;

        $ilCtrl->setReturn($this, "render");
        $cmd = $ilCtrl->getCmd();

        // new type
        if ($_REQUEST["new_type"]) {
            $class_name = $objDefinition->getClassName($_REQUEST["new_type"]);

            // Only set the fixed cmdClass if the next class is different to
            // the GUI class of the new object.
            // An example:
            // ilObjLinkResourceGUI tries to forward to ilLinkInputGUI (adding an internal link
            // when creating a link resource)
            // Without this fix, the cmdClass ilObjectCopyGUI would never be reached
            if (strtolower($ilCtrl->getNextClass($this)) != strtolower("ilObj" . $class_name . "GUI")) {
                $ilCtrl->setCmdClass("ilObj" . $class_name . "GUI");
            }
        }

        // root node
        $next_class = $ilCtrl->getNextClass();
        if (!$next_class) {
            $node = $this->tree->getNodeData($this->node_id);
            $next_class = "ilObj" . $objDefinition->getClassName($node["type"]) . "GUI";
            $ilCtrl->setCmdClass($next_class);
        }
        
        //  if we do this here the object can still change the breadcrumb
        $this->renderLocator();
        
        // current node
        $class_path = $ilCtrl->lookupClassPath($next_class);
        include_once($class_path);
        $class_name = $ilCtrl->getClassForClasspath($class_path);
        if ($_REQUEST["new_type"]) {
            $gui = new $class_name(null, ilObject2GUI::WORKSPACE_NODE_ID, $this->node_id);
            $gui->setCreationMode();
        } else {
            $gui = new $class_name($this->node_id, ilObject2GUI::WORKSPACE_NODE_ID, false);
        }
        $ilCtrl->forwardCommand($gui);
        
        if ($ilMainMenu->getMode() == ilMainMenuGUI::MODE_FULL) {
            $this->renderBack();
        }
        
        $tpl->setLocator();
    }

    /**
     * Init personal tree
     */
    protected function initTree()
    {
        $ilUser = $this->user;

        $user_id = $ilUser->getId();

        include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
        $this->tree = new ilWorkspaceTree($user_id);
        if (!$this->tree->getRootId()) {
            $this->tree->createTreeForUser($user_id);
        }
    }

    protected function renderBack()
    {
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        
        $root = $this->tree->getNodeData($this->node_id);
        if ($root["type"] != "wfld" && $root["type"] != "wsrt") {
            // do not override existing back targets, e.g. public user profile gui
            if (!$ilTabs->back_target) {
                $owner = $this->tree->lookupOwner($this->node_id);
                // workspace
                if ($owner == $ilUser->getId()) {
                    $parent = $this->tree->getParentNodeData($this->node_id);
                    if ($parent["wsp_id"]) {
                        if ($parent["type"] == "wsrt") {
                            $class = "ilobjworkspacerootfoldergui";
                        } else {
                            $class = "ilobjworkspacefoldergui";
                        }
                        $ilCtrl->setParameterByClass($class, "wsp_id", $parent["wsp_id"]);
                        $ilTabs->setBackTarget(
                            $lng->txt("back"),
                            $ilCtrl->getLinkTargetByClass($class, "")
                        );
                    }
                }
                // "shared by others"
                else {
                    $ilCtrl->setParameterByClass("ilobjworkspacerootfoldergui", "wsp_id", "");
                    $ilCtrl->setParameterByClass("ilobjworkspacerootfoldergui", "user", $owner);
                    $ilTabs->setBackTarget(
                        $lng->txt("back"),
                        $ilCtrl->getLinkTargetByClass("ilobjworkspacerootfoldergui", "share")
                    );
                }
            }
        }
    }
    
    /**
     * Build locator for current node
     */
    protected function renderLocator()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilLocator = $this->locator;
        $tpl = $this->tpl;
        $objDefinition = $this->obj_definition;

        $ilLocator->clearItems();
        
        // we have no path if shared item
        $path = $this->tree->getPathFull($this->node_id);
        if ($path) {
            foreach ($path as $node) {
                $obj_class = "ilObj" . $objDefinition->getClassName($node["type"]) . "GUI";

                $ilCtrl->setParameter($this, "wsp_id", $node["wsp_id"]);

                switch ($node["type"]) {
                    case "wsrt":
                        $ilLocator->addItem($lng->txt("wsp_personal_workspace"), $ilCtrl->getLinkTargetByClass($obj_class, "render"));
                        break;

                    case "blog":
                    case $objDefinition->isContainer($node["type"]):
                        $ilLocator->addItem($node["title"], $ilCtrl->getLinkTargetByClass($obj_class, "render"));
                        break;

                    default:
                        $ilLocator->addItem($node["title"], $ilCtrl->getLinkTargetByClass($obj_class, "edit"));
                        break;
                }
            }
        }

        $ilCtrl->setParameter($this, "wsp_id", $this->node_id);
    }
}
