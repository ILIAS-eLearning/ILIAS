<?php
namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Value;


use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Value\Format\AnswerOptionValueInFormat;

/**
 * Class UserDefinedTextAnswer
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\UserDefined
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerOptionValueUserDefinedText implements AnswerOptionValueIsUserdefined, AnswerOptionValue {

	/**
	 * @var string
	 */
	protected $userdefined_answer_value;

	public function __construct(AnswerOptionValueInFormat $userdefined_answer_value) {
		$this->userdefined_answer_value = $userdefined_answer_value;
	}


	public function getAnswerValue():AnswerOptionValueInFormat {
		return $this->userdefined_answer_value;
	}

}