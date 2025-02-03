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

use ILIAS\TestQuestionPool\QuestionPoolDIC;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Grégory Saive <gsaive@databay.de>
 * @version		$Id$

 *
 * @ilCtrl_Calls ilAssQuestionHintGUI: ilAssHintPageGUI
 */
class ilAssQuestionHintGUI extends ilAssQuestionHintAbstractGUI
{
    public const CMD_SHOW_FORM = 'showForm';
    public const CMD_SAVE_FORM = 'saveForm';
    public const CMD_CANCEL_FORM = 'cancelForm';
    private \ilGlobalTemplateInterface $main_tpl;
    private GeneralQuestionPropertiesRepository $questionrepository;

    public function __construct(assQuestionGUI $questionGUI)
    {
        parent::__construct($questionGUI);
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $local_dic = QuestionPoolDIC::dic();
        $this->questionrepository = $local_dic['question.general_properties.repository'];
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd(self::CMD_SHOW_FORM);
        $nextClass = $this->ctrl->getNextClass($this);

        switch ($nextClass) {
            case 'ilasshintpagegui':
                $forwarder = new ilAssQuestionHintPageObjectCommandForwarder(
                    $this->question_obj,
                    $this->ctrl,
                    $this->tabs,
                    $this->lng
                );
                $forwarder->setPresentationMode(ilAssQuestionHintPageObjectCommandForwarder::PRESENTATION_MODE_AUTHOR);
                $forwarder->forward();
                break;

            default:
                $this->tabs->setTabActive('tst_question_hints_tab');
                $cmd .= 'Cmd';
                $this->$cmd();
                break;
        }

        return true;
    }

    private function showFormCmd(?ilPropertyFormGUI $form = null): void
    {
        if ($form instanceof ilPropertyFormGUI) {
            $form->setValuesByPost();
        } elseif ($this->request_data_collector->isset('hint_id') && (int) $this->request_data_collector->raw('hint_id')) {
            $questionHint = new ilAssQuestionHint();

            if (!$questionHint->load((int) $this->request_data_collector->raw('hint_id'))) {
                $this->main_tpl->setOnScreenMessage(
                    'failure',
                    'invalid hint id given: ' . $this->request_data_collector->string('hint_id'),
                    true
                );
                $this->ctrl->redirectByClass(ilAssQuestionHintsGUI::class, ilAssQuestionHintsGUI::CMD_SHOW_LIST);
            }

            $form = $this->buildForm($questionHint);
        } else {
            $form = $this->buildForm();
        }

        $this->main_tpl->setContent($form->getHTML());
    }

    private function saveFormCmd(): void
    {
        $questionHint = new ilAssQuestionHint();
        if ($this->request_data_collector->isset('hint_id')) {
            $questionHint->load((int) $this->request_data_collector->int('hint_id'));

            $hintJustCreated = false;
            $form = $this->buildForm($questionHint);
        } else {
            $questionHint->setQuestionId($this->question_obj->getId());

            $questionHint->setIndex(
                ilAssQuestionHintList::getNextIndexByQuestionId($this->question_obj->getId())
            );

            $hintJustCreated = true;
            $form = $this->buildForm();
        }

        if ($form->checkInput()) {
            $questionHint->setText($form->getInput('hint_text'));
            $questionHint->setPoints($form->getInput('hint_points'));

            $questionHint->save();
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('tst_question_hints_form_saved_msg'), true);

            if (!$this->question_obj->isAdditionalContentEditingModePageObject()) {
                $this->question_obj->updateTimestamp();
            }

            if ($this->question_gui->needsSyncQuery()) {
                $this->ctrl->redirectByClass(
                    ilAssQuestionHintsGUI::class,
                    ilAssQuestionHintsGUI::CMD_CONFIRM_SYNC
                );
            }

            if ($hintJustCreated && $this->question_obj->isAdditionalContentEditingModePageObject()) {
                $this->ctrl->setParameterByClass(ilAssHintPageGUI::class, 'hint_id', $questionHint->getId());
                $this->ctrl->redirectByClass(ilAssHintPageGUI::class, 'edit');
            } else {
                $this->ctrl->redirectByClass(ilAssQuestionHintsGUI::class, ilAssQuestionHintsGUI::CMD_SHOW_LIST);
            }
        }

        $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('tst_question_hints_form_invalid_msg'));
        $this->showFormCmd($form);
    }

    private function cancelFormCmd(): void
    {

        $this->ctrl->redirectByClass(ilAssQuestionHintsGUI::class);
    }

    private function buildForm(?ilAssQuestionHint $questionHint = null): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTableWidth('100%');

        if (!$this->question_obj->isAdditionalContentEditingModePageObject()) {
            // form input: hint text

            $areaInp = new ilTextAreaInputGUI($this->lng->txt('tst_question_hints_form_label_hint_text'), 'hint_text');
            $areaInp->setRequired(true);
            $areaInp->setRows(10);
            $areaInp->setCols(80);

            if (!$this->question_obj->getPreventRteUsage()) {
                $areaInp->setUseRte(true);
            }

            $areaInp->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));

            $areaInp->setRTESupport($this->question_obj->getId(), 'qpl', 'assessment');

            $form->addItem($areaInp);
        }

        // form input: hint points

        $numInp = new ilNumberInputGUI($this->lng->txt('tst_question_hints_form_label_hint_points'), 'hint_points');
        $numInp->allowDecimals(true);
        $numInp->setRequired(true);
        $numInp->setSize(3);

        $form->addItem($numInp);

        if ($questionHint instanceof ilAssQuestionHint) {
            // build form title for an existing hint

            $form->setTitle(sprintf(
                $this->lng->txt('tst_question_hints_form_header_edit'),
                $questionHint->getIndex(),
                $this->question_obj->getTitle()
            ));

            $hiddenInp = new ilHiddenInputGUI('hint_id');
            $form->addItem($hiddenInp);

            if (!$this->question_obj->isAdditionalContentEditingModePageObject()) {
                $areaInp->setValue($questionHint->getText());
            }

            $numInp->setValue((string) $questionHint->getPoints());

            $hiddenInp->setValue((string) $questionHint->getId());
        } else {
            // build form title for a new hint
            $form->setTitle(sprintf(
                $this->lng->txt('tst_question_hints_form_header_create'),
                $this->question_obj->getTitle()
            ));
        }

        if ($this->question_obj->isAdditionalContentEditingModePageObject()) {
            if ($questionHint instanceof ilAssQuestionHint) {
                $saveCmdLabel = $this->lng->txt('tst_question_hints_form_cmd_save_points');
            } else {
                $saveCmdLabel = $this->lng->txt('tst_question_hints_form_cmd_save_points_and_edit_page');
            }
        } else {
            $saveCmdLabel = $this->lng->txt('tst_question_hints_form_cmd_save');
        }

        $form->setFormAction($this->ctrl->getFormAction($this));

        $form->addCommandButton(self::CMD_SAVE_FORM, $saveCmdLabel);
        $form->addCommandButton(self::CMD_CANCEL_FORM, $this->lng->txt('cancel'));

        return $form;
    }
}
