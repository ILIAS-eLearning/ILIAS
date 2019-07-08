<?php
namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Entity;

class Answer implements Entity {

	/**
	 * @var AnswerOption[]
	 */
	protected $answer_options;

	public function __construct($answer_options) {
		$this->anwser_options = $answer_options;
	}


	/**
	 * @return AnswerOption[]
	 */
	public function getAnserOptions(): array {
		return $this->answer_options;
	}

}