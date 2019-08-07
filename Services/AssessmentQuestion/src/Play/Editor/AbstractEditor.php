<?php

namespace ILIAS\AssessmentQuestion\Play\Editor;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Answer;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event\AbstractConfiguration;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionDto;
use JsonSerializable;
use stdClass;

/**
 * Abstract Class AbstractEditor
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class AbstractEditor {
	const EDITOR_DEFINITION_SUFFIX = 'DisplayDefinition';

	/**
	 * @var QuestionDto
	 */
	protected $question;

	/**
	 * AbstractEditor constructor.
	 *
	 * @param QuestionDto   $question
	 * @param array|null $configuration
	 */
	public function __construct(QuestionDto $question) {
		$this->question = $question;
	}

	/**
	 * @return string
	 */
	abstract public function generateHtml(): string;


	/**
	 * @return Answer
	 */
	abstract public function readAnswer(): string;


	/**
	 * @param string $answer
	 */
	abstract public function setAnswer(string $answer) : void;

	/**
	 * @param JsonSerializable|null $config
	 *
	 * @return array|null
	 */
	public static function generateFields(?AbstractConfiguration $config): ?array {
		return null;
	}

	/**
	 * @return JsonSerializable|null
	 */
	public static function readConfig() : ?AbstractConfiguration {
		return null;
	}

	/**
	 * @param stdClass $input
	 *
	 * @return JsonSerializable|null
	 */
	public static function deserialize(?stdClass $input) : ?JsonSerializable {
		return null;
	}


	/**
	 * @return string
	 */
	static function getDisplayDefinitionClass() : string {
		return get_called_class() . self::EDITOR_DEFINITION_SUFFIX;
	}
}