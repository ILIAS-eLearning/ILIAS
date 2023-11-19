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

use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestTabsManager
{
    /**
     * (Sub-)Tab ID constants
     */

    public const TAB_ID_QUESTIONS = 'assQuestions';
    public const SUBTAB_ID_QST_LIST_VIEW = 'qst_list_view';
    public const SUBTAB_ID_QST_PAGE_VIEW = 'qst_page_view';

    public const TAB_ID_TEST = 'test';
    public const TAB_ID_INFOSCREEN = 'info_short';
    public const TAB_ID_SETTINGS = 'settings';
    public const SUBTAB_ID_GENERAL_SETTINGS = 'general';
    public const TAB_ID_LEARNING_PROGRESS = 'learning_progress';
    public const TAB_ID_MANUAL_SCORING = 'manscoring';
    public const TAB_ID_CORRECTION = 'scoringadjust';
    public const TAB_ID_STATISTICS = 'statistics';
    public const TAB_ID_HISTORY = 'history';
    public const TAB_ID_META_DATA = 'meta_data';
    public const TAB_ID_EXPORT = 'export';
    public const TAB_ID_PERMISSIONS = 'perm_settings';

    public const TAB_ID_EXAM_DASHBOARD = 'dashboard_tab';
    public const SUBTAB_ID_FIXED_PARTICIPANTS = 'fixedparticipants';
    public const SUBTAB_ID_TIME_EXTENSION = 'timeextension';

    public const TAB_ID_RESULTS = 'results';
    public const SUBTAB_ID_PARTICIPANTS_RESULTS = 'participantsresults';
    public const SUBTAB_ID_MY_RESULTS = 'myresults';
    public const SUBTAB_ID_LO_RESULTS = 'loresults';
    public const SUBTAB_ID_HIGHSCORE = 'highscore';
    public const SUBTAB_ID_SKILL_RESULTS = 'skillresults';
    public const SUBTAB_ID_MY_SOLUTIONS = 'mysolutions';

    private const SETTINGS_SUBTAB_ID_GENERAL = 'general';
    private const SETTINGS_SUBTAB_ID_MARK_SCHEMA = 'mark_schema';
    private const SETTINGS_SUBTAB_ID_SCORING = 'scoring';
    public const SETTINGS_SUBTAB_ID_EDIT_INTRODUCTION_PAGE = 'edit_introduction';
    public const SETTINGS_SUBTAB_ID_EDIT_CONCLUSION_PAGE = 'edit_concluding_remarks';
    private const SETTINGS_SUBTAB_ID_CERTIFICATE = 'certificate';
    private const SETTINGS_SUBTAB_ID_PERSONAL_DEFAULT_SETTINGS = 'tst_default_settings';

    /**
     * @var ilObjTest
     */
    protected $test_object;

    /**
     * @var ilTestSession
     */
    protected $test_session;

    /**
     * @var ilTestQuestionSetConfig
     */
    protected $test_question_set_config;

    /**
     * @var string|null
     */
    protected $parent_back_href;

    /**
     * @var string|null
     */
    protected $parent_back_label;

    public function __construct(
        private ilTabsGUI $tabs,
        private ilLanguage $lng,
        private ilCtrl $ctrl,
        private RequestWrapper $wrapper,
        private Refinery $refinery,
        private ilAccess $access,
        private ilTestAccess $test_access,
        private ilTestObjectiveOrientedContainer $objective_parent
    ) {
    }

    /**
     * @param string $tabId
     */
    public function activateTab($tabId)
    {
        switch ($tabId) {
            case self::TAB_ID_EXAM_DASHBOARD:
            case self::TAB_ID_RESULTS:
            case self::TAB_ID_SETTINGS:

                $this->tabs->activateTab($tabId);
        }
    }

    /**
     * @param string $subTabId
     */
    public function activateSubTab($subTabId)
    {
        switch ($subTabId) {
            case self::SUBTAB_ID_FIXED_PARTICIPANTS:
            case self::SUBTAB_ID_TIME_EXTENSION:

            case self::SUBTAB_ID_PARTICIPANTS_RESULTS:
            case self::SUBTAB_ID_MY_RESULTS:
            case self::SUBTAB_ID_LO_RESULTS:
            case self::SUBTAB_ID_HIGHSCORE:
            case self::SUBTAB_ID_SKILL_RESULTS:
            case self::SUBTAB_ID_MY_SOLUTIONS:

            case self::SUBTAB_ID_QST_LIST_VIEW:
            case self::SUBTAB_ID_QST_PAGE_VIEW:

            case self::SETTINGS_SUBTAB_ID_GENERAL:
            case self::SETTINGS_SUBTAB_ID_MARK_SCHEMA:
            case self::SETTINGS_SUBTAB_ID_SCORING:
            case self::SETTINGS_SUBTAB_ID_EDIT_INTRODUCTION_PAGE:
            case self::SETTINGS_SUBTAB_ID_EDIT_CONCLUSION_PAGE:
            case self::SETTINGS_SUBTAB_ID_CERTIFICATE:
            case self::SETTINGS_SUBTAB_ID_PERSONAL_DEFAULT_SETTINGS:

                $this->tabs->activateSubTab($subTabId);
        }
    }

    /**
     * @return ilObjTest
     */
    public function getTestOBJ(): ilObjTest
    {
        return $this->test_object;
    }

    /**
     * @param ilObjTest $test_object
     */
    public function setTestOBJ(ilObjTest $test_object)
    {
        $this->test_object = $test_object;
    }

    /**
     * @return ilTestSession
     */
    public function getTestSession(): ilTestSession
    {
        return $this->test_session;
    }

    /**
     * @param ilTestSession $test_session
     */
    public function setTestSession($test_session)
    {
        $this->test_session = $test_session;
    }

    /**
     * @return ilTestQuestionSetConfig
     */
    public function getTestQuestionSetConfig(): ilTestQuestionSetConfig
    {
        return $this->test_question_set_config;
    }

    /**
     * @param ilTestQuestionSetConfig $test_question_set_config
     */
    public function setTestQuestionSetConfig(ilTestQuestionSetConfig $test_question_set_config)
    {
        $this->test_question_set_config = $test_question_set_config;
    }

    /**
     * @return null|string
     */
    public function getParentBackLabel(): ?string
    {
        return $this->parent_back_label;
    }

    /**
     * @param null|string $parent_back_label
     */
    public function setParentBackLabel($parent_back_label)
    {
        $this->parent_back_label = $parent_back_label;
    }

    /**
     * @return null|string
     */
    public function getParentBackHref(): ?string
    {
        return $this->parent_back_href;
    }

    /**
     * @param null|string $parent_back_href
     */
    public function setParentBackHref($parent_back_href)
    {
        $this->parent_back_href = $parent_back_href;
    }

    /**
     * @return null|string
     */
    public function hasParentBackLink()
    {
        if (!is_string($this->getParentBackHref()) || !strlen($this->getParentBackHref())) {
            return false;
        }

        if (!is_string($this->getParentBackLabel()) || !strlen($this->getParentBackLabel())) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function isReadAccessGranted(): bool
    {
        return $this->access->checkAccess('read', '', $this->getTestOBJ()->getRefId());
    }

    /**
     * @return bool
     */
    protected function isWriteAccessGranted(): bool
    {
        return $this->access->checkAccess('write', '', $this->getTestOBJ()->getRefId());
    }

    /**
     * @return bool
     */
    protected function isStatisticsAccessGranted(): bool
    {
        return $this->access->checkAccess('tst_statistics', '', $this->getTestOBJ()->getRefId());
    }

    /**
     * @return bool
     */
    protected function isPermissionsAccessGranted(): bool
    {
        return $this->access->checkAccess('edit_permission', '', $this->getTestOBJ()->getRefId());
    }

    /**
     * @return bool
     */
    protected function isLpAccessGranted(): bool
    {
        return ilLearningProgressAccess::checkAccess($this->getTestOBJ()->getRefId());
    }

    /**
     * @return bool
     */
    protected function checkDashboardTabAccess(): bool
    {
        if ($this->test_access->checkManageParticipantsAccess()) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function checkScoreParticipantsTabAccess(): bool
    {
        return $this->test_access->checkScoreParticipantsAccess();
    }

    /**
     * @return bool
     */
    protected function checkStatisticsTabAccess(): bool
    {
        return $this->test_access->checkStatisticsAccess();
    }

    /**
     */
    public function perform()
    {
        if ($this->isTabsConfigSetupRequired()) {
            $this->setupTabsGuiConfig();
        }
    }

    protected function isTabsConfigSetupRequired(): bool
    {
        if (preg_match('/^ass(.*?)gui$/i', $this->ctrl->getNextClass($this))) {
            return false;
        }

        if ($this->ctrl->getNextClass($this) == 'ilassquestionpagegui') {
            return false;
        }

        if ($this->ctrl->getCmdClass() == 'iltestoutputgui') {
            return false;
        }

        if ($this->ctrl->getCmdClass() == 'iltestevaluationgui') {
            return in_array($this->ctrl->getCmd(), [
                '', 'outUserResultsPassOverview', 'outUserListOfAnswerPasses', 'outEvaluation', 'eval_a', 'singleResults', 'detailedEvaluation'
            ]);
        }

        return true;
    }

    protected function setupTabsGuiConfig()
    {
        if ($this->hasParentBackLink()) {
            $this->tabs->setBack2Target($this->getParentBackLabel(), $this->getParentBackHref());
        }

        switch (strtolower($this->ctrl->getCmdClass())) {
            case 'ilmarkschemagui':
            case 'ilobjtestsettingsmaingui':
            case 'ilobjtestsettingsscoringresultsgui':

                if ($this->isWriteAccessGranted()) {
                    $this->getSettingsSubTabs();
                }

                break;
        }

        switch ($this->ctrl->getCmd()) {
            case 'resume':
            case 'previous':
            case 'next':
            case 'summary':
            case 'directfeedback':
            case 'finishTest':
            case 'outCorrectSolution':
            case 'passDetails':
            case 'showAnswersOfUser':
            case 'backFromSummary':
            case 'show_answers':
            case 'setsolved':
            case 'resetsolved':
            case 'confirmFinish':
            case 'outTestSummary':
            case 'outQuestionSummary':
            case 'gotoQuestion':
            case 'selectImagemapRegion':
            case 'confirmSubmitAnswers':
            case 'finalSubmission':
            case 'postpone':
            case 'outUserPassDetails':
            case 'checkPassword':
            case 'exportCertificate':
            case 'finishListOfAnswers':
            case 'backConfirmFinish':
            case 'showFinalStatement':
                return;
            case 'browseForQuestions':
            case 'filter':
            case 'resetFilter':
            case 'resetTextFilter':
            case 'insertQuestions':
                $classes = [
                    'iltestdashboardgui',
                    'iltestresultsgui',
                    'illearningprogressgui' // #8497: resetfilter is also used in lp
                ];
                if (!in_array($this->ctrl->getNextClass($this), $classes)) {
                    $this->getBrowseForQuestionsTab();
                }
                break;
            case 'scoring':
            case 'certificate':
            case 'certificateservice':
            case 'certificateImport':
            case 'certificateUpload':
            case 'certificateEditor':
            case 'certificateDelete':
            case 'certificateSave':
            case 'defaults':
            case 'deleteDefaults':
            case 'addDefaults':
            case 'applyDefaults':
            case 'inviteParticipants':
            case 'searchParticipants':
                if ($this->isWriteAccessGranted() && in_array(strtolower($this->ctrl->getCmdClass()), ['ilobjtestgui', 'ilcertificategui'])) {
                    $this->getSettingsSubTabs();
                }
                break;
            case 'export':
            case 'print':
                break;
            case 'statistics':
            case 'eval_a':
            case 'detailedEvaluation':
            case 'outEvaluation':
            case 'singleResults':
            case 'exportEvaluation':
            case 'evalUserDetail':
            case 'outStatisticsResultsOverview':
            case 'statisticsPassDetails':
                $this->getStatisticsSubTabs();
                break;
        }

        // test tab
        if ($this->isReadAccessGranted()) {
            $this->tabs->addTab(
                self::TAB_ID_TEST,
                $this->lng->txt('test'),
                $this->ctrl->getLinkTargetByClass(ilTestScreenGUI::class, 'testScreen')
            );
        }

        // questions tab
        if ($this->isWriteAccessGranted()) {
            $up = $this->wrapper->has('up')
                && $this->wrapper->retrieve('up', $this->refinery->string()) !== '';
            $down = $this->wrapper->has('down')
                && $this->wrapper->retrieve('down', $this->refinery->string()) !== '';
            $browse = $this->wrapper->has('browse')
                && $this->wrapper->retrieve('browse', $this->refinery->int()) === 1;

            $force_active = ($up || $down || $browse) ? true : false;

            if ($this->getTestOBJ()->isFixedTest()) {
                $target = $this->ctrl->getLinkTargetByClass(
                    'ilObjTestGUI',
                    'questions'
                );
            }

            if ($this->getTestOBJ()->isRandomTest()) {
                $target = $this->ctrl->getLinkTargetByClass('ilTestRandomQuestionSetConfigGUI');
            }

            $this->tabs->addTarget(
                'assQuestions',
                $target,
                [
                    'questions', 'browseForQuestions', 'questionBrowser', 'createQuestion',
                    'filter', 'resetFilter', 'insertQuestions',
                    'back', 'removeQuestions', 'moveQuestions',
                    'insertQuestionsBefore', 'insertQuestionsAfter', 'confirmRemoveQuestions',
                    'cancelRemoveQuestions', 'executeCreateQuestion', 'cancelCreateQuestion',
                    'addQuestionpool', 'saveRandomQuestions', 'saveQuestionSelectionMode', 'print',
                    'addsource', 'removesource', 'randomQuestions'
                ],
                '',
                '',
                $force_active
            );
        }

        // info tab
        if ($this->isReadAccessGranted() && !$this->getTestOBJ()->getMainSettings()->getAdditionalSettings()->getHideInfoTab()) {
            $this->tabs->addTarget(
                'info_short',
                $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'infoScreen'),
                ['infoScreen', 'outIntroductionPage', 'showSummary',
                    'setAnonymousId', 'redirectToInfoScreen']
            );
        }

        // settings tab
        if ($this->isWriteAccessGranted()) {
            $settingsCommands = [
                'marks', 'showMarkSchema','addMarkStep', 'deleteMarkSteps',
                'resetToSimpleMarkSchema', 'saveMarks', 'certificate',
                'certificateEditor', 'certificateRemoveBackground', 'certificateSave',
                'certificatePreview', 'certificateDelete', 'certificateUpload', 'certificateImport',
                'scoring', 'defaults', 'addDefaults', 'deleteDefaults', 'applyDefaults',
                'inviteParticipants', 'saveFixedParticipantsStatus', 'searchParticipants', 'addParticipants' // ARE THEY RIGHT HERE
            ];

            $reflection = new ReflectionClass('ilObjTestSettingsMainGUI');
            foreach ($reflection->getConstants() as $name => $value) {
                if (substr($name, 0, 4) === 'CMD_') {
                    $settingsCommands[] = $value;
                }
            }

            $reflection = new ReflectionClass('ilObjTestSettingsScoringResultsGUI');
            foreach ($reflection->getConstants() as $name => $value) {
                if (substr($name, 0, 4) === 'CMD_') {
                    $settingsCommands[] = $value;
                }
            }

            $settingsCommands[] = ''; // DO NOT KNOW WHAT THIS IS DOING, BUT IT'S REQUIRED

            $this->tabs->addTarget(
                'settings',
                $this->ctrl->getLinkTargetByClass('ilObjTestSettingsMainGUI'),
                $settingsCommands,
                [
                    'ilmarkschemagui',
                    'ilobjtestsettingsmaingui',
                    'ilobjtestsettingsscoringresultsgui',
                    'ilobjtestgui',
                    'ilcertificategui'
                ]
            );

            // skill service
            if ($this->getTestOBJ()->isSkillServiceToBeConsidered()) {
                $link = $this->ctrl->getLinkTargetByClass(
                    ['ilTestSkillAdministrationGUI', 'ilAssQuestionSkillAssignmentsGUI'],
                    ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS
                );

                $this->tabs->addTarget('tst_tab_competences', $link, [], []);
            }
        }

        if ($this->needsDashboardTab()) {
            $this->tabs->addTab(
                self::TAB_ID_EXAM_DASHBOARD,
                $this->lng->txt('dashboard_tab'),
                $this->getDashboardTabTarget()
            );
        }

        if ($this->needsResultsTab()) {
            $this->tabs->addTab(
                self::TAB_ID_RESULTS,
                $this->lng->txt('results_tab'),
                $this->getResultsTabTarget()
            );
        }

        if ($this->isLpAccessGranted()) {
            $this->tabs->addTarget(
                self::TAB_ID_LEARNING_PROGRESS,
                $this->ctrl->getLinkTargetByClass(['illearningprogressgui'], ''),
                '',
                [
                    'illplistofobjectsgui',
                    'illplistofsettingsgui',
                    'illearningprogressgui',
                    'illplistofprogressgui'
                ]
            );
        }

        if ($this->checkScoreParticipantsTabAccess()) {
            $scoring = ilObjAssessmentFolder::_getManualScoring();
            if (count($scoring)) {
                // scoring tab
                $this->tabs->addTarget(
                    self::TAB_ID_MANUAL_SCORING,
                    $this->ctrl->getLinkTargetByClass(
                        'ilTestScoringByQuestionsGUI',
                        'showManScoringByQuestionParticipantsTable'
                    ),
                    [
                        'showManScoringParticipantsTable',
                        'applyManScoringParticipantsFilter',
                        'resetManScoringParticipantsFilter',
                        'showManScoringParticipantScreen',
                        'showManScoringByQuestionParticipantsTable',
                        'applyManScoringByQuestionFilter',
                        'resetManScoringByQuestionFilter',
                        'saveManScoringByQuestion'
                    ],
                    ''
                );
            }
        }

        // NEW CORRECTIONS TAB
        $setting = new ilSetting('assessment');
        $scoring_adjust_active = (bool) $setting->get('assessment_adjustments_enabled', '0');
        if ($this->isWriteAccessGranted() && $scoring_adjust_active) {
            $this->tabs->addTab(
                self::TAB_ID_CORRECTION,
                $this->lng->txt(self::TAB_ID_CORRECTION),
                $this->ctrl->getLinkTargetByClass('ilTestCorrectionsGUI')
            );
        }

        if ($this->checkStatisticsTabAccess()) {
            // statistics tab
            $this->tabs->addTarget(
                self::TAB_ID_STATISTICS,
                $this->ctrl->getLinkTargetByClass(
                    [ilRepositoryGUI::class, ilObjTestGUI::class, ilTestEvaluationGUI::class],
                    'outEvaluation'
                ),
                [
                    'statistics',
                    'outEvaluation',
                    'exportEvaluation',
                    'detailedEvaluation',
                    'eval_a',
                    'evalUserDetail',
                    'passDetails',
                    'outStatisticsResultsOverview',
                    'statisticsPassDetails',
                    'singleResults'
                ],
                ''
            );
        }

        if ($this->isWriteAccessGranted()) {
            $this->tabs->addTarget(
                self::TAB_ID_HISTORY,
                $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'history'),
                'history',
                ''
            );

            $mdgui = new ilObjectMetaDataGUI($this->getTestOBJ());
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs->addTarget(
                    self::TAB_ID_META_DATA,
                    $mdtab,
                    '',
                    'ilmdeditorgui'
                );
            }

            $this->tabs->addTarget(
                self::TAB_ID_EXPORT,
                $this->ctrl->getLinkTargetByClass('iltestexportgui', ''),
                '',
                ['iltestexportgui']
            );
        }

        if ($this->isPermissionsAccessGranted()) {
            $this->tabs->addTarget(
                self::TAB_ID_PERMISSIONS,
                $this->ctrl->getLinkTargetByClass(['ilObjTestGUI','ilpermissiongui'], 'perm'),
                ['perm','info','owner'],
                'ilpermissiongui'
            );
        }

        if ($this->getTestQuestionSetConfig()->areDepenciesBroken()) {
            $hideTabs = $this->getTestQuestionSetConfig()->getHiddenTabsOnBrokenDepencies();

            foreach ($hideTabs as $tabId) {
                $this->tabs->removeTab($tabId);
            }
        }
    }

    protected function getBrowseForQuestionsTab()
    {
        if ($this->isWriteAccessGranted()) {
            $this->ctrl->saveParameterByClass($this->ctrl->getCmdClass(), 'q_id');
            // edit page
            $this->tabs->setBackTarget(
                $this->lng->txt('backtocallingtest'),
                $this->ctrl->getLinkTargetByClass($this->ctrl->getCmdClass(), 'questions')
            );
            $this->tabs->addTarget(
                'tst_browse_for_questions',
                $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'browseForQuestions'),
                ['browseForQuestions', 'filter', 'resetFilter', 'resetTextFilter', 'insertQuestions'],
                '',
                '',
                true
            );
        }
    }

    protected function getRandomQuestionsTab()
    {
        if ($this->isWriteAccessGranted()) {
            // edit page
            $this->tabs->setBackTarget($this->lng->txt('backtocallingtest'), $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'questions'));
            $this->tabs->addTarget(
                'random_selection',
                $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'randomQuestions'),
                ['randomQuestions'],
                '',
                ''
            );
        }
    }

    public function getQuestionsSubTabs()
    {
        $this->tabs->activateTab(self::TAB_ID_QUESTIONS);

        $this->tabs->addSubTab(
            self::SUBTAB_ID_QST_LIST_VIEW,
            $this->lng->txt('edit_test_questions'),
            $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'questions')
        );

        // print view subtab
        if (!$this->getTestOBJ()->isRandomTest()) {
            $this->tabs->addSubTabTarget(
                'print_view',
                $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'print'),
                'print',
                '',
                '',
                $this->ctrl->getCmd() == 'print'
            );
            $this->tabs->addSubTabTarget(
                'review_view',
                $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'review'),
                'review',
                '',
                '',
                $this->ctrl->getCmd() == 'review'
            );
        }
    }

    protected function getStatisticsSubTabs()
    {
        $this->tabs->addSubTabTarget(
            'eval_all_users',
            $this->ctrl->getLinkTargetByClass('iltestevaluationgui', 'outEvaluation'),
            ['outEvaluation', 'detailedEvaluation', 'exportEvaluation', 'evalUserDetail', 'passDetails',
                'outStatisticsResultsOverview', 'statisticsPassDetails'],
            ''
        );

        // aggregated results subtab
        $this->tabs->addSubTabTarget(
            'tst_results_aggregated',
            $this->ctrl->getLinkTargetByClass('iltestevaluationgui', 'eval_a'),
            ['eval_a'],
            '',
            ''
        );

        // question export
        $this->tabs->addSubTabTarget(
            'tst_single_results',
            $this->ctrl->getLinkTargetByClass('iltestevaluationgui', 'singleResults'),
            ['singleResults'],
            '',
            ''
        );
    }

    public function getSettingsSubTabs()
    {
        $this->tabs->addSubTabTarget(
            self::SETTINGS_SUBTAB_ID_GENERAL,
            $this->ctrl->getLinkTargetByClass('ilObjTestSettingsMainGUI'),
            '',											// auto activation regardless from cmd
            ['ilobjtestsettingsmaingui']			// auto activation for ilObjTestSettingsGeneralGUI
        );

        $this->tabs->addSubTabTarget(
            self::SETTINGS_SUBTAB_ID_MARK_SCHEMA,
            $this->ctrl->getLinkTargetByClass('ilmarkschemagui', 'showMarkSchema'),
            '',
            ['ilmarkschemagui']
        );

        $this->tabs->addSubTabTarget(
            self::SETTINGS_SUBTAB_ID_SCORING,
            $this->ctrl->getLinkTargetByClass('ilObjTestSettingsScoringResultsGUI'),
            '',                                             // auto activation regardless from cmd
            ['ilobjtestsettingsscoringresultsgui']     // auto activation for ilObjTestSettingsScoringResultsGUI
        );

        $this->ctrl->setParameterByClass(ilTestPageGUI::class, 'page_type', 'introductionpage');
        $this->tabs->addSubTabTarget(
            self::SETTINGS_SUBTAB_ID_EDIT_INTRODUCTION_PAGE,
            $this->ctrl->getLinkTargetByClass(ilTestPageGUI::class, 'preview'),
            ['iltestpagegui']
        );

        $this->ctrl->setParameterByClass(ilTestPageGUI::class, 'page_type', 'concludingremarkspage');
        $this->tabs->addSubTabTarget(
            self::SETTINGS_SUBTAB_ID_EDIT_CONCLUSION_PAGE,
            $this->ctrl->getLinkTargetByClass(ilTestPageGUI::class, 'preview'),
            ['iltestpagegui']
        );
        $this->ctrl->clearParameterByClass(ilTestPageGUI::class, 'page_type');

        $validator = new ilCertificateActiveValidator();
        if ($validator->validate() === true) {
            $this->tabs->addSubTabTarget(
                self::SETTINGS_SUBTAB_ID_CERTIFICATE,
                $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'certificate'),
                ['certificate', 'certificateEditor', 'certificateRemoveBackground', 'ceateSave',
                    'certificatePreview', 'certificateDelete', 'certificateUpload', 'certificateImport'],
                ['', 'ilobjtestgui', 'ilcertificategui']
            );
        }

        $this->tabs->addSubTabTarget(
            self::SETTINGS_SUBTAB_ID_PERSONAL_DEFAULT_SETTINGS,
            $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'defaults'),
            ['defaults', 'deleteDefaults', 'addDefaults', 'applyDefaults'],
            ['', 'ilobjtestgui', 'ilcertificategui']
        );

        $lti_settings = new ilLTIProviderObjectSettingGUI($this->test_object->getRefId());
        if ($lti_settings->hasSettingsAccess()) {
            $this->tabs->addSubTabTarget(
                'lti_provider',
                $this->ctrl->getLinkTargetByClass(ilLTIProviderObjectSettingGUI::class),
                '',
                [ilLTIProviderObjectSettingGUI::class]
            );
        }
    }

    /**
     * @return bool
     */
    protected function needsDashboardTab(): bool
    {
        if (!$this->checkDashboardTabAccess()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function needsTimeExtensionSubTab(): bool
    {
        if (!($this->getTestOBJ()->getProcessingTimeInSeconds() > 0)) {
            return false;
        }

        if ($this->getTestOBJ()->getNrOfTries() != 1) {
            return false;
        }

        if ($this->getTestQuestionSetConfig()->areDepenciesBroken()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    protected function getDashboardTabTarget(): string
    {
        return $this->ctrl->getLinkTargetByClass(['ilTestDashboardGUI', 'ilTestParticipantsGUI']);
    }

    public function getDashboardSubTabs()
    {
        if (!$this->test_access->checkManageParticipantsAccess()) {
            return;
        }

        $this->tabs->addSubTab(
            self::SUBTAB_ID_FIXED_PARTICIPANTS,
            $this->getDashbardParticipantsSubTabLabel(),
            $this->ctrl->getLinkTargetByClass('ilTestParticipantsGUI')
        );

        if ($this->needsTimeExtensionSubTab()) {
            $this->tabs->addSubTab(
                self::SUBTAB_ID_TIME_EXTENSION,
                $this->lng->txt('timing'),
                $this->ctrl->getLinkTargetByClass('ilTestParticipantsTimeExtensionGUI')
            );
        }
    }

    protected function getDashbardParticipantsSubTabLabel(): string
    {
        if ($this->getTestOBJ()->getFixedParticipants()) {
            return $this->lng->txt('fixedparticipants_subtab');
        }

        return $this->lng->txt('autoparticipants_subtab');
    }

    /**
     * @return bool
     */
    protected function needsResultsTab(): bool
    {
        return $this->needsParticipantsResultsSubTab() || $this->test_object->isScoreReportingEnabled() || $this->needsMySolutionsSubTab();
    }

    /**
     * @return string
     */
    protected function getResultsTabTarget(): string
    {
        if ($this->needsParticipantsResultsSubTab()) {
            return $this->ctrl->getLinkTargetByClass(['ilTestResultsGUI', 'ilParticipantsTestResultsGUI']);
        }

        if ($this->needsLoResultsSubTab()) {
            return $this->ctrl->getLinkTargetByClass(['ilTestResultsGUI', 'ilTestEvalObjectiveOrientedGUI']);
        }

        if ($this->needsMyResultsSubTab()) {
            return $this->ctrl->getLinkTargetByClass(['ilTestResultsGUI', 'ilMyTestResultsGUI', 'ilTestEvaluationGUI']);
        }

        if ($this->needsMySolutionsSubTab()) {
            return $this->ctrl->getLinkTargetByClass(['ilTestResultsGUI', 'ilMyTestSolutionsGUI', 'ilTestEvaluationGUI']);
        }

        return $this->ctrl->getLinkTargetByClass('ilTestResultsGUI');
    }

    /**
     * @return bool
     */
    public function needsMyResultsSubTab(): bool
    {
        return $this->getTestSession()->reportableResultsAvailable($this->getTestOBJ());
    }

    /**
     * @return bool
     */
    public function needsLoResultsSubTab(): bool
    {
        if (!$this->needsMyResultsSubTab()) {
            return false;
        }

        return $this->objective_parent->isObjectiveOrientedPresentationRequired();
    }

    /**
     * @return bool
     */
    public function needsParticipantsResultsSubTab(): bool
    {
        if ($this->test_access->checkParticipantsResultsAccess()) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function needsHighSoreSubTab(): bool
    {
        if (!$this->needsMyResultsSubTab()) {
            return false;
        }

        return $this->getTestOBJ()->getHighscoreEnabled();
    }

    /**
     * @return bool
     */
    public function needsSkillResultsSubTab(): bool
    {
        if (!$this->needsMyResultsSubTab()) {
            return false;
        }

        return $this->getTestOBJ()->isSkillServiceToBeConsidered();
    }

    public function needsMySolutionsSubTab(): bool
    {
        return $this->getTestOBJ()->canShowSolutionPrintview($this->getTestSession()->getUserId());
    }

    public function getResultsSubTabs()
    {
        if ($this->needsParticipantsResultsSubTab()) {
            $this->tabs->addSubTab(
                self::SUBTAB_ID_PARTICIPANTS_RESULTS,
                $this->lng->txt('participants_results_subtab'),
                $this->ctrl->getLinkTargetByClass(['ilTestResultsGUI', 'ilParticipantsTestResultsGUI'])
            );
        }

        if ($this->needsLoResultsSubTab()) {
            $this->tabs->addSubTab(
                self::SUBTAB_ID_LO_RESULTS,
                $this->lng->txt('tst_tab_results_objective_oriented'),
                $this->ctrl->getLinkTargetByClass(['ilTestResultsGUI', 'ilTestEvalObjectiveOrientedGUI'])
            );
        }

        if ($this->needsMyResultsSubTab()) {
            $myResultsLabel = $this->lng->txt('tst_show_results');

            if ($this->needsLoResultsSubTab()) {
                $myResultsLabel = $this->lng->txt('tst_tab_results_pass_oriented');
            }

            $this->tabs->addSubTab(
                self::SUBTAB_ID_MY_RESULTS,
                $myResultsLabel,
                $this->ctrl->getLinkTargetByClass(['ilTestResultsGUI', 'ilMyTestResultsGUI', 'ilTestEvaluationGUI'])
            );
        }

        if ($this->needsSkillResultsSubTab()) {
            $this->tabs->addSubTab(
                self::SUBTAB_ID_SKILL_RESULTS,
                $this->lng->txt('tst_show_comp_results'),
                $this->ctrl->getLinkTargetByClass(['ilTestResultsGUI', 'ilTestSkillEvaluationGUI'])
            );
        }

        if ($this->needsHighSoreSubTab()) {
            $this->tabs->addSubTab(
                self::SUBTAB_ID_HIGHSCORE,
                $this->lng->txt('tst_show_toplist'),
                $this->ctrl->getLinkTargetByClass(['ilTestResultsGUI', 'ilTestToplistGUI'], 'outResultsToplist')
            );
        }

        if ($this->needsMySolutionsSubTab()) {
            $this->tabs->addSubTab(
                self::SUBTAB_ID_MY_SOLUTIONS,
                $this->lng->txt('tst_list_of_answers_show'),
                $this->ctrl->getLinkTargetByClass(['ilTestResultsGUI', 'ilMyTestSolutionsGUI', 'ilTestEvaluationGUI'])
            );
        }
    }
}
