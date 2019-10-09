<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class ErrorTextEditorConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ErrorTextEditorConfiguration extends AbstractConfiguration
{
    /**
     * @var int
     */
    protected $text_size;
    /**
     * @var string
     */
    protected $error_text;

    /**
     * 
     * @param string $error_text
     * @param int $text_size
     * @return ErrorTextEditorConfiguration
     */
    public static function create(string $error_text, int $text_size) {
        $object = new ErrorTextEditorConfiguration();
        $object->error_text = $error_text;
        $object->text_size = $text_size;
        return $object;
    }
    
    /**
     * @return int
     */
    public function getTextSize()
    {
        return $this->text_size;
    }
    
    /**
     * @return string
     */
    public function getErrorText()
    {
        return $this->error_text;
    }
    
    public function getSanitizedErrorText() : string {
        if ($this->error_text === null) {
            return '';
        }
        
        $error_text = $this->error_text;
        $error_text = str_replace('#', '', $error_text);
        $error_text = str_replace('((', '', $error_text);
        $error_text = str_replace('))', '', $error_text);
        return $error_text;
    }
    
    /**
     * Compares ValueObjects to each other returns true if they are the same
     *
     * @param AbstractValueObject $other
     *
     * @return bool
     */
    function equals(AbstractValueObject $other) : bool
    {
        /** @var ErrorTextEditorConfiguration $other */
        return get_class($this) === get_class($other) &&
        $this->error_text === $other->error_text &&
        $this->text_size === $other->text_size;
    }
}