<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Legacy;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionData;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionDto;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionLegacyData;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Scoring\MultipleChoiceScoringDefinition;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Config\AnswerOptionForm;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;
use ILIAS\AssessmentQuestion\Play\Editor\MultipleChoiceEditorConfiguration;
use ILIAS\AssessmentQuestion\Play\Editor\MultipleChoiceEditorDisplayDefinition;
use Exception;
use ilCheckboxInputGUI;
use ilDurationInputGUI;
use ilHiddenInputGUI;
use ilNumberInputGUI;
use ilPropertyFormGUI;
use ilTextAreaInputGUI;
use ilTextInputGUI;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject;

class SingleChoiceQuestionGUI extends ilPropertyFormGUI {
	const VAR_AGGREGATE_ID = 'aggregate_id';

	const VAR_TITLE = 'title';
	const VAR_AUTHOR = 'author';
	const VAR_DESCRIPTION = 'description';
	const VAR_QUESTION = 'question';

	const VAR_WORKING_TIME = 'working_time';

	const VAR_MCE_SHUFFLE = 'shuffle';
	const VAR_MCE_THUMB_SIZE = 'thumbsize';
	const VAR_MCE_IS_SINGLELINE = 'singleline';
	
	const STR_TRUE = "true";
	const STR_FALSE = "false";
	
	const VAR_MCDD_TEXT = 'mcdd_text' ;
	const VAR_MCDD_IMAGE = 'mcdd_image';
	const VAR_MCSD_SELECTED = 'mcsd_selected';

	const VAR_LEGACY = 'legacy';

	const SECONDS_IN_MINUTE = 60;
	const SECONDS_IN_HOUR = 3600;

	/**
	 * QuestionFormGUI constructor.
	 *
	 * @param QuestionDto $question
	 */
	public function __construct($question) {
		$this->initForm($question);

		parent::__construct();
	}


	/**
	 * Init settings property form
	 *
	 * @access private
	 *
	 * @param QuestionDto $question
	 */
	private function initForm(QuestionDto $question) {
		$id = new ilHiddenInputGUI(self::VAR_AGGREGATE_ID);
		$id->setValue($question->getId());
		$this->addItem($id);

		$legacy = new ilHiddenInputGUI(self::VAR_LEGACY);
		$legacy->setValue(json_encode($question->getLegacyData()));
		$this->addItem($legacy);

		$this->initQuestionDataConfiguration($question->getData());

		$this->initiatePlayConfiguration($question->getPlayConfiguration());

		$this->addItem(new AnswerOptionForm(
			               'Answers',
			               $this->collectFields($question->getPlayConfiguration()),
			               $question->getAnswerOptions()->getOptions())
		);

		$this->addCommandButton('save', 'Save');
	}

	const SCORING_DEFINITION_SUFFIX = 'Definition';
	const EDITOR_DEFINITION_SUFFIX = 'DisplayDefinition';


	/**
	 * @param QuestionPlayConfiguration $play
	 *
	 * @return array
	 */
	private function collectFields(?QuestionPlayConfiguration $play) : array {
	    $fields = [];
		$fields[] = new AnswerOptionFormFieldDefinition(
			'Answer Text',
			AnswerOptionFormFieldDefinition::TYPE_TEXT,
			self::VAR_MCDD_TEXT
		);

		$fields[] = new AnswerOptionFormFieldDefinition(
			'Answer Image',
			AnswerOptionFormFieldDefinition::TYPE_IMAGE,
			self::VAR_MCDD_IMAGE
		);

		$fields[] = new AnswerOptionFormFieldDefinition(
			'Checked',
			AnswerOptionFormFieldDefinition::TYPE_NUMBER,
			self::VAR_MCSD_SELECTED
		);

		return $fields;
	}

	public function getQuestion() : QuestionDto {
		$question = new QuestionDto();
		$question->setId($_POST[self::VAR_AGGREGATE_ID]);
		$question->setLegacyData(AbstractValueObject::deserialize($_POST[self::VAR_LEGACY]));

		$question->setData($this->readQuestionData());

		$question->setPlayConfiguration($this->readPlayConfiguration());

		$question->setAnswerOptions($this->readAnswerOptions($question->getPlayConfiguration()));

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
	    }
	}


	/**
	 * @param QuestionPlayConfiguration $play
	 */
	private function initiatePlayConfiguration(?QuestionPlayConfiguration $play): void {
	    $shuffle = new ilCheckboxInputGUI('shuffle', self::VAR_MCE_SHUFFLE);
	    $shuffle->setValue(1);
	    $this->addItem($shuffle);
	    
	    $thumb_size = new ilNumberInputGUI('thumb size', self::VAR_MCE_THUMB_SIZE);
	    $this->addItem($thumb_size);
	    
	    $singleline = new \ilSelectInputGUI('single line', self::VAR_MCE_IS_SINGLELINE);
	    $singleline->setOptions([self::STR_TRUE => 'Singleline', self::STR_FALSE => 'Multiline']);
	    $this->addItem($singleline);
	    
	    if ($play !== null) {
	        $config = $play->getEditorConfiguration();
	        $shuffle->setChecked($config->isShuffleAnswers());
	        $thumb_size->setValue($config->getThumbnailSize());
	        $singleline->setValue($config->isSingleLine() ? self::STR_TRUE : self::STR_FALSE);
	    }
	}

	/**
	 * @return QuestionData
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
	private function readPlayConfiguration(): QuestionPlayConfiguration {

		return QuestionPlayConfiguration::create(
			MultipleChoiceEditorConfiguration::create(
				$_POST[self::VAR_MCE_SHUFFLE],
				1,
			    $_POST[self::VAR_MCE_THUMB_SIZE],
			    $_POST[self::VAR_MCE_IS_SINGLELINE] === self::STR_TRUE
			)
		);
	}

	private function readAnswerOptions(QuestionPlayConfiguration $play) : AnswerOptions {
		$options = new AnswerOptions();

		$count = intval($_POST[Answeroptionform::COUNT_POST_VAR]);

		for ($i = 1; $i <= $count; $i++) {
			$options->addOption(new AnswerOption
			                    (
				                    $i,
				                    new MultipleChoiceEditorDisplayDefinition(
					                    $_POST[$i . self::VAR_MCDD_TEXT],
					                    $_POST[$i . self::VAR_MCDD_IMAGE]
				                    ),
				                    new MultipleChoiceScoringDefinition(
					                    $_POST[$i . self::VAR_MCSD_SELECTED],
					                    0
				                    )
			                    ));
		}

		return $options;
	}

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
