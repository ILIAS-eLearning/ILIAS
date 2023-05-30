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
 */
class ilDashboardGUI implements ilCtrlBaseClassInterface
{
    public const CMD_JUMP_TO_MY_STAFF = 'jumpToMyStaff';
    public const DISENGAGE_MAINBAR = 'dash_mb_disengage';

    protected readonly ilCtrl $ctrl;
    protected readonly ilSetting $settings;
    protected readonly ilRbacSystem $rbacsystem;
    protected readonly ilHelpGUI $help;
    public readonly ilGlobalTemplateInterface $tpl;
    public readonly ilLanguage $lng;
    protected readonly ilAdvancedSelectionListGUI $action_menu;
    protected readonly ContextServices $tool_context;
    protected ilObjUser $user;
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
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        if ($this->user->getId() === ANONYMOUS_USER_ID) {
            $DIC->ui()->mainTemplate()->setOnScreenMessage('failure', $this->lng->txt('msg_not_available_for_anon'), true);
            $DIC->ctrl()->redirectToURL('login.php?cmd=force_login');
        }

        $this->ctrl->setContextObject($this->user->getId(), 'user');

        $this->lng->loadLanguageModule('pdesk');
        $this->lng->loadLanguageModule('pd');
        $this->lng->loadLanguageModule('dash');
        $this->lng->loadLanguageModule('mmbr');

        $params = $DIC->http()->request()->getQueryParams();
        $this->requested_view = (int) ($params['view'] ?? 0);
        $this->requested_prt_id = (int) ($params['prt_id'] ?? 0);
        $this->requested_gtp = (int) ($params['gtp'] ?? 0);
        $this->requested_dsh = $params['dsh'] ?? '';
        $this->requested_wsp_id = (int) ($params['wsp_id'] ?? 0);

