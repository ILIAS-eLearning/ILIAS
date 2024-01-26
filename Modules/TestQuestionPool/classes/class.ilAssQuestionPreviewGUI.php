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

use ILIAS\DI\RBACServices;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 *
 * @ilCtrl_Calls ilAssQuestionPreviewGUI: ilAssQuestionPreviewToolbarGUI
 * @ilCtrl_Calls ilAssQuestionPreviewGUI: ilAssQuestionRelatedNavigationBarGUI
 * @ilCtrl_Calls ilAssQuestionPreviewGUI: ilAssQuestionHintRequestGUI
 * @ilCtrl_Calls ilAssQuestionPreviewGUI: ilAssGenFeedbackPageGUI
 * @ilCtrl_Calls ilAssQuestionPreviewGUI: ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilAssQuestionPreviewGUI: ilNoteGUI
 */
class ilAssQuestionPreviewGUI
{
    const CMD_SHOW = 'show';
    const CMD_RESET = 'reset';
    const CMD_STATISTICS = 'assessment';
    const CMD_INSTANT_RESPONSE = 'instantResponse';
    const CMD_HANDLE_QUESTION_ACTION = 'handleQuestionAction';
    const CMD_GATEWAY_CONFIRM_HINT_REQUEST = 'gatewayConfirmHintRequest';
    const CMD_GATEWAY_SHOW_HINT_LIST = 'gatewayShowHintList';

    const TAB_ID_QUESTION = 'question';

    const FEEDBACK_FOCUS_ANCHOR = 'focus';

    /**
     * @var RBACServices
     */
    private $rbac_services;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilGlobalTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var assQuestionGUI
     */
    protected $questionGUI;

    /**
     * @var assQuestion
     */
    protected $questionOBJ;

    /**
     * @var ilAssQuestionPreviewSettings
     */
    protected $previewSettings;

    /**
     * @var ilAssQuestionPreviewSession
     */
    protected $previewSession;

    /**
     * @var ilAssQuestionPreviewHintTracking
     */
    protected $hintTracking;

