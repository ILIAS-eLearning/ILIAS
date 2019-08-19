<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class TextSubsetEditorConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class TextSubsetEditorConfiguration extends AbstractConfiguration
{
    /**
     * @var int
     */
    protected $number_of_requested_answers;
    
    
    /**
     * @param int $number_of_requested_answers
     *
     * @return TextSubsetEditorConfiguration
     */
    public static function create(int $number_of_requested_answers) {
        $object = new TextSubsetEditorConfiguration();
        $object->number_of_requested_answers = $number_of_requested_answers;
        return $object;
    }
    
    /**
     * @return int
     */
    public function getNumberOfRequestedAnswers()
    {
        return $this->number_of_requested_answers;
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
        /** @var TextSubsetEditorConfiguration $other */
        return get_class($this) === get_class($other) &&
               $this->number_of_requested_answers === $other->number_of_requested_answers;
    }
}