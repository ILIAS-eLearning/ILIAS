<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/User/classes/class.ilObjUser.php';
include_once "Services/Mail/classes/class.ilMail.php";
include_once 'Services/Mail/classes/class.ilMailGlobalServices.php';
include_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

/**
* GUI class for personal desktop
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilPersonalDesktopGUI: ilPersonalProfileGUI, ilBookmarkAdministrationGUI
* @ilCtrl_Calls ilPersonalDesktopGUI: ilObjUserGUI, ilPDNotesGUI
* @ilCtrl_Calls ilPersonalDesktopGUI: ilColumnGUI, ilPDNewsGUI, ilCalendarPresentationGUI
* @ilCtrl_Calls ilPersonalDesktopGUI: ilMailSearchGUI, ilContactGUI
* @ilCtrl_Calls ilPersonalDesktopGUI: ilPersonalWorkspaceGUI, ilPersonalSettingsGUI
* @ilCtrl_Calls ilPersonalDesktopGUI: ilPortfolioRepositoryGUI, ilObjChatroomGUI
* @ilCtrl_Calls ilPersonalDesktopGUI: ilMyStaffGUI
* @ilCtrl_Calls ilPersonalDesktopGUI: ilGroupUserActionsGUI, ilAchievementsGUI
*
*/
class ilPersonalDesktopGUI
{
    const CMD_JUMP_TO_MY_STAFF = "jumpToMyStaff";
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilMainMenuGUI
     */
    protected $main_menu;

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

    public $tpl;
    public $lng;

    public $cmdClass = '';

    /**
     * @var ilAdvancedSelectionListGUI
     */
    protected $action_menu;

