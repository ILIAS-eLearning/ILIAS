<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\DisplayDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;
use stdClass;

/**
 * Class TextSubsetEditorDisplayDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class TextSubsetEditorDisplayDefinition extends DisplayDefinition {
    
    const VAR_TSDD_TEXT = 'tsdd_text' ;
    
    /**
     * @var string
     */
    private $text;
    
    public function __construct(?string $text) {
        $this->text = $text;
    }
    
    /**
     * @return string
     */
    public function getText(): string {
        return $this->text;
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
            self::VAR_TSDD_TEXT
            );        
        
        return $fields;
    }
    
    public static function getValueFromPost(string $index) {
        return new TextSubsetEditorDisplayDefinition(
            $_POST[$index . self::VAR_TSDD_TEXT]);
    }
    
    public function getValues(): array {
        return [self::VAR_TSDD_TEXT => $this->text];
    }
    
    
    public static function deserialize(stdClass $data) {
        return new TextSubsetEditorDisplayDefinition(
            $data->text);
    }
}