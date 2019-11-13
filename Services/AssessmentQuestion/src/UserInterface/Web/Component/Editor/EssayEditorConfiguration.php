<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class EssayEditorConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class EssayEditorConfiguration extends AbstractConfiguration
{
    /**
     * @var int
     */
    protected $max_length;
    
    public static function create(int $max_length) {
        $object = new EssayEditorConfiguration();
        $object->max_length = $max_length;
        return $object;
    }
    
    /**
     * @return int
     */
    public function getMaxLength()
    {
        return $this->max_length;
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
               $this->max_length === $other->max_length;
    }
}