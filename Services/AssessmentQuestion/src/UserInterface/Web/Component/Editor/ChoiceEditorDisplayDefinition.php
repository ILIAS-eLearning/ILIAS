<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\DisplayDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\ImageUploader;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;
use stdClass;

/**
 * Class ChoiceEditorDisplayDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ChoiceEditorDisplayDefinition extends DisplayDefinition {

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
	    $fields = [];
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
		return new ChoiceEditorDisplayDefinition(
			$_POST[$index . self::VAR_MCDD_TEXT],
			ImageUploader::UploadImage($index . self::VAR_MCDD_IMAGE)
		);
	}

	public function getValues(): array {
		return [self::VAR_MCDD_TEXT => $this->text, 
		        self::VAR_MCDD_IMAGE => $this->image];
	}


	public static function deserialize(stdClass $data) {
		return new ChoiceEditorDisplayDefinition(
			$data->text,
			$data->image
		);
	}
	
	/**
	 * @var string
	 */
	private static $error_message;
	
	/**
	 * @param string $index
	 * @return bool
	 */
	public static function checkInput(string $index) : bool {
	    if ($_POST[$index . self::VAR_MCDD_TEXT] == null)
	    {
	        self::$error_message = "Answer text is necessary";
	        return false;
	    }
	    
	    return true;
	}
	
	/**
	 * @return string
	 */
	public static function getErrorMessage() : string {
	    return self::$error_message;
	}
}