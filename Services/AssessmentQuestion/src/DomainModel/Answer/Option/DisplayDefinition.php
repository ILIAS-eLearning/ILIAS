<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Answer\Option;

use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;
use JsonSerializable;
use stdClass;

/**
 * Abstract Class DisplayDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class DisplayDefinition implements JsonSerializable {

    /**
     * @return AnswerOptionFormFieldDefinition[]
     */
	public abstract static function getFields() : array;

	public abstract function getValues() : array;

	public abstract static function getValueFromPost(string $index);

	public abstract static function deserialize(stdClass $data);
	
	/**
	 * @return bool
	 */
	public static function checkInput(string $index) : bool {
	    return true;
	}
	
	/**
	 * @return string
	 */
	public static function getErrorMessage() : string {
	    return '';
	}
}