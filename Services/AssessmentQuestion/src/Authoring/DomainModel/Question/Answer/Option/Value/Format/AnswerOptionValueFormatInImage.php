<?php
namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Value\Format;

use ilObjMediaObject;

/**
 * Class AnswerOptionValueFormatHtml
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Value\Format
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerOptionValueFormatInImage implements AnswerOptionValueInFormat  {

	/**
	 * @var ilObjMediaObject
	 */
	protected $answer_value;


	public function __construct(ilObjMediaObject $answer_value) {
		$this->answer_value = $answer_value;
	}


	public function getAnswerValue(): ilObjMediaObject {
		return $this->answer_value;
	}
}