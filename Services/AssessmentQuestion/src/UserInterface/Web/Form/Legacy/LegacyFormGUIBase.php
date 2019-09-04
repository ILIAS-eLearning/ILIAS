<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form\Legacy;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\QuestionData;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionForm;
use Exception;
use ilDurationInputGUI;
use ilHiddenInputGUI;
use ilPropertyFormGUI;
use ilTextAreaInputGUI;
use ilTextInputGUI;

/**
 * Class MultipleChoiceQuestionGUI
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class LegacyFormGUIBase extends ilPropertyFormGUI {
	const VAR_AGGREGATE_ID = 'aggregate_id';

	const VAR_TITLE = 'title';
	const VAR_AUTHOR = 'author';
	const VAR_DESCRIPTION = 'description';
	const VAR_QUESTION = 'question';
	const VAR_WORKING_TIME = 'working_time';

	const VAR_LEGACY = 'legacy';

	const SECONDS_IN_MINUTE = 60;
	const SECONDS_IN_HOUR = 3600;

	const FORM_PART_LINK = 'form_part_link';
	
	/**
	 * QuestionFormGUI constructor.
	 *
	 * @param QuestionDto $question
	 */
	public function __construct($question) {
	    $this->initForm($question);
	    $this->setMultipart(true);
	    
	    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	        $this->setValuesByPost();
	    }
	    
	    parent::__construct();
	}


    /**
     * @param QuestionDto $question
     */
	private function initForm(QuestionDto $question) {
	    global $DIC;
	    
	    $id = new ilHiddenInputGUI(self::VAR_AGGREGATE_ID);
	    $id->setValue($question->getId());
	    $this->addItem($id);
	    
	    $form_part_link = new \ilHiddenInputGUI(self::FORM_PART_LINK);
	    $form_part_link->setValue($DIC->ctrl()->getLinkTargetByClass('ilAsqQuestionAuthoringGUI', \ilAsqQuestionAuthoringGUI::CMD_GET_FORM_SNIPPET));
	    $this->addItem($form_part_link);
	    
	    $legacy = new ilHiddenInputGUI(self::VAR_LEGACY);
	    $legacy->setValue(json_encode($question->getLegacyData()));
	    $this->addItem($legacy);
	    
	    $this->initQuestionDataConfiguration($question->getData());
	    
	    if (is_null($question->getPlayConfiguration())) {
	        $question->setPlayConfiguration($this->createDefaultPlayConfiguration());
	    }
	    
	    $this->initiatePlayConfiguration($question->getPlayConfiguration());
	    
        $this->option_form = new AnswerOptionForm(
            'Answers',
            $question->getPlayConfiguration(),
            $question->getAnswerOptions()->getOptions());
        
        $this->addItem($this->option_form);
	}

    /**
     * @return QuestionDto
     * @throws Exception
     */
	public function getQuestion() : QuestionDto {
	    $question = new QuestionDto();
	    $question->setId($_POST[self::VAR_AGGREGATE_ID]);
	    
	    $question->setLegacyData(AbstractValueObject::deserialize($_POST[self::VAR_LEGACY]));
	    
	    $question->setData($this->readQuestionData());
	    
	    $question->setPlayConfiguration($this->readPlayConfiguration());
	    
	    if (!is_null($this->option_form)) {
	        $this->option_form->setConfiguration($question->getPlayConfiguration());
	        $question->setAnswerOptions($this->option_form->readAnswerOptions());
	    }
	    
	    return $question;
	}


	/**
	 * @param QuestionData $data
	 */
	private function initQuestionDataConfiguration(?QuestionData $data): void {
	    $title = new ilTextInputGUI('title', self::VAR_TITLE);
	    $title->setRequired(true);
	    $this->addItem($title);
	    
	    $author = new ilTextInputGUI('author', self::VAR_AUTHOR);
	    $author->setRequired(true);
	    $this->addItem($author);
	    
	    $description = new ilTextInputGUI('description', self::VAR_DESCRIPTION);
	    $this->addItem($description);
	    
	    $question_text = new ilTextAreaInputGUI('question', self::VAR_QUESTION);
	    $question_text->setRequired(true);
	    $question_text->setRows(10);
	    $this->addItem($question_text);
	    
	    $working_time = new ilDurationInputGUI('working_time', self::VAR_WORKING_TIME);
	    $working_time->setShowHours(TRUE);
	    $working_time->setShowMinutes(TRUE);
	    $working_time->setShowSeconds(TRUE);
	    $this->addItem($working_time);
	    
	    if ($data !== null) {
	        $title->setValue($data->getTitle());
	        $author->setValue($data->getAuthor());
	        $description->setValue($data->getDescription());
	        $question_text->setValue($data->getQuestionText());
	        $working_time->setHours(floor($data->getWorkingTime() / self::SECONDS_IN_HOUR));
	        $working_time->setMinutes(floor($data->getWorkingTime() / self::SECONDS_IN_MINUTE));
	        $working_time->setSeconds($data->getWorkingTime() % self::SECONDS_IN_MINUTE);
	    } else {
	        global $DIC;
	        $author->setValue($DIC->user()->fullname);
	    }
	}


	/**
	 * @param QuestionPlayConfiguration $play
	 */
	protected abstract function initiatePlayConfiguration(?QuestionPlayConfiguration $play): void ;

    /**
     * @return QuestionData
     * @throws Exception
     */
	private function readQuestionData(): QuestionData {
		return QuestionData::create(
			$_POST[self::VAR_TITLE],
			$_POST[self::VAR_AUTHOR],
			$_POST[self::VAR_QUESTION],
			$_POST[self::VAR_DESCRIPTION],
		    $this->readWorkingTime($_POST[self::VAR_WORKING_TIME])
		);
	}

	/**
	 * @return QuestionPlayConfiguration
	 */
	protected abstract function readPlayConfiguration(): QuestionPlayConfiguration;

    /**
     * @return AnswerOptions
     */
	protected abstract function readAnswerOptions() : AnswerOptions;

	/**
	 * @return QuestionPlayConfiguration
	 */
	protected abstract function createDefaultPlayConfiguration() : QuestionPlayConfiguration;
	
    /**
     * @param $postval
     *
     * @return int
     * @throws Exception
     */
	private function readWorkingTime($postval) : int {
		$HOURS = 'hh';
		$MINUTES = 'mm';
		$SECONDS = 'ss';

		if (
			is_array($postval) &&
			array_key_exists($HOURS, $postval) &&
			array_key_exists($MINUTES, $postval) &&
			array_key_exists($SECONDS, $postval)) {
			return $postval[$HOURS] * self::SECONDS_IN_HOUR + $postval[$MINUTES] * self::SECONDS_IN_MINUTE + $postval[$SECONDS];
		} else {
			throw new Exception("This should be impossible, please fix implementation");
		}
	}
}
