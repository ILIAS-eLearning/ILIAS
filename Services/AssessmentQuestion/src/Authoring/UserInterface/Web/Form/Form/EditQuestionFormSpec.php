<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form;

use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Section\QuestionDataSection;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Section\QuestionTypeSection;

class EditQuestionFormSpec {

	/**
	 * @var string
	 */
	protected $form_post_url;
	/**
	 * @var QuestionDataSection
	 */
	protected $question_data_section;


	public function __construct(string $form_post_url, QuestionDataSection $question_data_section) {
		$this->form_post_url = $form_post_url;
		$this->question_data_section = $question_data_section;
	}


	/**
	 * @return string
	 */
	public function getFormPostUrl(): string {
		return $this->form_post_url;
	}


	/**
	 * @return QuestionDataSection
	 */
	public function getQuestionDataSection(): QuestionDataSection {
		return $this->question_data_section;
	}
}