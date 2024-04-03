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

declare(strict_types=1);

use ILIAS\GlobalScreen\ScreenContext\ContextServices;

/**
 * @ilCtrl_Calls ilDashboardGUI: ilPersonalProfileGUI
 * @ilCtrl_Calls ilDashboardGUI: ilObjUserGUI, ilPDNotesGUI
 * @ilCtrl_Calls ilDashboardGUI: ilColumnGUI, ilPDNewsGUI, ilCalendarPresentationGUI
 * @ilCtrl_Calls ilDashboardGUI: ilMailSearchGUI, ilContactGUI
 * @ilCtrl_Calls ilDashboardGUI: ilPersonalWorkspaceGUI, ilPersonalSettingsGUI
 * @ilCtrl_Calls ilDashboardGUI: ilPortfolioRepositoryGUI, ilObjChatroomGUI
 * @ilCtrl_Calls ilDashboardGUI: ilMyStaffGUI
 * @ilCtrl_Calls ilDashboardGUI: ilGroupUserActionsGUI, ilAchievementsGUI
 * @ilCtrl_Calls ilDashboardGUI: ilPDMailBlockGUI
 * @ilCtrl_Calls ilDashboardGUI: ilSelectedItemsBlockGUI, ilDashboardRecommendedContentGUI, ilMembershipBlockGUI, ilDashboardLearningSequenceGUI, ilStudyProgrammeDashboardViewGUI, ilObjStudyProgrammeGUI
 *
 */
class ilDashboardGUI implements ilCtrlBaseClassInterface
{
    public const CMD_JUMP_TO_MY_STAFF = 'jumpToMyStaff';
    public const DISENGAGE_MAINBAR = 'dash_mb_disengage';

    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected ilSetting $settings;
    protected ilRbacSystem $rbacsystem;
    protected ilHelpGUI $help;
    public ilGlobalTemplateInterface $tpl;
    public ilLanguage $lng;
    public string $cmdClass = '';
    protected ContextServices $tool_context;
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
        $this->help = $DIC['ilHelp'];
        $tpl = $DIC['tpl'];
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        if ($this->user->getId() === ANONYMOUS_USER_ID) {
            $DIC->ui()->mainTemplate()->setOnScreenMessage('failure', $this->lng->txt('msg_not_available_for_anon'), true);
            $DIC->ctrl()->redirectToURL('login.php?cmd=force_login');
        }

        $this->tpl = $tpl;

        $this->ctrl->setContextObject(
            $this->user->getId(),
            'user'
        );

        $this->lng->loadLanguageModule('pdesk');
        $this->lng->loadLanguageModule('pd'); // #16813
        $this->lng->loadLanguageModule('dash');
        $this->lng->loadLanguageModule('mmbr');

        $params = $DIC->http()->request()->getQueryParams();
        $this->cmdClass = ($params['cmdClass'] ?? '');
        $this->requested_view = (int) ($params['view'] ?? 0);
        $this->requested_prt_id = (int) ($params['prt_id'] ?? 0);
        $this->requested_gtp = (int) ($params['gtp'] ?? 0);
        $this->requested_dsh = (string) ($params['dsh'] ?? null);
        $this->requested_wsp_id = (int) ($params['wsp_id'] ?? 0);

