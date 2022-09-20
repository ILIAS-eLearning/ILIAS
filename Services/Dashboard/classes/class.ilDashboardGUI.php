<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Dashboard UI
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilDashboardGUI: ilPersonalProfileGUI
 * @ilCtrl_Calls ilDashboardGUI: ilObjUserGUI, ilPDNotesGUI
 * @ilCtrl_Calls ilDashboardGUI: ilColumnGUI, ilPDNewsGUI, ilCalendarPresentationGUI
 * @ilCtrl_Calls ilDashboardGUI: ilMailSearchGUI, ilContactGUI
 * @ilCtrl_Calls ilDashboardGUI: ilPersonalWorkspaceGUI, ilPersonalSettingsGUI
 * @ilCtrl_Calls ilDashboardGUI: ilPortfolioRepositoryGUI, ilObjChatroomGUI
 * @ilCtrl_Calls ilDashboardGUI: ilMyStaffGUI
 * @ilCtrl_Calls ilDashboardGUI: ilGroupUserActionsGUI, ilAchievementsGUI
 * @ilCtrl_Calls ilDashboardGUI: ilPDSelectedItemsBlockGUI, ilPDMembershipBlockGUI, ilPDMailBlockGUI, ilDashboardRecommendedContentGUI, ilStudyProgrammeDashboardViewGUI
 *
 */
class ilDashboardGUI implements ilCtrlBaseClassInterface
{
    public const CMD_JUMP_TO_MY_STAFF = "jumpToMyStaff";
    public const DISENGAGE_MAINBAR = "dash_mb_disengage";

    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected ilSetting $settings;
    protected ilRbacSystem $rbacsystem;
    protected ilHelpGUI $help;
    public \ilGlobalTemplateInterface $tpl;
    public \ilLanguage $lng;
    public string $cmdClass = '';
    protected ilAdvancedSelectionListGUI $action_menu;
    protected \ILIAS\GlobalScreen\ScreenContext\ContextServices $tool_context;
    protected int $requested_view;
    protected int $requested_prt_id;
    protected int $requested_gtp;
    protected string $requested_dsh;
    protected int $requested_wsp_id;

    public function __construct()
    {
        global $DIC;

        $this->tool_context = $DIC->globalScreen()->tool()->context();
        $this->user = $DIC->user();
        $this->settings = $DIC->settings();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->help = $DIC["ilHelp"];
        $tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->tpl = $tpl;

        $this->ctrl->setContextObject(
            $this->user->getId(),
            "user"
        );

        $this->lng->loadLanguageModule("pdesk");
        $this->lng->loadLanguageModule("pd"); // #16813
        $this->lng->loadLanguageModule("dash");
        $this->lng->loadLanguageModule("mmbr");

        if ($this->user->getId() == ANONYMOUS_USER_ID) {
            throw new ilPermissionException($this->lng->txt("msg_not_available_for_anon"));
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

    public function executeCommand(): void
    {
        $context = $this->tool_context;
        $context->stack()->desktop();
        $ilSetting = $this->settings;

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
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
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
                    throw new ilPermissionException($this->lng->txt('msg_no_perm_read'));
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

    public function getStandardTemplates(): void
    {
        $this->tpl->loadStandardTemplate();
    }

    public function show(): void
    {
        // preload block settings
        ilBlockSetting::preloadPDBlockSettings();

        $this->tpl->setTitle($this->lng->txt("dash_dashboard"));
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_dshs.svg"), $this->lng->txt("dash_dashboard"));
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

    public function getCenterColumnHTML(): string
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

    public function getRightColumnHTML(): string
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

    public function prepareContentView(): void
    {
        $this->tpl->loadStandardTemplate();

        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd.svg"));
        $this->tpl->setTitle($this->lng->txt("personal_desktop"));
        $this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.png", false));
    }

    public function setTabs(): void
    {
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent("pd");
    }

    public function jumpToMemberships(): void
    {
        $viewSettings = new ilPDSelectedItemsBlockViewSettings($GLOBALS['DIC']->user(), $this->requested_view);
        if ($viewSettings->enabledMemberships()) {
            $this->ctrl->setParameter($this, "view", $viewSettings->getMembershipsView());
        }
        $this->ctrl->redirect($this, "show");
    }

    public function jumpToSelectedItems(): void
    {
        $viewSettings = new ilPDSelectedItemsBlockViewSettings($GLOBALS['DIC']->user(), $this->requested_view);
        if ($viewSettings->enabledSelectedItems()) {
            $this->ctrl->setParameter($this, "view", $viewSettings->getSelectedItemsView());
        }
        $this->show();
    }

    public function jumpToProfile(): void
    {
        $this->ctrl->redirectByClass("ilpersonalprofilegui");
    }

    public function jumpToPortfolio(): void
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

    public function jumpToSettings(): void
    {
        $this->ctrl->redirectByClass("ilpersonalsettingsgui");
    }

    public function jumpToNews(): void
    {
        $this->ctrl->redirectByClass("ilpdnewsgui");
    }

    public function jumpToCalendar(): void
    {
        global $DIC;
        $request = $DIC->http()->request();

        $query_params = $request->getQueryParams();

        if (array_key_exists("cal_view", $query_params) && $query_params["cal_view"]) {
            $cal_view = $query_params["cal_view"];
            $this->ctrl->setParameter($this, "cal_view", $cal_view);
        }

        if (!empty($query_params["cal_agenda_per"])) {
            $cal_period = $query_params["cal_agenda_per"];
            $this->ctrl->setParameter($this, "cal_agenda_per", $cal_period);
        }

        $this->ctrl->redirectByClass("ilcalendarpresentationgui");
    }

    public function jumpToWorkspace(): void
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

    protected function jumpToMyStaff(): void
    {
        $this->ctrl->redirectByClass(ilMyStaffGUI::class);
    }

    public function jumpToBadges(): void
    {
        $this->ctrl->redirectByClass(["ilAchievementsGUI", "ilbadgeprofilegui"]);
    }

    public function jumpToSkills(): void
    {
        $this->ctrl->redirectByClass("ilpersonalskillsgui");
    }

    public function initColumn(ilColumnGUI $a_column_gui): void
    {
        $a_column_gui->setActionMenu($this->action_menu);
    }

    public function displayHeader(): void
    {
        $this->tpl->setTitle($this->lng->txt("dash_dashboard"));
    }

    protected function toggleHelp(): void
    {
        if (ilSession::get("show_help_tool") == "1") {
            ilSession::set("show_help_tool", "0");
        } else {
            ilSession::set("show_help_tool", "1");
        }
        $this->ctrl->redirect($this, "show");
    }

    protected function getMainContent(): string
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

    protected function renderFavourites(): string
    {
        $block = new ilPDSelectedItemsBlockGUI();
        return $block->getHTML();
    }

    protected function renderRecommendedContent(): string
    {
        $db_rec_content = new ilDashboardRecommendedContentGUI();
        return $db_rec_content->render();
    }

    protected function renderStudyProgrammes(): string
    {
        $st_block = ilStudyProgrammeDIC::dic()['ilStudyProgrammeDashboardViewGUI'];
        return $st_block->getHTML();
    }

    protected function renderMemberships(): string
    {
        $block = new ilPDMembershipBlockGUI();
        return $block->getHTML();
    }

    protected function renderLearningSequences(): string
    {
        $st_block = new ilDashboardLearningSequenceGUI();
        return $st_block->getHTML();
    }
}
