<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\PersonalWorkspace\StandardGUIRequest;
use ILIAS\GlobalScreen\ScreenContext\ContextServices;

/**
 * GUI class for personal workspace
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjWorkspaceRootFolderGUI, ilObjWorkspaceFolderGUI
 * @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjectCopyGUI, ilObjFileGUI, ilObjBlogGUI
 * @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjTestVerificationGUI, ilObjExerciseVerificationGUI
 * @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjLinkResourceGUI, ilObjCourseVerificationGUI
 * @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjSCORMVerificationGUI
 * @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjCmiXapiVerificationGUI
 * @ilCtrl_Calls ilPersonalWorkspaceGUI: ilObjLTIConsumerVerificationGUI
 */
class ilPersonalWorkspaceGUI
{
    protected ilSetting $settings;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilHelpGUI $help;
    protected ilObjectDefinition $obj_definition;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjUser $user;
    protected ilTabsGUI $tabs;
    protected ilLocatorGUI $locator;
    protected ilTree $tree;
    protected int $node_id; // [int]
    protected ContextServices $tool_context;
    protected StandardGUIRequest $std_request;
    
    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->help = $DIC["ilHelp"];
        $this->obj_definition = $DIC["objDefinition"];
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->locator = $DIC["ilLocator"];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->settings = $DIC->settings();

        $lng->loadLanguageModule("wsp");

        $this->std_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->initTree();

        $ilCtrl->saveParameter($this, "wsp_id");

        $this->node_id = $this->std_request->getWspId();
        if (!$this->node_id || !$this->tree->isInTree($this->node_id)) {
            $this->node_id = $this->tree->getRootId();
        }
        $this->tool_context = $DIC->globalScreen()->tool()->context();
    }
    
    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;
        $objDefinition = $this->obj_definition;
        $tpl = $this->tpl;

        if ($this->settings->get("disable_personal_workspace")) {
            throw new ilException($this->lng->txt("no_permission"));
        }

        $ilCtrl->setReturn($this, "render");

        $this->tool_context->current()->addAdditionalData(ilWorkspaceGSToolProvider::SHOW_WS_TREE, true);

        // new type
        if ($this->std_request->getNewType()) {
            $class_name = $objDefinition->getClassName(
                $this->std_request->getNewType()
            );

            // Only set the fixed cmdClass if the next class is different to
            // the GUI class of the new object.
            // An example:
            // ilObjLinkResourceGUI tries to forward to ilLinkInputGUI (adding an internal link
            // when creating a link resource)
            // Without this fix, the cmdClass ilObjectCopyGUI would never be reached
            if (strtolower($ilCtrl->getNextClass($this)) !== strtolower("ilObj" . $class_name . "GUI")) {
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
        if ($this->std_request->getNewType()) {
            $gui = new $class_name(0, ilObject2GUI::WORKSPACE_NODE_ID, $this->node_id);
            $gui->setCreationMode();
        } else {
            $gui = new $class_name($this->node_id, ilObject2GUI::WORKSPACE_NODE_ID, false);
        }
        $ilCtrl->forwardCommand($gui);

        //$this->renderBack();

        $tpl->setLocator();
    }

    protected function initTree() : void
    {
        $ilUser = $this->user;

        $user_id = $ilUser->getId();

        $this->tree = new ilWorkspaceTree($user_id);
        if (!$this->tree->getRootId()) {
            $this->tree->createTreeForUser($user_id);
        }
    }

    protected function renderBack() : void
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
    protected function renderLocator() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilLocator = $this->locator;
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
                        $ilLocator->addItem($lng->txt("personal_resources"), $ilCtrl->getLinkTargetByClass($obj_class, "render"));
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
