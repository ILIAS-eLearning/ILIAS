<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form;

use ilCheckboxInputGUI;
use ilDurationInputGUI;
use ilHiddenInputGUI;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionData;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionDto;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Scoring\AvailableScorings;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Config\AnswerOptionForm;
use ILIAS\AssessmentQuestion\Play\Editor\AvailableEditors;
use ILIAS\AssessmentQuestion\Play\Editor\MultipleChoiceEditor;
use ILIAS\AssessmentQuestion\Play\Presenter\AvailablePresenters;
use ilImageFileInputGUI;
use ilNumberInputGUI;
use \ilPropertyFormGUI;
use ilSelectInputGUI;
use ilTextAreaInputGUI;
use \ilTextInputGUI;
use mysql_xdevapi\Exception;
use srag\CustomInputGUIs\SrAssessment\MultiLineInputGUI\MultiLineInputGUI;

class QuestionFormGUI extends ilPropertyFormGUI {
	const VAR_AGGREGATE_ID = 'aggregate_id';

	const VAR_TITLE = 'title';
	const VAR_AUTHOR = 'author';
	const VAR_DESCRIPTION = 'description';
	const VAR_QUESTION = 'question';

	const VAR_EDITOR = 'editor';
	const VAR_PRESENTER = 'presenter';
	const VAR_SCORING = 'scoring';
	const VAR_WORKING_TIME = 'working_time';

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
		$display_definition_class = $this->getDisplayDefinitionClass($play);

		$scoring_definition_class = $this->getScoringDefinitionClass($play);


		return array_merge(
			call_user_func(array($display_definition_class, 'getFields')),
			call_user_func(array($scoring_definition_class, 'getFields'))
		);
	}

	public function getQuestion() : QuestionDto {
		$question = new QuestionDto();
		$question->setId($_POST[self::VAR_AGGREGATE_ID]);

		$question->setData($this->readQuestionData());

		$question->setPlayConfiguration($this->readPlayConfiguration());

		$question->setAnswerOptions($this->readAnswerOptions($question->getPlayConfiguration()));

		return $question;
	}


	/**
	 * @param QuestionData $data
	 */
	private function initQuestionDataConfiguration(QuestionData $data): void {
		$title = new ilTextInputGUI('title', self::VAR_TITLE);
		$title->setRequired(true);
		$title->setValue($data->getTitle());
		$this->addItem($title);

		$author = new ilTextInputGUI('author', self::VAR_AUTHOR);
		$author->setRequired(true);
		$author->setValue($data->getAuthor());
		$this->addItem($author);

		$description = new ilTextInputGUI('description', self::VAR_DESCRIPTION);
		$description->setValue($data->getDescription());
		$this->addItem($description);

		$question_text = new ilTextAreaInputGUI('question', self::VAR_QUESTION);
		$question_text->setRequired(true);
		$question_text->setValue($data->getQuestionText());
		$question_text->setRows(10);
		$this->addItem($question_text);
	}


	/**
	 * @param QuestionPlayConfiguration $play
	 */
	private function initiatePlayConfiguration(?QuestionPlayConfiguration $play): void {
		$editor = $this->createSelectControl('editor',self::VAR_EDITOR, AvailableEditors::getAvailableEditors());
		$this->addItem($editor);

		$this->initiateEditorConfiguration($play);

		$presenter = $this->createSelectControl('presenter', self::VAR_PRESENTER, AvailablePresenters::getAvailablePresenters());
		$this->addItem($presenter);

		$scorings = $this->createSelectControl('scoring', self::VAR_SCORING, AvailableScorings::getAvailableScorings());
		$this->addItem($scorings);

		$working_time = new ilDurationInputGUI('working_time', self::VAR_WORKING_TIME);
		$working_time->setShowHours(TRUE);
		$working_time->setShowMinutes(TRUE);
		$working_time->setShowSeconds(TRUE);
		$this->addItem($working_time);

		if ($play !== null) {
			$editor->setValue($play->getEditorClass());
			$presenter->setValue($play->getPresenterClass());
			$scorings->setValue($play->getScoringClass());
			$working_time->setHours($play->getWorkingTime() / self::SECONDS_IN_HOUR);
			$working_time->setMinutes($play->getWorkingTime() / self::SECONDS_IN_MINUTE);
			$working_time->setSeconds($play->getWorkingTime() % self::SECONDS_IN_MINUTE);
		}
	}

	private function createSelectControl(string $title, string $post_var, array $options) : ilSelectInputGUI {
		$control = new ilSelectInputGUI($title, $post_var);
		$control->setOptions($options);
		return $control;
	}

	private function initiateEditorConfiguration(?QuestionPlayConfiguration $play) {
		$editor_class = $play ? $play->getEditorClass() : 'ILIAS\\AssessmentQuestion\\Play\\Editor\\MultipleChoiceEditor';

		foreach(
			call_user_func(
				array($editor_class, 'generateFields'),
				$play ? $play->getEditorConfiguration() : null
			) as $field) {
			$this->addItem($field);
		}
	}

	/**
	 * @return QuestionData
	 */
	private function readQuestionData(): QuestionData {
		return new QuestionData(
			$_POST[self::VAR_TITLE],
			$_POST[self::VAR_DESCRIPTION],
			$_POST[self::VAR_QUESTION],
			$_POST[self::VAR_AUTHOR]
		);
	}

	/**
	 * @return QuestionPlayConfiguration
	 */
	private function readPlayConfiguration(): QuestionPlayConfiguration {
		$editor_class = $_POST[self::VAR_EDITOR];

		return new QuestionPlayConfiguration(
			$_POST[self::VAR_PRESENTER],
			$editor_class,
			$_POST[self::VAR_SCORING],
			$this->readWorkingTime($_POST[self::VAR_WORKING_TIME]),
			call_user_func(array($editor_class, 'readConfig'))
		);
	}

	private function readAnswerOptions(QuestionPlayConfiguration $play) : AnswerOptions {
		$options = new AnswerOptions();

		$sd_class = $this->getScoringDefinitionClass($play);
		$dd_class = $this->getDisplayDefinitionClass($play);

		$count = intval($_POST[Answeroptionform::COUNT_POST_VAR]);

		for ($i = 1; $i <= $count; $i++) {
			$options->addOption(new AnswerOption
			                    (
			                    	$i,
				                    call_user_func(array($dd_class, 'getValueFromPost'), $i),
				                    call_user_func(array($sd_class, 'getValueFromPost'), $i)
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


	/**
	 * @param QuestionPlayConfiguration|null $play
	 *
	 * @return string
	 */
	private function getDisplayDefinitionClass(?QuestionPlayConfiguration $play): string {
		$display_definition_class
			= ($play === null ? AvailableEditors::getDefaultEditor() : $play->getEditorClass()) .
			self::EDITOR_DEFINITION_SUFFIX;

		return $display_definition_class;
}


	/**
	 * @param QuestionPlayConfiguration|null $play
	 *
	 * @return string
	 */
	private function getScoringDefinitionClass(?QuestionPlayConfiguration $play): string {
		$scoring_definition_class
			= ($play === null ? AvailableScorings::getDefaultScoring() : $play->getScoringClass()) .
			self::SCORING_DEFINITION_SUFFIX;

		return $scoring_definition_class;
}
}
