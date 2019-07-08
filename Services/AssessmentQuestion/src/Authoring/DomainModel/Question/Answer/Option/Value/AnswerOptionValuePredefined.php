<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Value;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Value\Format\AnswerOptionValueInFormat;

/**
 * Class AnswerOptionValuePredefined
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Value
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerOptionValuePredefined implements AnswerOptionValue, AnswerOptionValueIsPredefined {

	/**
	 * @var AnswerOptionValueInFormat
	 */
	protected $predefined_answer_value;


	/**
	 * AnswerOptionValuePredefined constructor.
	 *
	 * @param AnswerOptionValueInFormat $predefined_answer_value
	 */
	public function __construct(AnswerOptionValueInFormat $predefined_answer_value) {
		$this->predefined_answer_value = $predefined_answer_value;
	}


	public function getAnswerValue(): AnswerOptionValueInFormat {
		return $this->predefined_answer_value;
	}
}