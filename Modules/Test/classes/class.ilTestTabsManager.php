<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    const TAB_ID_QUESTIONS = 'assQuestions';
    const SUBTAB_ID_QST_LIST_VIEW = 'qst_list_view';
    const SUBTAB_ID_QST_PAGE_VIEW = 'qst_page_view';

    const TAB_ID_INFOSCREEN = 'info_short';
    const TAB_ID_SETTINGS = 'settings';
    const TAB_ID_LEARNING_PROGRESS = 'learning_progress';
    const TAB_ID_MANUAL_SCORING = 'manscoring';
    const TAB_ID_CORRECTION = 'scoringadjust';
    const TAB_ID_STATISTICS = 'statistics';
    const TAB_ID_HISTORY = 'history';
    const TAB_ID_META_DATA = 'meta_data';
    const TAB_ID_EXPORT = 'export';
    const TAB_ID_PERMISSIONS = 'perm_settings';
    
    const TAB_ID_EXAM_DASHBOARD = 'dashboard_tab';
    const SUBTAB_ID_FIXED_PARTICIPANTS = 'fixedparticipants';
    const SUBTAB_ID_TIME_EXTENSION = 'timeextension';
    
    const TAB_ID_RESULTS = 'results';
    const SUBTAB_ID_PARTICIPANTS_RESULTS = 'participantsresults';
    const SUBTAB_ID_MY_RESULTS = 'myresults';
    const SUBTAB_ID_LO_RESULTS = 'loresults';
    const SUBTAB_ID_HIGHSCORE = 'highscore';
    const SUBTAB_ID_SKILL_RESULTS = 'skillresults';
    const SUBTAB_ID_MY_SOLUTIONS = 'mysolutions';
    
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    
    /**
     * @var ilAccessHandler
     */
    protected $access;
    
    /**
     * @var ilTestAccess
     */
    protected $testAccess;
    
    /**
     * @var ilTestObjectiveOrientedContainer
     */
    protected $objectiveParent;
    
    /**
     * @var ilLanguage
     */
    protected $lng;
    
    /**
     * @var ilObjTest
     */
    protected $testOBJ;
    
    /**
     * @var ilTestSession
     */
    protected $testSession;
    
    /**
     * @var ilTestQuestionSetConfig
     */
    protected $testQuestionSetConfig;
    
    /**
     * @var string|null
     */
    protected $parentBackHref;
    
    /**
     * @var string|null
     */
    protected $parentBackLabel;
    
    /**
     * @var array[string]
     */
    protected $hiddenTabs;
    
    /**
     * ilTestTabsManager constructor.
     */
    public function __construct(ilTestAccess $testAccess, ilTestObjectiveOrientedContainer $objectiveParent)
    {
        $this->testAccess = $testAccess;
        $this->objectiveParent = $objectiveParent;
        
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $this->tabs = $DIC['ilTabs'];
        $this->access = $DIC['ilAccess'];
        $this->lng = $DIC['lng'];
    }
    
    /**
     * @param string $tabId
     */
    public function activateTab($tabId)
    {
        switch ($tabId) {
            case self::TAB_ID_EXAM_DASHBOARD:
            case self::TAB_ID_RESULTS:
                
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

                $this->tabs->activateSubTab($subTabId);
        }
    }
    
    /**
     * @return ilObjTest
     */
    public function getTestOBJ()
    {
        return $this->testOBJ;
    }
    
    /**
     * @param ilObjTest $testOBJ
     */
    public function setTestOBJ(ilObjTest $testOBJ)
    {
        $this->testOBJ = $testOBJ;
    }
    
    /**
     * @return ilTestSession
     */
    public function getTestSession()
    {
        return $this->testSession;
    }
    
    /**
     * @param ilTestSession $testSession
     */
    public function setTestSession($testSession)
    {
        $this->testSession = $testSession;
    }
    
    /**
     * @return ilTestQuestionSetConfig
     */
    public function getTestQuestionSetConfig()
    {
        return $this->testQuestionSetConfig;
    }
    
    /**
     * @param ilTestQuestionSetConfig $testQuestionSetConfig
     */
    public function setTestQuestionSetConfig(ilTestQuestionSetConfig $testQuestionSetConfig)
    {
        $this->testQuestionSetConfig = $testQuestionSetConfig;
    }
    
    /**
     * @return array
     */
    public function getHiddenTabs()
    {
        return $this->hiddenTabs;
    }
    
    /**
     * @param array $hiddenTabs
     */
    public function setHiddenTabs($hiddenTabs)
    {
        $this->hiddenTabs = $hiddenTabs;
    }
    
    /**
     * @param array $hiddenTabs
     */
    public function resetHiddenTabs()
    {
        $this->hiddenTabs = array();
    }
    
    /**
     * @return null|string
     */
    public function getParentBackLabel()
    {
        return $this->parentBackLabel;
    }
    
    /**
     * @param null|string $parentBackLabel
     */
    public function setParentBackLabel($parentBackLabel)
    {
        $this->parentBackLabel = $parentBackLabel;
    }
    
    /**
     * @return null|string
     */
    public function getParentBackHref()
    {
        return $this->parentBackHref;
    }
    
    /**
     * @param null|string $parentBackHref
     */
    public function setParentBackHref($parentBackHref)
    {
        $this->parentBackHref = $parentBackHref;
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
     */
    public function initSettingsTemplate()
    {
        $this->resetHiddenTabs();
        
        if ($this->getTestOBJ()->getTemplate()) {
            require_once 'Services/Administration/classes/class.ilSettingsTemplate.php';
            
            $template = new ilSettingsTemplate(
                $this->getTestOBJ()->getTemplate(),
                ilObjAssessmentFolderGUI::getSettingsTemplateConfig()
            );
            
            $this->setHiddenTabs($template->getHiddenTabs());
        }
    }
    
    /**
     * @param string $tabId
     * @return bool
     */
    protected function isHiddenTab($tabId)
    {
        return in_array($tabId, $this->getHiddenTabs());
    }
    
    /**
     * @return bool
     */
    protected function isReadAccessGranted()
    {
        return $this->access->checkAccess('read', '', $this->getTestOBJ()->getRefId());
    }
    
    /**
     * @return bool
     */
    protected function isWriteAccessGranted()
    {
        return $this->access->checkAccess('write', '', $this->getTestOBJ()->getRefId());
    }
    
    /**
     * @return bool
     */
    protected function isStatisticsAccessGranted()
    {
        return $this->access->checkAccess('tst_statistics', '', $this->getTestOBJ()->getRefId());
    }
    
    /**
     * @return bool
     */
    protected function isPermissionsAccessGranted()
    {
        return $this->access->checkAccess('edit_permission', '', $this->getTestOBJ()->getRefId());
    }
    
    /**
     * @return bool
     */
    protected function isLpAccessGranted()
    {
        include_once 'Services/Tracking/classes/class.ilLearningProgressAccess.php';
        return ilLearningProgressAccess::checkAccess($this->getTestOBJ()->getRefId());
    }
    
    /**
     * @return bool
     */
    protected function checkDashboardTabAccess()
    {
        if ($this->testAccess->checkManageParticipantsAccess()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @return bool
     */
    protected function checkScoreParticipantsTabAccess()
    {
        return $this->testAccess->checkScoreParticipantsAccess();
    }
    
    /**
     * @return bool
     */
    protected function checkStatisticsTabAccess()
    {
        return $this->testAccess->checkStatisticsAccess();
    }
    
    /**
     */
    public function perform()
    {
        if ($this->isTabsConfigSetupRequired()) {
            $this->setupTabsGuiConfig();
        }
    }
    
    protected function isTabsConfigSetupRequired()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if (preg_match('/^ass(.*?)gui$/i', $DIC->ctrl()->getNextClass($this))) {
            return false;
        }
        
        if ($DIC->ctrl()->getNextClass($this) == 'ilassquestionpagegui') {
            return false;
        }
        
        if ($DIC->ctrl()->getCmdClass() == 'iltestoutputgui') {
            return false;
        }
        
        if ($DIC->ctrl()->getCmdClass() == 'iltestevaluationgui') {
            return in_array($DIC->ctrl()->getCmd(), array(
                '', 'outUserResultsPassOverview', 'outUserListOfAnswerPasses', 'outEvaluation', 'eval_a', 'singleResults', 'detailedEvaluation'
            ));
        }
        
        return true;
    }
    
    protected function setupTabsGuiConfig()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if ($this->hasParentBackLink()) {
            $this->tabs->setBack2Target($this->getParentBackLabel(), $this->getParentBackHref());
        }
        
        switch ($DIC->ctrl()->getCmdClass()) {
            case 'ilmarkschemagui':
            case 'ilobjtestsettingsgeneralgui':
            case 'ilobjtestsettingsscoringresultsgui':
                
                if ($this->isWriteAccessGranted()) {
                    $this->getSettingsSubTabs();
                }
                
                break;
        }
        
        switch ($DIC->ctrl()->getCmd()) {
            case "resume":
            case "previous":
            case "next":
            case "summary":
            case "directfeedback":
            case "finishTest":
            case "outCorrectSolution":
            case "passDetails":
            case "showAnswersOfUser":
            case "backFromSummary":
            case "show_answers":
            case "setsolved":
            case "resetsolved":
            case "confirmFinish":
            case "outTestSummary":
            case "outQuestionSummary":
            case "gotoQuestion":
            case "selectImagemapRegion":
            case "confirmSubmitAnswers":
            case "finalSubmission":
            case "postpone":
            case "outUserPassDetails":
            case "checkPassword":
            case "exportCertificate":
            case "finishListOfAnswers":
            case "backConfirmFinish":
            case "showFinalStatement":
                return;
                break;
            case "browseForQuestions":
            case "filter":
            case "resetFilter":
            case "resetTextFilter":
            case "insertQuestions":
                $classes = array(
                    'iltestdashboardgui',
                    'iltestresultsgui',
                    "illearningprogressgui" // #8497: resetfilter is also used in lp
                );
                if (!in_array($DIC->ctrl()->getNextClass($this), $classes)) {
                    $this->getBrowseForQuestionsTab();
                    return;
                }
                break;
            case "scoring":
            case "certificate":
            case "certificateservice":
            case "certificateImport":
            case "certificateUpload":
            case "certificateEditor":
            case "certificateDelete":
            case "certificateSave":
            case "defaults":
            case "deleteDefaults":
            case "addDefaults":
            case "applyDefaults":
            case "inviteParticipants":
            case "searchParticipants":
                if ($this->isWriteAccessGranted() && in_array($DIC->ctrl()->getCmdClass(), array('ilobjtestgui', 'ilcertificategui'))) {
                    $this->getSettingsSubTabs();
                }
                break;
            case "export":
            case "print":
                break;
            case "statistics":
            case "eval_a":
            case "detailedEvaluation":
            case "outEvaluation":
            case "singleResults":
            case "exportEvaluation":
            case "evalUserDetail":
            case "outStatisticsResultsOverview":
            case "statisticsPassDetails":
                $this->getStatisticsSubTabs();
                break;
        }
        
        // questions tab
        if ($this->isWriteAccessGranted() && !$this->isHiddenTab('assQuestions')) {
            $force_active = ($_GET["up"] != "" || $_GET["down"] != "")
                ? true
                : false;
            if (!$force_active) {
                if ($_GET["browse"] == 1) {
                    $force_active = true;
                }
            }
            
            switch ($this->getTestOBJ()->getQuestionSetType()) {
                case ilObjTest::QUESTION_SET_TYPE_FIXED:
                    $target = $DIC->ctrl()->getLinkTargetByClass(
                        'ilTestExpresspageObjectGUI',
                        'showPage'
                    );
                    break;
                
                case ilObjTest::QUESTION_SET_TYPE_RANDOM:
                    $target = $DIC->ctrl()->getLinkTargetByClass('ilTestRandomQuestionSetConfigGUI');
                    break;
                
                case ilObjTest::QUESTION_SET_TYPE_DYNAMIC:
                    $target = $DIC->ctrl()->getLinkTargetByClass('ilObjTestDynamicQuestionSetConfigGUI');
                    break;
                    
                default: $target = '';
            }
            
            $this->tabs->addTarget(
                "assQuestions",
                $target,
                array("questions", "browseForQuestions", "questionBrowser", "createQuestion",
                    "randomselect", "filter", "resetFilter", "insertQuestions",
                    "back", "createRandomSelection", "cancelRandomSelect",
                    "insertRandomSelection", "removeQuestions", "moveQuestions",
                    "insertQuestionsBefore", "insertQuestionsAfter", "confirmRemoveQuestions",
                    "cancelRemoveQuestions", "executeCreateQuestion", "cancelCreateQuestion",
                    "addQuestionpool", "saveRandomQuestions", "saveQuestionSelectionMode", "print",
                    "addsource", "removesource", "randomQuestions"),
                "",
                "",
                $force_active
            );
        }
        
        // info tab
        if ($this->isReadAccessGranted() && !$this->isHiddenTab('info_short')) {
            $this->tabs->addTarget(
                "info_short",
                $DIC->ctrl()->getLinkTargetByClass('ilObjTestGUI', 'infoScreen'),
                array("infoScreen", "outIntroductionPage", "showSummary",
                    "setAnonymousId", "redirectToInfoScreen")
            );
        }
        
        // settings tab
        if ($this->isWriteAccessGranted()) {
            if (!$this->isHiddenTab('settings')) {
                $settingsCommands = array(
                    "marks", "showMarkSchema","addMarkStep", "deleteMarkSteps", "addSimpleMarkSchema", "saveMarks",
                    "certificate", "certificateEditor", "certificateRemoveBackground", "certificateSave",
                    "certificatePreview", "certificateDelete", "certificateUpload", "certificateImport",
                    "scoring", "defaults", "addDefaults", "deleteDefaults", "applyDefaults",
                    "inviteParticipants", "saveFixedParticipantsStatus", "searchParticipants", "addParticipants" // ARE THEY RIGHT HERE
                );
                
                require_once 'Modules/Test/classes/class.ilObjTestSettingsGeneralGUI.php';
                $reflection = new ReflectionClass('ilObjTestSettingsGeneralGUI');
                foreach ($reflection->getConstants() as $name => $value) {
                    if (substr($name, 0, 4) == 'CMD_') {
                        $settingsCommands[] = $value;
                    }
                }
                
                require_once 'Modules/Test/classes/class.ilObjTestSettingsScoringResultsGUI.php';
                $reflection = new ReflectionClass('ilObjTestSettingsScoringResultsGUI');
                foreach ($reflection->getConstants() as $name => $value) {
                    if (substr($name, 0, 4) == 'CMD_') {
                        $settingsCommands[] = $value;
                    }
                }
                
                $settingsCommands[] = ""; // DO NOT KNOW WHAT THIS IS DOING, BUT IT'S REQUIRED
                
                $this->tabs->addTarget(
                    "settings",
                    $DIC->ctrl()->getLinkTargetByClass('ilObjTestSettingsGeneralGUI'),
                    $settingsCommands,
                    array("ilmarkschemagui", "ilobjtestsettingsgeneralgui", "ilobjtestsettingsscoringresultsgui", "ilobjtestgui", "ilcertificategui")
                );
            }
            
            // skill service
            if ($this->getTestOBJ()->isSkillServiceEnabled() && ilObjTest::isSkillManagementGloballyActivated()) {
                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentsGUI.php';
                
                $link = $DIC->ctrl()->getLinkTargetByClass(
                    array('ilTestSkillAdministrationGUI', 'ilAssQuestionSkillAssignmentsGUI'),
                    ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS
                );
                
                $this->tabs->addTarget('tst_tab_competences', $link, array(), array());
            }
        }
        
        if ($this->needsDashboardTab()) {
            $this->tabs->addTab(
                self::TAB_ID_EXAM_DASHBOARD,
                $DIC->language()->txt('dashboard_tab'),
                $this->getDashboardTabTarget()
            );
        }
        
        if ($this->needsResultsTab()) {
            $this->tabs->addTab(
                self::TAB_ID_RESULTS,
                $DIC->language()->txt('results_tab'),
                $this->getResultsTabTarget()
            );
        }
        
        if ($this->isLpAccessGranted() && !$this->isHiddenTab(self::TAB_ID_LEARNING_PROGRESS)) {
            $this->tabs->addTarget(
                self::TAB_ID_LEARNING_PROGRESS,
                $DIC->ctrl()->getLinkTargetByClass(array('illearningprogressgui'), ''),
                '',
                array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui')
            );
        }
        
        if ($this->checkScoreParticipantsTabAccess()  && !$this->isHiddenTab(self::TAB_ID_MANUAL_SCORING)) {
            include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
            $scoring = ilObjAssessmentFolder::_getManualScoring();
            if (count($scoring)) {
                // scoring tab
                $this->tabs->addTarget(
                    self::TAB_ID_MANUAL_SCORING,
                    $DIC->ctrl()->getLinkTargetByClass('ilTestScoringByQuestionsGUI', 'showManScoringByQuestionParticipantsTable'),
                    array(
                        'showManScoringParticipantsTable', 'applyManScoringParticipantsFilter', 'resetManScoringParticipantsFilter', 'showManScoringParticipantScreen',
                        'showManScoringByQuestionParticipantsTable', 'applyManScoringByQuestionFilter', 'resetManScoringByQuestionFilter', 'saveManScoringByQuestion'
                    
                    ),
                    ''
                );
            }
        }
        
        // NEW CORRECTIONS TAB
        $setting = new ilSetting('assessment');
        $scoring_adjust_active = (bool) $setting->get('assessment_adjustments_enabled', false);
        if ($this->isWriteAccessGranted() && $scoring_adjust_active && !$this->isHiddenTab(self::TAB_ID_CORRECTION)) {
            $this->tabs->addTab(
                self::TAB_ID_CORRECTION,
                $DIC->language()->txt(self::TAB_ID_CORRECTION),
                $DIC->ctrl()->getLinkTargetByClass('ilTestCorrectionsGUI')
            );
        }
        
        if ($this->checkStatisticsTabAccess()  && !$this->isHiddenTab(self::TAB_ID_STATISTICS)) {
            // statistics tab
            $this->tabs->addTarget(
                self::TAB_ID_STATISTICS,
                $DIC->ctrl()->getLinkTargetByClass("iltestevaluationgui", "outEvaluation"),
                array(
                    "statistics", "outEvaluation", "exportEvaluation", "detailedEvaluation", "eval_a", "evalUserDetail",
                    "passDetails", "outStatisticsResultsOverview", "statisticsPassDetails", "singleResults"
                ),
                ""
            );
        }
        
        if ($this->isWriteAccessGranted()) {
            if (!$this->isHiddenTab(self::TAB_ID_HISTORY)) {
                
                // history
                $this->tabs->addTarget(
                    self::TAB_ID_HISTORY,
                    $DIC->ctrl()->getLinkTargetByClass('ilObjTestGUI', 'history'),
                    "history",
                    ""
                );
            }
            
            if (!$this->isHiddenTab(self::TAB_ID_META_DATA)) {
                // meta data
                include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
                $mdgui = new ilObjectMetaDataGUI($this->getTestOBJ());
                $mdtab = $mdgui->getTab();
                if ($mdtab) {
                    $this->tabs->addTarget(
                        self::TAB_ID_META_DATA,
                        $mdtab,
                        "",
                        "ilmdeditorgui"
                    );
                }
            }
            
            if (!$this->isHiddenTab(self::TAB_ID_EXPORT)) {
                // export tab
                $this->tabs->addTarget(
                    self::TAB_ID_EXPORT,
                    $DIC->ctrl()->getLinkTargetByClass('iltestexportgui', ''),
                    '',
                    array('iltestexportgui')
                );
            }
        }
        
        if ($this->isPermissionsAccessGranted() && !$this->isHiddenTab(self::TAB_ID_PERMISSIONS)) {
            $this->tabs->addTarget(
                self::TAB_ID_PERMISSIONS,
                $DIC->ctrl()->getLinkTargetByClass(array('ilObjTestGUI','ilpermissiongui'), "perm"),
                array("perm","info","owner"),
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
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if ($this->isWriteAccessGranted()) {
            $DIC->ctrl()->saveParameterByClass($DIC->ctrl()->getCmdClass(), 'q_id');
            // edit page
            $this->tabs->setBackTarget($this->lng->txt("backtocallingtest"), $DIC->ctrl()->getLinkTargetByClass($DIC->ctrl()->getCmdClass(), "questions"));
            $this->tabs->addTarget(
                "tst_browse_for_questions",
                $DIC->ctrl()->getLinkTargetByClass('ilObjTestGUI', "browseForQuestions"),
                array("browseForQuestions", "filter", "resetFilter", "resetTextFilter", "insertQuestions"),
                "",
                "",
                true
            );
        }
    }
    
    protected function getRandomQuestionsTab()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if ($this->isWriteAccessGranted()) {
            // edit page
            $this->tabs->setBackTarget($this->lng->txt("backtocallingtest"), $DIC->ctrl()->getLinkTargetByClass('ilObjTestGUI', "questions"));
            $this->tabs->addTarget(
                "random_selection",
                $DIC->ctrl()->getLinkTargetByClass('ilObjTestGUI', "randomQuestions"),
                array("randomQuestions"),
                "",
                ""
            );
        }
    }
    
    public function getQuestionsSubTabs()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $this->tabs->activateTab(self::TAB_ID_QUESTIONS);

        if (!$this->getTestOBJ()->isRandomTest()) {
            $this->tabs->addSubTab(
                self::SUBTAB_ID_QST_PAGE_VIEW,
                $DIC->language()->txt('questions_per_page_view'),
                $DIC->ctrl()->getLinkTargetByClass('iltestexpresspageobjectgui', 'showPage')
            );
        }
        include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
        $template = new ilSettingsTemplate($this->getTestOBJ()->getTemplate(), ilObjAssessmentFolderGUI::getSettingsTemplateConfig());
        
        if (!$this->isHiddenTab('questions')) {
            $this->tabs->addSubTab(
                self::SUBTAB_ID_QST_LIST_VIEW,
                $DIC->language()->txt('edit_test_questions'),
                $DIC->ctrl()->getLinkTargetByClass('ilObjTestGUI', 'questions')
            );
        }
        
        // print view subtab
        if (!$this->getTestOBJ()->isRandomTest()) {
            $this->tabs->addSubTabTarget(
                "print_view",
                $DIC->ctrl()->getLinkTargetByClass('ilObjTestGUI', 'print'),
                "print",
                "",
                "",
                $DIC->ctrl()->getCmd() == 'print'
            );
            $this->tabs->addSubTabTarget(
                'review_view',
                $DIC->ctrl()->getLinkTargetByClass('ilObjTestGUI', 'review'),
                'review',
                '',
                '',
                $DIC->ctrl()->getCmd() == 'review'
            );
        }
    }
    
    protected function getStatisticsSubTabs()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        // user results subtab
        $this->tabs->addSubTabTarget(
            "eval_all_users",
            $DIC->ctrl()->getLinkTargetByClass("iltestevaluationgui", "outEvaluation"),
            array("outEvaluation", "detailedEvaluation", "exportEvaluation", "evalUserDetail", "passDetails",
                "outStatisticsResultsOverview", "statisticsPassDetails"),
            ""
        );
        
        // aggregated results subtab
        $this->tabs->addSubTabTarget(
            "tst_results_aggregated",
            $DIC->ctrl()->getLinkTargetByClass("iltestevaluationgui", "eval_a"),
            array("eval_a"),
            "",
            ""
        );
        
        // question export
        $this->tabs->addSubTabTarget(
            "tst_single_results",
            $DIC->ctrl()->getLinkTargetByClass("iltestevaluationgui", "singleResults"),
            array("singleResults"),
            "",
            ""
        );
    }
    
    public function getSettingsSubTabs()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        // general subtab
        $this->tabs->addSubTabTarget(
            'general',
            $DIC->ctrl()->getLinkTargetByClass('ilObjTestSettingsGeneralGUI'),
            '',											// auto activation regardless from cmd
            array('ilobjtestsettingsgeneralgui')			// auto activation for ilObjTestSettingsGeneralGUI
        );
        
        if (!$this->isHiddenTab('mark_schema')) {
            $this->tabs->addSubTabTarget(
                'mark_schema',
                $DIC->ctrl()->getLinkTargetByClass('ilmarkschemagui', 'showMarkSchema'),
                '',
                array('ilmarkschemagui')
            );
        }
        
        // scoring subtab
        $this->tabs->addSubTabTarget(
            'scoring',
            $DIC->ctrl()->getLinkTargetByClass('ilObjTestSettingsScoringResultsGUI'),
            '',                                             // auto activation regardless from cmd
            array('ilobjtestsettingsscoringresultsgui')     // auto activation for ilObjTestSettingsScoringResultsGUI
        );
        
        // certificate subtab
        include_once "Services/Certificate/classes/class.ilCertificate.php";
        if (!$this->isHiddenTab('certificate') && ilCertificate::isActive()) {
            $this->tabs->addSubTabTarget(
                "certificate",
                $DIC->ctrl()->getLinkTargetByClass('ilObjTestGUI', 'certificate'),
                array("certificate", "certificateEditor", "certificateRemoveBackground", "certificateSave",
                    "certificatePreview", "certificateDelete", "certificateUpload", "certificateImport"),
                array("", "ilobjtestgui", "ilcertificategui")
            );
        }
        
        if (!$this->isHiddenTab('defaults')) {
            // defaults subtab
            $this->tabs->addSubTabTarget(
                "tst_default_settings",
                $DIC->ctrl()->getLinkTargetByClass('ilObjTestGUI', "defaults"),
                array("defaults", "deleteDefaults", "addDefaults", "applyDefaults"),
                array("", "ilobjtestgui", "ilcertificategui")
            );
        }
        
        $lti_settings = new ilLTIProviderObjectSettingGUI($this->testOBJ->getRefId());
        if ($lti_settings->hasSettingsAccess()) {
            $this->tabs->addSubTabTarget(
                'lti_provider',
                $DIC->ctrl()->getLinkTargetByClass(ilLTIProviderObjectSettingGUI::class),
                '',
                [ilLTIProviderObjectSettingGUI::class]
            );
        }
    }
    
    /**
     * @return bool
     */
    protected function needsDashboardTab()
    {
        if ($this->isHiddenTab(self::TAB_ID_EXAM_DASHBOARD)) {
            return false;
        }
        
        if (!$this->checkDashboardTabAccess()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @return bool
     */
    protected function needsTimeExtensionSubTab()
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
    protected function getDashboardTabTarget()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        return $DIC->ctrl()->getLinkTargetByClass(array('ilTestDashboardGUI', 'ilTestParticipantsGUI'));
    }
    
    public function getDashboardSubTabs()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if (!$this->testAccess->checkManageParticipantsAccess()) {
            return;
        }
        
        $this->tabs->addSubTab(
            self::SUBTAB_ID_FIXED_PARTICIPANTS,
            $this->getDashbardParticipantsSubTabLabel(),
            $DIC->ctrl()->getLinkTargetByClass('ilTestParticipantsGUI')
        );
        
        if ($this->needsTimeExtensionSubTab()) {
            $this->tabs->addSubTab(
                self::SUBTAB_ID_TIME_EXTENSION,
                $DIC->language()->txt('timing'),
                $DIC->ctrl()->getLinkTargetByClass('ilTestParticipantsTimeExtensionGUI')
            );
        }
    }
    
    protected function getDashbardParticipantsSubTabLabel()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($this->getTestOBJ()->getFixedParticipants()) {
            return $DIC->language()->txt('fixedparticipants_subtab');
        }
        
        return $DIC->language()->txt('autoparticipants_subtab');
    }
    
    /**
     * @return bool
     */
    protected function needsResultsTab()
    {
        return $this->needsParticipantsResultsSubTab() || $this->testOBJ->isScoreReportingEnabled() || $this->needsMySolutionsSubTab();
    }
    
    /**
     * @return string
     */
    protected function getResultsTabTarget()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if ($this->needsParticipantsResultsSubTab()) {
            return $DIC->ctrl()->getLinkTargetByClass(array('ilTestResultsGUI', 'ilParticipantsTestResultsGUI'));
        }
        
        if ($this->needsLoResultsSubTab()) {
            return $DIC->ctrl()->getLinkTargetByClass(array('ilTestResultsGUI', 'ilTestEvalObjectiveOrientedGUI'));
        }
        
        if ($this->needsMyResultsSubTab()) {
            return $DIC->ctrl()->getLinkTargetByClass(array('ilTestResultsGUI', 'ilMyTestResultsGUI', 'ilTestEvaluationGUI'));
        }

        if ($this->needsMySolutionsSubTab()) {
            return $DIC->ctrl()->getLinkTargetByClass(array('ilTestResultsGUI', 'ilMyTestSolutionsGUI', 'ilTestEvaluationGUI'));
        }

        return $DIC->ctrl()->getLinkTargetByClass('ilTestResultsGUI');
    }
    
    /**
     * @return bool
     */
    public function needsMyResultsSubTab()
    {
        return $this->getTestSession()->reportableResultsAvailable($this->getTestOBJ());
    }
    
    /**
     * @return bool
     */
    public function needsLoResultsSubTab()
    {
        if (!$this->needsMyResultsSubTab()) {
            return false;
        }
        
        return $this->objectiveParent->isObjectiveOrientedPresentationRequired();
    }
    
    /**
     * @return bool
     */
    public function needsParticipantsResultsSubTab()
    {
        if ($this->testAccess->checkManageParticipantsAccess()) {
            return true;
        }
        
        if ($this->testAccess->checkParticipantsResultsAccess()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @return bool
     */
    public function needsHighSoreSubTab()
    {
        if (!$this->needsMyResultsSubTab()) {
            return false;
        }
        
        return $this->getTestOBJ()->getHighscoreEnabled();
    }
    
    /**
     * @return bool
     */
    public function needsSkillResultsSubTab()
    {
        if (!$this->needsMyResultsSubTab()) {
            return false;
        }
        
        return $this->getTestOBJ()->isSkillServiceToBeConsidered();
    }
    
    public function needsMySolutionsSubTab()
    {
        return $this->getTestOBJ()->canShowSolutionPrintview($this->getTestSession()->getUserId());
    }
    
    public function getResultsSubTabs()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if ($this->needsParticipantsResultsSubTab()) {
            $this->tabs->addSubTab(
                self::SUBTAB_ID_PARTICIPANTS_RESULTS,
                $DIC->language()->txt('participants_results_subtab'),
                $DIC->ctrl()->getLinkTargetByClass(array('ilTestResultsGUI', 'ilParticipantsTestResultsGUI'))
            );
        }
        
        if ($this->needsLoResultsSubTab()) {
            $this->tabs->addSubTab(
                self::SUBTAB_ID_LO_RESULTS,
                $DIC->language()->txt('tst_tab_results_objective_oriented'),
                $DIC->ctrl()->getLinkTargetByClass(array('ilTestResultsGUI', 'ilTestEvalObjectiveOrientedGUI'))
            );
        }
        
        if ($this->needsMyResultsSubTab()) {
            $myResultsLabel = $DIC->language()->txt('tst_show_results');
            
            if ($this->needsLoResultsSubTab()) {
                $myResultsLabel = $DIC->language()->txt('tst_tab_results_pass_oriented');
            }
            
            $this->tabs->addSubTab(
                self::SUBTAB_ID_MY_RESULTS,
                $myResultsLabel,
                $DIC->ctrl()->getLinkTargetByClass(array('ilTestResultsGUI', 'ilMyTestResultsGUI', 'ilTestEvaluationGUI'))
                // 'ilTestEvaluationGUI' => 'outUserResultsOverview'
            );
        }
        
        if ($this->needsSkillResultsSubTab()) {
            $this->tabs->addSubTab(
                self::SUBTAB_ID_SKILL_RESULTS,
                $DIC->language()->txt('tst_show_comp_results'),
                $DIC->ctrl()->getLinkTargetByClass(array('ilTestResultsGUI', 'ilTestSkillEvaluationGUI'))
            );
        }
        
        if ($this->needsHighSoreSubTab()) {
            $this->tabs->addSubTab(
                self::SUBTAB_ID_HIGHSCORE,
                $DIC->language()->txt('tst_show_toplist'),
                $DIC->ctrl()->getLinkTargetByClass(array('ilTestResultsGUI', 'ilTestToplistGUI'), 'outResultsToplist')
            );
        }
        
        if ($this->needsMySolutionsSubTab()) {
            $this->tabs->addSubTab(
                self::SUBTAB_ID_MY_SOLUTIONS,
                $DIC->language()->txt('tst_list_of_answers_show'),
                $DIC->ctrl()->getLinkTargetByClass(array('ilTestResultsGUI', 'ilMyTestSolutionsGUI', 'ilTestEvaluationGUI'))
            );
        }
    }
}
