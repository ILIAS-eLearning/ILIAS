<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;


use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Scoring\AnswerOptionScoring;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Value\AnswerOptionValue;

/**
 * Class PointableAnswerOption
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Pointable
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerOptionWithScoring implements AnswerOption, AnswerOptionHasScoring {

	/**
	 * @var AnswerOptionValue[] $answer_option_values;
	 */
	protected $answer_option_values;
	/**
	 * @var AnswerOptionScoring
	 */
	protected $answer_option_scoring;

	public function __construct(array $answer_option_values, AnswerOptionScoring $answer_option_scoring) {
		$this->answer_option_values[] = $answer_option_values;
		$this->answer_option_scoring = $answer_option_scoring;
	}


	/**
	 * @return AnswerOptionValue[]
	 */
	public function getAnswerOptionValues(): array {
		return $this->answer_option_values;
	}


	/**
	 * @return AnswerOptionScoring
	 */
	public function getAnswerOptionScoring(): AnswerOptionScoring {
		return $this->answer_option_scoring;
	}
}