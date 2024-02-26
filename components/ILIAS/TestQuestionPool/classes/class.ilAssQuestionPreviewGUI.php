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

use ILIAS\Refinery\Random\Group as RandomGroup;
use ILIAS\Refinery\Random\Seed\RandomSeed;
use ILIAS\Refinery\Random\Seed\GivenSeed;
use ILIAS\Refinery\Transformation;
use ILIAS\GlobalScreen\Services as GlobalScreen;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/TestQuestionPool
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

    private assQuestionGUI $question_gui;
    private assQuestion $question_obj;
    private ?ilAssQuestionPreviewSettings $preview_settings = null;
    private ?ilAssQuestionPreviewSession $preview_session = null;
    private ?ilAssQuestionPreviewHintTracking $hint_tracking = null;

    public function __construct(
        private ilCtrl $ctrl,
        private ilRbacSystem $rbac_system,
        private ilTabsGUI $tabs,
        private ilGlobalTemplateInterface $tpl,
        private ilLanguage $lng,
        private ilDBInterface $db,
        private RandomGroup $random_group,
        private GlobalScreen $global_screen
    ) {
        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());
    }

    public function initQuestion(int $question_id, int $parent_obj_id): void
    {
        $this->question_gui = assQuestion::instantiateQuestionGUI($question_id);
        $this->question_obj = $this->question_gui->getObject();

        $this->question_obj->setObjId($parent_obj_id);

        $this->tabs->clearTargets();
        $this->tabs->addTarget(
            self::TAB_ID_QUESTION,
            $this->ctrl->getLinkTargetByClass('ilAssQuestionPreviewGUI', self::CMD_SHOW),
            '',
            [strtolower(__CLASS__)]
        );
        // Assessment of questions sub menu entry
        $q_type = $this->question_obj->getQuestionType();
        $classname = $q_type . "GUI";
        $this->tabs->addTarget(
            "statistics",
            $this->ctrl->getLinkTargetByClass('ilAssQuestionPreviewGUI', "assessment"),
            ["assessment"],
            $classname,
            ""
        );

        $this->question_gui->outAdditionalOutput();

        $this->question_gui->populateJavascriptFilesRequiredForWorkForm($this->tpl);
        $this->question_gui->setTargetGui($this);
        $this->question_gui->setQuestionActionCmd(self::CMD_HANDLE_QUESTION_ACTION);

        $this->question_gui->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_DEMOPLAY);
    }

    public function initPreviewSettings($parentRefId): void
    {
        $this->preview_settings = new ilAssQuestionPreviewSettings($parentRefId);

        $this->preview_settings->init();
    }

    public function initPreviewSession($userId, $questionId): void
    {
        $this->preview_session = new ilAssQuestionPreviewSession($userId, $questionId);

        $this->preview_session->init();
    }

    public function initHintTracking(): void
    {
        $this->hint_tracking = new ilAssQuestionPreviewHintTracking($this->db, $this->preview_session);
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
                    $this->question_gui,
                    $this->hint_tracking,
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
                $forwarder = new ilAssQuestionFeedbackPageObjectCommandForwarder($this->question_obj, $this->ctrl, $this->tabs, $this->lng);
                $forwarder->forward();
                break;
            case 'ilcommentgui':
                $comment_gui = new ilCommentGUI($this->questionOBJ->getObjId(), $this->questionOBJ->getId(), 'quest');
                $comments_panel_html = $this->ctrl->forwardCommand($comment_gui);
                $this->showCmd($comments_panel_html);
                break;
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_SHOW);
                $this->{$cmd . 'Cmd'}();
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
        if ($this->preview_settings->isTestRefId()) {
            return false;
        }

        return (bool) $this->rbac_system->checkAccess('write', (int) $_GET['ref_id']);
    }

    private function showCmd(string $commands_panel_html = ''): void
    {
        $tpl = new ilTemplate('tpl.qpl_question_preview.html', true, true, 'components/ILIAS/TestQuestionPool');
        $tpl->setVariable('PREVIEW_FORMACTION', $this->buildPreviewFormAction());

        $this->populatePreviewToolbar($tpl);
        $this->populateQuestionOutput($tpl);
        $this->handleInstantResponseRendering($tpl);

        if ($this->isCommentingRequired()) {
            $this->populateCommentsPanel($tpl, $commands_panel_html);
        }

        $this->tpl->setContent($tpl->get());
    }

    private function assessmentCmd()
    {
        $this->tabs->activateTab('statistics');
        $this->question_gui->assessment();
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

            if ($this->question_gui->hasInlineFeedback()) {
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
                if ($this->question_gui->hasInlineFeedback()) {
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
        $this->preview_session->setRandomizerSeed(null);
        $this->preview_session->setParticipantsSolution(null);
        $this->preview_session->resetRequestedHints();
        $this->preview_session->setInstantResponseActive(false);

        $this->tpl->setOnScreenMessage('info', $this->lng->txt('qst_preview_reset_msg'), true);

        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    private function instantResponseCmd(): void
    {
        if ($this->saveQuestionSolution()) {
            $this->preview_session->setInstantResponseActive(true);
        } else {
            $this->preview_session->setInstantResponseActive(false);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    private function handleQuestionActionCmd(): void
    {
        $this->question_obj->persistPreviewState($this->preview_session);
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
                    get_class($this->question_gui),
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

        $pageGUI = new ilAssQuestionPageGUI($this->question_obj->getId());
        $pageGUI->setRenderPageContainer(false);
        $pageGUI->setEditPreview(true);
        $pageGUI->setEnabledTabs(false);

        $this->question_gui->setPreviewSession($this->preview_session);
        $this->question_gui->getObject()->setShuffler($this->getQuestionAnswerShuffler());

        $questionHtml = $this->question_gui->getPreview(true, $this->isShowSpecificQuestionFeedbackRequired());
        $this->question_gui->magicAfterTestOutput();

        $questionHtml .= $this->getQuestionNavigationHtml();

        $pageGUI->setQuestionHTML([$this->question_obj->getId() => $questionHtml]);

        $pageGUI->setPresentationTitle($this->question_obj->getTitle());

        $tpl->setVariable('QUESTION_OUTPUT', $pageGUI->preview());
        // \ilPageObjectGUI::preview sets an undefined tab, so the "question" tab has to be activated again
        $this->tabs->setTabActive(self::TAB_ID_QUESTION);
    }

    protected function populateReachedPointsOutput(ilTemplate $tpl): void
    {
        $reachedPoints = $this->question_obj->calculateReachedPointsFromPreviewSession($this->preview_session);
        $maxPoints = $this->question_obj->getMaximumPoints();

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

        $pageGUI = new ilAssQuestionPageGUI($this->question_obj->getId());

        $pageGUI->setEditPreview(true);
        $pageGUI->setEnabledTabs(false);

        // FOR WHICH SITUATION IS THIS WORKAROUND NECCESSARY? (sure .. imagemaps, but where this can be done?)
        if (strlen($this->ctrl->getCmd()) == 0 && !isset($_POST['editImagemapForward_x'])) { // workaround for page edit imagemaps, keep in mind
            // @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
            // $this->ctrl->setCmdClass(get_class($pageGUI));
            // $this->ctrl->setCmd('preview');
        }

        $this->question_gui->setPreviewSession($this->preview_session);

        $pageGUI->setQuestionHTML([$this->question_obj->getId() => $this->question_gui->getSolutionOutput(0, null, false, false, true, false, true, false, false)]);

        $output = $this->question_gui->getSolutionOutput(0, null, false, false, true, false, true, false, false);

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

        $navGUI->setInstantResponseEnabled($this->preview_settings->isInstantFeedbackNavigationRequired());
        $navGUI->setHintProvidingEnabled($this->preview_settings->isHintProvidingEnabled());

        $navGUI->setHintRequestsPossible($this->hint_tracking->requestsPossible());
        $navGUI->setHintRequestsExist($this->hint_tracking->requestsExist());

        return $this->ctrl->getHTML($navGUI);
    }

    /**
     * Populate the block for an instant generic feedback
     * @return bool     true, if there is some feedback populated
     */
    private function populateGenericQuestionFeedback(ilTemplate $tpl): bool
    {
        if ($this->question_obj->isPreviewSolutionCorrect($this->preview_session)) {
            $feedback = $this->question_gui->getGenericFeedbackOutputForCorrectSolution();
            $cssClass = ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_CORRECT;
        } else {
            $feedback = $this->question_gui->getGenericFeedbackOutputForIncorrectSolution();
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
        $fb = $this->question_gui->getSpecificFeedbackOutput(
            (array) $this->preview_session->getParticipantsSolution()
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
        if (!$this->preview_settings->isBestSolutionEnabled()) {
            return false;
        }

        return $this->preview_session->isInstantResponseActive();
    }

    private function isShowGenericQuestionFeedbackRequired()
    {
        if (!$this->preview_settings->isGenericFeedbackEnabled()) {
            return false;
        }

        return $this->preview_session->isInstantResponseActive();
    }

    private function isShowSpecificQuestionFeedbackRequired()
    {
        if (!$this->preview_settings->isSpecificFeedbackEnabled()) {
            return false;
        }

        return $this->preview_session->isInstantResponseActive();
    }

    private function isShowReachedPointsRequired()
    {
        if (!$this->preview_settings->isReachedPointsEnabled()) {
            return false;
        }

        return $this->preview_session->isInstantResponseActive();
    }

    public function saveQuestionSolution(): bool
    {
        return $this->question_obj->persistPreviewState($this->preview_session);
    }

    public function gatewayConfirmHintRequestCmd(): void
    {
        if (!$this->saveQuestionSolution()) {
            $this->preview_session->setInstantResponseActive(false);
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
            $this->preview_session->setInstantResponseActive(false);
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
        if (!$this->preview_session->randomizerSeedExists()) {
            $this->preview_session->setRandomizerSeed((new RandomSeed())->createSeed());
        }
        return $this->random_group->shuffleArray(new GivenSeed((int) $this->preview_session->getRandomizerSeed()));
    }

    protected function populateCommentsPanel(ilTemplate $tpl, string $comments_panel_html): void
    {
        if ($comments_panel_html === '') {
            $comments_panel_html = $this->question_gui->geCommentsPanelHTML();
        }

        $tpl->setCurrentBlock('notes_panel');
        $tpl->setVariable('NOTES_PANEL', $comments_panel_html);
        $tpl->parseCurrentBlock();
    }
}
