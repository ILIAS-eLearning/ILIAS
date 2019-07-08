<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;

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
class AnswerOptionWithoutScoring implements AnswerOption {

	/**
	 * @var AnswerOptionValue[] $answer_option_values;
	 */
	protected $answer_option_values;


	public function __construct(array $answer_option_values) {
		$this->answer_option_values[] = $answer_option_values;
	}


	/**
	 * @return AnswerOptionValue[]
	 */
	public function getAnswerOptionValues(): array {
		return $this->answer_option_values;
	}
}