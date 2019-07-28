<?php
namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form;

use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Section\QuestionDataSection;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Section\QuestionTypeSection;
use ilTextInputGUI;

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
use mysql_xdevapi\Exception;
use srag\CustomInputGUIs\SrAssessment\MultiLineInputGUI\MultiLineInputGUI;

class EditQuestionForm extends ilPropertyFormGUI {
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
	 * @var string
	 */
	protected $form_post_url;

	/**
	 * QuestionFormGUI constructor.
	 *
	 * @param Question $question
	 */
	public function __construct(Question $question, $form_post_url) {
		$this->setFormAction($form_post_url);
		$this->initForm($question);

		parent::__construct();
	}


	/**
	 * Init settings property form
	 *
	 * @access private
	 *
	 * @param Question $question
	 */
	private function initForm(Question $question) {

		$id = new ilHiddenInputGUI(self::VAR_AGGREGATE_ID);
		$id->setValue($question->getAggregateId()->getId());
		$this->addItem($id);
		$this->initQuestionDataConfiguration($question);

	//	$this->initiatePlayConfiguration();

		$this->addCommandButton('save', 'Save');
	}

	/**
	 * @param Question $question_data
	 */
	private function initQuestionDataConfiguration(Question $question): void {
		$question_data_section = new QuestionDataSection($question);
		foreach($question_data_section->getInputItems() as $item) {
			$this->addItem($item);
		}
	}
}
