<?php

namespace ILIAS\AssessmentQuestion\Play\Presenter;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionDto;
use JsonSerializable;
use stdClass;

/**
 * Abstract Class AbstractPresenter
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class AbstractPresenter {
	/**
	 * @var QuestionDto
	 */
	protected $question;
	/**
	 * @var
	 */
	protected $editor;


	/**
	 * AbstractEditor constructor.
	 *
	 * @param QuestionDto   $question
	 * @param array|null $configuration
	 */
	public function __construct(QuestionDto $question) {
		$this->question = $question;

		$editor_class = $question->getPlayConfiguration()->getEditorClass();
		$this->editor = new $editor_class($question);
	}

	public function getEditor() {
		return $this->editor;
	}

	/**
	 * @return string
	 */
	abstract public function generateHtml(): string;

	/**
	 * @return array|null
	 */
	public static function generateFields(): ?array {
		return null;
	}

	/**
	 * @return JsonSerializable|null
	 */
	public static function readConfig() : ?JsonSerializable {
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
}