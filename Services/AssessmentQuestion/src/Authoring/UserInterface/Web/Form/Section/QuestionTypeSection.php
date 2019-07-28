<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Section;

use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Input\QuestionTypeSelectInput;

class QuestionTypeSection {

	const LNG_KEY_LABEL = 'question_type';
	const LNG_KEY_BYLINE = '';
	protected $answer_types;


	public function __construct($answer_types) {
		$this->answer_types = $answer_types;
	}


	public function getSection() {
		global $DIC;
		$ui = $DIC->ui()->factory();

		$question_type_select_input = new QuestionTypeSelectInput($this->answer_types);

		$section = $ui->input()->field()->section(
			[
				$question_type_select_input->getPostKey() => $question_type_select_input->getInput()
			],
			self::LNG_KEY_LABEL, self::LNG_KEY_BYLINE
		);

		return $section;
	}
}