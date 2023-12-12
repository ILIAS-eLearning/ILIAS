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

/**
 * GUI class for management/output of hint requests during test session
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 *
 * @ilCtrl_Calls ilAssQuestionHintRequestGUI: ilAssQuestionHintsTableGUI
 * @ilCtrl_Calls ilAssQuestionHintRequestGUI: ilConfirmationGUI, ilPropertyFormGUI, ilAssHintPageGUI
 */
class ilAssQuestionHintRequestGUI extends ilAssQuestionHintAbstractGUI
{
    public const CMD_SHOW_LIST = 'showList';
    public const CMD_SHOW_HINT = 'showHint';
    public const CMD_CONFIRM_REQUEST = 'confirmRequest';
    public const CMD_PERFORM_REQUEST = 'performRequest';
    public const CMD_BACK_TO_QUESTION = 'backToQuestion';

    public function __construct(
        private ilTestOutputGUI|ilAssQuestionPreviewGUI $parent_gui,
        private string $parent_cmd,
        assQuestionGUI $question_gui,
        private $question_hint_tracking,
        private ilCtrl $ctrl,
        private ilLanguage $lng,
        private ilGlobalTemplateInterface $tpl,
        protected ilTabsGUI $tabs,
    ) {

        parent::__construct($question_gui);
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd(self::CMD_SHOW_LIST);
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            case 'ilasshintpagegui':
                $forwarder = new ilAssQuestionHintPageObjectCommandForwarder(
                    $this->questionOBJ,
                    $this->ctrl,
                    $this->tabs,
                    $this->lng
                );
                $forwarder->setPresentationMode(ilAssQuestionHintPageObjectCommandForwarder::PRESENTATION_MODE_REQUEST);
                $forwarder->forward();
                return '';

            default:
                $cmd .= 'Cmd';
                return $this->$cmd();
        }
    }

    private function showListCmd(): void
    {
        $question_hint_list = $this->question_hint_tracking->getRequestedHintsList();

        $table = new ilAssQuestionHintsTableGUI(
            $this->questionOBJ,
            $question_hint_list,
            $this,
            self::CMD_SHOW_LIST
        );

        $this->populateContent($this->ctrl->getHtml($table), $this->tpl);
    }

    private function showHintCmd(): void
    {
        if (!$this->request->isset('hintId') || $this->request->int('hintId') === 0) {
            throw new ilTestException('no hint id given');
        }

        $is_requested = $this->question_hint_tracking->isRequested($this->request->int('hintId'));

        if (!$is_requested) {
            throw new ilTestException('hint with given id is not yet requested for given testactive and testpass');
        }

        $question_hint = ilAssQuestionHint::getInstanceById((int) $this->request->raw('hintId'));

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTableWidth('100%');
        $form->setTitle(sprintf(
            $this->lng->txt('tst_question_hints_form_header_edit'),
            $question_hint->getIndex(),
            $this->questionOBJ->getTitle()
        ));
        $form->addCommandButton(self::CMD_BACK_TO_QUESTION, $this->lng->txt('tst_question_hints_back_to_question'));

        $num_existing_requests = $this->question_hint_tracking->getNumExistingRequests();

        if ($num_existing_requests > 1) {
            $form->addCommandButton(self::CMD_SHOW_LIST, $this->lng->txt('button_show_requested_question_hints'));
        }

        // form input: hint text

        $non_editable_hint_text = new ilNonEditableValueGUI($this->lng->txt('tst_question_hints_form_label_hint_text'), 'hint_text', true);
        $non_editable_hint_text->setValue(ilLegacyFormElementsUtil::prepareTextareaOutput($question_hint->getText(), true));
        $form->addItem($non_editable_hint_text);

        // form input: hint points

        $non_editable_hint_point = new ilNonEditableValueGUI($this->lng->txt('tst_question_hints_form_label_hint_points'), 'hint_points');
        $non_editable_hint_point->setValue($question_hint->getPoints());
        $form->addItem($non_editable_hint_point);

        $this->populateContent($this->ctrl->getHtml($form), $this->tpl);
    }

    private function confirmRequestCmd(): void
    {
        try {
            $next_requestable_hint = $this->question_hint_tracking->getNextRequestableHint();
        } catch (ilTestNoNextRequestableHintExistsException $e) {
            $this->c->redirect($this, self::CMD_BACK_TO_QUESTION);
        }

        $confirmation = new ilConfirmationGUI();

        $form_action = ilUtil::appendUrlParameterString(
            $this->ctrl->getFormAction($this),
            "hintId={$next_requestable_hint->getId()}"
        );

        $confirmation->setFormAction($form_action);

        $confirmation->setConfirm($this->lng->txt('tst_question_hints_confirm_request'), self::CMD_PERFORM_REQUEST);
        $confirmation->setCancel($this->lng->txt('tst_question_hints_cancel_request'), self::CMD_BACK_TO_QUESTION);

        if ($next_requestable_hint->getPoints() == 0.0) {
            $confirmation->setHeaderText($this->lng->txt('tst_question_hints_request_confirmation_no_deduction'));
        } else {
            $confirmation->setHeaderText(sprintf(
                $this->lng->txt('tst_question_hints_request_confirmation'),
                $next_requestable_hint->getIndex(),
                $next_requestable_hint->getPoints()
            ));
        }

        $this->populateContent($this->ctrl->getHtml($confirmation), $this->tpl);
    }

    private function performRequestCmd(): void
    {
        if (!$this->request->isset('hintId') || !(int) $this->request->raw('hintId')) {
            throw new ilTestException('no hint id given');
        }

        try {
            $next_requestable_hint = $this->question_hint_tracking->getNextRequestableHint();
        } catch (ilTestNoNextRequestableHintExistsException $e) {
            $this->ctrl->redirect($this, self::CMD_BACK_TO_QUESTION);
        }

        if ($next_requestable_hint->getId() != (int) $this->request->raw('hintId')) {
            throw new ilTestException('given hint id does not relate to the next requestable hint');
        }

        $this->question_hint_tracking->storeRequest($next_requestable_hint);

        $redirectTarget = $this->getHintPresentationLinkTarget($next_requestable_hint->getId(), false);

        ilUtil::redirect($redirectTarget);
    }

    private function backToQuestionCmd(): void
    {
        $this->ctrl->redirect($this->parent_gui, $this->parent_cmd);
    }

    private function populateContent($content, $tpl): void
    {
        if ($this->isQuestionPreview() || !$this->parent_gui->getObject()->getKioskMode()) {
            $tpl->setContent($content);
            return;
        }

        $tpl->hideFooter();
        $tpl->addBlockFile(
            'CONTENT',
            'kiosk_content',
            'tpl.il_tst_question_hints_kiosk_page.html',
            'components/ILIAS/TestQuestionPool'
        );
        $tpl->setVariable('KIOSK_HEAD', $this->parent_gui->getKioskHead());
        $tpl->setVariable('KIOSK_CONTENT', $content);
    }

    private function isQuestionPreview(): bool
    {
        if ($this->question_hint_tracking instanceof ilAssQuestionPreviewHintTracking) {
            return true;
        }

        return false;
    }

    public function getHintPresentationLinkTarget($hint_id, $xml_style = true): string
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $this->ctrl->setParameterByClass('ilasshintpagegui', 'hint_id', $hint_id);
            return $this->ctrl->getLinkTargetByClass('ilAssHintPageGUI', '', '', false, $xml_style);
        }

        $this->ctrl->setParameter($this, 'hintId', $hint_id);
        return $this->ctrl->getLinkTarget($this, self::CMD_SHOW_HINT, '', false, $xml_style);
    }
}
