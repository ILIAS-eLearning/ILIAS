<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form;


/**
 * Class ImagemapQuestionConfigFormGUI
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class ImagemapQuestionConfigFormGUI extends AbstractQuestionConfigFormGUI
{
	/**
	 * @var \ilAsqErrorTextQuestion
	 */
	protected $question;
	
	protected function addQuestionSpecificProperties()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$radioGroup = new \ilRadioGroupInputGUI($DIC->language()->txt( 'tst_imap_qst_mode' ), 'is_multiple_choice');
		$radioGroup->setValue( $this->getQuestion()->getIsMultipleChoice() );
		$modeSingleChoice = new \ilRadioOption($DIC->language()->txt( 'tst_imap_qst_mode_sc'),
			assImagemapQuestion::MODE_SINGLE_CHOICE);
		$modeMultipleChoice = new \ilRadioOption($DIC->language()->txt( 'tst_imap_qst_mode_mc'),
			assImagemapQuestion::MODE_MULTIPLE_CHOICE);
		$radioGroup->addOption( $modeSingleChoice );
		$radioGroup->addOption( $modeMultipleChoice );
		$this->addItem( $radioGroup );
		
		$image = new \ilImagemapFileInputGUI($DIC->language()->txt( 'image' ), 'image');
		$image->setPointsUncheckedFieldEnabled( $this->getQuestion()->getIsMultipleChoice() );
		$image->setRequired( true );
		
		if (strlen( $this->getQuestion()->getImageFilename() ))
		{
			$image->setImage( $this->getQuestion()->getImagePathWeb() . $this->getQuestion()->getImageFilename() );
			$image->setValue( $this->getQuestion()->getImageFilename() );
			$image->setAreas( $this->getQuestion()->getAnswers() );
			$assessmentSetting = new \ilSetting("assessment");
			$linecolor         = (strlen( $assessmentSetting->get( "imap_line_color" )
			)) ? "\"#" . $assessmentSetting->get( "imap_line_color" ) . "\"" : "\"#FF0000\"";
			$image->setLineColor( $linecolor );
			$image->setImagePath( $this->getQuestion()->getImagePath() );
			$image->setImagePathWeb( $this->getQuestion()->getImagePathWeb() );
		}
		$this->addItem( $image );
		
		$imagemapfile = new \ilHtmlImageMapFileInputGUI($DIC->language()->txt('add_imagemap'), 'imagemapfile');
		$imagemapfile->setRequired(false);
		$this->addItem($imagemapfile);
	}
	
	protected function addAnswerSpecificProperties()
	{
		// Nothing to do here since selectable areas are handled in question-specific-form part
		// due to their immediate dependency to the image. I decide to not break up the interfaces
		// more just to support this very rare case. tl;dr: See the issue, ignore it.
	}
	
	protected function fillQuestionSpecificProperties()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		if ($DIC->ctrl()->getCmd() != 'deleteImage')
		{
			if (strlen( $_FILES['image']['tmp_name'] ) == 0)
			{
				$this->getQuestion()->setImageFilename( $_POST["image_name"] );
			}
		}
		if (strlen( $_FILES['image']['tmp_name'] ))
		{
			if ($this->isLearningModuleContext() && $this->getQuestion()->getId() < 1)
				$this->getQuestion()->createNewQuestion();
			$this->getQuestion()->setImageFilename( $_FILES['image']['name'], $_FILES['image']['tmp_name'] );
		}
		
		$this->getQuestion()->setIsMultipleChoice($_POST['is_multiple_choice'] == assImagemapQuestion::MODE_MULTIPLE_CHOICE);
	}
	
	protected function fillAnswerSpecificProperties()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		if ($DIC->ctrl()->getCmd() != 'deleteImage')
		{
			$this->getQuestion()->flushAnswers();
			if (is_array( $_POST['image']['coords']['name'] ))
			{
				foreach ($_POST['image']['coords']['name'] as $idx => $name)
				{
					if( $this->getQuestion()->getIsMultipleChoice() && isset($_POST['image']['coords']['points_unchecked']) )
					{
						$pointsUnchecked = $_POST['image']['coords']['points_unchecked'][$idx];
					}
					else
					{
						$pointsUnchecked = 0.0;
					}
					
					$this->getQuestion()->addAnswer(
						$name,
						$_POST['image']['coords']['points'][$idx],
						$idx,
						$_POST['image']['coords']['coords'][$idx],
						$_POST['image']['coords']['shape'][$idx],
						$pointsUnchecked
					);
				}
			}
			
			if(strlen($_FILES['imagemapfile']['tmp_name']))
			{
				if($this->isLearningModuleContext() && $this->getQuestion()->getId() < 1)
				{
					$this->getQuestion()->createNewQuestion();
				}
				
				$this->getQuestion()->uploadImagemap($this->getItemByPostVar('imagemapfile')->getShapes());
			}
		}
	}
}
