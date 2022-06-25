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
 * ACL access handler GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilWorkspaceAccessGUI: ilMailSearchCoursesGUI, ilMailSearchGroupsGUI
 * @ilCtrl_Calls ilWorkspaceAccessGUI: ilMailSearchGUI, ilPublicUserProfileGUI, ilSingleUserShareGUI
 */
class ilWorkspaceAccessGUI
{
    public const PERMISSION_REGISTERED = -1;
    public const PERMISSION_ALL_PASSWORD = -3;
    public const PERMISSION_ALL = -5;
    protected bool $is_portfolio;
    protected ilTabsGUI $tabs;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilObjUser $user;
    protected ilSetting $settings;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected int $node_id;
    /**
     * @var ilPortfolioAccessHandler|ilWorkspaceAccessHandler
     */
    protected $access_handler;
    protected string $footer = "";
    
    protected string $blocking_message = "";
    protected StandardGUIRequest $std_request;

    /**
     * @param ilPortfolioAccessHandler|ilWorkspaceAccessHandler $a_access_handler
     */
    public function __construct(
        int $a_node_id,
        $a_access_handler,
        bool $a_is_portfolio = false,
        string $a_footer = ""
    ) {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->settings = $DIC->settings();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->node_id = $a_node_id;
        $this->access_handler = $a_access_handler;
        $this->is_portfolio = $a_is_portfolio;
        $this->footer = $a_footer;
        $this->std_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }

    // Set blocking message
    public function setBlockingMessage(string $a_val) : void
    {
        $this->blocking_message = $a_val;
    }

    public function getBlockingMessage() : string
    {
        return $this->blocking_message;
    }

