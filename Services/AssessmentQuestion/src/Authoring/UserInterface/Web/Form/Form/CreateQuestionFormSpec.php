<?php

namespace ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Form;

use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Section\QuestionTypeSection;

class CreateQuestionFormSpec {

	/**
	 * @var string
	 */
	protected $form_post_url;
	/**
	 * @var QuestionTypeSection
	 */
	protected $question_type_section;


	public function __construct(string $form_post_url, QuestionTypeSection $question_type_section) {
		$this->form_post_url = $form_post_url;
		$this->question_type_section = $question_type_section;
	}


	/**
	 * @return string
	 */
	public function getFormPostUrl(): string {
		return $this->form_post_url;
	}


	/**
	 * @return QuestionTypeSection
	 */
	public function getQuestionTypeSection(): QuestionTypeSection {
		return $this->question_type_section;
	}
}