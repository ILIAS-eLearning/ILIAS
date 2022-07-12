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

/**
 * Workspace deep link handler GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilSharedResourceGUI: ilObjBlogGUI, ilObjFileGUI, ilObjTestVerificationGUI
 * @ilCtrl_Calls ilSharedResourceGUI: ilObjExerciseVerificationGUI, ilObjLinkResourceGUI
 * @ilCtrl_Calls ilSharedResourceGUI: ilObjPortfolioGUI
 */
class ilSharedResourceGUI implements ilCtrlBaseClassInterface
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLocatorGUI $locator;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilObjectDefinition $obj_definition;
    protected ilTabsGUI $tabs;
    protected int $node_id;
    protected int $portfolio_id;
    protected StandardGUIRequest $request;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->locator = $DIC["ilLocator"];
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->obj_definition = $DIC["objDefinition"];
        $this->tabs = $DIC->tabs();
        $ilCtrl = $DIC->ctrl();

        $this->request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
        
        $ilCtrl->saveParameter($this, "wsp_id");
        $ilCtrl->saveParameter($this, "prt_id");
        $this->node_id = $this->request->getWspId();
        $this->portfolio_id = $this->request->getPrtId();
    }
    
    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilLocator = $this->locator;
        $ilUser = $this->user;
        $lng = $this->lng;
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();
        
        $tpl->loadStandardTemplate();
        
        // #12096
        if ($ilUser->getId() != ANONYMOUS_USER_ID &&
            $next_class &&
            !in_array($next_class, array("ilobjbloggui", "ilobjportfoliogui"))) {
            $tree = new ilWorkspaceTree($ilUser->getId());
            $access_handler = new ilWorkspaceAccessHandler($tree);
            $owner_id = $tree->lookupOwner($this->node_id);
            $obj_id = $tree->lookupObjectId($this->node_id);
            
            $lng->loadLanguageModule("wsp");

            // see ilPersonalWorkspaceGUI
            if ($owner_id != $ilUser->getId()) {
                $ilCtrl->setParameterByClass("ildashboardgui", "dsh", $owner_id);
                $link = $ilCtrl->getLinkTargetByClass("ildashboardgui", "jumptoworkspace");
                $ilLocator->addItem($lng->txt("wsp_tab_shared"), $link);

                $ilLocator->addItem(ilUserUtil::getNamePresentation($owner_id), $link);
            } else {
                $link = $ilCtrl->getLinkTargetByClass("ildashboardgui", "jumptoworkspace");
                $ilLocator->addItem($lng->txt("wsp_tab_personal"), $link);
            }
            
            $link = $access_handler->getGotoLink($this->node_id, $obj_id);
            $ilLocator->addItem(ilObject::_lookupTitle($obj_id), $link);
            $tpl->setLocator();
        }
        
        switch ($next_class) {
            case "ilobjbloggui":
                $bgui = new ilObjBlogGUI($this->node_id, ilObject2GUI::WORKSPACE_NODE_ID);
                $ilCtrl->forwardCommand($bgui);
                break;
            
            case "ilobjfilegui":
                $fgui = new ilObjFileGUI($this->node_id, ilObject2GUI::WORKSPACE_NODE_ID);
                $ilCtrl->forwardCommand($fgui);
                break;
            
            case "ilobjtestverificationgui":
                $tgui = new ilObjTestVerificationGUI($this->node_id, ilObject2GUI::WORKSPACE_NODE_ID);
                $ilCtrl->forwardCommand($tgui);
                break;
            
            case "ilobjexerciseverificationgui":
                $egui = new ilObjExerciseVerificationGUI($this->node_id, ilObject2GUI::WORKSPACE_NODE_ID);
                $ilCtrl->forwardCommand($egui);
                break;
            
            case "ilobjlinkresourcegui":
                $lgui = new ilObjLinkResourceGUI($this->node_id, ilObject2GUI::WORKSPACE_NODE_ID);
                $ilCtrl->forwardCommand($lgui);
                break;
            
            case "ilobjportfoliogui":
                $pgui = new ilObjPortfolioGUI($this->portfolio_id);
                $ilCtrl->forwardCommand($pgui);
                break;
            
            default:
                if (!$cmd) {
                    $cmd = "process";
                }
                $this->$cmd();
        }
        
        $tpl->printToStdout();
    }
    
    protected function process() : void
    {
        if (!$this->node_id && !$this->portfolio_id) {
            throw new ilPermissionException("invalid call");
        }
            
        // if already logged in, we need to re-check for public password
        if ($this->node_id) {
            if (!self::hasAccess($this->node_id)) {
                throw new ilPermissionException("no permission");
            }
            $this->redirectToResource($this->node_id);
        } else {
            if (!self::hasAccess($this->portfolio_id, true)) {
                throw new ilPermissionException("no permission");
            }
            $this->redirectToResource($this->portfolio_id, true);
        }
    }
    
    public static function hasAccess(
        int $a_node_id,
        bool $a_is_portfolio = false
    ) : bool {
        global $DIC;

        $ilUser = $DIC->user();
        $ilSetting = $DIC->settings();
    
        // if we have current user - check with normal access handler
        if ($ilUser->getId() != ANONYMOUS_USER_ID) {
            if (!$a_is_portfolio) {
                $tree = new ilWorkspaceTree($ilUser->getId());
                $access_handler = new ilWorkspaceAccessHandler($tree);
            } else {
                $access_handler = new ilPortfolioAccessHandler();
            }
            if ($access_handler->checkAccess("read", "", $a_node_id)) {
                return true;
            }
        }
        
        if (!$a_is_portfolio) {
            $shared = ilWorkspaceAccessHandler::_getPermissions($a_node_id);
        } else {
            // #12059
            if (!$ilSetting->get('user_portfolios')) {
                return false;
            }
            
            // #12039
            $prtf = new ilObjPortfolio($a_node_id, false);
            if (!$prtf->isOnline()) {
                return false;
            }
                        
            $shared = ilPortfolioAccessHandler::_getPermissions($a_node_id);
        }
        
        // object is "public"
        if (in_array(ilWorkspaceAccessGUI::PERMISSION_ALL, $shared)) {
            return true;
        }

        // password protected
        if (in_array(ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD, $shared)) {
            if (!$a_is_portfolio) {
                ilUtil::redirect("ilias.php?baseClass=ilSharedResourceGUI&cmd=passwordForm&wsp_id=" . $a_node_id);
            } else {
                ilUtil::redirect("ilias.php?baseClass=ilSharedResourceGUI&cmd=passwordForm&prt_id=" . $a_node_id);
            }
        }
        
        return false;
    }
    
    protected function redirectToResource(
        int $a_node_id,
        bool $a_is_portfolio = false
    ) : void {
        $ilCtrl = $this->ctrl;
        $objDefinition = $this->obj_definition;
                
        if (!$a_is_portfolio) {
            $object_data = ilWorkspaceAccessHandler::getObjectDataFromNode($a_node_id);
            if (!$object_data["obj_id"]) {
                throw new ilPermissionException("invalid object");
            }
        } else {
            if (!ilObject::_lookupType($a_node_id, false)) {
                throw new ilPermissionException("invalid object");
            }
            $object_data["obj_id"] = $a_node_id;
            $object_data["type"] = "prtf";
        }
        
        $class = $objDefinition->getClassName($object_data["type"]);
        $gui = "ilobj" . $class . "gui";
        
        switch ($object_data["type"]) {
            case "blog":
                $ilCtrl->setParameterByClass($gui, "wsp_id", $a_node_id);
                $ilCtrl->setParameterByClass($gui, "gtp", $this->request->getBlogGtp());
                $ilCtrl->setParameterByClass($gui, "edt", $this->request->getBlogEdt());
                $ilCtrl->redirectByClass($gui, "preview");
                break;

            case "tstv":
            case "excv":
            case "crsv":
            case "scov":
            case "cmxv":
            case "ltiv":
                $ilCtrl->setParameterByClass($gui, "wsp_id", $a_node_id);
                $ilCtrl->redirectByClass($gui, "deliver");
                break;

            case "file":
            case "webr":
                $ilCtrl->setParameterByClass($gui, "wsp_id", $a_node_id);
                $ilCtrl->redirectByClass($gui);
                break;
                
            case "prtf":
                $ilCtrl->setParameterByClass($gui, "prt_id", $a_node_id);
                $ilCtrl->setParameterByClass($gui, "gtp", $this->request->getBlogGtp());
                if ($this->request->getBackUrl()) {
                    $ilCtrl->setParameterByClass($gui, "back_url", rawurlencode($this->request->getBackUrl()));
                }
                $ilCtrl->redirectByClass($gui, "preview");
                break;
                
            default:
                exit("invalid object type");
        }
    }
    
    protected function passwordForm(?ilPropertyFormGUI $form = null) : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        
        $lng->loadLanguageModule("wsp");
        
        $tpl->setTitle($lng->txt("wsp_password_protected_resource"));
        $tpl->setDescription($lng->txt("wsp_password_protected_resource_info"));
        
        if (!$form) {
            $form = $this->initPasswordForm();
        }
    
        $tpl->setContent($form->getHTML());
    }
    
    protected function initPasswordForm() : ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;
        $ilTabs = $this->tabs;
        
        if ($this->node_id) {
            $object_data = ilWorkspaceAccessHandler::getObjectDataFromNode($this->node_id);
        } else {
            $object_data["title"] = ilObject::_lookupTitle($this->portfolio_id);
        }
        
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "checkPassword"));
        $form->setTitle($lng->txt("wsp_password_for") . ": " . $object_data["title"]);
        
        $password = new ilPasswordInputGUI($lng->txt("password"), "password");
        $password->setRetype(false);
        $password->setRequired(true);
        $password->setSkipSyntaxCheck(true); // #17757
        $form->addItem($password);
        
        $form->addCommandButton("checkPassword", $lng->txt("submit"));
        
        if ($ilUser->getId() && $ilUser->getId() != ANONYMOUS_USER_ID) {
            $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "cancelPassword"));
            $form->addCommandButton("cancelPassword", $lng->txt("cancel"));
        }
        
        return $form;
    }
    
    protected function cancelPassword() : void
    {
        $ilUser = $this->user;
        
        if ($ilUser->getId() && $ilUser->getId() != ANONYMOUS_USER_ID) {
            if ($this->node_id) {
                $tree = new ilWorkspaceTree($ilUser->getId());
                $owner = $tree->lookupOwner($this->node_id);
                ilUtil::redirect("ilias.php?baseClass=ilDashboardGUI&cmd=jumpToWorkspace&dsh=" . $owner);
            } else {
                $prtf = new ilObjPortfolio($this->portfolio_id, false);
                $owner = $prtf->getOwner();
                ilUtil::redirect("ilias.php?baseClass=ilDashboardGUI&cmd=jumpToPortfolio&dsh=" . $owner);
            }
        }
    }
    
    protected function checkPassword() : void
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("wsp");
         
        $form = $this->initPasswordForm();
        if ($form->checkInput()) {
            $input = md5($form->getInput("password"));
            if ($this->node_id) {
                $password = ilWorkspaceAccessHandler::getSharedNodePassword($this->node_id);
            } else {
                $password = ilPortfolioAccessHandler::getSharedNodePassword($this->portfolio_id);
            }
            if ($input == $password) {
                if ($this->node_id) {
                    ilWorkspaceAccessHandler::keepSharedSessionPassword($this->node_id, $input);
                    $this->redirectToResource($this->node_id);
                } else {
                    ilPortfolioAccessHandler::keepSharedSessionPassword($this->portfolio_id, $input);
                    $this->redirectToResource($this->portfolio_id, true);
                }
            } else {
                $item = $form->getItemByPostVar("password");
                $item->setAlert($lng->txt("wsp_invalid_password"));
                $this->tpl->setOnScreenMessage('failure', $lng->txt("form_input_not_valid"));
            }
        }
        
        $form->setValuesByPost();
        $this->passwordForm($form);
    }
}