    public function executeCommand() : void
    {
        $ilTabs = $this->tabs;
        $tpl = $this->tpl;
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case "ilmailsearchcoursesgui":
                $ilTabs->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, "share")
                );
                $csearch = new ilMailSearchCoursesGUI($this->access_handler, $this->node_id);
                $this->ctrl->setReturn($this, 'share');
                $this->ctrl->forwardCommand($csearch);
                
                $this->setObjectTitle();
                break;
            
            case "ilmailsearchgroupsgui":
                $ilTabs->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, "share")
                );
                $gsearch = new ilMailSearchGroupsGUI($this->access_handler, $this->node_id);
                $this->ctrl->setReturn($this, 'share');
                $this->ctrl->forwardCommand($gsearch);
                
                $this->setObjectTitle();
                break;
            
            case "ilmailsearchgui":
                $ilTabs->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, "share")
                );
                $usearch = new ilMailSearchGUI($this->access_handler, $this->node_id);
                $this->ctrl->setReturn($this, 'share');
                $this->ctrl->forwardCommand($usearch);
                
                $this->setObjectTitle();
                break;

            case "ilsingleusersharegui":
                $ilTabs->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, "share")
                );
                $ushare = new ilSingleUserShareGUI($this->access_handler, $this->node_id);
                $this->ctrl->setReturn($this, 'share');
                $this->ctrl->forwardCommand($ushare);

                $this->setObjectTitle();
                break;

            case "ilpublicuserprofilegui":
                $ilTabs->clearTargets();
                $ilTabs->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, "share")
                );
                
                $prof = new ilPublicUserProfileGUI(
                    $this->std_request->getUser()
                );
                $prof->setBackUrl($this->ctrl->getLinkTarget($this, "share"));
                $tpl->setContent($prof->getHTML());
                break;

            default:
                // $this->prepareOutput();
                if (!$cmd) {
                    $cmd = "share";
                }
                //return $this->$cmd();
                $this->$cmd();
                break;
        }
    }
    
    protected function setObjectTitle() : void
    {
        $tpl = $this->tpl;
        
        if (!$this->is_portfolio) {
            $obj_id = $this->access_handler->getTree()->lookupObjectId($this->node_id);
        } else {
            $obj_id = $this->node_id;
        }
        $tpl->setTitle(ilObject::_lookupTitle($obj_id));
    }

    /**
     * @return ilPortfolioAccessHandler|ilWorkspaceAccessHandler
     */
    protected function getAccessHandler()
    {
        return $this->access_handler;
    }
    
    protected function share() : void
    {
        $ilToolbar = $this->toolbar;
        $tpl = $this->tpl;
        $ilUser = $this->user;
        $ilSetting = $this->settings;


        // blocking message
        if ($this->getBlockingMessage() != "") {
            $tpl->setContent($this->getBlockingMessage());
            return;
        }
        
        $options = array();
        $options["user"] = $this->lng->txt("wsp_set_permission_single_user");
        
        $grp_ids = ilGroupParticipants::_getMembershipByType($ilUser->getId(), ['grp']);
        if (sizeof($grp_ids)) {
            $options["group"] = $this->lng->txt("wsp_set_permission_group");
        }
        
        $crs_ids = ilCourseParticipants::_getMembershipByType($ilUser->getId(), ['crs']);
        if (sizeof($crs_ids)) {
            $options["course"] = $this->lng->txt("wsp_set_permission_course");
        }
        
        if (!$this->getAccessHandler()->hasRegisteredPermission($this->node_id)) {
            $options["registered"] = $this->lng->txt("wsp_set_permission_registered");
        }
        
        if ($ilSetting->get("enable_global_profiles")) {
            if (!$this->getAccessHandler()->hasGlobalPasswordPermission($this->node_id)) {
                $options["password"] = $this->lng->txt("wsp_set_permission_all_password");
            }

            if (!$this->getAccessHandler()->hasGlobalPermission($this->node_id)) {
                $options["all"] = $this->lng->txt("wsp_set_permission_all");
            }
        }
        
        $actions = new ilSelectInputGUI("", "action");
        $actions->setOptions($options);
        $ilToolbar->addStickyItem($actions);
        
        $ilToolbar->setFormAction($this->ctrl->getFormAction($this));
        
        $button = ilSubmitButton::getInstance();
        $button->setCaption("add");
        $button->setCommand("addpermissionhandler");
        $ilToolbar->addStickyItem($button);
    
        $table = new ilWorkspaceAccessTableGUI($this, "share", $this->node_id, $this->getAccessHandler());
        $tpl->setContent($table->getHTML() . $this->footer);
    }
    
    public function addPermissionHandler() : void
    {
        switch ($this->std_request->getAction()) {
            case "user":

                if (ilUserAccountSettings::getInstance()->isUserAccessRestricted()) {
                    $this->ctrl->redirectByClass("ilsingleusersharegui");
                } else {
                    $this->ctrl->setParameterByClass("ilmailsearchgui", "ref", "wsp");
                    $this->ctrl->redirectByClass("ilmailsearchgui");
                }
                break;

            case "group":
                $this->ctrl->setParameterByClass("ilmailsearchgroupsgui", "ref", "wsp");
                $this->ctrl->redirectByClass("ilmailsearchgroupsgui");
                break;

            case "course":
                $this->ctrl->setParameterByClass("ilmailsearchcoursesgui", "ref", "wsp");
                $this->ctrl->redirectByClass("ilmailsearchcoursesgui");
                break;
            
            case "registered":
                $this->getAccessHandler()->addPermission($this->node_id, self::PERMISSION_REGISTERED);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("wsp_permission_registered_info"), true);
                $this->ctrl->redirect($this, "share");
                break;
            
            case "password":
                $this->showPasswordForm();
                break;
            
            case "all":
                $this->getAccessHandler()->addPermission($this->node_id, self::PERMISSION_ALL);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("wsp_permission_all_info"), true);
                $this->ctrl->redirect($this, "share");
        }
    }
    
    public function removePermission() : void
    {
        $obj_id = $this->std_request->getObjId();
        if ($obj_id !== 0) {
            $this->getAccessHandler()->removePermission($this->node_id, $obj_id);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("wsp_permission_removed"), true);
        }

        $this->ctrl->redirect($this, "share");
    }
    
    protected function initPasswordForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("wsp_set_permission_all_password"));
        
        $password = new ilPasswordInputGUI($this->lng->txt("password"), "password");
        $password->setRequired(true);
        $form->addItem($password);
        
        $form->addCommandButton('savepasswordform', $this->lng->txt("save"));
        $form->addCommandButton('share', $this->lng->txt("cancel"));
        
        return $form;
    }
    
    protected function showPasswordForm(ilPropertyFormGUI $a_form = null) : void
    {
        $tpl = $this->tpl;
        
        if (!$a_form) {
            $a_form = $this->initPasswordForm();
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function savePasswordForm() : void
    {
        $form = $this->initPasswordForm();
        if ($form->checkInput()) {
            $this->getAccessHandler()->addPermission(
                $this->node_id,
                self::PERMISSION_ALL_PASSWORD,
                md5($form->getInput("password"))
            );
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("wsp_permission_all_pw_info"), true);
            $this->ctrl->redirect($this, "share");
        }
    
        $form->setValuesByPost();
        $this->showPasswordForm($form);
    }
}
