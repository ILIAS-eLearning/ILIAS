<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config;

/**
 * Class SingleChoiceConfigFormGUI
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
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
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		return;
		// shuffle
		$shuffle = new \ilCheckboxInputGUI($DIC->language()->txt( "shuffle_answers" ), "shuffle");
		$shuffle->setValue( 1 );
		$shuffle->setChecked( $this->getQuestion()->isShuffle() );
		$shuffle->setRequired( false );
		$this->addItem($shuffle);
		
		if( !$this->isLearningModuleContext() )
		{
			// Answer types
			$types = new \ilSelectInputGUI($DIC->language()->txt( "answer_types" ), "types");
			$types->setRequired( false );
			$types->setValue( ($this->getQuestion()->isAllowImages()) ? 0 : 1 );
			$types->setOptions( array(
					0 => $DIC->language()->txt( 'answers_singleline' ),
					1 => $DIC->language()->txt( 'answers_multiline' ),
				)
			);
			$this->addItem( $types );
		}
		
		if( $this->getQuestion()->isAllowImages() )
		{
			// thumb size
			$thumb_size = new \ilNumberInputGUI($DIC->language()->txt( "thumb_size" ), "thumb_size");
			$thumb_size->setSuffix($DIC->language()->txt("thumb_size_unit_pixel"));
			$thumb_size->setMinValue( 20 );
			$thumb_size->setDecimals( 0 );
			$thumb_size->setSize( 6 );
			$thumb_size->setInfo( $DIC->language()->txt( 'thumb_size_info' ) );
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
		return;
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		// Choices
		$choices = new \ilSingleChoiceWizardInputGUI($DIC->language()->txt( "answers" ), "choice");
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
			$choices->setValues([new \ASS_AnswerBinaryStateImage()]);
		}
		
		$this->addItem( $choices );
	}
	
	protected function fillQuestionSpecificProperties() {
		return;
		$this->getQuestion()->setShuffle($this->getInput("shuffle"));
		
		if ($this->getQuestion()->isAllowImages()) {
			$this->getQuestion()->setThumbSize((int)$this->getInput("thumb_size"));
		}
		
		if( !$this->isLearningModuleContext() )
		{
			$this->getQuestion()->setAllowImages($this->getInput("types"));
		}
	}
	
	protected function fillAnswerSpecificProperties() {
		return;
		/** @var \ilSingleChoiceWizardInputGUI $choices */
		$choices = $this->getItemByPostVar("choice");
		$this->getQuestion()->setAnswersFromBinaryState($choices->getValues());
	}
}
