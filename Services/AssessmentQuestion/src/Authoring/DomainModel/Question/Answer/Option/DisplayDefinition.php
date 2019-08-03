<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;

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
	public abstract static function getFields() : array;

	public abstract function getValues() : array;

	public abstract static function getValueFromPost(string $index);

	public abstract static function deserialize(stdClass $data);
}