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

use ILIAS\Refinery\Random\Group as RandomGroup;
use ILIAS\Refinery\Random\Seed\RandomSeed;
use ILIAS\Refinery\Random\Seed\GivenSeed;
use ILIAS\Refinery\Transformation;
use ILIAS\GlobalScreen\Services as GlobalScreen;

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
 * @ilCtrl_Calls ilAssQuestionPreviewGUI: ilCommentGUI
 */
class ilAssQuestionPreviewGUI
{
    public const CMD_SHOW = 'show';
    public const CMD_RESET = 'reset';
    public const CMD_STATISTICS = 'assessment';
    public const CMD_INSTANT_RESPONSE = 'instantResponse';
    public const CMD_HANDLE_QUESTION_ACTION = 'handleQuestionAction';
    public const CMD_GATEWAY_CONFIRM_HINT_REQUEST = 'gatewayConfirmHintRequest';
    public const CMD_GATEWAY_SHOW_HINT_LIST = 'gatewayShowHintList';

    public const TAB_ID_QUESTION = 'question';

    public const FEEDBACK_FOCUS_ANCHOR = 'focus';

    private assQuestionGUI $questionGUI;
    private assQuestion $questionOBJ;
    private ?ilAssQuestionPreviewSettings $previewSettings = null;
    private ?ilAssQuestionPreviewSession $previewSession = null;
    private ?ilAssQuestionPreviewHintTracking $hintTracking = null;

