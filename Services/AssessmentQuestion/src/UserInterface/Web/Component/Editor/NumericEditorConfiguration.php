<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class NumericEditorConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class NumericEditorConfiguration extends AbstractConfiguration
{
    /**
     * @var int
     */
    protected $max_num_of_chars;


    /**
     * @param int $max_num_of_chars
     *
     * @return NumericEditorConfiguration
     */
    public static function create(int $max_num_of_chars) {
        $object = new NumericEditorConfiguration();
        $object->max_num_of_chars = $max_num_of_chars;
        return $object;
    }

    /**
     * @return int
     */
    public function getMaxNumOfChars()
    {
        return $this->max_num_of_chars;
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
        /** @var NumericEditorConfiguration $other */
        return $this->max_num_of_chars === $other->max_num_of_chars;
    }
}