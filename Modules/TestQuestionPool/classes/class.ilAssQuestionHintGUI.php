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

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintAbstractGUI.php';

/**
 * GUI class for management of a single hint for assessment questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Grégory Saive <gsaive@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 *
 * @ilCtrl_Calls ilAssQuestionHintGUI: ilAssHintPageGUI
 */
class ilAssQuestionHintGUI extends ilAssQuestionHintAbstractGUI
{
    /**
     * command constants
     */
    public const CMD_SHOW_FORM = 'showForm';
    public const CMD_SAVE_FORM = 'saveForm';
    public const CMD_CANCEL_FORM = 'cancelForm';
    public const CMD_CONFIRM_FORM = 'confirmForm';
    private \ilGlobalTemplateInterface $main_tpl;
    public function __construct(assQuestionGUI $questionGUI)
    {
        parent::__construct($questionGUI);
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    /**
     * Execute Command
     *
     * @access	public
     * @global	ilCtrl	$ilCtrl
     * @return	mixed
     */
    public function executeCommand()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];

        $cmd = $ilCtrl->getCmd(self::CMD_SHOW_FORM);
        $nextClass = $ilCtrl->getNextClass($this);

        switch ($nextClass) {
            case 'ilasshintpagegui':
                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintPageObjectCommandForwarder.php';
                $forwarder = new ilAssQuestionHintPageObjectCommandForwarder($this->questionOBJ, $ilCtrl, $ilTabs, $lng);
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

    /**
     * shows the form for managing a new/existing hint
     *
     * @access	private
     * @global	ilCtrl		$ilCtrl
     * @global	ilTemplate	$tpl
     */
    private function showFormCmd(ilPropertyFormGUI $form = null): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilToolbar = $DIC['ilToolbar'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        if ($form instanceof ilPropertyFormGUI) {
            $form->setValuesByPost();
        } elseif ($this->request->isset('hint_id') && (int) $this->request->raw('hint_id')) {
            $questionHint = new ilAssQuestionHint();

            if (!$questionHint->load((int) $this->request->raw('hint_id'))) {
                $this->main_tpl->setOnScreenMessage('failure', 'invalid hint id given: ' . (int) $this->request->raw('hint_id'), true);
                $ilCtrl->redirectByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST);
            }

            $form = $this->buildForm($questionHint);
        } else {
            $form = $this->buildForm();
        }

        $tpl->setContent($form->getHTML());
    }

