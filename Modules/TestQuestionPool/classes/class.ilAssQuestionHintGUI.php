<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintAbstractGUI.php';

/**
 * GUI class for management of a single hint for assessment questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionHintGUI extends ilAssQuestionHintAbstractGUI
{
	/**
	 * command constants
	 */
	const CMD_SHOW_FORM		= 'showForm';
	const CMD_SAVE_FORM		= 'saveForm';
	const CMD_CANCEL_FORM	= 'cancelForm';
	
	/**
	 * Execute Command
	 * 
	 * @access	public
	 * @global	ilCtrl	$ilCtrl
	 * @return	mixed 
	 */
	public function executeCommand()
	{
		global $ilCtrl;
		
		$cmd = $ilCtrl->getCmd(self::CMD_SHOW_FORM);
		$nextClass = $ilCtrl->getNextClass($this);

		switch($nextClass)
		{
			default:
				
				$cmd .= 'Cmd';
				return $this->$cmd();
				break;
		}
	}
	
	/**
	 * shows the form for managing a new/existing hint
	 * 
	 * @access	private
	 * @global	ilCtrl		$ilCtrl
	 * @global	ilTemplate	$tpl 
	 */
	private function showFormCmd(ilPropertyFormGUI $form = null)
	{
		global $ilCtrl, $tpl;
		
		if( $form instanceof ilPropertyFormGUI )
		{
			$form->setValuesByPost();
		}
		elseif( isset($_GET['hint_id']) && (int)$_GET['hint_id'] )
		{
			$questionHint = new ilAssQuestionHint();

			if( !$questionHint->load((int)$_GET['hint_id']) )
			{
				ilUtil::sendFailure('invalid hint id given: '.(int)$_GET['hint_id'], true);
				$ilCtrl->redirectByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST);
			}

			$form = $this->buildForm($questionHint);
		}
		else
		{
			$form = $this->buildForm();
		}
		
		$tpl->setContent( $form->getHTML() );
	}
	
	/**
	 * saves the form on successfull validation and redirects to showForm command
	 * 
	 * @access	private
	 * @global	ilCtrl		$ilCtrl
	 * @global	ilLanguage	$lng
	 */
	private function saveFormCmd()
	{
		global $ilCtrl, $lng;
		
		$form = $this->buildForm();
		
		if( $form->checkInput() )
		{
			$questionHint = new ilAssQuestionHint();
			
			if( (int)$form->getInput('hint_id') )
			{
				$questionHint->load( (int)$form->getInput('hint_id') );
			}
			else
			{
				$questionHint->setQuestionId( $this->questionOBJ->getId() );
				
				$questionHint->setIndex(
						ilAssQuestionHintList::getNextIndexByQuestionId($this->questionOBJ->getId())
				);
			}
			
			$questionHint->setText( $form->getInput('hint_text') );
			$questionHint->setPoints( $form->getInput('hint_points') );
			
			$questionHint->save();
			
			ilUtil::sendSuccess($lng->txt('tst_question_hints_form_saved_msg'), true);
			$ilCtrl->redirectByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST);
		}
		
		ilUtil::sendFailure($lng->txt('tst_question_hints_form_invalid_msg'));
		$this->showFormCmd($form);
	}
	
	/**
	 * gateway command method to jump back to question hints overview
	 * 
	 * @access	private
	 * @global	ilCtrl	$ilCtrl
	 */
	private function cancelFormCmd()
	{
		global $ilCtrl;
		
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
	private function buildForm(ilAssQuestionHint $questionHint = null)
	{
		global $ilCtrl, $lng;
		
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		require_once 'Services/Form/classes/class.ilTextAreaInputGUI.php';
		require_once 'Services/Form/classes/class.ilNumberInputGUI.php';
		require_once 'Services/Form/classes/class.ilHiddenInputGUI.php';

		$form = new ilPropertyFormGUI();
		$form->setTableWidth('100%');
		
		// form input: hint text
		
		$areaInp = new ilTextAreaInputGUI($lng->txt('tst_question_hints_form_label_hint_text'), 'hint_text');
		$areaInp->setRequired(true);
		$areaInp->setRows(10);
		$areaInp->setCols(80);
				
		if( !$this->questionGUI->getPreventRteUsage() ) $areaInp->setUseRte(true);

		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$areaInp->setRteTags( ilObjAdvancedEditing::_getUsedHTMLTags("assessment") );

		$areaInp->setRTESupport($this->questionOBJ->getId(), 'qpl', 'assessment');

		$areaInp->addPlugin("latex");
		$areaInp->addButton("latex");
		$areaInp->addButton("pastelatex");
		
		$form->addItem($areaInp);
		
		// form input: hint points
		
		$numInp = new ilNumberInputGUI($lng->txt('tst_question_hints_form_label_hint_points'), 'hint_points');
		$numInp->allowDecimals(true);
		$numInp->setRequired(true);
		$numInp->setSize(3);
		
		$form->addItem($numInp);
		
		if( $questionHint instanceof ilAssQuestionHint )
		{
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
			
			$areaInp->setValue(	ilUtil::prepareTextareaOutput($questionHint->getText(), true) );
			
			$numInp->setValue($questionHint->getPoints());
			
			$hiddenInp->setValue($questionHint->getId());
		}
		else
		{
			// build form title for a new hint
			$form->setTitle(sprintf(
					$lng->txt('tst_question_hints_form_header_create'),
					$this->questionOBJ->getTitle()
			));
		}

		$form->setFormAction($ilCtrl->getFormAction($this));
		
		$form->addCommandButton(self::CMD_CANCEL_FORM, $lng->txt('cancel'));
		$form->addCommandButton(self::CMD_SAVE_FORM, $lng->txt('tst_question_hints_form_cmd_save'));
		
		return $form;
	}
}