    public function __construct(
        ilCtrl $ctrl,
        ilTabsGUI $tabs,
        ilGlobalTemplateInterface $tpl,
        ilLanguage $lng,
        ilDBInterface $db,
        ilObjUser $user,
        RBACServices $rbac_services
    ) {
        $this->ctrl = $ctrl;
        $this->tabs = $tabs;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->db = $db;
        $this->user = $user;
        $this->rbac_services = $rbac_services;

        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());
    }

    public function initQuestion($questionId, $parentObjId)
    {
        require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';

        $this->questionGUI = assQuestion::instantiateQuestionGUI($questionId);
        $this->questionOBJ = $this->questionGUI->object;

        $this->questionOBJ->setObjId($parentObjId);

        if ($this->ctrl->getCmd() == 'editQuestion') {
            $this->questionGUI->setQuestionTabs();
        } else {
            if ($_GET["q_id"]) {
                $this->tabs->clearTargets();
                $this->tabs->addTarget(
                    ilAssQuestionPreviewGUI::TAB_ID_QUESTION,
                    $this->ctrl->getLinkTargetByClass('ilAssQuestionPreviewGUI', ilAssQuestionPreviewGUI::CMD_SHOW),
                    array(),
                    array('ilAssQuestionPreviewGUI')
                );
                // Assessment of questions sub menu entry
                $q_type = $this->questionOBJ->getQuestionType();
                $classname = $q_type . "GUI";
                $this->tabs->addTarget(
                    "statistics",
                    $this->ctrl->getLinkTargetByClass('ilAssQuestionPreviewGUI', "assessment"),
                    array("assessment"),
                    $classname,
                    ""
                );
                if (($_GET["calling_test"] > 0) || ($_GET["test_ref_id"] > 0)) {
                    $ref_id = $_GET["calling_test"];
                    if (strlen($ref_id) == 0) {
                        $ref_id = $_GET["test_ref_id"];
                    }

                    if (!$_GET['test_express_mode'] && !$GLOBALS['___test_express_mode']) {
                        $this->tabs->setBackTarget(
                            $this->lng->txt("backtocallingtest"),
                            "ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=$ref_id"
                        );
                    //BACK FROM Question Page to Test
                    } else {
                        $link = ilTestExpressPage::getReturnToPageLink();
                        //$this->tabs->setBackTarget($this->lng->txt("backtocallingtest"), $link);
                        $this->tabs->setBackTarget(
                            $this->lng->txt("backtocallingtest"),
                            "ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=$ref_id"
                        );
                    }
                } elseif (isset($_GET['calling_consumer']) && (int) $_GET['calling_consumer']) {
                    $ref_id = (int) $_GET['calling_consumer'];
                    $consumer = ilObjectFactory::getInstanceByRefId($ref_id);
                    if ($consumer instanceof ilQuestionEditingFormConsumer) {
                        $this->tabs->setBackTarget(
                            $consumer->getQuestionEditingFormBackTargetLabel(),
                            $consumer->getQuestionEditingFormBackTarget($_GET['consumer_context'])
                        );
                    } else {
                        require_once 'Services/Link/classes/class.ilLink.php';
                        $this->tabs->setBackTarget($this->lng->txt("qpl"), ilLink::_getLink($ref_id));
                    }
                    //} elseif (true) {
                    // We're in the underworld and want to go back to the question page
                } else {
                    $this->tabs->setBackTarget($this->lng->txt("backtocallingpool"), $this->ctrl->getLinkTargetByClass("ilobjquestionpoolgui", "questions"));
                    //BACK FROM Question Page to Pool
                }
            }
        }
        $this->questionGUI->outAdditionalOutput();

        $this->questionGUI->populateJavascriptFilesRequiredForWorkForm($this->tpl);
        $this->questionOBJ->setOutputType(OUTPUT_JAVASCRIPT); // TODO: remove including depending stuff

        $this->questionGUI->setTargetGui($this);
        $this->questionGUI->setQuestionActionCmd(self::CMD_HANDLE_QUESTION_ACTION);

        $this->questionGUI->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_DEMOPLAY);
    }

    public function initPreviewSettings($parentRefId)
    {
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionPreviewSettings.php';
        $this->previewSettings = new ilAssQuestionPreviewSettings($parentRefId);

        $this->previewSettings->init();
    }

    public function initPreviewSession($userId, $questionId)
    {
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionPreviewSession.php';
        $this->previewSession = new ilAssQuestionPreviewSession($userId, $questionId);

        $this->previewSession->init();
    }

    public function initHintTracking()
    {
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionPreviewHintTracking.php';
        $this->hintTracking = new ilAssQuestionPreviewHintTracking($this->db, $this->previewSession);
    }

    public function initStyleSheets()
    {
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");

        $this->tpl->setCurrentBlock("ContentStyle");
        $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
        $this->tpl->parseCurrentBlock();
    }

    public function executeCommand()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilHelp = $DIC['ilHelp']; /* @var ilHelpGUI $ilHelp */
        $ilHelp->setScreenIdComponent('qpl');

        $this->tabs->setTabActive(self::TAB_ID_QUESTION);

        $this->lng->loadLanguageModule('content');

        $nextClass = $this->ctrl->getNextClass($this);

        switch ($nextClass) {
            case 'ilassquestionhintrequestgui':
                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';
                $gui = new ilAssQuestionHintRequestGUI($this, self::CMD_SHOW, $this->questionGUI, $this->hintTracking);

                $this->ctrl->forwardCommand($gui);

                break;

            case 'ilassspecfeedbackpagegui':
            case 'ilassgenfeedbackpagegui':
                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionFeedbackPageObjectCommandForwarder.php';
                $forwarder = new ilAssQuestionFeedbackPageObjectCommandForwarder($this->questionOBJ, $this->ctrl, $this->tabs, $this->lng);
                $forwarder->forward();
                break;

            case 'ilnotegui':
                $notesGUI = new ilNoteGUI(
                    $this->questionOBJ->getObjId(),
                    $this->questionOBJ->getId(),
                    'quest',
                    false,
                    0,
                    false
                );
                $notesGUI->enablePublicNotes(true);
                $notesGUI->enablePublicNotesDeletion(true);
                $notesPanelHTML = $this->ctrl->forwardCommand($notesGUI);
                $this->showCmd($notesPanelHTML);
                break;


            default:

                $cmd = $this->ctrl->getCmd(self::CMD_SHOW) . 'Cmd';

                $this->$cmd();
        }
    }

    /**
     * @return string
     */
    protected function buildPreviewFormAction()
    {
        return $this->ctrl->getFormAction($this, self::CMD_SHOW) . '#' . self::FEEDBACK_FOCUS_ANCHOR;
    }

    protected function isCommentingRequired()
    {
        if ($this->previewSettings->isTestRefId()) {
            return false;
        }
        return (bool) $this->rbac_services->system()->checkAccess('write', (int) $_GET['ref_id']);
    }

    private function showCmd($notesPanelHTML = '')
    {
        $tpl = new ilTemplate('tpl.qpl_question_preview.html', true, true, 'Modules/TestQuestionPool');

        $tpl->setVariable('PREVIEW_FORMACTION', $this->buildPreviewFormAction());

        $this->populatePreviewToolbar($tpl);

        $this->populateQuestionOutput($tpl);

        $this->handleInstantResponseRendering($tpl);

        if ($this->isCommentingRequired()) {
            $this->populateNotesPanel($tpl, $notesPanelHTML);
        }

        $this->tpl->setContent($tpl->get());
    }

    private function assessmentCmd()
    {
        $this->tabs->activateTab('statistics');
        $this->questionGUI->assessment();
    }

    /**
     * @see ilTestPlayerAbstractGUI::populateInstantResponseBlocks()
     */
    protected function handleInstantResponseRendering(ilTemplate $tpl)
    {
        $response_required = false;
        $response_available = false;
        $jump_to_response = false;

        if ($this->isShowReachedPointsRequired()) {
            $this->populateReachedPointsOutput($tpl);
            $response_required = true;
            $response_available = true;
            $jump_to_response = true;
        }

        if ($this->isShowBestSolutionRequired()) {
            $this->populateSolutionOutput($tpl);
            $response_required = true;
            $response_available = true;
            $jump_to_response = true;
        }

        if ($this->isShowGenericQuestionFeedbackRequired()) {
            $response_required = true;
            if ($this->populateGenericQuestionFeedback($tpl)) {
                $response_available = true;
                $jump_to_response = true;
            }
        }

        if ($this->isShowSpecificQuestionFeedbackRequired()) {
            $response_required = true;

            if ($this->questionGUI->hasInlineFeedback()) {
                // Don't jump to the feedback below the question if some feedback is shown within the question
                $jump_to_response = false;
            } else {
                if ($this->populateSpecificQuestionFeedback($tpl)) {
                    $response_available = true;
                    $jump_to_response = true;
                }
            }
        }

        if ($response_required) {
            $this->populateInstantResponseHeader($tpl, $jump_to_response);
            if (!$response_available) {
                if ($this->questionGUI->hasInlineFeedback()) {
                    $this->populateInstantResponseMessage(
                        $tpl,
                        $this->lng->txt('tst_feedback_is_given_inline')
                    );
                } else {
                    $this->populateInstantResponseMessage(
                        $tpl,
                        $this->lng->txt('tst_feedback_not_available_for_answer')
                    );
                }
            }
        }
    }

    private function resetCmd()
    {
        $this->previewSession->setRandomizerSeed(null);
        $this->previewSession->setParticipantsSolution(null);
        $this->previewSession->resetRequestedHints();
        $this->previewSession->setInstantResponseActive(false);

        ilUtil::sendInfo($this->lng->txt('qst_preview_reset_msg'), true);

        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    private function instantResponseCmd()
    {
        if ($this->saveQuestionSolution()) {
            $this->previewSession->setInstantResponseActive(true);
        } else {
            $this->previewSession->setInstantResponseActive(false);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    private function handleQuestionActionCmd()
    {
        $this->questionOBJ->persistPreviewState($this->previewSession);
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    private function populatePreviewToolbar(ilTemplate $tpl)
    {
        $toolbarGUI = new ilAssQuestionPreviewToolbarGUI($this->lng);

        $toolbarGUI->setFormAction($this->ctrl->getFormAction($this, self::CMD_SHOW));
        $toolbarGUI->setResetPreviewCmd(self::CMD_RESET);

        // Check Permissions first, some Toolbar Actions are only available for write access
        if ($this->rbac_services->system()->checkAccess('write', (int) $_GET['ref_id'])) {
            $toolbarGUI->setEditPageCmd(
                $this->ctrl->getLinkTargetByClass('ilAssQuestionPageGUI', 'edit')
            );

            $toolbarGUI->setEditQuestionCmd(
                $this->ctrl->getLinkTargetByClass(
                    array('ilrepositorygui','ilobjquestionpoolgui', get_class($this->questionGUI)),
                    'editQuestion'
                )
            );
        }
        $toolbarGUI->build();

        $tpl->setVariable('PREVIEW_TOOLBAR', $this->ctrl->getHTML($toolbarGUI));
    }

    private function populateQuestionOutput(ilTemplate $tpl)
    {
        // FOR WHAT EXACTLY IS THIS USEFUL?
        $this->ctrl->setReturnByClass('ilAssQuestionPageGUI', 'view');
        $this->ctrl->setReturnByClass('ilObjQuestionPoolGUI', 'questions');

        include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php");
        $pageGUI = new ilAssQuestionPageGUI($this->questionOBJ->getId());
        $pageGUI->setRenderPageContainer(false);
        $pageGUI->setEditPreview(true);
        $pageGUI->setEnabledTabs(false);

        // FOR WHICH SITUATION IS THIS WORKAROUND NECCESSARY? (sure .. imagemaps, but where this can be done?)
        if (strlen($this->ctrl->getCmd()) == 0 && !isset($_POST['editImagemapForward_x'])) { // workaround for page edit imagemaps, keep in mind
            $this->ctrl->setCmdClass(get_class($pageGUI));
            $this->ctrl->setCmd('preview');
        }

        $this->questionGUI->setPreviewSession($this->previewSession);
        $this->questionGUI->object->setShuffler($this->getQuestionAnswerShuffler());

        $questionHtml = $this->questionGUI->getPreview(true, $this->isShowSpecificQuestionFeedbackRequired());
        $this->questionGUI->magicAfterTestOutput();

        $questionHtml .= $this->getQuestionNavigationHtml();

        $pageGUI->setQuestionHTML(array($this->questionOBJ->getId() => $questionHtml));

        //$pageGUI->setHeader($this->questionOBJ->getTitle()); // NO ADDITIONAL HEADER
        $pageGUI->setPresentationTitle($this->questionOBJ->getTitle());

        //$pageGUI->setTemplateTargetVar("ADM_CONTENT"); // NOT REQUIRED, OR IS?

        $tpl->setVariable('QUESTION_OUTPUT', $pageGUI->preview());
    }

    protected function populateReachedPointsOutput(ilTemplate $tpl)
    {
        $reachedPoints = $this->questionOBJ->calculateReachedPointsFromPreviewSession($this->previewSession);
        $maxPoints = $this->questionOBJ->getMaximumPoints();

        $scoreInformation = sprintf(
            $this->lng->txt("you_received_a_of_b_points"),
            $reachedPoints,
            $maxPoints
        );

        $tpl->setCurrentBlock("reached_points_feedback");
        $tpl->setVariable("REACHED_POINTS_FEEDBACK", $scoreInformation);
        $tpl->parseCurrentBlock();
    }

    private function populateSolutionOutput(ilTemplate $tpl)
    {
        // FOR WHAT EXACTLY IS THIS USEFUL?
        $this->ctrl->setReturnByClass('ilAssQuestionPageGUI', 'view');
        $this->ctrl->setReturnByClass('ilObjQuestionPoolGUI', 'questions');

        include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php");
        $pageGUI = new ilAssQuestionPageGUI($this->questionOBJ->getId());

        $pageGUI->setEditPreview(true);
        $pageGUI->setEnabledTabs(false);

        // FOR WHICH SITUATION IS THIS WORKAROUND NECCESSARY? (sure .. imagemaps, but where this can be done?)
        if (strlen($this->ctrl->getCmd()) == 0 && !isset($_POST['editImagemapForward_x'])) { // workaround for page edit imagemaps, keep in mind
            $this->ctrl->setCmdClass(get_class($pageGUI));
            $this->ctrl->setCmd('preview');
        }

        $this->questionGUI->setPreviewSession($this->previewSession);

        $pageGUI->setQuestionHTML(array($this->questionOBJ->getId() => $this->questionGUI->getSolutionOutput(0, null, false, false, true, false, true, false, false)));

        //$pageGUI->setHeader($this->questionOBJ->getTitle()); // NO ADDITIONAL HEADER
        //$pageGUI->setPresentationTitle($this->questionOBJ->getTitle());

        //$pageGUI->setTemplateTargetVar("ADM_CONTENT"); // NOT REQUIRED, OR IS?

        $output = $this->questionGUI->getSolutionOutput(0, null, false, false, true, false, true, false, false);
        //$output = $pageGUI->preview();
        //$output = str_replace('<h1 class="ilc_page_title_PageTitle"></h1>', '', $output);

        $tpl->setCurrentBlock('solution_output');
        $tpl->setVariable('TXT_CORRECT_SOLUTION', $this->lng->txt('tst_best_solution_is'));
        $tpl->setVariable('SOLUTION_OUTPUT', $output);
        $tpl->parseCurrentBlock();
    }

    private function getQuestionNavigationHtml()
    {
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionRelatedNavigationBarGUI.php';
        $navGUI = new ilAssQuestionRelatedNavigationBarGUI($this->ctrl, $this->lng);

        $navGUI->setInstantResponseCmd(self::CMD_INSTANT_RESPONSE);
        $navGUI->setHintRequestCmd(self::CMD_GATEWAY_CONFIRM_HINT_REQUEST);
        $navGUI->setHintListCmd(self::CMD_GATEWAY_SHOW_HINT_LIST);

        $navGUI->setInstantResponseEnabled($this->previewSettings->isInstantFeedbackNavigationRequired());
        $navGUI->setHintProvidingEnabled($this->previewSettings->isHintProvidingEnabled());

        $navGUI->setHintRequestsPossible($this->hintTracking->requestsPossible());
        $navGUI->setHintRequestsExist($this->hintTracking->requestsExist());

        return $this->ctrl->getHTML($navGUI);
    }

    /**
     * Populate the block for an instant generic feedback
     * @return bool     true, if there is some feedback populated
     */
    private function populateGenericQuestionFeedback(ilTemplate $tpl) : bool
    {
        if ($this->questionOBJ->isPreviewSolutionCorrect($this->previewSession)) {
            $feedback = $this->questionGUI->getGenericFeedbackOutputForCorrectSolution();
            $cssClass = ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_CORRECT;
        } else {
            $feedback = $this->questionGUI->getGenericFeedbackOutputForIncorrectSolution();
            $cssClass = ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_WRONG;
        }

        if (strlen($feedback)) {
            $tpl->setCurrentBlock('instant_feedback_generic');
            $tpl->setVariable('GENERIC_FEEDBACK', $feedback);
            $tpl->setVariable('ILC_FB_CSS_CLASS', $cssClass);
            $tpl->parseCurrentBlock();
            return true;
        }
        return false;
    }

    /**
     * Populate the block for an instant specific feedback
     * @return bool     true, if there is some feedback populated
     */
    private function populateSpecificQuestionFeedback(ilTemplate $tpl) : bool
    {
        $fb = $this->questionGUI->getSpecificFeedbackOutput(
            (array) $this->previewSession->getParticipantsSolution()
        );

        if (!empty($fb)) {
            $tpl->setCurrentBlock('instant_feedback_specific');
            $tpl->setVariable('ANSWER_FEEDBACK', $fb);
            $tpl->parseCurrentBlock();
            return true;
        }
        return false;
    }

    protected function populateInstantResponseHeader(ilTemplate $tpl, $withFocusAnchor)
    {
        if ($withFocusAnchor) {
            $tpl->setCurrentBlock('inst_resp_id');
            $tpl->setVariable('INSTANT_RESPONSE_FOCUS_ID', self::FEEDBACK_FOCUS_ANCHOR);
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock('instant_response_header');
        $tpl->setVariable('INSTANT_RESPONSE_HEADER', $this->lng->txt('tst_feedback'));
        $tpl->parseCurrentBlock();
    }

    protected function populateInstantResponseMessage(ilTemplate $tpl, string $a_message)
    {
        $tpl->setCurrentBlock('instant_response_message');
        $tpl->setVariable('INSTANT_RESPONSE_MESSAGE', $a_message);
        $tpl->parseCurrentBlock();
    }

    private function isShowBestSolutionRequired()
    {
        if (!$this->previewSettings->isBestSolutionEnabled()) {
            return false;
        }

        return $this->previewSession->isInstantResponseActive();
    }

    private function isShowGenericQuestionFeedbackRequired()
    {
        if (!$this->previewSettings->isGenericFeedbackEnabled()) {
            return false;
        }

        return $this->previewSession->isInstantResponseActive();
    }

    private function isShowSpecificQuestionFeedbackRequired()
    {
        if (!$this->previewSettings->isSpecificFeedbackEnabled()) {
            return false;
        }

        return $this->previewSession->isInstantResponseActive();
    }

    private function isShowReachedPointsRequired()
    {
        if (!$this->previewSettings->isReachedPointsEnabled()) {
            return false;
        }

        return $this->previewSession->isInstantResponseActive();
    }

    public function saveQuestionSolution()
    {
        return $this->questionOBJ->persistPreviewState($this->previewSession);
    }

    public function gatewayConfirmHintRequestCmd()
    {
        if (!$this->saveQuestionSolution()) {
            $this->previewSession->setInstantResponseActive(false);
            $this->showCmd();
            return;
        }

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';

        $this->ctrl->redirectByClass(
            'ilAssQuestionHintRequestGUI',
            ilAssQuestionHintRequestGUI::CMD_CONFIRM_REQUEST
        );
    }

    public function gatewayShowHintListCmd()
    {
        if (!$this->saveQuestionSolution()) {
            $this->previewSession->setInstantResponseActive(false);
            $this->showCmd();
            return;
        }

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintRequestGUI.php';

        $this->ctrl->redirectByClass(
            'ilAssQuestionHintRequestGUI',
            ilAssQuestionHintRequestGUI::CMD_SHOW_LIST
        );
    }

    /**
     * @return ilArrayElementShuffler
     */
    private function getQuestionAnswerShuffler()
    {
        require_once 'Services/Randomization/classes/class.ilArrayElementShuffler.php';
        $shuffler = new ilArrayElementShuffler();

        if (!$this->previewSession->randomizerSeedExists()) {
            $this->previewSession->setRandomizerSeed($shuffler->buildRandomSeed());
        }

        $shuffler->setSeed($this->previewSession->getRandomizerSeed());

        return $shuffler;
    }

    protected function populateNotesPanel(ilTemplate $tpl, $notesPanelHTML)
    {
        if (!strlen($notesPanelHTML)) {
            $notesPanelHTML = $this->questionGUI->getNotesHTML();
        }

        $tpl->setCurrentBlock('notes_panel');
        $tpl->setVariable('NOTES_PANEL', $notesPanelHTML);
        $tpl->parseCurrentBlock();
    }
}