    /**
    * constructor
    */
    public function __construct()
    {
        global $DIC;

        $this->main_menu = $DIC["ilMainMenu"];
        $this->user = $DIC->user();
        $this->error = $DIC["ilErr"];
        $this->settings = $DIC->settings();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->help = $DIC["ilHelp"];
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $ilMainMenu = $DIC["ilMainMenu"];
        $ilUser = $DIC->user();
        $ilErr = $DIC["ilErr"];
        
        
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        $ilCtrl->setContext(
            $ilUser->getId(),
            "user"
        );

        $ilMainMenu->setActive("desktop");
        $this->lng->loadLanguageModule("pdesk");
        $this->lng->loadLanguageModule("pd"); // #16813
        
        // catch hack attempts
        if ($GLOBALS['DIC']['ilUser']->getId() == ANONYMOUS_USER_ID) {
            $ilErr->raiseError($this->lng->txt("msg_not_available_for_anon"), $ilErr->MESSAGE);
        }
        $this->cmdClass = $_GET['cmdClass'];

        $this->ctrl->saveParameter($this, array("view"));

        //$tree->useCache(false);

        $this->action_menu = new ilAdvancedSelectionListGUI();
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilSetting = $this->settings;
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;

        $next_class = $this->ctrl->getNextClass();
        $this->ctrl->setReturn($this, "show");

        $this->tpl->addCss(ilUtil::getStyleSheetLocation('filesystem', 'delos.css', 'Services/Calendar'));

        // read last active subsection
        if (isset($_GET['PDHistory']) && $_GET['PDHistory']) {
            $next_class = $this->__loadNextClass();
        }
        $this->__storeLastClass($next_class);

        switch ($next_class) {
            case "ilbookmarkadministrationgui":
                if ($ilSetting->get('disable_bookmarks')) {
                    ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
                    ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
                    return;
                }
                include_once("./Services/Bookmarks/classes/class.ilBookmarkAdministrationGUI.php");
                $bookmark_gui = new ilBookmarkAdministrationGUI();
                $this->getStandardTemplates();
                $this->setTabs();
                $ret = $this->ctrl->forwardCommand($bookmark_gui);
                break;
            
                // profile
            case "ilpersonalprofilegui":
                $this->getStandardTemplates();
                $this->setTabs();
                include_once("./Services/User/classes/class.ilPersonalProfileGUI.php");
                $profile_gui = new ilPersonalProfileGUI();
                $ret = $this->ctrl->forwardCommand($profile_gui);
                break;
                
            // settings
            case "ilpersonalsettingsgui":
                $this->getStandardTemplates();
                $this->setTabs();
                include_once("./Services/User/classes/class.ilPersonalSettingsGUI.php");
                $settings_gui = new ilPersonalSettingsGUI();
                $ret = $this->ctrl->forwardCommand($settings_gui);
                break;
            
                // profile
            case "ilobjusergui":
                include_once('./Services/User/classes/class.ilObjUserGUI.php');
                $user_gui = new ilObjUserGUI("", $_GET["user"], false, false);
                $ret = $this->ctrl->forwardCommand($user_gui);
                break;
            
            case 'ilcalendarpresentationgui':
                $this->getStandardTemplates();
                $this->displayHeader();
                $this->tpl->setTitle($this->lng->txt("calendar"));
                $this->setTabs();
                include_once('./Services/Calendar/classes/class.ilCalendarPresentationGUI.php');
                $cal = new ilCalendarPresentationGUI();
                $ret = $this->ctrl->forwardCommand($cal);
                $this->tpl->show();
                break;
            
                // pd notes
            case "ilpdnotesgui":
                if ($ilSetting->get('disable_notes') && $ilSetting->get('disable_comments')) {
                    ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
                    ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
                    return;
                }
                
                $this->getStandardTemplates();
                $this->setTabs();
                include_once("./Services/Notes/classes/class.ilPDNotesGUI.php");
                $pd_notes_gui = new ilPDNotesGUI();
                $ret = $this->ctrl->forwardCommand($pd_notes_gui);
                break;
            
            // pd news
            case "ilpdnewsgui":
                $this->getStandardTemplates();
                $this->setTabs();
                include_once("./Services/News/classes/class.ilPDNewsGUI.php");
                $pd_news_gui = new ilPDNewsGUI();
                $ret = $this->ctrl->forwardCommand($pd_news_gui);
                break;

            case "ilcolumngui":
                $this->getStandardTemplates();
                $this->setTabs();
                include_once("./Services/Block/classes/class.ilColumnGUI.php");
                $column_gui = new ilColumnGUI("pd");
                $this->initColumn($column_gui);
                $this->show();
                break;

            case 'ilcontactgui':
                require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystem.php';
                if (!ilBuddySystem::getInstance()->isEnabled()) {
                    $ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->MESSAGE);
                }

                $this->getStandardTemplates();
                $this->setTabs();
                $this->tpl->setTitle($this->lng->txt('mail_addressbook'));

                require_once 'Services/Contact/classes/class.ilContactGUI.php';
                $this->ctrl->forwardCommand(new ilContactGUI());
                break;

            case 'ilpersonalworkspacegui':
                // $this->getStandardTemplates();
                // $this->setTabs();
                include_once 'Services/PersonalWorkspace/classes/class.ilPersonalWorkspaceGUI.php';
                $wsgui = new ilPersonalWorkspaceGUI();
                $ret = $this->ctrl->forwardCommand($wsgui);
                $this->tpl->show();
                break;
            
            case 'ilportfoliorepositorygui':
                $this->getStandardTemplates();
                $this->setTabs();
                include_once 'Modules/Portfolio/classes/class.ilPortfolioRepositoryGUI.php';
                $pfgui = new ilPortfolioRepositoryGUI();
                $ret = $this->ctrl->forwardCommand($pfgui);
                $this->tpl->show();
                break;

            case 'ilachievementsgui':
                $this->getStandardTemplates();
                $this->setTabs();
                $achievegui = new ilAchievementsGUI();
                $ret = $this->ctrl->forwardCommand($achievegui);
                break;

            case strtolower(ilMyStaffGUI::class):
                $this->getStandardTemplates();
                $mstgui = new ilMyStaffGUI();
                $ret = $this->ctrl->forwardCommand($mstgui);
                break;
            case 'ilgroupuseractionsgui':
                $this->getStandardTemplates();
                $this->setTabs();
                include_once './Modules/Group/UserActions/classes/class.ilGroupUserActionsGUI.php';
                $ggui = new ilGroupUserActionsGUI();
                $ret = $this->ctrl->forwardCommand($ggui);
                $this->tpl->show();
                break;
            case 'redirect':
                $this->redirect();
                break;

            default:
                $this->getStandardTemplates();
                $this->setTabs();
                $cmd = $this->ctrl->getCmd("show");
                $this->$cmd();
                break;
        }
        $ret = null;
        return $ret;
    }

    /**
    * get standard templates
    */
    public function getStandardTemplates()
    {
        $this->tpl->getStandardTemplate();
    }
    
    /**
    * show desktop
    */
    public function show()
    {
        // preload block settings
        include_once("Services/Block/classes/class.ilBlockSetting.php");
        ilBlockSetting::preloadPDBlockSettings();

        // add template for content
        $this->pd_tpl = new ilTemplate("tpl.usr_personaldesktop.html", true, true, "Services/PersonalDesktop");
        //		$this->tpl->getStandardTemplate();

        // display infopanel if something happened
        ilUtil::infoPanel();
        
        $this->tpl->setTitle($this->lng->txt("overview"));
        $this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.png", false));
        
        $this->tpl->setContent($this->getCenterColumnHTML());
        $this->tpl->setRightContent($this->getRightColumnHTML());
        $this->tpl->setLeftContent($this->getLeftColumnHTML());

        if (count($this->action_menu->getItems())) {
            /**
             * @var $tpl ilTemplate
             * @var $lng ilLanguage
             */
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
        
        $this->tpl->show();
    }
    
    
    /**
    * Display center column
    */
    public function getCenterColumnHTML()
    {
        $ilCtrl = $this->ctrl;
        $ilPluginAdmin = $this->plugin_admin;
        
        include_once("Services/Block/classes/class.ilColumnGUI.php");
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
                    include_once("./Services/UIComponent/classes/class.ilUIHookProcessor.php");
                    $uip = new ilUIHookProcessor(
                        "Services/PersonalDesktop",
                        "center_column",
                        array("personal_desktop_gui" => $this)
                    );
                    if (!$uip->replaced()) {
                        $html = $ilCtrl->getHTML($column_gui);
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
    public function getRightColumnHTML()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilPluginAdmin = $this->plugin_admin;
        
        include_once("Services/Block/classes/class.ilColumnGUI.php");
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
                include_once("./Services/UIComponent/classes/class.ilUIHookProcessor.php");
                $uip = new ilUIHookProcessor(
                    "Services/PersonalDesktop",
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
    public function getLeftColumnHTML()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilPluginAdmin = $this->plugin_admin;

        include_once("Services/Block/classes/class.ilColumnGUI.php");
        $column_gui = new ilColumnGUI("pd", IL_COL_LEFT);
        $this->initColumn($column_gui);

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
                include_once("./Services/UIComponent/classes/class.ilUIHookProcessor.php");
                $uip = new ilUIHookProcessor(
                    "Services/PersonalDesktop",
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

    public function prepareContentView()
    {
        // add template for content
        $this->pd_tpl = new ilTemplate("tpl.usr_personaldesktop.html", true, true, "Services/PersonalDesktop");
        $this->tpl->getStandardTemplate();
                
        // display infopanel if something happened
        ilUtil::infoPanel();

        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd.svg"));
        $this->tpl->setTitle($this->lng->txt("personal_desktop"));
        $this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.png", false));
    }
    
    /**
    * Display system messages.
    */
    public function displaySystemMessages()
    {
        include_once("Services/Mail/classes/class.ilPDSysMessageBlockGUI.php");
        $sys_block = new ilPDSysMessageBlockGUI("ilpersonaldesktopgui", "show");
        return $sys_block->getHTML();
    }
    
    /**
    * Returns the multidimenstional sorted array
    *
    * Returns the multidimenstional sorted array
    *
    * @author       Muzaffar Altaf <maltaf@tzi.de>
    * @param array $arrays The array to be sorted
    * @param string $key_sort The keys on which array must be sorted
    * @access public
    */
    public function multiarray_sort($array, $key_sort)
    {
        if ($array) {
            $key_sorta = explode(";", $key_sort);
            
            $multikeys = array_keys($array);
            $keys = array_keys($array[$multikeys[0]]);
            
            for ($m = 0; $m < count($key_sorta); $m++) {
                $nkeys[$m] = trim($key_sorta[$m]);
            }
            $n += count($key_sorta);
            
            for ($i = 0; $i < count($keys); $i++) {
                if (!in_array($keys[$i], $key_sorta)) {
                    $nkeys[$n] = $keys[$i];
                    $n += "1";
                }
            }
            
            for ($u = 0;$u < count($array); $u++) {
                $arr = $array[$multikeys[$u]];
                for ($s = 0; $s < count($nkeys); $s++) {
                    $k = $nkeys[$s];
                    $output[$multikeys[$u]][$k] = $array[$multikeys[$u]][$k];
                }
            }
            sort($output);
            return $output;
        }
    }
    
    /**
    * set personal desktop tabs
    */
    public function setTabs()
    {
        $ilHelp = $this->help;
        
        $ilHelp->setScreenIdComponent("pd");
    }

    /**
     * Jump to memberships
     */
    public function jumpToMemberships()
    {
        require_once 'Services/PersonalDesktop/ItemsBlock/classes/class.ilPDSelectedItemsBlockViewSettings.php';
        $viewSettings = new ilPDSelectedItemsBlockViewSettings($GLOBALS['DIC']->user(), (int) $_GET['view']);
        if ($viewSettings->enabledMemberships()) {
            $_GET['view'] = $viewSettings->getMembershipsView();
            $this->ctrl->setParameter($this, "view", $viewSettings->getMembershipsView());
        }
        $this->show();
    }

    /**
     * Jump to selected items
     */
    public function jumpToSelectedItems()
    {
        require_once 'Services/PersonalDesktop/ItemsBlock/classes/class.ilPDSelectedItemsBlockViewSettings.php';
        $viewSettings = new ilPDSelectedItemsBlockViewSettings($GLOBALS['DIC']->user(), (int) $_GET['view']);
        if ($viewSettings->enabledSelectedItems()) {
            $_GET['view'] = $viewSettings->getSelectedItemsView();
            $this->ctrl->setParameter($this, "view", $viewSettings->getSelectedItemsView());
        }
        $this->show();
    }

    /**
     * workaround for menu in calendar only
     */
    public function jumpToProfile()
    {
        $this->ctrl->redirectByClass("ilpersonalprofilegui");
    }

    public function jumpToPortfolio()
    {
        // incoming back link from shared resource
        $cmd = "";
        if ($_REQUEST["dsh"]) {
            $this->ctrl->setParameterByClass("ilportfoliorepositorygui", "shr_id", $_REQUEST["dsh"]);
            $cmd = "showOther";
        }
        
        // used for goto links
        if ($_GET["prt_id"]) {
            $this->ctrl->setParameterByClass("ilobjportfoliogui", "prt_id", (int) $_GET["prt_id"]);
            $this->ctrl->setParameterByClass("ilobjportfoliogui", "gtp", (int) $_GET["gtp"]);
            $this->ctrl->redirectByClass(array("ilportfoliorepositorygui", "ilobjportfoliogui"), "preview");
        } else {
            $this->ctrl->redirectByClass("ilportfoliorepositorygui", $cmd);
        }
    }
    
    /**
     * workaround for menu in calendar only
     */
    public function jumpToSettings()
    {
        $this->ctrl->redirectByClass("ilpersonalsettingsgui");
    }
    
    /**
    * workaround for menu in calendar only
    */
    public function jumpToBookmarks()
    {
        $ilSetting = $this->settings;

        if ($ilSetting->get("disable_bookmarks")) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
            return;
        }
        
        $this->ctrl->redirectByClass("ilbookmarkadministrationgui");
    }
    
    /**
    * workaround for menu in calendar only
    */
    public function jumpToNotes()
    {
        $ilSetting = $this->settings;

        if ($ilSetting->get('disable_notes')) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
            return;
        }
        
        $this->ctrl->redirectByClass("ilpdnotesgui");
    }

    /**
     * workaround for menu in calendar only
     */
    public function jumpToComments()
    {
        $ilSetting = $this->settings;

        if ($ilSetting->get('disable_comments')) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
            return;
        }

        $this->ctrl->redirectByClass("ilpdnotesgui", "showPublicComments");
    }

    /**
    * workaround for menu in calendar only
    */
    public function jumpToNews()
    {
        $this->ctrl->redirectByClass("ilpdnewsgui");
    }
    
    /**
    * workaround for menu in calendar only
    */
    public function jumpToLP()
    {
        $this->ctrl->redirectByClass("illearningprogressgui");
    }

    /**
     * Jump to calendar
     */
    public function jumpToCalendar()
    {
        $this->ctrl->redirectByClass("ilcalendarpresentationgui");
    }

    /**
     * Jump to contacts
     */
    public function jumpToContacts()
    {
        $this->ctrl->redirectByClass(array('ilpersonaldesktopgui', 'ilcontactgui'));
    }

    /**
     * Jump to personal workspace
     */
    public function jumpToWorkspace()
    {
        // incoming back link from shared resource
        $cmd = "";
        if ($_REQUEST["dsh"]) {
            $this->ctrl->setParameterByClass("ilpersonalworkspacegui", "shr_id", $_REQUEST["dsh"]);
            $cmd = "share";
        }
        
        if ($_REQUEST["wsp_id"]) {
            $this->ctrl->setParameterByClass("ilpersonalworkspacegui", "wsp_id", (int) $_REQUEST["wsp_id"]);
        }
        
        if ($_REQUEST["gtp"]) {
            $this->ctrl->setParameterByClass("ilpersonalworkspacegui", "gtp", (int) $_REQUEST["gtp"]);
        }
        
        $this->ctrl->redirectByClass("ilpersonalworkspacegui", $cmd);
    }

    /**
     *
     */
    protected function jumpToMyStaff()
    {
        $this->ctrl->redirectByClass(ilMyStaffGUI::class);
    }
    
    /**
     * Jump to badges
     */
    public function jumpToBadges()
    {
        $this->ctrl->redirectByClass(["ilAchievementsGUI", "ilbadgeprofilegui"]);
    }
    
    /**
     * Jump to personal skills
     */
    public function jumpToSkills()
    {
        $this->ctrl->redirectByClass("ilpersonalskillsgui");
    }
    
    public function __loadNextClass()
    {
        $stored_classes = array('ilpersonaldesktopgui',
                                'ilpersonalprofilegui',
                                'ilpdnotesgui',
                                'ilcalendarpresentationgui',
                                'ilbookmarkadministrationgui',
                                'illearningprogressgui');

        if (isset($_SESSION['il_pd_history']) and in_array($_SESSION['il_pd_history'], $stored_classes)) {
            return $_SESSION['il_pd_history'];
        } else {
            $this->ctrl->getNextClass($this);
        }
    }
    public function __storeLastClass($a_class)
    {
        $_SESSION['il_pd_history'] = $a_class;
        $this->cmdClass = $a_class;
    }

    /**
     * Init ilColumnGUI
     * @var ilColumnGUI $a_column_gui
     */
    public function initColumn($a_column_gui)
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
    public function displayHeader()
    {
        $this->tpl->setTitle($this->lng->txt("personal_desktop"));
    }
}
