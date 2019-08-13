<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;

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
     * @param AbstractConfiguration|null $config
     *
     * @return array|null
     */
	public static function generateFields(?AbstractConfiguration $config): ?array {
		return null;
	}

	public static abstract function readConfig();
	
	/**
	 * @return string
	 */
	static function getDisplayDefinitionClass() : string {
		return get_called_class() . self::EDITOR_DEFINITION_SUFFIX;
	}
}