    public function __construct(
        private ilCtrl $ctrl,
        private ilRbacSystem $rbac_system,
        private ilTabsGUI $tabs,
        private ilGlobalTemplateInterface $tpl,
        private ilLanguage $lng,
        private ilDBInterface $db,
        private ilObjUser $user,
        private RandomGroup $randomGroup,
        private GlobalScreen $global_screen
    ) {
        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());
    }

    public function initQuestion($questionId, $parentObjId): void
    {
        $this->questionGUI = assQuestion::instantiateQuestionGUI($questionId);
        $this->questionOBJ = $this->questionGUI->object;

        $this->questionOBJ->setObjId($parentObjId);

        if ($this->ctrl->getCmd() === 'editQuestion') {
            $this->questionGUI->setQuestionTabs();
        } else {
            if ($_GET["q_id"]) {
                $this->tabs->clearTargets();
                $this->tabs->addTarget(
                    self::TAB_ID_QUESTION,
                    $this->ctrl->getLinkTargetByClass('ilAssQuestionPreviewGUI', self::CMD_SHOW),
                    '',
                    [strtolower(__CLASS__)]
                );
                // Assessment of questions sub menu entry
                $q_type = $this->questionOBJ->getQuestionType();
                $classname = $q_type . "GUI";
                $this->tabs->addTarget(
                    "statistics",
                    $this->ctrl->getLinkTargetByClass('ilAssQuestionPreviewGUI', "assessment"),
                    ["assessment"],
                    $classname,
                    ""
                );
                if ((isset($_GET['calling_test']) && strlen($_GET['calling_test']) !== 0) ||
                    (isset($_GET['test_ref_id']) && strlen($_GET['test_ref_id']) !== 0)) {
                    $ref_id = $_GET['calling_test'];
                    if (strlen($ref_id) !== 0 && !is_numeric($ref_id)) {
                        $ref_id_array = explode('_', $ref_id);
                        $ref_id = array_pop($ref_id_array);
                    }

                    if (strlen($ref_id) === 0) {
                        $ref_id = $_GET['test_ref_id'];
                    }

                    $link = ilTestExpressPage::getReturnToPageLink();
                    $this->tabs->setBackTarget(
                        $this->lng->txt("backtocallingtest"),
                        "ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=$ref_id"
                    );
                } else {
                    $this->ctrl->clearParameterByClass(ilObjQuestionPoolGUI::class, 'q_id');
                    $this->tabs->setBackTarget($this->lng->txt("backtocallingpool"), $this->ctrl->getLinkTargetByClass(ilObjQuestionPoolGUI::class, "questions"));
                    $this->ctrl->setParameterByClass(ilObjQuestionPoolGUI::class, 'q_id', $questionId);
                }
            }
        }
        $this->questionGUI->outAdditionalOutput();

        $this->questionGUI->populateJavascriptFilesRequiredForWorkForm($this->tpl);
        $this->questionGUI->setTargetGui($this);
        $this->questionGUI->setQuestionActionCmd(self::CMD_HANDLE_QUESTION_ACTION);

        $this->questionGUI->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_DEMOPLAY);
    }

    public function initPreviewSettings($parentRefId): void
    {
        $this->previewSettings = new ilAssQuestionPreviewSettings($parentRefId);

        $this->previewSettings->init();
    }

    public function initPreviewSession($userId, $questionId): void
    {
        $this->previewSession = new ilAssQuestionPreviewSession($userId, $questionId);

        $this->previewSession->init();
    }

    public function initHintTracking(): void
    {
        $this->hintTracking = new ilAssQuestionPreviewHintTracking($this->db, $this->previewSession);
    }

    public function initStyleSheets(): void
    {
        $this->tpl->setCurrentBlock("ContentStyle");
        $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
        $this->tpl->parseCurrentBlock();
    }

    public function executeCommand(): void
    {
        global $DIC;
        $ilHelp = $DIC['ilHelp'];
        $ilHelp->setScreenIdComponent('qpl');

        $this->tabs->setTabActive(self::TAB_ID_QUESTION);

        $this->lng->loadLanguageModule('content');

        $nextClass = $this->ctrl->getNextClass($this);

        switch ($nextClass) {
            case 'ilassquestionhintrequestgui':
                $gui = new ilAssQuestionHintRequestGUI(
                    $this,
                    self::CMD_SHOW,
                    $this->questionGUI,
                    $this->hintTracking,
                    $this->ctrl,
                    $this->lng,
                    $this->tpl,
                    $this->tabs,
                    $this->global_screen
                );
                $this->ctrl->forwardCommand($gui);
                break;
            case 'ilassspecfeedbackpagegui':
            case 'ilassgenfeedbackpagegui':
                $forwarder = new ilAssQuestionFeedbackPageObjectCommandForwarder($this->questionOBJ, $this->ctrl, $this->tabs, $this->lng);
                $forwarder->forward();
                break;
            case 'ilcommentgui':
                $comment_gui = new ilCommentGUI($this->questionOBJ->getObjId(), $this->questionOBJ->getId(), 'quest');
                $comments_panel_html = $this->ctrl->forwardCommand($comment_gui);
                $this->showCmd($comments_panel_html);
                break;
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_SHOW) . 'Cmd';
                $this->$cmd();
        }
    }

    /**
     * @return string
     */
    protected function buildPreviewFormAction(): string
    {
        return $this->ctrl->getFormAction($this, self::CMD_SHOW) . '#' . self::FEEDBACK_FOCUS_ANCHOR;
    }

    protected function isCommentingRequired(): bool
    {
        if ($this->previewSettings->isTestRefId()) {
            return false;
        }

        return (bool) $this->rbac_system->checkAccess('write', (int) $_GET['ref_id']);
    }

    private function showCmd(string $notes_panel_html = ''): void
    {
        $tpl = new ilTemplate('tpl.qpl_question_preview.html', true, true, 'Modules/TestQuestionPool');
        $tpl->setVariable('PREVIEW_FORMACTION', $this->buildPreviewFormAction());

        $this->populatePreviewToolbar($tpl);
        $this->populateQuestionOutput($tpl);
        $this->handleInstantResponseRendering($tpl);

        if ($this->isCommentingRequired()) {
            $this->populateCommentsPanel($tpl, $notes_panel_html);
        }

        $this->tpl->setContent($tpl->get());
    }

    private function assessmentCmd()
    {
        $this->tabs->activateTab('statistics');
        $this->questionGUI->assessment();
    }

    protected function handleInstantResponseRendering(ilTemplate $tpl): void
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

    private function resetCmd(): void
    {
        $this->previewSession->setRandomizerSeed(null);
        $this->previewSession->setParticipantsSolution(null);
        $this->previewSession->resetRequestedHints();
        $this->previewSession->setInstantResponseActive(false);

        $this->tpl->setOnScreenMessage('info', $this->lng->txt('qst_preview_reset_msg'), true);

        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    private function instantResponseCmd(): void
    {
        if ($this->saveQuestionSolution()) {
            $this->previewSession->setInstantResponseActive(true);
        } else {
            $this->previewSession->setInstantResponseActive(false);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    private function handleQuestionActionCmd(): void
    {
        $this->questionOBJ->persistPreviewState($this->previewSession);
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    private function populatePreviewToolbar(ilTemplate $tpl): void
    {
        $toolbarGUI = new ilAssQuestionPreviewToolbarGUI($this->lng);

        $toolbarGUI->setFormAction($this->ctrl->getFormAction($this, self::CMD_SHOW));
        $toolbarGUI->setResetPreviewCmd(self::CMD_RESET);

        // Check Permissions first, some Toolbar Actions are only available for write access
        if ($this->rbac_system->checkAccess('write', (int) $_GET['ref_id'])) {
            $toolbarGUI->setEditPageCmd(
                $this->ctrl->getLinkTargetByClass('ilAssQuestionPageGUI', 'edit')
            );

            $toolbarGUI->setEditQuestionCmd(
                $this->ctrl->getLinkTargetByClass(
                    ['ilrepositorygui','ilobjquestionpoolgui', get_class($this->questionGUI)],
                    'editQuestion'
                )
            );
        }

        $toolbarGUI->build();

        $tpl->setVariable('PREVIEW_TOOLBAR', $this->ctrl->getHTML($toolbarGUI));
    }

    private function populateQuestionOutput(ilTemplate $tpl): void
    {
        // FOR WHAT EXACTLY IS THIS USEFUL?
        $this->ctrl->setReturnByClass('ilAssQuestionPageGUI', 'view');
        $this->ctrl->setReturnByClass('ilObjQuestionPoolGUI', 'questions');

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

        $pageGUI->setQuestionHTML([$this->questionOBJ->getId() => $questionHtml]);

        $pageGUI->setPresentationTitle($this->questionOBJ->getTitle());

        $tpl->setVariable('QUESTION_OUTPUT', $pageGUI->preview());
        // \ilPageObjectGUI::preview sets an undefined tab, so the "question" tab has to be activated again
        $this->tabs->setTabActive(self::TAB_ID_QUESTION);
    }

    protected function populateReachedPointsOutput(ilTemplate $tpl): void
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

    private function populateSolutionOutput(ilTemplate $tpl): void
    {
        // FOR WHAT EXACTLY IS THIS USEFUL?
        $this->ctrl->setReturnByClass('ilAssQuestionPageGUI', 'view');
        $this->ctrl->setReturnByClass('ilObjQuestionPoolGUI', 'questions');

        $pageGUI = new ilAssQuestionPageGUI($this->questionOBJ->getId());

        $pageGUI->setEditPreview(true);
        $pageGUI->setEnabledTabs(false);

        // FOR WHICH SITUATION IS THIS WORKAROUND NECCESSARY? (sure .. imagemaps, but where this can be done?)
        if (strlen($this->ctrl->getCmd()) == 0 && !isset($_POST['editImagemapForward_x'])) { // workaround for page edit imagemaps, keep in mind
            $this->ctrl->setCmdClass(get_class($pageGUI));
            $this->ctrl->setCmd('preview');
        }

        $this->questionGUI->setPreviewSession($this->previewSession);

        $pageGUI->setQuestionHTML([$this->questionOBJ->getId() => $this->questionGUI->getSolutionOutput(0, null, false, false, true, false, true, false, false)]);

        $output = $this->questionGUI->getSolutionOutput(0, null, false, false, true, false, true, false, false);

        $tpl->setCurrentBlock('solution_output');
        $tpl->setVariable('TXT_CORRECT_SOLUTION', $this->lng->txt('tst_best_solution_is'));
        $tpl->setVariable('SOLUTION_OUTPUT', $output);
        $tpl->parseCurrentBlock();
    }

    private function getQuestionNavigationHtml(): string
    {
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
    private function populateGenericQuestionFeedback(ilTemplate $tpl): bool
    {
        if ($this->questionOBJ->isPreviewSolutionCorrect($this->previewSession)) {
            $feedback = $this->questionGUI->getGenericFeedbackOutputForCorrectSolution();
            $cssClass = ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_CORRECT;
        } else {
            $feedback = $this->questionGUI->getGenericFeedbackOutputForIncorrectSolution();
            $cssClass = ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_WRONG;
        }

        if ($feedback !== '') {
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
    private function populateSpecificQuestionFeedback(ilTemplate $tpl): bool
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

    protected function populateInstantResponseHeader(ilTemplate $tpl, $withFocusAnchor): void
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

    public function saveQuestionSolution(): bool
    {
        return $this->questionOBJ->persistPreviewState($this->previewSession);
    }

    public function gatewayConfirmHintRequestCmd(): void
    {
        if (!$this->saveQuestionSolution()) {
            $this->previewSession->setInstantResponseActive(false);
            $this->showCmd();
            return;
        }

        $this->ctrl->redirectByClass(
            'ilAssQuestionHintRequestGUI',
            ilAssQuestionHintRequestGUI::CMD_CONFIRM_REQUEST
        );
    }

    public function gatewayShowHintListCmd(): void
    {
        if (!$this->saveQuestionSolution()) {
            $this->previewSession->setInstantResponseActive(false);
            $this->showCmd();
            return;
        }

        $this->ctrl->redirectByClass(
            'ilAssQuestionHintRequestGUI',
            ilAssQuestionHintRequestGUI::CMD_SHOW_LIST
        );
    }

    /**
     * @return Transformation
     */
    private function getQuestionAnswerShuffler(): Transformation
    {
        if (!$this->previewSession->randomizerSeedExists()) {
            $this->previewSession->setRandomizerSeed((new RandomSeed())->createSeed());
        }
        return $this->randomGroup->shuffleArray(new GivenSeed((int) $this->previewSession->getRandomizerSeed()));
    }

    protected function populateCommentsPanel(ilTemplate $tpl, string $comments_panel_html): void
    {
        if ($comments_panel_html === '') {
            $comments_panel_html = $this->questionGUI->geCommentsPanelHTML();
        }

        $tpl->setCurrentBlock('notes_panel');
        $tpl->setVariable('NOTES_PANEL', $comments_panel_html);
        $tpl->parseCurrentBlock();
    }
}