    /**
     * saves the form on successfull validation and redirects to showForm command
     *
     * @access	private
     * @global	ilCtrl		$ilCtrl
     * @global	ilLanguage	$lng
     */
    private function saveFormCmd(): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];

        $questionHint = new ilAssQuestionHint();
        if ($this->request->isset('hint_id')) {
            $questionHint->load((int) $this->request->int('hint_id'));

            $hintJustCreated = false;
            $form = $this->buildForm($questionHint);
        } else {
            $questionHint->setQuestionId($this->questionOBJ->getId());

            $questionHint->setIndex(
                ilAssQuestionHintList::getNextIndexByQuestionId($this->questionOBJ->getId())
            );

            $hintJustCreated = true;
            $form = $this->buildForm();
        }

        if ($form->checkInput()) {
            $questionHint->setText($form->getInput('hint_text'));
            $questionHint->setPoints($form->getInput('hint_points'));

            $questionHint->save();
            $this->main_tpl->setOnScreenMessage('success', $lng->txt('tst_question_hints_form_saved_msg'), true);

            if (!$this->questionOBJ->isAdditionalContentEditingModePageObject()) {
                $this->questionOBJ->updateTimestamp();
            }

            $originalexists = $this->questionOBJ->_questionExistsInPool((int) $this->questionOBJ->getOriginalId());
            include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
            if ($this->request->raw('calling_test') && $originalexists && assQuestion::_isWriteable($this->questionOBJ->getOriginalId(), $ilUser->getId())) {
                $ilCtrl->redirectByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_CONFIRM_SYNC);
            }


            if ($hintJustCreated && $this->questionOBJ->isAdditionalContentEditingModePageObject()) {
                $ilCtrl->setParameterByClass('ilasshintpagegui', 'hint_id', $questionHint->getId());
                $ilCtrl->redirectByClass('ilasshintpagegui', 'edit');
            } else {
                $ilCtrl->redirectByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST);
            }
        }

        $this->main_tpl->setOnScreenMessage('failure', $lng->txt('tst_question_hints_form_invalid_msg'));
        $this->showFormCmd($form);
    }

    /**
     * gateway command method to jump back to question hints overview
     *
     * @access	private
     * @global	ilCtrl	$ilCtrl
     */
    private function cancelFormCmd(): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $ilCtrl->redirectByClass('ilAssQuestionHintsGUI');
    }

    /**
     * builds the questions hints form
     *
     * @access	private
     * @global	ilCtrl				$ilCtrl
     * @global	ilLanguage			$lng
     * @return	ilPropertyFormGUI	$form
     */
    private function buildForm(ilAssQuestionHint $questionHint = null): ilPropertyFormGUI
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        require_once 'Services/Form/classes/class.ilTextAreaInputGUI.php';
        require_once 'Services/Form/classes/class.ilNumberInputGUI.php';
        require_once 'Services/Form/classes/class.ilHiddenInputGUI.php';

        $form = new ilPropertyFormGUI();
        $form->setTableWidth('100%');

        if (!$this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            // form input: hint text

            $areaInp = new ilTextAreaInputGUI($lng->txt('tst_question_hints_form_label_hint_text'), 'hint_text');
            $areaInp->setRequired(true);
            $areaInp->setRows(10);
            $areaInp->setCols(80);

            if (!$this->questionOBJ->getPreventRteUsage()) {
                $areaInp->setUseRte(true);
            }

            include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
            $areaInp->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));

            $areaInp->setRTESupport($this->questionOBJ->getId(), 'qpl', 'assessment');

            $areaInp->addPlugin("latex");
            $areaInp->addButton("latex");
            $areaInp->addButton("pastelatex");

            $form->addItem($areaInp);
        }

        // form input: hint points

        $numInp = new ilNumberInputGUI($lng->txt('tst_question_hints_form_label_hint_points'), 'hint_points');
        $numInp->allowDecimals(true);
        $numInp->setRequired(true);
        $numInp->setSize(3);

        $form->addItem($numInp);

        if ($questionHint instanceof ilAssQuestionHint) {
            // build form title for an existing hint

            $form->setTitle(sprintf(
                $lng->txt('tst_question_hints_form_header_edit'),
                $questionHint->getIndex(),
                $this->questionOBJ->getTitle()
            ));

            // hidden input: hint id

            $hiddenInp = new ilHiddenInputGUI('hint_id');
            $form->addItem($hiddenInp);

            // init values

            require_once 'Services/Utilities/classes/class.ilUtil.php';

            if (!$this->questionOBJ->isAdditionalContentEditingModePageObject()) {
                $areaInp->setValue($questionHint->getText());
            }

            $numInp->setValue($questionHint->getPoints());

            $hiddenInp->setValue($questionHint->getId());
        } else {
            // build form title for a new hint
            $form->setTitle(sprintf(
                $lng->txt('tst_question_hints_form_header_create'),
                $this->questionOBJ->getTitle()
            ));
        }

        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            if ($questionHint instanceof ilAssQuestionHint) {
                $saveCmdLabel = $lng->txt('tst_question_hints_form_cmd_save_points');
            } else {
                $saveCmdLabel = $lng->txt('tst_question_hints_form_cmd_save_points_and_edit_page');
            }
        } else {
            $saveCmdLabel = $lng->txt('tst_question_hints_form_cmd_save');
        }

        $form->setFormAction($ilCtrl->getFormAction($this));

        $form->addCommandButton(self::CMD_SAVE_FORM, $saveCmdLabel);
        $form->addCommandButton(self::CMD_CANCEL_FORM, $lng->txt('cancel'));

        return $form;
    }
}
