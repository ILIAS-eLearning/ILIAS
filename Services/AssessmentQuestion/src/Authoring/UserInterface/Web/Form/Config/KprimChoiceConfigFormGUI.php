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
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		// shuffle answers
		$shuffleAnswers = new \ilCheckboxInputGUI($DIC->language()->txt( "shuffle_answers" ), "shuffle_answers_enabled");
		$shuffleAnswers->setChecked( $this->getQuestion()->isShuffleAnswersEnabled() );
		$this->addItem($shuffleAnswers);
		
		if( !$this->isLearningModuleContext() )
		{
			// answer mode (single-/multi-line)
			$answerType = new \ilSelectInputGUI($DIC->language()->txt('answer_types'), 'answer_type');
			$answerType->setOptions($this->getQuestion()->getAnswerTypeSelectOptions($DIC->language()));
			$answerType->setValue( $this->getQuestion()->getAnswerType() );
			$this->addItem($answerType);
		}
		
		if( !$this->isLearningModuleContext() && $this->getQuestion()->isSingleLineAnswerType($this->getQuestion()->getAnswerType()) )
		{
			// thumb size
			$thumbSize = new \ilNumberInputGUI($DIC->language()->txt('thumb_size'), 'thumb_size');
			$thumbSize->setSuffix($DIC->language()->txt("thumb_size_unit_pixel"));
			$thumbSize->setInfo( $DIC->language()->txt('thumb_size_info') );
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
		$optionLabel = new \ilRadioGroupInputGUI($DIC->language()->txt('option_label'), 'option_label');
		$optionLabel->setInfo($DIC->language()->txt('option_label_info'));
		$optionLabel->setRequired(true);
		$optionLabel->setValue($this->getQuestion()->getOptionLabel());
		foreach($this->getQuestion()->getValidOptionLabelsTranslated($DIC->language()) as $labelValue => $labelText)
		{
			$option = new \ilRadioOption($labelText, $labelValue);
			$optionLabel->addOption($option);
			
			if( $this->getQuestion()->isCustomOptionLabel($labelValue) )
			{
				$customLabelTrue = new \ilTextInputGUI(
					$DIC->language()->txt('option_label_custom_true'), 'option_label_custom_true'
				);
				$customLabelTrue->setValue($this->getQuestion()->getCustomTrueOptionLabel());
				$option->addSubItem($customLabelTrue);
				
				$customLabelFalse = new \ilTextInputGUI(
					$DIC->language()->txt('option_label_custom_false'), 'option_label_custom_false'
				);
				$customLabelFalse->setValue($this->getQuestion()->getCustomFalseOptionLabel());
				$option->addSubItem($customLabelFalse);
			}
		}
		$this->addItem($optionLabel);
		
		// points
		$points = new \ilNumberInputGUI($DIC->language()->txt('points'), 'points');
		$points->setRequired(true);
		$points->setSize(3);
		$points->allowDecimals(true);
		$points->setMinValue(0);
		$points->setMinvalueShouldBeGreater(true);
		$points->setValue($this->getQuestion()->getPoints());
		$this->addItem($points);
		
		// score partial solution
		$scorePartialSolution = new \ilCheckboxInputGUI($DIC->language()->txt('score_partsol_enabled'), 'score_partsol_enabled');
		$scorePartialSolution->setInfo($DIC->language()->txt('score_partsol_enabled_info'));
		$scorePartialSolution->setChecked( $this->getQuestion()->isScorePartialSolutionEnabled() );
		$this->addItem($scorePartialSolution);
	}
	
	protected function addAnswerSpecificProperties()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$kprimAnswers = new \ilKprimChoiceWizardInputGUI($DIC->language()->txt('answers'), 'kprim_answers');
		$kprimAnswers->setInfo($DIC->language()->txt('kprim_answers_info'));
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
	}
	
	protected function fillQuestionSpecificProperties()
	{
		$oldAnswerType = $this->getQuestion()->getAnswerType();
		
		$this->getQuestion()->setShuffleAnswersEnabled($this->getItemByPostVar('shuffle_answers_enabled')->getChecked());
		
		if( !$this->getQuestion()->getSelfAssessmentEditingMode() )
		{
			$this->getQuestion()->setAnswerType($this->getItemByPostVar('answer_type')->getValue());
		}
		else
		{
			$this->getQuestion()->setAnswerType(assKprimChoice::ANSWER_TYPE_MULTI_LINE);
		}
		
		if( !$this->getQuestion()->getSelfAssessmentEditingMode() && $this->getQuestion()->isSingleLineAnswerType($oldAnswerType) )
		{
			$this->getQuestion()->setThumbSize($this->getItemByPostVar('thumb_size')->getValue());
		}
		
		$this->getQuestion()->setOptionLabel($this->getItemByPostVar('option_label')->getValue());
		
		if( $this->getQuestion()->isCustomOptionLabel($this->getQuestion()->getOptionLabel()) )
		{
			$this->getQuestion()->setCustomTrueOptionLabel( strip_tags(
				$this->getItemByPostVar('option_label_custom_true')->getValue()
			));
			$this->getQuestion()->setCustomFalseOptionLabel( strip_tags(
				$this->getItemByPostVar('option_label_custom_false')->getValue()
			));
		}
		
		$this->getQuestion()->setPoints($this->getItemByPostVar('points')->getValue());
		
		$this->getQuestion()->setScorePartialSolutionEnabled($this->getItemByPostVar('score_partsol_enabled')->getChecked());
	}
	
	protected function fillAnswerSpecificProperties()
	{
		$answers = $this->getItemByPostVar('kprim_answers')->getValues();
		$answers = $this->handleAnswerTextsSubmit($answers);
		$files = $this->getItemByPostVar('kprim_answers')->getFiles();
		
		$this->getQuestion()->handleFileUploads($answers, $files);
		$this->getQuestion()->setAnswers($answers);
	}
	
}