        $this->ctrl->saveParameter($this, ['view']);
        $this->action_menu = new ilAdvancedSelectionListGUI();
    }

    public function executeCommand(): void
    {
        $this->tool_context->stack()->desktop();

        $next_class = $this->ctrl->getNextClass();
        $this->ctrl->setReturn($this, 'show');
        switch ($next_class) {
            case strtolower(ilPersonalProfileGUI::class):
                $this->getStandardTemplates();
                $this->setTabs();
                $profile_gui = new ilPersonalProfileGUI();
                $this->ctrl->forwardCommand($profile_gui);
                break;
            case strtolower(ilPersonalSettingsGUI::class):
                $this->getStandardTemplates();
                $this->setTabs();
                $settings_gui = new ilPersonalSettingsGUI();
                $this->ctrl->forwardCommand($settings_gui);
                break;
            case strtolower(ilCalendarPresentationGUI::class):
                $this->getStandardTemplates();
                $this->displayHeader();
                $this->tpl->setTitle($this->lng->txt('calendar'));
                $this->setTabs();
                $cal = new ilCalendarPresentationGUI();
                $this->ctrl->forwardCommand($cal);
                $this->tpl->printToStdout();
                break;
            case strtolower(ilPDNotesGUI::class):
                if ($this->settings->get('disable_notes') && $this->settings->get('disable_comments')) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
                    ilUtil::redirect('ilias.php?baseClass=' . __CLASS__);
                    return;
                }
                $this->getStandardTemplates();
                $this->setTabs();
                $pd_notes_gui = new ilPDNotesGUI();
                $this->ctrl->forwardCommand($pd_notes_gui);
                break;
            case strtolower(ilPDNewsGUI::class):
                $this->getStandardTemplates();
                $this->setTabs();
                $pd_news_gui = new ilPDNewsGUI();
                $this->ctrl->forwardCommand($pd_news_gui);
                break;
            case strtolower(ilColumnGUI::class):
                $this->getStandardTemplates();
                $this->setTabs();
                $column_gui = new ilColumnGUI('pd');
                $this->initColumn($column_gui);
                $this->show();
                break;
            case strtolower(ilPDSelectedItemsBlockGUI::class):
                $block = new ilPDSelectedItemsBlockGUI();
                $this->displayHeader();
                $ret = $this->ctrl->forwardCommand($block);
                if ($ret) {
                    $this->tpl->setContent($ret);
                    $this->tpl->printToStdout();
                }
                break;
            case strtolower(ilPDMembershipBlockGUI::class):
                $block = new ilPDMembershipBlockGUI();
                $ret = $this->ctrl->forwardCommand($block);
                if ($ret) {
                    $this->displayHeader();
                    $this->tpl->setContent($ret);
                    $this->tpl->printToStdout();
                }
                break;
            case strtolower(ilContactGUI::class):
                if (!ilBuddySystem::getInstance()->isEnabled()) {
                    throw new ilPermissionException($this->lng->txt('msg_no_perm_read'));
                }

                $this->getStandardTemplates();
                $this->setTabs();
                $this->tpl->setTitle($this->lng->txt('mail_addressbook'));

                $this->ctrl->forwardCommand(new ilContactGUI());
                break;
            case strtolower(ilPersonalWorkspaceGUI::class):
                $wsgui = new ilPersonalWorkspaceGUI();
                $this->ctrl->forwardCommand($wsgui);
                $this->tpl->printToStdout();
                break;
            case strtolower(ilPortfolioRepositoryGUI::class):
                $this->getStandardTemplates();
                $this->setTabs();
                $pfgui = new ilPortfolioRepositoryGUI();
                $this->ctrl->forwardCommand($pfgui);
                $this->tpl->printToStdout();
                break;
            case strtolower(ilAchievementsGUI::class):
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
            case strtolower(ilGroupUserActionsGUI::class):
                $this->getStandardTemplates();
                $this->setTabs();
                $ggui = new ilGroupUserActionsGUI();
                $this->ctrl->forwardCommand($ggui);
                $this->tpl->printToStdout();
                break;
            case strtolower(ilDashboardRecommendedContentGUI::class):
                $this->ctrl->forwardCommand(new ilDashboardRecommendedContentGUI());
                break;
            case strtolower(ilStudyProgrammeDashboardViewGUI::class):
                $this->ctrl->forwardCommand($this->getStudyProgrammeDashboardView());
                break;
            default:
                $this->tool_context->current()->addAdditionalData(self::DISENGAGE_MAINBAR, true);
                $this->getStandardTemplates();
                $this->setTabs();
                $cmd = $this->ctrl->getCmd('show');
                $this->$cmd();
                break;
        }
    }

    protected function getStudyProgrammeDashboardView(): ilStudyProgrammeDashboardViewGUI
    {
        return ilStudyProgrammeDIC::dic()[ilStudyProgrammeDashboardViewGUI::class];
    }

    public function getStandardTemplates(): void
    {
        $this->tpl->loadStandardTemplate();
    }

    public function show(): void
    {
        ilBlockSetting::preloadPDBlockSettings();

        $this->tpl->setTitle($this->lng->txt('dash_dashboard'));
        $this->tpl->setTitleIcon(ilUtil::getImagePath('icon_dshs.svg'), $this->lng->txt('dash_dashboard'));
        $this->tpl->setVariable('IMG_SPACE', ilUtil::getImagePath('spacer.png'));

        $this->tpl->setContent($this->getCenterColumnHTML());
        $this->tpl->setRightContent($this->getRightColumnHTML());

        if ($this->action_menu->getItems() !== []) {
            $this->action_menu->setAsynch(false);
            $this->action_menu->setAsynchUrl('');
            $this->action_menu->setListTitle($this->lng->txt('actions'));
            $this->action_menu->setId('act_pd');
            $this->action_menu->setSelectionHeaderClass('small');
            $this->action_menu->setItemLinkClass('xsmall');
            $this->action_menu->setLinksMode('il_ContainerItemCommand2');
            $this->action_menu->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
            $this->action_menu->setUseImages(false);

            $htpl = new ilTemplate('tpl.header_action.html', true, true, 'Services/Repository');
            $htpl->setVariable('ACTION_DROP_DOWN', $this->action_menu->getHTML());

            $this->tpl->setHeaderActionMenu($htpl->get());
        }

        $this->tpl->printToStdout();
    }

    public function getCenterColumnHTML(): string
    {
        $html = '';
        $column_gui = new ilColumnGUI('pd', IL_COL_CENTER);
        $this->initColumn($column_gui);

        if ($column_gui::getCmdSide() === IL_COL_CENTER &&
            $this->ctrl->getNextClass() === strtolower(ilColumnGUI::class)) {
            $html = $this->ctrl->forwardCommand($column_gui);
        } elseif (!$this->ctrl->isAsynch()) {
            if ($column_gui::getScreenMode() !== IL_SCREEN_SIDE) {
                if ($column_gui::getCmdSide() === IL_COL_RIGHT) {
                    $column_gui = new ilColumnGUI('pd', IL_COL_RIGHT);
                    $this->initColumn($column_gui);
                    $html = $this->ctrl->forwardCommand($column_gui);
                }
                if ($column_gui::getCmdSide() === IL_COL_LEFT) {
                    $column_gui = new ilColumnGUI('pd', IL_COL_LEFT);
                    $this->initColumn($column_gui);
                    $html = $this->ctrl->forwardCommand($column_gui);
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
        return $html;
    }

    public function getRightColumnHTML(): string
    {
        $html = '';

        $column_gui = new ilColumnGUI('pd', IL_COL_RIGHT);
        $this->initColumn($column_gui);

        if ($column_gui::getScreenMode() === IL_SCREEN_FULL) {
            return '';
        }

        if ($column_gui::getCmdSide() === IL_COL_RIGHT &&
            $column_gui::getScreenMode() === IL_SCREEN_SIDE &&
            $this->ctrl->getNextClass() === strtolower(ilColumnGUI::class)
        ) {
            $html = $this->ctrl->forwardCommand($column_gui);
        } elseif (!$this->ctrl->isAsynch()) {
            $html = '';

            $uip = new ilUIHookProcessor(
                'Services/Dashboard',
                'right_column',
                ['personal_desktop_gui' => $this]
            );
            if (!$uip->replaced()) {
                $html = $this->ctrl->getHTML($column_gui);
            }
            $html = $uip->getHTML($html);
        }

        return $html;
    }

    public function prepareContentView(): void
    {
        $this->tpl->loadStandardTemplate();
        $this->tpl->setTitleIcon(ilUtil::getImagePath('icon_pd.svg'));
        $this->tpl->setTitle($this->lng->txt('personal_desktop'));
        $this->tpl->setVariable('IMG_SPACE', ilUtil::getImagePath('spacer.png'));
    }

    public function setTabs(): void
    {
        $this->help->setScreenIdComponent('pd');
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
        $this->ctrl->redirectByClass(ilPersonalProfileGUI::class);
    }

    public function jumpToPortfolio(): void
    {
        $cmd = '';
        if ($this->requested_dsh !== '') {
            $this->ctrl->setParameterByClass(ilPortfolioRepositoryGUI::class, 'shr_id', $this->requested_dsh);
            $cmd = 'showOther';
        }

        if ($this->requested_prt_id > 0) {
            $this->ctrl->setParameterByClass(ilObjPortfolioGUI::class, 'prt_id', $this->requested_prt_id);
            $this->ctrl->setParameterByClass(ilObjPortfolioGUI::class, 'gtp', $this->requested_gtp);
            $this->ctrl->redirectByClass([ilPortfolioRepositoryGUI::class, ilObjPortfolioGUI::class], 'preview');
        } else {
            $this->ctrl->redirectByClass(ilPortfolioRepositoryGUI::class, $cmd);
        }
    }

    public function jumpToSettings(): void
    {
        $this->ctrl->redirectByClass(ilPersonalSettingsGUI::class);
    }

    public function jumpToNews(): void
    {
        $this->ctrl->redirectByClass(ilPDNewsGUI::class);
    }

    public function jumpToCalendar(): void
    {
        global $DIC;
        $query_params = $DIC->http()->request()->getQueryParams();

        if (array_key_exists('cal_view', $query_params) && $query_params['cal_view']) {
            $cal_view = $query_params['cal_view'];
            $this->ctrl->setParameter($this, 'cal_view', $cal_view);
        }

        if (!empty($query_params['cal_agenda_per'])) {
            $cal_period = $query_params['cal_agenda_per'];
            $this->ctrl->setParameter($this, 'cal_agenda_per', $cal_period);
        }

        $this->ctrl->redirectByClass(ilCalendarPresentationGUI::class);
    }

    public function jumpToWorkspace(): void
    {
        $cmd = '';
        if ($this->requested_dsh !== '') {
            $this->ctrl->setParameterByClass(ilPersonalWorkspaceGUI::class, 'shr_id', $this->requested_dsh);
            $cmd = 'share';
        }

        if ($this->requested_wsp_id > 0) {
            $this->ctrl->setParameterByClass(ilPersonalWorkspaceGUI::class, 'wsp_id', $this->requested_wsp_id);
        }

        if ($this->requested_gtp) {
            $this->ctrl->setParameterByClass(ilPersonalWorkspaceGUI::class, 'gtp', $this->requested_gtp);
        }

        $this->ctrl->redirectByClass(ilPersonalWorkspaceGUI::class, $cmd);
    }

    protected function jumpToMyStaff(): void
    {
        $this->ctrl->redirectByClass(ilMyStaffGUI::class);
    }

    public function jumpToBadges(): void
    {
        $this->ctrl->redirectByClass([ilAchievementsGUI::class, ilBadgeProfileGUI::class]);
    }

    public function jumpToSkills(): void
    {
        $this->ctrl->redirectByClass(ilPersonalSkillsGUI::class);
    }

    public function initColumn(ilColumnGUI $column_gui): void
    {
        $column_gui->setActionMenu($this->action_menu);
    }

    public function displayHeader(): void
    {
        $this->tpl->setTitle($this->lng->txt('dash_dashboard'));
    }

    protected function toggleHelp(): void
    {
        if (ilSession::get('show_help_tool') === '1') {
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

        if ($settings->enabledSelectedItems()) {
            $html = $this->renderFavourites();
        }
        $html .= $this->renderRecommendedContent();
        $html .= $this->renderStudyProgrammes();
        $html .= $this->renderLearningSequences();
        if ($settings->enabledMemberships()) {
            $html .= $this->renderMemberships();
        }

        $tpl->setVariable('CONTENT', $html);

        return $tpl->get();
    }

    protected function renderFavourites(): string
    {
        return (new ilPDSelectedItemsBlockGUI())->getHTML();
    }

    protected function renderRecommendedContent(): string
    {
        return (new ilDashboardRecommendedContentGUI())->render();
    }

    protected function renderStudyProgrammes(): string
    {
        return ilStudyProgrammeDIC::dic()[ilStudyProgrammeDashboardViewGUI::class]->getHTML();
    }

    protected function renderMemberships(): string
    {
        return (new ilPDMembershipBlockGUI())->getHTML();
    }

    protected function renderLearningSequences(): string
    {
        return (new ilDashboardLearningSequenceGUI())->getHTML();
    }
}
