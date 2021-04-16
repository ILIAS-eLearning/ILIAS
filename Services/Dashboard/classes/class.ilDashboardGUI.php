<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Dashboard UI
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilDashboardGUI: ilPersonalProfileGUI
 * @ilCtrl_Calls ilDashboardGUI: ilObjUserGUI, ilPDNotesGUI
 * @ilCtrl_Calls ilDashboardGUI: ilColumnGUI, ilPDNewsGUI, ilCalendarPresentationGUI
 * @ilCtrl_Calls ilDashboardGUI: ilMailSearchGUI, ilContactGUI
 * @ilCtrl_Calls ilDashboardGUI: ilPersonalWorkspaceGUI, ilPersonalSettingsGUI
 * @ilCtrl_Calls ilDashboardGUI: ilPortfolioRepositoryGUI, ilObjChatroomGUI
 * @ilCtrl_Calls ilDashboardGUI: ilMyStaffGUI
 * @ilCtrl_Calls ilDashboardGUI: ilGroupUserActionsGUI, ilAchievementsGUI
 * @ilCtrl_Calls ilDashboardGUI: ilPDSelectedItemsBlockGUI, ilPDMembershipBlockGUI, ilDashboardRecommendedContentGUI, ilStudyProgrammeDashboardViewGUI
 *
 */
class ilDashboardGUI
{
    public const CMD_JUMP_TO_MY_STAFF = "jumpToMyStaff";
    public const DISENGAGE_MAINBAR = "dash_mb_disengage";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilPluginAdmin
     */
    protected $plugin_admin;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var \ilGlobalTemplateInterface
     */
    public $tpl;

    /**
     * @var \ilLanguage
     */
    public $lng;

    /**
     * @var string
     */
    public $cmdClass = '';

    /**
     * @var ilAdvancedSelectionListGUI
     */
    protected $action_menu;

    /**
     * @var \ILIAS\GlobalScreen\ScreenContext\ContextServices
     */
    protected $tool_context;

    /**
     * @var int
     */
    protected $requested_view;

    /**
     * @var int
     */
    protected $requested_prt_id;

    /**
     * @var int
     */
    protected $requested_gtp;

    /**
     * @var string
     */
    protected $requested_dsh;

    /**
     * @var int
     */
    protected $requested_wsp_id;

    /**
    * constructor
    */
    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->tool_context = $DIC->globalScreen()->tool()->context();
        $this->user = $DIC->user();
        $this->error = $DIC["ilErr"];
        $this->settings = $DIC->settings();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->help = $DIC["ilHelp"];
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $ilUser = $DIC->user();
        $ilErr = $DIC["ilErr"];
        
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        $ilCtrl->setContext(
            $ilUser->getId(),
            "user"
        );

        $this->lng->loadLanguageModule("pdesk");
        $this->lng->loadLanguageModule("pd"); // #16813
        $this->lng->loadLanguageModule("dash");
        $this->lng->loadLanguageModule("mmbr");

        // catch hack attempts
        if ($this->user->getId() == ANONYMOUS_USER_ID) {
            $ilErr->raiseError($this->lng->txt("msg_not_available_for_anon"), $ilErr->MESSAGE);
        }


        $params = $DIC->http()->request()->getQueryParams();
        $this->cmdClass = ($params['cmdClass'] ?? "");
        $this->requested_view = (int) ($params['view'] ?? 0);
        $this->requested_prt_id = (int) ($params["prt_id"] ?? 0);
        $this->requested_gtp = (int) ($params["gtp"] ?? 0);
        $this->requested_dsh = (string) ($params["dsh"] ?? null);
        $this->requested_wsp_id = (int) ($params["wsp_id"] ?? 0);

