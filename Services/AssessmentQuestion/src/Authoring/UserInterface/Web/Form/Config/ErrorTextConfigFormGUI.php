<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form;


/**
 * Class ErrorTextConfigFormGUI
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class ErrorTextConfigFormGUI extends AbstractQuestionConfigFormGUI
{
	/**
	 * @var \ilAsqErrorTextQuestion
	 */
	protected $question;
	
	protected function addQuestionSpecificProperties()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		// errortext
		$errortext = new \ilTextAreaInputGUI($DIC->language()->txt( "errortext" ), "errortext");
		$errortext->setValue( $this->getQuestion()->getErrorText() );
		$errortext->setRequired( TRUE );
		$errortext->setInfo( $DIC->language()->txt( "errortext_info" ) );
		$errortext->setRows( 10 );
		$errortext->setCols( 80 );
		$this->addItem( $errortext );
		
		if (!$this->isLearningModuleContext())
		{
			// textsize
			$textsize = new \ilNumberInputGUI($DIC->language()->txt( "textsize" ), "textsize");
			$textsize->setValue( strlen( $this->getQuestion()->getTextSize() ) ? $this->getQuestion()->getTextSize() : 100.0 );
			$textsize->setInfo( $DIC->language()->txt( "textsize_errortext_info" ) );
			$textsize->setSize( 6 );
			$textsize->setSuffix( "%" );
			$textsize->setMinValue( 10 );
			$textsize->setRequired( true );
			$this->addItem( $textsize );
		}
	}
	
	protected function addAnswerSpecificProperties()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$header = new \ilFormSectionHeaderGUI();
		$header->setTitle( $DIC->language()->txt( "errors_section" ) );
		$this->addItem( $header );
		
		$errordata = new \ilErrorTextWizardInputGUI($DIC->language()->txt( "errors" ), "errordata");
		$errordata->setKeyName( $DIC->language()->txt( 'text_wrong' ) );
		$errordata->setValueName( $DIC->language()->txt( 'text_correct' ) );
		$errordata->setValues( $this->getQuestion()->getErrorData() );
		$this->addItem( $errordata );
		
		// points for wrong selection
		$points_wrong = new \ilNumberInputGUI($DIC->language()->txt( "points_wrong" ), "points_wrong");
		$points_wrong->allowDecimals(true);
		$points_wrong->setMaxValue(0);
		$points_wrong->setMaxvalueShouldBeLess(true);
		$points_wrong->setValue( $this->getQuestion()->getPointsWrong() );
		$points_wrong->setInfo( $DIC->language()->txt( "points_wrong_info" ) );
		$points_wrong->setSize( 6 );
		$points_wrong->setRequired( true );
		$this->addItem( $points_wrong );
	}
	
	protected function fillQuestionSpecificProperties()
	{
		$questiontext = $_POST["question"];
		$this->getQuestion()->setQuestion( $questiontext );
		$this->getQuestion()->setErrorText( $_POST["errortext"] );
		$points_wrong = str_replace( ",", ".", $_POST["points_wrong"] );
		if (strlen( $points_wrong ) == 0)
			$points_wrong = -1.0;
		$this->getQuestion()->setPointsWrong( $points_wrong );
		
		if (!$this->isLearningModuleContext())
		{
			$this->getQuestion()->setTextSize( $_POST["textsize"] );
		}
	}
	
	protected function fillAnswerSpecificProperties()
	{
		if (is_array( $_POST['errordata']['key'] ))
		{
			$this->getQuestion()->flushErrorData();
			foreach ($_POST['errordata']['key'] as $idx => $val)
			{
				$this->getQuestion()->addErrorData( $val,
					$_POST['errordata']['value'][$idx],
					$_POST['errordata']['points'][$idx]
				);
			}
		}
	}
}
