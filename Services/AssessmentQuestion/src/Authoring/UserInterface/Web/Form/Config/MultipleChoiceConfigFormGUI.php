<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form;


/**
 * Class MultipleChoiceConfigFormGUI
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class MultipleChoiceConfigFormGUI extends AbstractQuestionConfigFormGUI
{
	/**
	 * @var \ilAsqMultipleChoiceQuestion
	 */
	protected $question;
	
	protected function addQuestionSpecificProperties()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		// shuffle
		$shuffle = new \ilCheckboxInputGUI($DIC->language()->txt( "shuffle_answers" ), "shuffle");
		$shuffle->setValue( 1 );
		$shuffle->setChecked( $this->getQuestion()->getShuffle() );
		$shuffle->setRequired( FALSE );
		$this->addItem( $shuffle );
		
		require_once 'Services/Form/classes/class.ilNumberInputGUI.php';
		$selLim = new \ilNumberInputGUI($DIC->language()->txt('ass_mc_sel_lim_setting'), 'selection_limit');
		$selLim->setInfo($DIC->language()->txt('ass_mc_sel_lim_setting_desc'));
		$selLim->setSize(2);
		$selLim->setRequired(false);
		$selLim->allowDecimals(false);
		$selLim->setMinvalueShouldBeGreater(false);
		$selLim->setMaxvalueShouldBeLess(false);
		$selLim->setMinValue(1);
		$selLim->setMaxValue($this->getQuestion()->getAnswerCount());
		$selLim->setValue($this->getQuestion()->getSelectionLimit());
		$this->addItem($selLim);
		
		if ($this->getQuestion()->getId())
		{
			$hidden = new \ilHiddenInputGUI("", "ID");
			$hidden->setValue( $this->getQuestion()->getId() );
			$this->addItem( $hidden );
		}
		
		$isSingleline = $this->getEditAnswersSingleLine();
		
		if (!$this->getQuestion()->getSelfAssessmentEditingMode())
		{
			// Answer types
			$types = new \ilSelectInputGUI($DIC->language()->txt( "answer_types" ), "types");
			$types->setRequired( false );
			$types->setValue( ($isSingleline) ? 0 : 1 );
			$types->setOptions( array(
					0 => $DIC->language()->txt( 'answers_singleline' ),
					1 => $DIC->language()->txt( 'answers_multiline' ),
				)
			);
			$this->addItem( $types );
		}
		
		if ($isSingleline)
		{
			// thumb size
			$thumb_size = new \ilNumberInputGUI($DIC->language()->txt( "thumb_size" ), "thumb_size");
			$thumb_size->setSuffix($DIC->language()->txt("thumb_size_unit_pixel"));
			$thumb_size->setMinValue( 20 );
			$thumb_size->setDecimals( 0 );
			$thumb_size->setSize( 6 );
			$thumb_size->setInfo( $DIC->language()->txt( 'thumb_size_info' ) );
			$thumb_size->setValue( $this->getQuestion()->getThumbSize() );
			$thumb_size->setRequired( false );
			$this->addItem( $thumb_size );
			return $isSingleline;
		}
		return $isSingleline;
	}
	
	protected function addAnswerSpecificProperties()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		// Choices
		include_once "./Modules/TestQuestionPool/classes/class.ilMultipleChoiceWizardInputGUI.php";
		$choices = new \ilMultipleChoiceWizardInputGUI($DIC->language()->txt( "answers" ), "choice");
		$choices->setRequired( true );
		$choices->setQuestionObject( $this->getQuestion() );
		$isSingleline = $this->getEditAnswersSingleLine();
		$choices->setSingleline( $isSingleline );
		$choices->setAllowMove( false );
		if ($this->getQuestion()->getSelfAssessmentEditingMode())
		{
			$choices->setSize( 40 );
			$choices->setMaxLength( 800 );
		}
		if ($this->getQuestion()->getAnswerCount() == 0)
			$this->getQuestion()->addAnswer( "", 0, 0, 0 );
		$choices->setValues( $this->getQuestion()->getAnswers() );
		$this->addItem( $choices );
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