        $this->ctrl->saveParameter($this, ['view']);
    }

    public function executeCommand(): void
    {
        $context = $this->tool_context;
        $context->stack()->desktop();
        $ilSetting = $this->settings;

        $next_class = $this->ctrl->getNextClass();
        $this->ctrl->setReturn($this, 'show');
        switch ($next_class) {
            case 'ilpersonalprofilegui':
                $this->getStandardTemplates();
                $this->setTabs();
                $profile_gui = new ilPersonalProfileGUI();
                $this->ctrl->forwardCommand($profile_gui);
                break;

            case 'ilpersonalsettingsgui':
                $this->getStandardTemplates();
                $this->setTabs();
                $settings_gui = new ilPersonalSettingsGUI();
                $this->ctrl->forwardCommand($settings_gui);
                break;

            case 'ilcalendarpresentationgui':
                $this->getStandardTemplates();
                $this->displayHeader();
                $this->tpl->setTitle($this->lng->txt('calendar'));
                $this->setTabs();
                $cal = new ilCalendarPresentationGUI();
                $this->ctrl->forwardCommand($cal);
                $this->tpl->printToStdout();
                break;

            case 'ilpdnotesgui':
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

            case 'ilpdnewsgui':
                $this->getStandardTemplates();
                $this->setTabs();
                $pd_news_gui = new ilPDNewsGUI();
                $this->ctrl->forwardCommand($pd_news_gui);
                break;

            case 'ilcolumngui':
                if (strtolower($cmdClass = $this->ctrl->getCmdClass()) === strtolower(ilSelectedItemsBlockGUI::class)) {
                    $gui = new $cmdClass();
                    $ret = $this->ctrl->forwardCommand($gui);
                    if ($ret !== '') {
                        $this->tpl->setContent($ret);
                        $this->tpl->printToStdout();
                    }
                    break;
                }
                $this->getStandardTemplates();
                $this->setTabs();
                $column_gui = new ilColumnGUI('pd');
                $this->show();
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
            case strtolower(ilDashboardLearningSequenceGUI::class):
            case strtolower(ilMembershipBlockGUI::class):
            case strtolower(ilDashboardRecommendedContentGUI::class):
            case strtolower(ilSelectedItemsBlockGUI::class):
            case strtolower(ilStudyProgrammeDashboardViewGUI::class):
                $gui = new $next_class();
                $ret = $this->ctrl->forwardCommand($gui);
                if ($ret !== '' && $ret !== null) {
                    $this->tpl->setContent($ret);
                }
                $this->tpl->printToStdout();
                break;
            case strtolower(ilObjStudyProgrammeGUI::class):
                $gui = new ilObjStudyProgrammeGUI();
                $ret = $this->ctrl->forwardCommand($gui);
                $this->tpl->printToStdout();
                break;
            default:
                $context->current()->addAdditionalData(self::DISENGAGE_MAINBAR, true);
                $this->getStandardTemplates();
                $this->setTabs();
                $cmd = $this->ctrl->getCmd('show');
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
        ilBlockSetting::preloadPDBlockSettings();

        $this->tpl->setTitle($this->lng->txt('dash_dashboard'));
        $this->tpl->setTitleIcon(ilUtil::getImagePath('standard/icon_dshs.svg'), $this->lng->txt('dash_dashboard'));
        $this->tpl->setVariable('IMG_SPACE', ilUtil::getImagePath('media/spacer.png'));

        $this->tpl->setContent($this->getCenterColumnHTML());
        $this->tpl->setRightContent($this->getRightColumnHTML());
        $this->tpl->printToStdout();
    }

    public function getCenterColumnHTML(): string
    {
        $ilCtrl = $this->ctrl;

        $html = '';
        $column_gui = new ilColumnGUI('pd', IL_COL_CENTER);

        if ($ilCtrl->getNextClass() == 'ilcolumngui' &&
            $column_gui->getCmdSide() == IL_COL_CENTER) {
            $html = $ilCtrl->forwardCommand($column_gui);
        } else {
            if (!$ilCtrl->isAsynch()) {
                if ($column_gui->getScreenMode() != IL_SCREEN_SIDE) {
                    if ($column_gui->getCmdSide() == IL_COL_RIGHT) {
                        $column_gui = new ilColumnGUI('pd', IL_COL_RIGHT);
                        $html = $ilCtrl->forwardCommand($column_gui);
                    }
                    if ($column_gui->getCmdSide() == IL_COL_LEFT) {
                        $column_gui = new ilColumnGUI('pd', IL_COL_LEFT);
                        $html = $ilCtrl->forwardCommand($column_gui);
                    }
                } else {
                    $html = '';

                    $uip = new ilUIHookProcessor(
                        'Services/Dashboard',
                        'center_column',
                        ['personal_desktop_gui' => $this]
                    );
                    if (!$uip->replaced()) {
                        $html = $this->getMainContent();
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

        $html = '';

        $column_gui = new ilColumnGUI('pd', IL_COL_RIGHT);

        if ($column_gui->getScreenMode() == IL_SCREEN_FULL) {
            return '';
        }

        if ($ilCtrl->getNextClass() == 'ilcolumngui' &&
            $column_gui->getCmdSide() == IL_COL_RIGHT &&
            $column_gui->getScreenMode() == IL_SCREEN_SIDE) {
            $html = $ilCtrl->forwardCommand($column_gui);
        } else {
            if (!$ilCtrl->isAsynch()) {
                $html = '';

                $uip = new ilUIHookProcessor(
                    'Services/Dashboard',
                    'right_column',
                    ['personal_desktop_gui' => $this]
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

        $this->tpl->setTitleIcon(ilUtil::getImagePath('standard/icon_pd.svg'));
        $this->tpl->setTitle($this->lng->txt('personal_desktop'));
        $this->tpl->setVariable('IMG_SPACE', ilUtil::getImagePath('media/spacer.png'));
    }

    public function setTabs(): void
    {
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent('pd');
    }

    public function jumpToMemberships(): void
    {
        $viewSettings = new ilPDSelectedItemsBlockViewSettings($GLOBALS['DIC']->user(), $this->requested_view);
        if ($viewSettings->enabledMemberships()) {
            $this->ctrl->setParameter($this, 'view', $viewSettings->getMembershipsView());
        }
        $this->ctrl->redirect($this, 'show');
    }

    public function jumpToSelectedItems(): void
    {
        $viewSettings = new ilPDSelectedItemsBlockViewSettings($GLOBALS['DIC']->user(), $this->requested_view);
        if ($viewSettings->enabledSelectedItems()) {
            $this->ctrl->setParameter($this, 'view', $viewSettings->getSelectedItemsView());
        }
        $this->show();
    }

    public function jumpToProfile(): void
    {
        $this->ctrl->redirectByClass('ilpersonalprofilegui');
    }

    public function jumpToPortfolio(): void
    {
        $cmd = '';
        if ($this->requested_dsh != '') {
            $this->ctrl->setParameterByClass('ilportfoliorepositorygui', 'shr_id', $this->requested_dsh);
            $cmd = 'showOther';
        }

        if ($this->requested_prt_id > 0) {
            $this->ctrl->setParameterByClass('ilobjportfoliogui', 'prt_id', $this->requested_prt_id);
            $this->ctrl->setParameterByClass('ilobjportfoliogui', 'gtp', $this->requested_gtp);
            $this->ctrl->redirectByClass(['ilportfoliorepositorygui', 'ilobjportfoliogui'], 'preview');
        } else {
            $this->ctrl->redirectByClass('ilportfoliorepositorygui', $cmd);
        }
    }

    public function jumpToSettings(): void
    {
        $this->ctrl->redirectByClass('ilpersonalsettingsgui');
    }

    public function jumpToNews(): void
    {
        $this->ctrl->redirectByClass('ilpdnewsgui');
    }

    public function jumpToCalendar(): void
    {
        global $DIC;
        $request = $DIC->http()->request();

        $query_params = $request->getQueryParams();

        if (array_key_exists('cal_view', $query_params) && $query_params['cal_view']) {
            $cal_view = $query_params['cal_view'];
            $this->ctrl->setParameter($this, 'cal_view', $cal_view);
        }

        if (!empty($query_params['cal_agenda_per'])) {
            $cal_period = $query_params['cal_agenda_per'];
            $this->ctrl->setParameter($this, 'cal_agenda_per', $cal_period);
        }

        $this->ctrl->redirectByClass('ilcalendarpresentationgui');
    }

    public function jumpToWorkspace(): void
    {
        $cmd = '';
        if ($this->requested_dsh != '') {
            $this->ctrl->setParameterByClass('ilpersonalworkspacegui', 'shr_id', $this->requested_dsh);
            $cmd = 'share';
        }

        if ($this->requested_wsp_id > 0) {
            $this->ctrl->setParameterByClass('ilpersonalworkspacegui', 'wsp_id', $this->requested_wsp_id);
        }

        if ($this->requested_gtp) {
            $this->ctrl->setParameterByClass('ilpersonalworkspacegui', 'gtp', $this->requested_gtp);
        }

        $this->ctrl->redirectByClass('ilpersonalworkspacegui', $cmd);
    }

    protected function jumpToMyStaff(): void
    {
        $this->ctrl->redirectByClass(ilMyStaffGUI::class);
    }

    public function jumpToBadges(): void
    {
        $this->ctrl->redirectByClass(['ilAchievementsGUI', 'ilbadgeprofilegui']);
    }

    public function jumpToSkills(): void
    {
        $this->ctrl->redirectByClass('ilpersonalskillsgui');
    }

    public function displayHeader(): void
    {
        $this->tpl->setTitle($this->lng->txt('dash_dashboard'));
    }

    protected function toggleHelp(): void
    {
        if (ilSession::get('show_help_tool') == '1') {
            ilSession::set('show_help_tool', '0');
        } else {
            ilSession::set('show_help_tool', '1');
        }
        $this->ctrl->redirect($this, 'show');
    }

    protected function getMainContent(): string
    {
        $html = '';
        $tpl = new ilTemplate('tpl.dashboard.html', true, true, 'Services/Dashboard');
        $settings = new ilPDSelectedItemsBlockViewSettings($this->user);

        foreach ($settings->getViewPositions() as $view_position) {
            if ($settings->isViewEnabled($view_position)) {
                $html .= $this->renderView($view_position);
            }
        }


        $tpl->setVariable('CONTENT', $html);

        return $tpl->get();
    }

    protected function renderView(int $view): string
    {
        switch ($view) {
            case ilPDSelectedItemsBlockConstants::VIEW_SELECTED_ITEMS:
                return (new ilSelectedItemsBlockGUI())->getHTML();
            case ilPDSelectedItemsBlockConstants::VIEW_RECOMMENDED_CONTENT:
                return (new ilDashboardRecommendedContentGUI())->getHTML();
            case ilPDSelectedItemsBlockConstants::VIEW_MY_MEMBERSHIPS:
                return (new ilMembershipBlockGUI())->getHTML();
            case ilPDSelectedItemsBlockConstants::VIEW_LEARNING_SEQUENCES:
                return (new ilDashboardLearningSequenceGUI())->getHTML();
            case ilPDSelectedItemsBlockConstants::VIEW_MY_STUDYPROGRAMME:
                return (new ilStudyProgrammeDashboardViewGUI())->getHTML();
            default:
                return '';
        }
    }
}