        $this->ctrl->saveParameter($this, array("view"));
        $this->action_menu = new ilAdvancedSelectionListGUI();
    }
    
    /**
    * execute command
    */
    public function executeCommand() : void
    {
        $context = $this->tool_context;
        $context->stack()->desktop();
        $ilSetting = $this->settings;
        $ilErr = $this->error;

        $next_class = $this->ctrl->getNextClass();
        $this->ctrl->setReturn($this, "show");

        switch ($next_class) {

                // profile
            case "ilpersonalprofilegui":
                $this->getStandardTemplates();
                $this->setTabs();
                $profile_gui = new ilPersonalProfileGUI();
                $this->ctrl->forwardCommand($profile_gui);
                break;
                
            // settings
            case "ilpersonalsettingsgui":
                $this->getStandardTemplates();
                $this->setTabs();
                $settings_gui = new ilPersonalSettingsGUI();
                $this->ctrl->forwardCommand($settings_gui);
                break;

            // profile
            /* probably not used anymore
            case "ilobjusergui":
                $user_gui = new ilObjUserGUI("", $_GET["user"], false, false);
                $this->ctrl->forwardCommand($user_gui);
                break;*/
            
            case 'ilcalendarpresentationgui':
                $this->getStandardTemplates();
                $this->displayHeader();
                $this->tpl->setTitle($this->lng->txt("calendar"));
                $this->setTabs();
                $cal = new ilCalendarPresentationGUI();
                $this->ctrl->forwardCommand($cal);
                $this->tpl->printToStdout();
                break;
            
                // pd notes
            case "ilpdnotesgui":
                if ($ilSetting->get('disable_notes') && $ilSetting->get('disable_comments')) {
                    ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
                    ilUtil::redirect('ilias.php?baseClass=ilDashboardGUI');
                    return;
                }
                
                $this->getStandardTemplates();
                $this->setTabs();
                $pd_notes_gui = new ilPDNotesGUI();
                $this->ctrl->forwardCommand($pd_notes_gui);
                break;
            
            // pd news
            case "ilpdnewsgui":
                $this->getStandardTemplates();
                $this->setTabs();
                $pd_news_gui = new ilPDNewsGUI();
                $this->ctrl->forwardCommand($pd_news_gui);
                break;

            case "ilcolumngui":
                $this->getStandardTemplates();
                $this->setTabs();
                $column_gui = new ilColumnGUI("pd");
                $this->initColumn($column_gui);
                $this->show();
                break;

            case "ilpdselecteditemsblockgui":
                $block = new ilPDSelectedItemsBlockGUI();
                $this->displayHeader();
                $ret = $this->ctrl->forwardCommand($block);
                if ($ret != "") {
                    $this->tpl->setContent($ret);
                    $this->tpl->printToStdout();
                }
                break;

            case "ilpdmembershipblockgui":
                $block = new ilPDMembershipBlockGUI();
                $ret = $this->ctrl->forwardCommand($block);
                if ($ret != "") {
                    $this->displayHeader();
                    $this->tpl->setContent($ret);
                    $this->tpl->printToStdout();
                }
                break;

            case 'ilcontactgui':
                if (!ilBuddySystem::getInstance()->isEnabled()) {
                    $ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->MESSAGE);
                }

                $this->getStandardTemplates();
                $this->setTabs();
                $this->tpl->setTitle($this->lng->txt('mail_addressbook'));

                $this->ctrl->forwardCommand(new ilContactGUI());
                break;

            case 'ilpersonalworkspacegui':
                $wsgui = new ilPersonalWorkspaceGUI();
                $this->ctrl->forwardCommand($wsgui);
                $this->tpl->printToStdout();
                break;
            
            case 'ilportfoliorepositorygui':
                $this->getStandardTemplates();
                $this->setTabs();
                $pfgui = new ilPortfolioRepositoryGUI();
                $this->ctrl->forwardCommand($pfgui);
                $this->tpl->printToStdout();
                break;

            case 'ilachievementsgui':
                $this->getStandardTemplates();
                $this->setTabs();
                $achievegui = new ilAchievementsGUI();
                $this->ctrl->forwardCommand($achievegui);
                break;

            case strtolower(ilMyStaffGUI::class):
                $this->getStandardTemplates();
                $mstgui = new ilMyStaffGUI();
                $this->ctrl->forwardCommand($mstgui);
                break;
            case 'ilgroupuseractionsgui':
                $this->getStandardTemplates();
                $this->setTabs();
                $ggui = new ilGroupUserActionsGUI();
                $this->ctrl->forwardCommand($ggui);
                $this->tpl->printToStdout();
                break;

            case "ildashboardrecommendedcontentgui":
                $gui = new ilDashboardRecommendedContentGUI();
                $this->ctrl->forwardCommand($gui);
                break;
            case "ilstudyprogrammedashboardviewgui":
                $gui = new ilStudyProgrammeDashboardViewGUI();
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                $context->current()->addAdditionalData(self::DISENGAGE_MAINBAR, true);
                $this->getStandardTemplates();
                $this->setTabs();
                $cmd = $this->ctrl->getCmd("show");
                $this->$cmd();
                break;
        }
    }

    /**
    * get standard templates
    */
    public function getStandardTemplates()
    {
        $this->tpl->loadStandardTemplate();
    }
    
    /**
    * show desktop
    */
    public function show()
    {
        // preload block settings
        ilBlockSetting::preloadPDBlockSettings();

        // display infopanel if something happened
        ilUtil::infoPanel();
        
        $this->tpl->setTitle($this->lng->txt("dash_dashboard"));
        $this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.png", false));
        
        $this->tpl->setContent($this->getCenterColumnHTML());
        $this->tpl->setRightContent($this->getRightColumnHTML());

        if (count($this->action_menu->getItems())) {
            $tpl = $this->tpl;
            $lng = $this->lng;

            $this->action_menu->setAsynch(false);
            $this->action_menu->setAsynchUrl('');
            $this->action_menu->setListTitle($lng->txt('actions'));
            $this->action_menu->setId('act_pd');
            $this->action_menu->setSelectionHeaderClass('small');
            $this->action_menu->setItemLinkClass('xsmall');
            $this->action_menu->setLinksMode('il_ContainerItemCommand2');
            $this->action_menu->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
            $this->action_menu->setUseImages(false);

            $htpl = new ilTemplate('tpl.header_action.html', true, true, 'Services/Repository');
            $htpl->setVariable('ACTION_DROP_DOWN', $this->action_menu->getHTML());

            $tpl->setHeaderActionMenu($htpl->get());
        }
        
        $this->tpl->printToStdout();
    }
    
    
    /**
     * Display center column
     */
    public function getCenterColumnHTML() : string
    {
        $ilCtrl = $this->ctrl;

        $html = "";
        $column_gui = new ilColumnGUI("pd", IL_COL_CENTER);
        $this->initColumn($column_gui);

        if ($ilCtrl->getNextClass() == "ilcolumngui" &&
            $column_gui->getCmdSide() == IL_COL_CENTER) {
            $html = $ilCtrl->forwardCommand($column_gui);
        } else {
            if (!$ilCtrl->isAsynch()) {
                if ($column_gui->getScreenMode() != IL_SCREEN_SIDE) {
                    // right column wants center
                    if ($column_gui->getCmdSide() == IL_COL_RIGHT) {
                        $column_gui = new ilColumnGUI("pd", IL_COL_RIGHT);
                        $this->initColumn($column_gui);
                        $html = $ilCtrl->forwardCommand($column_gui);
                    }
                    // left column wants center
                    if ($column_gui->getCmdSide() == IL_COL_LEFT) {
                        $column_gui = new ilColumnGUI("pd", IL_COL_LEFT);
                        $this->initColumn($column_gui);
                        $html = $ilCtrl->forwardCommand($column_gui);
                    }
                } else {
                    $html = "";

                    // user interface plugin slot + default rendering
                    $uip = new ilUIHookProcessor(
                        "Services/Dashboard",
                        "center_column",
                        array("personal_desktop_gui" => $this)
                    );
                    if (!$uip->replaced()) {
                        $html = $this->getMainContent();
                        //$html = $ilCtrl->getHTML($column_gui);
                    }
                    $html = $uip->getHTML($html);
                }
            }
        }
        return $html;
    }

    /**
    * Display right column
    */
    public function getRightColumnHTML() : string
    {
        $ilCtrl = $this->ctrl;

        $html = "";

        $column_gui = new ilColumnGUI("pd", IL_COL_RIGHT);
        $this->initColumn($column_gui);

        if ($column_gui->getScreenMode() == IL_SCREEN_FULL) {
            return "";
        }

        if ($ilCtrl->getNextClass() == "ilcolumngui" &&
            $column_gui->getCmdSide() == IL_COL_RIGHT &&
            $column_gui->getScreenMode() == IL_SCREEN_SIDE) {
            $html = $ilCtrl->forwardCommand($column_gui);
        } else {
            if (!$ilCtrl->isAsynch()) {
                $html = "";
                
                // user interface plugin slot + default rendering
                $uip = new ilUIHookProcessor(
                    "Services/Dashboard",
                    "right_column",
                    array("personal_desktop_gui" => $this)
                );
                if (!$uip->replaced()) {
                    $html = $ilCtrl->getHTML($column_gui);
                }
                $html = $uip->getHTML($html);
            }
        }

        return $html;
    }

    /**
    * Display left column
    */
    public function getLeftColumnHTML() : string
    {
        $ilCtrl = $this->ctrl;

        $column_gui = new ilColumnGUI("pd", IL_COL_LEFT);
        $this->initColumn($column_gui);

        $html = "";
        if ($column_gui->getScreenMode() == IL_SCREEN_FULL) {
            return "";
        }

        if ($ilCtrl->getNextClass() == "ilcolumngui" &&
            $column_gui->getCmdSide() == IL_COL_LEFT &&
            $column_gui->getScreenMode() == IL_SCREEN_SIDE) {
            $html = $ilCtrl->forwardCommand($column_gui);
        } else {
            if (!$ilCtrl->isAsynch()) {
                $html = "";
                
                // user interface plugin slot + default rendering
                $uip = new ilUIHookProcessor(
                    "Services/Dashboard",
                    "left_column",
                    array("personal_desktop_gui" => $this)
                );
                if (!$uip->replaced()) {
                    $html = $ilCtrl->getHTML($column_gui);
                }
                $html = $uip->getHTML($html);
            }
        }

        return $html;
    }

    public function prepareContentView() : void
    {
        $this->tpl->loadStandardTemplate();
                
        // display infopanel if something happened
        ilUtil::infoPanel();

        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd.svg"));
        $this->tpl->setTitle($this->lng->txt("personal_desktop"));
        $this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.png", false));
    }

    /**
    * set personal desktop tabs
    */
    public function setTabs() : void
    {
        $ilHelp = $this->help;
        
        $ilHelp->setScreenIdComponent("pd");
    }

    /**
     * Jump to memberships
     */
    public function jumpToMemberships() : void
    {
        $viewSettings = new ilPDSelectedItemsBlockViewSettings($GLOBALS['DIC']->user(), $this->requested_view);
        if ($viewSettings->enabledMemberships()) {
            $this->ctrl->setParameter($this, "view", $viewSettings->getMembershipsView());
        }
        $this->ctrl->redirect($this, "show");
    }

    /**
     * Jump to selected items
     */
    public function jumpToSelectedItems() : void
    {
        $viewSettings = new ilPDSelectedItemsBlockViewSettings($GLOBALS['DIC']->user(), $this->requested_view);
        if ($viewSettings->enabledSelectedItems()) {
            $this->ctrl->setParameter($this, "view", $viewSettings->getSelectedItemsView());
        }
        $this->show();
    }

    /**
     * workaround for menu in calendar only
     */
    public function jumpToProfile() : void
    {
        $this->ctrl->redirectByClass("ilpersonalprofilegui");
    }

    public function jumpToPortfolio() : void
    {
        // incoming back link from shared resource
        $cmd = "";
        if ($this->requested_dsh != "") {
            $this->ctrl->setParameterByClass("ilportfoliorepositorygui", "shr_id", $this->requested_dsh);
            $cmd = "showOther";
        }
        
        // used for goto links
        if ($this->requested_prt_id > 0) {
            $this->ctrl->setParameterByClass("ilobjportfoliogui", "prt_id", $this->requested_prt_id);
            $this->ctrl->setParameterByClass("ilobjportfoliogui", "gtp", $this->requested_gtp);
            $this->ctrl->redirectByClass(array("ilportfoliorepositorygui", "ilobjportfoliogui"), "preview");
        } else {
            $this->ctrl->redirectByClass("ilportfoliorepositorygui", $cmd);
        }
    }
    
    /**
     * workaround for menu in calendar only
     */
    public function jumpToSettings() : void
    {
        $this->ctrl->redirectByClass("ilpersonalsettingsgui");
    }
    

    /**
    * workaround for menu in calendar only
    */
    public function jumpToNews() : void
    {
        $this->ctrl->redirectByClass("ilpdnewsgui");
    }
    
    /**
     * Jump to calendar
     */
    public function jumpToCalendar() : void
    {
        global $DIC;
        $request = $DIC->http()->request();

        if ($request->getQueryParams()["cal_view"]) {
            $cal_view = $request->getQueryParams()["cal_view"];
            $this->ctrl->setParameter($this, "cal_view", $cal_view);
        }

        if (!empty($request->getQueryParams()["cal_agenda_per"])) {
            $cal_period = $request->getQueryParams()["cal_agenda_per"];
            $this->ctrl->setParameter($this, "cal_agenda_per", $cal_period);
        }

        $this->ctrl->redirectByClass("ilcalendarpresentationgui");
    }

    /**
     * Jump to contacts
     */
    public function jumpToContacts() : void
    {
        $this->ctrl->redirectByClass(array('ildashboardgui', 'ilcontactgui'));
    }

    /**
     * Jump to personal workspace
     */
    public function jumpToWorkspace() : void
    {
        // incoming back link from shared resource
        $cmd = "";
        if ($this->requested_dsh != "") {
            $this->ctrl->setParameterByClass("ilpersonalworkspacegui", "shr_id", $this->requested_dsh);
            $cmd = "share";
        }
        
        if ($this->requested_wsp_id > 0) {
            $this->ctrl->setParameterByClass("ilpersonalworkspacegui", "wsp_id", $this->requested_wsp_id);
        }
        
        if ($this->requested_gtp) {
            $this->ctrl->setParameterByClass("ilpersonalworkspacegui", "gtp", $this->requested_gtp);
        }
        
        $this->ctrl->redirectByClass("ilpersonalworkspacegui", $cmd);
    }

    /**
     *
     */
    protected function jumpToMyStaff() : void
    {
        $this->ctrl->redirectByClass(ilMyStaffGUI::class);
    }
    
    /**
     * Jump to badges
     */
    public function jumpToBadges() : void
    {
        $this->ctrl->redirectByClass(["ilAchievementsGUI", "ilbadgeprofilegui"]);
    }
    
    /**
     * Jump to personal skills
     */
    public function jumpToSkills() : void
    {
        $this->ctrl->redirectByClass("ilpersonalskillsgui");
    }
    

    /**
     * Init ilColumnGUI
     * @var ilColumnGUI $a_column_gui
     */
    public function initColumn(ilColumnGUI $a_column_gui) : void
    {
        $pd_set = new ilSetting("pd");
        if ($pd_set->get("enable_block_moving")) {
            $a_column_gui->setEnableMovement(true);
        }
        $a_column_gui->setActionMenu($this->action_menu);
    }
    
    /**
    * display header and locator
    */
    public function displayHeader() : void
    {
        $this->tpl->setTitle($this->lng->txt("dash_dashboard"));
    }

    /**
     * Temporary workaround for toggling the help
     */
    protected function toggleHelp() : void
    {
        if (ilSession::get("show_help_tool") == "1") {
            ilSession::set("show_help_tool", "0");
        } else {
            ilSession::set("show_help_tool", "1");
        }
        $this->ctrl->redirect($this, "show");
    }


    /**
     * Get main content
     * @return string
     */
    protected function getMainContent() : string
    {
        $html = "";
        $tpl = new ilTemplate("tpl.dashboard.html", true, true, "Services/Dashboard");
        $settings = new ilPDSelectedItemsBlockViewSettings($this->user);

        if ($settings->enabledSelectedItems()) {
            $html = $this->renderFavourites();
        }
        $html .= $this->renderRecommendedContent();
        $html .= $this->renderStudyProgrammes();
        $html .= $this->renderLearningSequences();
        if ($settings->enabledMemberships()) {
            $html .= $this->renderMemberships();
        }

        $tpl->setVariable("CONTENT", $html);

        return $tpl->get();
    }

    /**
     * Render favourites
     *
     * @return string
     */
    protected function renderFavourites() : string
    {
        $block = new ilPDSelectedItemsBlockGUI();
        return $block->getHTML();
    }

    /**
     * Render recommended content
     *
     * @return string
     */
    protected function renderRecommendedContent() : string
    {
        $db_rec_content = new ilDashboardRecommendedContentGUI();
        return $db_rec_content->render();
    }

    /**
     * Render study programmes
     *
     * @return string
     */
    protected function renderStudyProgrammes() : string
    {
        $st_block = ilStudyProgrammeDIC::dic()['ilStudyProgrammeDashboardViewGUI'];
        return $st_block->getHTML();
    }

    /**
     * Render memberships
     *
     * @return string
     */
    protected function renderMemberships() : string
    {
        $block = new ilPDMembershipBlockGUI();
        return $block->getHTML();
    }

    /**
     * Render learning sequences
     *
     * @return string
     */
    protected function renderLearningSequences() : string
    {
        $st_block = new ilDashboardLearningSequenceGUI();
        return $st_block->getHTML();
    }
}
