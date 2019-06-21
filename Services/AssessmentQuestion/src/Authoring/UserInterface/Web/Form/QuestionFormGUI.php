<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\DI\Container;
use ILIAS\UI\Implementation\Component\Input\Field\Numeric;
use ilImageFileInputGUI;
use ilNumberInputGUI;
use \ilPropertyFormGUI;
use \ilTextInputGUI;
use ilUtil;
use srag\CustomInputGUIs\SrAssessment\MultiLineInputGUI\MultiLineInputGUI;

//JUST A DEMO

class QuestionFormGUI extends ilPropertyFormGUI {
	const QuestionVAR_TITLE = 'title';
	const QuestionVAR_POSSIBLEANSWER = 'possibleanswers';
	const QuestionVAR_ANSWER = 'answer';
	const QuestionVAR_POINTS = 'points';
	const QuestionVAR_IMAGE = 'image';
	const QuestionVAR_DESCRIPTION = 'description';


	/**
	 * QuestionFormGUI constructor.
	 *
	 * @param Question $question
	 */
	public function __construct($question) {
		$this->initForm($question);


		parent::__construct();
	}

	/**
	 * Init settings property form
	 *
	 * @access private
	 */
	private function initForm($question) {

		$title = new ilTextInputGUI('title', self::QuestionVAR_TITLE);
		$title->setRequired(true);
		$title->setValue($question->getTitle());
		$this->addItem($title);

		$description = new ilTextInputGUI('description',self::QuestionVAR_DESCRIPTION);
		$title->setValue($question->getDescription());
		$this->addItem($description);


		$muliliine = new MultiLineInputGUI('answers',self::QuestionVAR_POSSIBLEANSWER);
		$muliliine->addInput(new ilTextInputGUI('answer', self::QuestionVAR_ANSWER));
		$muliliine->addInput(new ilImageFileInputGUI('image', self::QuestionVAR_POINTS));
		$muliliine->addInput(new ilNumberInputGUI('points', self::QuestionVAR_POINTS));
		$this->addItem($muliliine);


		$this->addCommandButton('save', 'Save');
	}

	public function getQuestionTitle() : string {
		return $_POST[self::QuestionVAR_TITLE];
	}

	public function getQuestionDescription() : string {
		return $_POST[self::QuestionVAR_DESCRIPTION];
	}
}
