<?php

namespace ILIAS\AssessmentQuestion\Play\Editor;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\DisplayDefinition;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Config\AnswerOptionForm;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;
use stdClass;

/**
 * Class MultipleChoiceEditorDisplayDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class MultipleChoiceEditorDisplayDefinition extends DisplayDefinition {

	const VAR_MCDD_TEXT = 'mcdd_text' ;
	const VAR_MCDD_IMAGE = 'mcdd_image';

	/**
	 * @var string
	 */
	private $text;
	/**
	 * @var string
	 */
	private $image;

	public function __construct(string $text, string $image) {
		$this->text = $text;
		$this->image = $image;
	}


	/**
	 * @return string
	 */
	public function getText(): string {
		return $this->text;
	}

	public function getImage(): string {
		return $this->image;
	}

	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize() {
		return get_object_vars($this);
	}

	public static function getFields(): array {
		$fields[] = new AnswerOptionFormFieldDefinition(
			'Answer Text',
			AnswerOptionFormFieldDefinition::TYPE_TEXT,
			self::VAR_MCDD_TEXT
		);

		$fields[] = new AnswerOptionFormFieldDefinition(
			'Answer Image',
			AnswerOptionFormFieldDefinition::TYPE_IMAGE,
			self::VAR_MCDD_IMAGE
		);


		return $fields;
	}

	public static function getValueFromPost(string $index) {
		return new MultipleChoiceEditorDisplayDefinition(
			$_POST[$index . self::VAR_MCDD_TEXT],
			$_POST[$index . self::VAR_MCDD_IMAGE]
		);
	}

	public function getValues(): array {
		return [$this->text, $this->image];
	}


	public static function deserialize(stdClass $data) {
		return new MultipleChoiceEditorDisplayDefinition(
			$data->text,
			$data->image
		);
	}
}