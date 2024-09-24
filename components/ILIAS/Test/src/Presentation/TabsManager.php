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

namespace ILIAS\Test\Presentation;

use ILIAS\Test\Settings\MainSettings\SettingsMainGUI;
use ILIAS\Test\Settings\ScoreReporting\SettingsScoringGUI;
use ILIAS\Test\Scoring\Manual\TestScoringByQuestionGUI;
use ILIAS\Test\Scoring\Marks\MarkSchemaGUI;
use ILIAS\Test\Presentation\TestScreenGUI;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 */
class TabsManager
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

    public const TAB_ID_YOUR_RESULTS = 'your_results';
    public const SUBTAB_ID_PARTICIPANTS_RESULTS = 'participantsresults';
    public const SUBTAB_ID_MY_RESULTS = 'myresults';
    public const SUBTAB_ID_LO_RESULTS = 'loresults';
    public const SUBTAB_ID_HIGHSCORE = 'highscore';
    public const SUBTAB_ID_SKILL_RESULTS = 'skillresults';
    public const SUBTAB_ID_MY_SOLUTIONS = 'mysolutions';

    private const SETTINGS_SUBTAB_ID_GENERAL = 'general';
    public const SETTINGS_SUBTAB_ID_MARK_SCHEMA = 'mark_schema';
    public const SETTINGS_SUBTAB_ID_SCORING = 'scoring';
    public const SETTINGS_SUBTAB_ID_EDIT_INTRODUCTION_PAGE = 'edit_introduction';
    public const SETTINGS_SUBTAB_ID_EDIT_CONCLUSION_PAGE = 'edit_concluding_remarks';
    private const SETTINGS_SUBTAB_ID_CERTIFICATE = 'certificate';
    public const SETTINGS_SUBTAB_ID_ASSIGN_SKILL_TRESHOLDS = 'skill_level_thresholds';
    public const SETTINGS_SUBTAB_ID_ASSIGN_SKILLS_TO_QUESTIONS = 'question_skill_assignment';
    private const SETTINGS_SUBTAB_ID_PERSONAL_DEFAULT_SETTINGS = 'tst_default_settings';

    protected ?string $parent_back_href = null;
    protected ?string $parent_back_label = null;

    public function __construct(
        private readonly \ilTabsGUI $tabs,
        private readonly \ilLanguage $lng,
        private readonly \ilCtrlInterface $ctrl,
        private readonly RequestWrapper $wrapper,
        private readonly Refinery $refinery,
        private readonly \ilAccess $access,
        private readonly \ilTestAccess $test_access,
        private readonly \ilObjTest $test_object,
        private readonly \ilTestObjectiveOrientedContainer $objective_parent,
        private readonly \ilTestSession $test_session
    ) {
    }

    public function activateTab(string $tab_id): void
    {
        switch ($tab_id) {
            case self::TAB_ID_EXAM_DASHBOARD:
            case self::TAB_ID_YOUR_RESULTS:
            case self::TAB_ID_SETTINGS:

                $this->tabs->activateTab($tab_id);
        }
    }

    public function activateSubTab(string $sub_tab_id): void
    {
        switch ($sub_tab_id) {
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
                $this->tabs->activateSubTab($sub_tab_id);
        }
    }

    public function resetTabsAndAddBacklink(string $back_link_target): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->lng->txt('back'), $back_link_target);
    }

    public function getParentBackLabel(): ?string
    {
        return $this->parent_back_label;
    }

    public function setParentBackLabel(?string $parent_back_label)
    {
        $this->parent_back_label = $parent_back_label;
    }

    public function getParentBackHref(): ?string
    {
        return $this->parent_back_href;
    }

    public function setParentBackHref(?string $parent_back_href): void
    {
        $this->parent_back_href = $parent_back_href;
    }

    public function hasParentBackLink(): bool
    {
        if ($this->getParentBackHref() === null) {
            return false;
        }

        if ($this->getParentBackLabel() === null) {
            return false;
        }

        return true;
    }

    protected function isReadAccessGranted(): bool
    {
        return $this->access->checkAccess('read', '', $this->test_object->getRefId());
    }

    protected function isWriteAccessGranted(): bool
    {
        return $this->access->checkAccess('write', '', $this->test_object->getRefId());
    }

    protected function isStatisticsAccessGranted(): bool
    {
        return $this->access->checkAccess('tst_statistics', '', $this->test_object->getRefId());
    }

    protected function isHistoryAccessGranted(): bool
    {
        return $this->access->checkAccess('tst_history_read', '', $this->test_object->getRefId());
    }

    protected function isPermissionsAccessGranted(): bool
    {
        return $this->access->checkAccess('edit_permission', '', $this->test_object->getRefId());
    }

    protected function isLpAccessGranted(): bool
    {
        return \ilLearningProgressAccess::checkAccess($this->test_object->getRefId());
    }

    protected function checkDashboardTabAccess(): bool
    {
        if ($this->test_access->checkManageParticipantsAccess()) {
            return true;
        }

        return false;
    }

    protected function checkScoreParticipantsTabAccess(): bool
    {
        return $this->test_access->checkScoreParticipantsAccess();
    }

    protected function checkStatisticsTabAccess(): bool
    {
        return $this->test_access->checkStatisticsAccess();
    }

    public function perform(): void
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

    protected function setupTabsGuiConfig(): void
    {
        if ($this->hasParentBackLink()) {
            $this->tabs->setBack2Target($this->getParentBackLabel(), $this->getParentBackHref());
        }

        $class_path = $this->ctrl->getCurrentClassPath();

        switch (array_pop($class_path)) {
            case MarkSchemaGUI::class:
            case SettingsMainGUI::class:
            case SettingsScoringGUI::class:

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
            case 'exportCertificateArchive':
                $this->getStatisticsSubTabs();
                break;
        }

        // test tab
        if ($this->isReadAccessGranted()) {
            $this->tabs->addTab(
                self::TAB_ID_TEST,
                $this->lng->txt('test'),
                $this->ctrl->getLinkTargetByClass(
                    [
                    \ilObjTestGUI::class, TestScreenGUI::class],
                    TestScreenGUI::DEFAULT_CMD
                )
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

            if ($this->test_object->isFixedTest()) {
                $target = $this->ctrl->getLinkTargetByClass(
                    \ilObjTestGUI::class,
                    \ilObjTestGUI::SHOW_QUESTIONS_CMD
                );
            }

            if ($this->test_object->isRandomTest()) {
                $target = $this->ctrl->getLinkTargetByClass('ilTestRandomQuestionSetConfigGUI');
            }

            $this->tabs->addTarget(
                'assQuestions',
                $target,
                [
                    'showQuestions', 'browseForQuestions', 'questionBrowser', 'createQuestion',
                    'filter', 'resetFilter', 'insertQuestions', 'back',
                    'executeCreateQuestion', 'cancelCreateQuestion',
                    'addQuestionpool', 'saveRandomQuestions', 'saveQuestionSelectionMode', 'print',
                    'addsource', 'removesource', 'randomQuestions'
                ],
                '',
                '',
                $force_active
            );
        }

        // info tab
        if ($this->isReadAccessGranted() && !$this->test_object->getMainSettings()->getAdditionalSettings()->getHideInfoTab()) {
            $this->tabs->addTarget(
                'info_short',
                $this->ctrl->getLinkTargetByClass(
                    [
                        ilRepositoryGUI::class,
                        ilObjTestGUI::class,
                        ilInfoScreenGUI::class
                    ]
                ),
                ['', 'outIntroductionPage', 'setAnonymousId', 'redirectToInfoScreen']
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

            $reflection = new \ReflectionClass(SettingsMainGUI::class);
            foreach ($reflection->getConstants() as $name => $value) {
                if (substr($name, 0, 4) === 'CMD_') {
                    $settingsCommands[] = $value;
                }
            }

            $reflection = new \ReflectionClass(SettingsScoringGUI::class);
            foreach ($reflection->getConstants() as $name => $value) {
                if (substr($name, 0, 4) === 'CMD_') {
                    $settingsCommands[] = $value;
                }
            }

            $settingsCommands[] = ''; // DO NOT KNOW WHAT THIS IS DOING, BUT IT'S REQUIRED

            $this->tabs->addTarget(
                'settings',
                $this->ctrl->getLinkTargetByClass([\ilObjTestGUI::class, SettingsMainGUI::class]),
                $settingsCommands,
                [
                    'ilmarkschemagui',
                    'ilobjtestsettingsmaingui',
                    'ilobjtestsettingsscoringresultsgui',
                    'ilobjtestgui',
                    'ilcertificategui'
                ]
            );
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
                self::TAB_ID_YOUR_RESULTS,
                $this->lng->txt('your_results'),
                $this->getResultsTabTarget()
            );
        }

        if ($this->isLpAccessGranted()) {
            $this->tabs->addTarget(
                self::TAB_ID_LEARNING_PROGRESS,
                $this->ctrl->getLinkTargetByClass([\ilObjTestGUI::class, \ilLearningProgressGUI::class], ''),
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
            if ($this->test_object->getGlobalSettings()->isManualScoringEnabled()) {
                // scoring tab
                $this->tabs->addTarget(
                    self::TAB_ID_MANUAL_SCORING,
                    $this->ctrl->getLinkTargetByClass(
                        [\ilObjTestGUI::class, TestScoringByQuestionGUI::class],
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

        if ($this->checkStatisticsTabAccess()) {
            // statistics tab
            $this->tabs->addTarget(
                self::TAB_ID_STATISTICS,
                $this->ctrl->getLinkTargetByClass(
                    [\ilRepositoryGUI::class, \ilObjTestGUI::class, \ilTestEvaluationGUI::class],
                    'outEvaluation'
                ),
                [
                    'statistics',
                    'outEvaluation',
                    'exportEvaluation',
                    'detailedEvaluation',
                    'eval_a',
                    'evalUserDetail',
                    'outStatisticsResultsOverview',
                    'statisticsPassDetails',
                    'singleResults'
                ],
                ''
            );
        }

        if ($this->isHistoryAccessGranted()) {
            $this->tabs->addTarget(
                self::TAB_ID_HISTORY,
                $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'history'),
                'history',
                ''
            );
        }

        if ($this->isWriteAccessGranted()) {
            $mdgui = new \ilObjectMetaDataGUI($this->test_object);
            $mdtab = $mdgui->getTab(\ilObjTestGUI::class);
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
                $this->ctrl->getLinkTargetByClass([\ilObjTestGUI::class, \ilTestExportGUI::class], ''),
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
    }

    protected function getBrowseForQuestionsTab(): void
    {
        if ($this->isWriteAccessGranted()) {
            $this->ctrl->saveParameterByClass($this->ctrl->getCmdClass(), 'q_id');
            // edit page
            $this->tabs->setBackTarget(
                $this->lng->txt('backtocallingtest'),
                $this->ctrl->getLinkTargetByClass($this->ctrl->getCmdClass(), \ilObjTestGUI::SHOW_QUESTIONS_CMD)
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

    protected function getRandomQuestionsTab(): void
    {
        if ($this->isWriteAccessGranted()) {
            // edit page
            $this->tabs->setBackTarget(
                $this->lng->txt('backtocallingtest'),
                $this->ctrl->getLinkTargetByClass(ilTestRandomQuestionSetConfigGUI, \ilObjTestGUI::SHOW_QUESTIONS_CMD)
            );
            $this->tabs->addTarget(
                'random_selection',
                $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'randomQuestions'),
                ['randomQuestions'],
                '',
                ''
            );
        }
    }

    public function getQuestionsSubTabs(): void
    {
        if (!$this->isWriteAccessGranted()) {
            return;
        }

        $this->tabs->activateTab(self::TAB_ID_QUESTIONS);

        if ($this->test_object->isRandomTest()) {
            $this->tabs->addSubTab(
                'tstRandQuestSetGeneralConfig',
                $this->lng->txt('tst_rnd_quest_cfg_tab_general'),
                $this->ctrl->getLinkTargetByClass(
                    \ilTestRandomQuestionSetConfigGUI::class,
                    \ilTestRandomQuestionSetConfigGUI::CMD_SHOW_GENERAL_CONFIG_FORM
                )
            );

            $this->tabs->addSubTab(
                'tstRandQuestSetPoolConfig',
                $this->lng->txt('tst_rnd_quest_cfg_tab_pool'),
                $this->ctrl->getLinkTargetByClass(
                    \ilTestRandomQuestionSetConfigGUI::class,
                    \ilTestRandomQuestionSetConfigGUI::CMD_SHOW_SRC_POOL_DEF_LIST
                )
            );
        }

        $this->tabs->addSubTab(
            self::SUBTAB_ID_QST_LIST_VIEW,
            $this->lng->txt('edit_test_questions'),
            $this->ctrl->getLinkTargetByClass(\ilObjTestGUI::class, \ilObjTestGUI::SHOW_QUESTIONS_CMD)
        );

        if ($this->test_object->isSkillServiceToBeConsidered()) {
            $this->tabs->addSubTab(
                self::SETTINGS_SUBTAB_ID_ASSIGN_SKILLS_TO_QUESTIONS,
                $this->lng->txt('qpl_skl_sub_tab_quest_assign'),
                $this->ctrl->getLinkTargetByClass(
                    [
                        \ilTestSkillAdministrationGUI::class,
                        \ilAssQuestionSkillAssignmentsGUI::class
                    ],
                    \ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS
                )
            );
        }
    }

    protected function getStatisticsSubTabs(): void
    {
        $this->tabs->addSubTabTarget(
            'eval_all_users',
            $this->ctrl->getLinkTargetByClass('iltestevaluationgui', 'outEvaluation'),
            ['outEvaluation', 'detailedEvaluation', 'exportEvaluation', 'evalUserDetail', 'passDetails',
                'outStatisticsResultsOverview', 'statisticsPassDetails', 'exportCertificateArchive'],
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

    public function getSettingsSubTabs(): void
    {
        $this->tabs->addSubTabTarget(
            self::SETTINGS_SUBTAB_ID_GENERAL,
            $this->ctrl->getLinkTargetByClass([\ilObjTestGUI::class, SettingsMainGUI::class]),
            '',
            [SettingsMainGUI::class]
        );

        $this->tabs->addSubTabTarget(
            self::SETTINGS_SUBTAB_ID_MARK_SCHEMA,
            $this->ctrl->getLinkTargetByClass([\ilObjTestGUI::class, MarkSchemaGUI::class], 'showMarkSchema'),
            '',
            [MarkSchemaGUI::class]
        );

        $this->tabs->addSubTabTarget(
            self::SETTINGS_SUBTAB_ID_SCORING,
            $this->ctrl->getLinkTargetByClass([\ilObjTestGUI::class, SettingsScoringGUI::class]),
            '',
            [SettingsScoringGUI::class]
        );

        $this->ctrl->setParameterByClass(\ilTestPageGUI::class, 'page_type', 'introductionpage');
        $this->tabs->addSubTabTarget(
            self::SETTINGS_SUBTAB_ID_EDIT_INTRODUCTION_PAGE,
            $this->ctrl->getLinkTargetByClass([\ilObjTestGUI::class, \ilTestPageGUI::class], 'preview'),
            ['iltestpagegui']
        );

        $this->ctrl->setParameterByClass(\ilTestPageGUI::class, 'page_type', 'concludingremarkspage');
        $this->tabs->addSubTabTarget(
            self::SETTINGS_SUBTAB_ID_EDIT_CONCLUSION_PAGE,
            $this->ctrl->getLinkTargetByClass([\ilObjTestGUI::class, \ilTestPageGUI::class], 'preview'),
            ['iltestpagegui']
        );
        $this->ctrl->clearParameterByClass(\ilTestPageGUI::class, 'page_type');

        $validator = new \ilCertificateActiveValidator();
        if ($validator->validate() === true) {
            $this->tabs->addSubTabTarget(
                self::SETTINGS_SUBTAB_ID_CERTIFICATE,
                $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'certificate'),
                ['certificate', 'certificateEditor', 'certificateRemoveBackground', 'ceateSave',
                    'certificatePreview', 'certificateDelete', 'certificateUpload', 'certificateImport'],
                ['', 'ilobjtestgui', 'ilcertificategui']
            );
        }

        if ($this->test_object->isSkillServiceToBeConsidered()) {
            $this->tabs->addSubTab(
                self::SETTINGS_SUBTAB_ID_ASSIGN_SKILL_TRESHOLDS,
                $this->lng->txt('tst_skl_sub_tab_thresholds'),
                $this->ctrl->getLinkTargetByClass(
                    [
                        \ilTestSkillAdministrationGUI::class,
                        \ilTestSkillLevelThresholdsGUI::class
                    ],
                    \ilTestSkillLevelThresholdsGUI::CMD_SHOW_SKILL_THRESHOLDS
                )
            );
        }

        $this->tabs->addSubTabTarget(
            self::SETTINGS_SUBTAB_ID_PERSONAL_DEFAULT_SETTINGS,
            $this->ctrl->getLinkTargetByClass('ilObjTestGUI', 'defaults'),
            ['defaults', 'deleteDefaults', 'addDefaults', 'applyDefaults'],
            ['', 'ilobjtestgui', 'ilcertificategui']
        );

        $lti_settings = new \ilLTIProviderObjectSettingGUI($this->test_object->getRefId());
        if ($lti_settings->hasSettingsAccess()) {
            $this->tabs->addSubTabTarget(
                'lti_provider',
                $this->ctrl->getLinkTargetByClass(\ilLTIProviderObjectSettingGUI::class),
                '',
                [\ilLTIProviderObjectSettingGUI::class]
            );
        }
    }

    protected function needsDashboardTab(): bool
    {
        if (!$this->checkDashboardTabAccess()) {
            return false;
        }

        return true;
    }

    protected function needsTimeExtensionSubTab(): bool
    {
        if (!($this->test_object->getProcessingTimeInSeconds() > 0)) {
            return false;
        }

        if ($this->test_object->getNrOfTries() != 1) {
            return false;
        }

        return true;
    }

    protected function getDashboardTabTarget(): string
    {
        return $this->ctrl->getLinkTargetByClass([\ilObjTestGUI::class, \ilTestDashboardGUI::class, \ilTestParticipantsGUI::class]);
    }

    public function getDashboardSubTabs(): void
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
        if ($this->test_object->getFixedParticipants()) {
            return $this->lng->txt('fixedparticipants_subtab');
        }

        return $this->lng->txt('autoparticipants_subtab');
    }

    protected function needsResultsTab(): bool
    {
        return $this->needsParticipantsResultsSubTab() || $this->test_object->isScoreReportingEnabled() || $this->needsMySolutionsSubTab();
    }

    protected function getResultsTabTarget(): string
    {
        if ($this->needsParticipantsResultsSubTab()) {
            return $this->ctrl->getLinkTargetByClass([\ilObjTestGUI::class, \ilTestResultsGUI::class, \ilParticipantsTestResultsGUI::class]);
        }

        if ($this->needsLoResultsSubTab()) {
            return $this->ctrl->getLinkTargetByClass([\ilObjTestGUI::class, \ilTestResultsGUI::class, \ilTestEvalObjectiveOrientedGUI::class]);
        }

        if ($this->needsMyResultsSubTab()) {
            return $this->ctrl->getLinkTargetByClass(['ilTestResultsGUI', 'ilMyTestResultsGUI', 'ilTestEvaluationGUI']);
        }

        if ($this->needsMySolutionsSubTab()) {
            return $this->ctrl->getLinkTargetByClass(['ilTestResultsGUI', 'ilMyTestSolutionsGUI', 'ilTestEvaluationGUI']);
        }

        return $this->ctrl->getLinkTargetByClass('ilTestResultsGUI');
    }

    public function needsMyResultsSubTab(): bool
    {
        return $this->test_session->reportableResultsAvailable($this->test_object);
    }

    public function needsLoResultsSubTab(): bool
    {
        if (!$this->needsMyResultsSubTab()) {
            return false;
        }

        return $this->objective_parent->isObjectiveOrientedPresentationRequired();
    }

    public function needsParticipantsResultsSubTab(): bool
    {
        if ($this->test_access->checkParticipantsResultsAccess()) {
            return true;
        }

        return false;
    }

    public function needsHighSoreSubTab(): bool
    {
        if (!$this->needsMyResultsSubTab()) {
            return false;
        }

        return $this->test_object->getHighscoreEnabled();
    }

    public function needsSkillResultsSubTab(): bool
    {
        if (!$this->needsMyResultsSubTab()) {
            return false;
        }

        return $this->test_object->isSkillServiceToBeConsidered();
    }

    public function needsMySolutionsSubTab(): bool
    {
        return $this->test_object->canShowSolutionPrintview($this->test_session->getUserId());
    }

    public function getResultsSubTabs(): void
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
