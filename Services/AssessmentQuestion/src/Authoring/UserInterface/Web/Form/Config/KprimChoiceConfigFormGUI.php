<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form;


/**
 * Class KprimChoiceConfigFormGUI
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class KprimChoiceConfigFormGUI extends AbstractQuestionConfigFormGUI
{
	/**
	 * @var \ilAsqKprimChoiceQuestion
	 */
	protected $question;
	
	protected function addQuestionSpecificProperties()
	{
		// shuffle answers
		$shuffleAnswers = new \ilCheckboxInputGUI($this->lng->txt( "shuffle_answers" ), "shuffle_answers_enabled");
		$shuffleAnswers->setChecked( $this->getQuestion()->isShuffleAnswersEnabled() );
		$this->addItem($shuffleAnswers);
		
		if( !$this->isLearningModuleContext() )
		{
			// answer mode (single-/multi-line)
			$answerType = new ilSelectInputGUI($this->lng->txt('answer_types'), 'answer_type');
			$answerType->setOptions($this->getQuestion()->getAnswerTypeSelectOptions($this->lng));
			$answerType->setValue( $this->getQuestion()->getAnswerType() );
			$this->addItem($answerType);
		}
		
		if( !$this->isLearningModuleContext() && $this->getQuestion()->isSingleLineAnswerType($this->getQuestion()->getAnswerType()) )
		{
			// thumb size
			$thumbSize = new ilNumberInputGUI($this->lng->txt('thumb_size'), 'thumb_size');
			$thumbSize->setSuffix($this->lng->txt("thumb_size_unit_pixel"));
			$thumbSize->setInfo( $this->lng->txt('thumb_size_info') );
			$thumbSize->setDecimals(false);
			$thumbSize->setMinValue(20);
			$thumbSize->setSize(6);
			if( $this->getQuestion()->getThumbSize() > 0 )
			{
				$thumbSize->setValue($this->getQuestion()->getThumbSize());
			}
			$this->addItem($thumbSize);
		}
		
		// option label
		$optionLabel = new ilRadioGroupInputGUI($this->lng->txt('option_label'), 'option_label');
		$optionLabel->setInfo($this->lng->txt('option_label_info'));
		$optionLabel->setRequired(true);
		$optionLabel->setValue($this->getQuestion()->getOptionLabel());
		foreach($this->getQuestion()->getValidOptionLabelsTranslated($this->lng) as $labelValue => $labelText)
		{
			$option = new ilRadioOption($labelText, $labelValue);
			$optionLabel->addOption($option);
			
			if( $this->getQuestion()->isCustomOptionLabel($labelValue) )
			{
				$customLabelTrue = new ilTextInputGUI(
					$this->lng->txt('option_label_custom_true'), 'option_label_custom_true'
				);
				$customLabelTrue->setValue($this->getQuestion()->getCustomTrueOptionLabel());
				$option->addSubItem($customLabelTrue);
				
				$customLabelFalse = new ilTextInputGUI(
					$this->lng->txt('option_label_custom_false'), 'option_label_custom_false'
				);
				$customLabelFalse->setValue($this->getQuestion()->getCustomFalseOptionLabel());
				$option->addSubItem($customLabelFalse);
			}
		}
		$this->addItem($optionLabel);
		
		// points
		$points = new ilNumberInputGUI($this->lng->txt('points'), 'points');
		$points->setRequired(true);
		$points->setSize(3);
		$points->allowDecimals(true);
		$points->setMinValue(0);
		$points->setMinvalueShouldBeGreater(true);
		$points->setValue($this->getQuestion()->getPoints());
		$this->addItem($points);
		
		// score partial solution
		$scorePartialSolution = new ilCheckboxInputGUI($this->lng->txt('score_partsol_enabled'), 'score_partsol_enabled');
		$scorePartialSolution->setInfo($this->lng->txt('score_partsol_enabled_info'));
		$scorePartialSolution->setChecked( $this->getQuestion()->isScorePartialSolutionEnabled() );
		$this->addItem($scorePartialSolution);
		
		return $form;
	}
	
	protected function addAnswerSpecificProperties()
	{
		require_once 'Modules/TestQuestionPool/classes/class.ilKprimChoiceWizardInputGUI.php';
		$kprimAnswers = new ilKprimChoiceWizardInputGUI($this->lng->txt('answers'), 'kprim_answers');
		$kprimAnswers->setInfo($this->lng->txt('kprim_answers_info'));
		$kprimAnswers->setSize(64);
		$kprimAnswers->setMaxLength(1000);
		$kprimAnswers->setRequired(true);
		$kprimAnswers->setAllowMove(true);
		$kprimAnswers->setQuestionObject($this->getQuestion());
		if( !$this->isLearningModuleContext() )
		{
			$kprimAnswers->setSingleline($this->getQuestion()->isSingleLineAnswerType($this->getQuestion()->getAnswerType()));
		}
		else
		{
			$kprimAnswers->setSingleline(false);
		}
		$kprimAnswers->setValues($this->getQuestion()->getAnswers());
		$this->addItem($kprimAnswers);
		
		return $form;
	}
	
	protected function fillQuestionSpecificProperties()
	{
		// TODO: Implement fillQuestionSpecificProperties() method.
	}
	
	protected function fillAnswerSpecificProperties()
	{
		// TODO: Implement fillAnswerSpecificProperties() method.
	}
	
}
