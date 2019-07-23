<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\AnswerOptions;

class QuestionDto {

	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var string
	 */
	private $revision_id;
	/**
	 * @var string
	 */
	private $revision_name = "";
	/**
	 * @var QuestionData
	 */
	private $data;
	/**
	 * @var QuestionPlayConfiguration
	 */
	private $play_configuration;
	/**
	 * @var AnswerOptions
	 */
	private $answer_options;
}