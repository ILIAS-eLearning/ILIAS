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

use ILIAS\TestQuestionPool\InternalRequestService;
use ILIAS\TestQuestionPool\QuestionInfoService;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 *
 * @ilCtrl_Calls ilAssQuestionFeedbackEditingGUI: ilAssGenFeedbackPageGUI, ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilAssQuestionFeedbackEditingGUI: ilPropertyFormGUI
 */
class ilAssQuestionFeedbackEditingGUI
{
    /**
     * command constants
     */
    public const CMD_SHOW = 'showFeedbackForm';
    public const CMD_SAVE = 'saveFeedbackForm';
    public const CMD_SHOW_SYNC = 'showSync';

    protected ?assQuestion $question_obj = null;
    protected ?ilAssQuestionFeedback $feedback_obj = null;

    public function __construct(
        protected assQuestionGUI $question_gui,
        protected ilCtrl $ctrl,
        protected ilAccessHandler $access,
        protected ilGlobalTemplateInterface $tpl,
        protected ilTabsGUI $tabs,
        protected ilLanguage $lng,
        protected ilHelp $help,
        private InternalRequestService $qplrequest,
        private QuestionInfoService $questioninfo
    ) {
        $this->question_obj = $question_gui->object;
        $this->feedback_obj = $question_gui->object->feedbackOBJ;
    }

    /**
     * Execute Command
     *
     * @access public
     */
    public function executeCommand(): void
    {
        $this->help->setScreenIdComponent('qpl');

        $cmd = $this->ctrl->getCmd(self::CMD_SHOW);
        $nextClass = $this->ctrl->getNextClass($this);

        $this->ctrl->setParameter($this, 'q_id', $this->qplrequest->getQuestionId());

        $this->setContentStyle();

        switch ($nextClass) {
            case 'ilassspecfeedbackpagegui':
            case 'ilassgenfeedbackpagegui':
                $forwarder = new ilAssQuestionFeedbackPageObjectCommandForwarder($this->question_obj, $this->ctrl, $this->tabs, $this->lng);
                $forwarder->forward();
                break;

            default:
                $this->tabs->setTabActive('feedback');
                $cmd .= 'Cmd';
                $this->$cmd();
                break;
        }
    }

    /**
     * Set content style
     */
    protected function setContentStyle(): void
    {
        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));
    }

    /**
     * command for rendering the feedback editing form to the content area
     *
     * @access private
     */
    private function showFeedbackFormCmd(): void
    {
        $this->tpl->setCurrentBlock("ContentStyle");
        $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->parseCurrentBlock();

        $form = $this->buildForm();

        $this->feedback_obj->initGenericFormProperties($form);
        if ($this->question_obj->hasSpecificFeedback()) {
            $this->feedback_obj->initSpecificFormProperties($form);
        }

        $this->tpl->setContent($this->ctrl->getHTML($form));
    }

    /**
     * command for processing the submitted feedback editing form.
     * first it validates the submitted values.
     * - in case of successfull validation it saves the properties and redirects to either form presentation again,
     *   or to the syncWithOriginal form for question
     * - in case of failed validation it renders the form with post values and error info to the content area again
     *
     * @access private
     */
    private function saveFeedbackFormCmd(): void
    {
        $form = $this->buildForm();
        $form->setValuesByPost();

        if ($form->checkInput()) {
            $this->feedback_obj->saveGenericFormProperties($form);
            if ($this->question_obj->hasSpecificFeedback()) {
                $this->feedback_obj->saveSpecificFormProperties($form);
            }
            $this->question_obj->cleanupMediaObjectUsage();
            $this->question_obj->updateTimestamp();

            if ($this->isSyncAfterSaveRequired()) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
                $this->ctrl->redirect($this, self::CMD_SHOW_SYNC);
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
            $this->ctrl->redirect($this, self::CMD_SHOW);
        }

        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
        $this->tpl->setContent($this->ctrl->getHTML($form));
    }

    private function createFeedbackPageCmd(): void
    {
        $mode = $this->request->raw('fb_mode');
        $this->ctrl->redirectToUrl(
            $this->feedbackOBJ->createFeedbackPages($mode)
        );
    }

    /**
     * builds the feedback editing form object
     *
     * @access private
     * @return \ilPropertyFormGUI
     */
    private function buildForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('feedback_generic'));
        $form->setTableWidth("100%");
        $form->setId("feedback");

        $this->feedback_obj->completeGenericFormProperties($form);
        if ($this->question_obj->hasSpecificFeedback()) {
            $this->feedback_obj->completeSpecificFormProperties($form);
        }

        if ($this->isFormSaveable()) {
            $form->addCommandButton(self::CMD_SAVE, $this->lng->txt("save"));
        }

        return $form;
    }

    /**
     * returns the fact wether the feedback editing form has to be saveable or not.
     * this depends on the additional content editing mode and the current question type,
     * as well as on fact wether the question is writable for current user or not,
     * or the fact if we are in self assessment mode or not
     *
     * @access private
     * @return boolean $isFormSaveable
     */
    private function isFormSaveable(): bool
    {
        if ($this->question_obj->isAdditionalContentEditingModePageObject()
            && !($this->feedback_obj->isSaveableInPageObjectEditingMode())) {
            return false;
        }

        $hasWriteAccess = $this->access->checkAccess("write", "", $this->qplrequest->getRefId());
        $isSelfAssessmentEditingMode = $this->question_obj->getSelfAssessmentEditingMode();

        return $hasWriteAccess || $isSelfAssessmentEditingMode;
    }

    /**
     * returns the fact wether the presentation of the question sync2pool form
     * is required after saving the form or not
     *
     * @access private
     * @return boolean $isSyncAfterSaveRequired
     */
    private function isSyncAfterSaveRequired(): bool
    {
        if (!$this->qplrequest->isset("calling_test")) {
            return false;
        }

        if ($this->question_obj->isAdditionalContentEditingModePageObject()) {
            return false;
        }

        if (!$this->questioninfo->questionExistsInPool((int) $this->question_obj->getOriginalId())) {
            return false;
        }

        if (!$this->questioninfo->questionExistsInPool((int) $this->question_obj->getOriginalId())) {
            return false;
        }

        if (!assQuestion::_isWriteable($this->question_obj->getOriginalId(), $this->question_obj->getCurrentUser()->getId())) {
            return false;
        }

        return true;
    }

    public function showSyncCmd(): void
    {
        $this->question_gui->originalSyncForm('', 'true');
    }
}
