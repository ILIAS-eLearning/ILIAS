<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form;

/**
 * Class SingleChoiceFormGUI
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class SingleChoiceConfigFormGUI extends AbstractQuestionConfigFormGUI
{
	/**
	 * @var \ilAsqSingleChoiceQuestion
	 */
	protected $question;
	
	/**
	 * this method does add properties that relates to the concerns of the question
	 * for a specific question type
	 */
	protected function addQuestionSpecificProperties()
	{
		// shuffle
		$shuffle = new \ilCheckboxInputGUI($this->lng->txt( "shuffle_answers" ), "shuffle");
		$shuffle->setValue( 1 );
		$shuffle->setChecked( $this->getQuestion()->isShuffle() );
		$shuffle->setRequired( false );
		$this->addItem($shuffle);
		
		if( !$this->isLearningModuleContext() )
		{
			// Answer types
			$types = new \ilSelectInputGUI($this->lng->txt( "answer_types" ), "types");
			$types->setRequired( false );
			$types->setValue( ($this->getQuestion()->isAllowImages()) ? 0 : 1 );
			$types->setOptions( array(
					0 => $this->lng->txt( 'answers_singleline' ),
					1 => $this->lng->txt( 'answers_multiline' ),
				)
			);
			$this->addItem( $types );
		}
		
		if( $this->getQuestion()->isAllowImages() )
		{
			// thumb size
			$thumb_size = new \ilNumberInputGUI($this->lng->txt( "thumb_size" ), "thumb_size");
			$thumb_size->setSuffix($this->lng->txt("thumb_size_unit_pixel"));
			$thumb_size->setMinValue( 20 );
			$thumb_size->setDecimals( 0 );
			$thumb_size->setSize( 6 );
			$thumb_size->setInfo( $this->lng->txt( 'thumb_size_info' ) );
			$thumb_size->setValue( $this->getQuestion()->getThumbSize() ? $this->getQuestion()->getThumbSize() : '' );
			$thumb_size->setRequired( false );
			$this->addItem( $thumb_size );
		}
	}
	
	/**
	 * this method does add properties that relates to the concerns of the question's answers
	 * for a specific question type
	 */
	protected function addAnswerSpecificProperties()
	{
		// Choices
		$choices = new \ilAsqSingleChoiceWizardInputGUI($this->lng->txt( "answers" ), "choice");
		$choices->setRequired( true );
		$choices->setQuestionObject( $this->getQuestion() );
		$choices->setSingleline( $this->getQuestion()->isAllowImages() );
		$choices->setAllowMove( false );
		
		if( $this->isLearningModuleContext() )
		{
			$choices->setSize( 40 );
			$choices->setMaxLength( 800 );
		}
		
		if ($this->getQuestion()->getAnswerCount() > 0)
		{
			$choices->setValues( $this->getQuestion()->getAnswers());
		}
		else
		{
			$choices->setValues([new \ilAsqSingleChoiceQuestionAnswer()]);
		}
		
		$this->addItem( $choices );
	}
	
	protected function fillQuestionSpecificProperties() {
		$this->question->setShuffle($this->getInput("shuffle"));
		
		if ($this->question->isAllowImages()) {
			$this->question->setThumbSize((int)$this->getInput("thumb_size"));
		}
		
		if( !$this->isLearningModuleContext() )
		{
			$this->question->setAllowImages($this->getInput("types"));
		}
	}
	
	protected function fillAnswerSpecificProperties() {
		/** @var \ilSingleChoiceWizardInputGUI $choices */
		$choices = $this->getItemByPostVar("choice");
		$this->question->setAnswersFromBinaryState($choices->getValues());
	}
}
