<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\ImageUploader;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqTableInputFieldDefinition;
use stdClass;

/**
 * Class ImageAndTextDisplayDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ImageAndTextDisplayDefinition extends AnswerDefinition {

	const VAR_MCDD_TEXT = 'mcdd_text' ;
	const VAR_MCDD_IMAGE = 'mcdd_image';

	/**
	 * @var string
	 */
	protected $text;
	/**
	 * @var string
	 */
	protected $image;

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

	public static function getFields(QuestionPlayConfiguration $play): array {
	    global $DIC;
	    
	    $fields = [];
	    
        $fields[] = new AsqTableInputFieldDefinition(
            $DIC->language()->txt('asq_label_answer_text'),
            AsqTableInputFieldDefinition::TYPE_TEXT,
            self::VAR_MCDD_TEXT
            );
        
        $fields[] = new AsqTableInputFieldDefinition(
            $DIC->language()->txt('asq_label_answer_image'),
            AsqTableInputFieldDefinition::TYPE_IMAGE,
            self::VAR_MCDD_IMAGE
            );

		return $fields;
	}

	public static function getValueFromPost(string $index) {
	    $image_key = $index . self::VAR_MCDD_IMAGE;
	    
		return new ImageAndTextDisplayDefinition(
		    ilAsqHtmlPurifier::getInstance()->purify($_POST[$index . self::VAR_MCDD_TEXT]),
		    ImageUploader::getInstance()->processImage($image_key)
		);
	}

	public function getValues(): array {
		return [self::VAR_MCDD_TEXT => $this->text, 
		        self::VAR_MCDD_IMAGE => $this->image];
	}


	public static function deserialize(stdClass $data) {
		return new ImageAndTextDisplayDefinition(
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
	public static function checkInput(int $count) : bool {
	    global $DIC;
	    
	    for ($i = 1; $i <= $count; $i++) {
	        if ($_POST[$i . self::VAR_MCDD_TEXT] == null)
	        {
	            self::$error_message = $DIC->language()->txt('msg_input_is_required');
	            return false;
	        }
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