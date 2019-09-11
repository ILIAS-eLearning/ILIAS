<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy;

use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\MultipleChoiceScoringConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\MultipleChoiceEditorConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;
use ilCheckboxInputGUI;
use ilNumberInputGUI;
use ilSelectInputGUI;

/**
 * Class SingleChoiceQuestionGUI
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class SingleChoiceQuestionGUI extends LegacyFormGUIBase {
	const VAR_MCE_SHUFFLE = 'shuffle';
	const VAR_MCE_THUMB_SIZE = 'thumbsize';
	const VAR_MCE_IS_SINGLELINE = 'singleline';
	
	const STR_TRUE = "true";
	const STR_FALSE = "false";
	
	const VAR_MCDD_TEXT = 'mcdd_text' ;
	const VAR_MCDD_IMAGE = 'mcdd_image';
	const VAR_MCSD_SELECTED = 'mcsd_selected';

	protected function createDefaultPlayConfiguration(): QuestionPlayConfiguration
	{
	    return QuestionPlayConfiguration::create
	    (
	        MultipleChoiceEditorConfiguration::create(false, 1),
	        new MultipleChoiceScoringConfiguration());
	}
	
	/**
	 * @param QuestionPlayConfiguration $play
	 *
	 * @return array
	 */
	protected function collectFields() : array {
	    $fields = [];
	    $fields[] = new AnswerOptionFormFieldDefinition(
	        $this->lang->txt('asq_label_answer_text'),
	        AnswerOptionFormFieldDefinition::TYPE_TEXT,
	        self::VAR_MCDD_TEXT
	        );
	    
	    $fields[] = new AnswerOptionFormFieldDefinition(
	        $this->lang->txt('asq_label_answer_image'),
	        AnswerOptionFormFieldDefinition::TYPE_IMAGE,
	        self::VAR_MCDD_IMAGE
	        );

	    $fields[] = new AnswerOptionFormFieldDefinition(
	        $this->lang->txt('asq_label_checked'),
	        AnswerOptionFormFieldDefinition::TYPE_NUMBER,
	        self::VAR_MCSD_SELECTED
	        );

		return $fields;
	}

	/**
	 * @param QuestionPlayConfiguration $play
	 */
	protected function initiatePlayConfiguration(?QuestionPlayConfiguration $play): void {
	    $shuffle = new ilCheckboxInputGUI(
	        $this->lang->txt('asq_label_shuffle'),
	        self::VAR_MCE_SHUFFLE);
	    
	    $shuffle->setValue(1);
	    $this->addItem($shuffle);
	    
	    $singleline = new ilSelectInputGUI(
	        $this->lang->txt('asq_label_editor'),
	        self::VAR_MCE_IS_SINGLELINE);
	    
	    $singleline->setOptions([
	        self::STR_TRUE => $this->lang->txt('asq_option_single_line'),
	        self::STR_FALSE => $this->lang->txt('asq_option_multi_line')]);
	    
	    $this->addItem($singleline);
	    
	    $thumb_size = new ilNumberInputGUI(
	        $this->lang->txt('asq_label_thumb_size'),
	        self::VAR_MCE_THUMB_SIZE);
	    $thumb_size->setInfo($this->lang->txt('asq_description_thumb_size'));
	    $this->addItem($thumb_size);
	    
	    if ($play !== null) {
	        /** @var MultipleChoiceEditorConfiguration $config */
	        $config = $play->getEditorConfiguration();
	        $shuffle->setChecked($config->isShuffleAnswers());
	        $thumb_size->setValue($config->getThumbnailSize());
	        $singleline->setValue($config->isSingleLine() ? self::STR_TRUE : self::STR_FALSE);
	    }
	}

	/**
	 * @return QuestionPlayConfiguration
	 */
	protected function readPlayConfiguration(): QuestionPlayConfiguration {

		return QuestionPlayConfiguration::create(
			MultipleChoiceEditorConfiguration::create(
				$_POST[self::VAR_MCE_SHUFFLE],
				1,
			    intval($_POST[self::VAR_MCE_THUMB_SIZE]),
			    $_POST[self::VAR_MCE_IS_SINGLELINE] === self::STR_TRUE
			),
		    new MultipleChoiceScoringConfiguration()
		);
	}
}
