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
		
		if (!$this->isLearningModuleContext())
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
		if ($this->isLearningModuleContext())
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
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$this->getQuestion()->setShuffle( $_POST["shuffle"] );
		
		$selectionLimit = (int)$this->getItemByPostVar('selection_limit')->getValue();
		$this->getQuestion()->setSelectionLimit($selectionLimit > 0 ? $selectionLimit : null);
		
		$this->getQuestion()->setSpecificFeedbackSetting( $_POST['feedback_setting'] );
		
		$this->getQuestion()->setMultilineAnswerSetting( $_POST["types"] );
		if (is_array( $_POST['choice']['imagename'] ) && $_POST["types"] == 1)
		{
			$this->getQuestion()->isSingleline = true;
			\ilUtil::sendInfo( $DIC->language()->txt( 'info_answer_type_change' ), true );
		}
		else
		{
			$this->getQuestion()->isSingleline = ($_POST["types"] == 0) ? true : false;
		}
		$this->getQuestion()->setThumbSize( (strlen( $_POST["thumb_size"] )) ? $_POST["thumb_size"] : "" );
	}
	
	protected function fillAnswerSpecificProperties()
	{
		// Delete all existing answers and create new answers from the form data
		$this->getQuestion()->flushAnswers();
		if ($this->getQuestion()->isSingleline)
		{
			foreach ($_POST['choice']['answer'] as $index => $answertext)
			{
				$answertext = \ilUtil::secureString($answertext);
				
				$picturefile    = $_POST['choice']['imagename'][$index];
				$file_org_name  = $_FILES['choice']['name']['image'][$index];
				$file_temp_name = $_FILES['choice']['tmp_name']['image'][$index];
				
				if (strlen( $file_temp_name ))
				{
					// check suffix						
					$suffix = strtolower( array_pop( explode( ".", $file_org_name ) ) );
					if (in_array( $suffix, array( "jpg", "jpeg", "png", "gif" ) ))
					{
						// upload image
						$filename = $this->getQuestion()->buildHashedImageFilename( $file_org_name );
						if ($this->getQuestion()->setImageFile( $filename, $file_temp_name ) == 0)
						{
							$picturefile = $filename;
						}
					}
				}
				$this->getQuestion()->addAnswer( $answertext,
					$_POST['choice']['points'][$index],
					$_POST['choice']['points_unchecked'][$index],
					$index,
					$picturefile
				);
			}
		}
		else
		{
			foreach ($_POST['choice']['answer'] as $index => $answer)
			{
				$answertext = $answer;
				$this->getQuestion()->addAnswer( $answertext,
					$_POST['choice']['points'][$index],
					$_POST['choice']['points_unchecked'][$index],
					$index
				);
			}
		}
	}
}
