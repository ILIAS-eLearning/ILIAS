<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\DisplayDefinition;

/**
 * Class NumericEditorDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class NumericEditorDisplayDefinition extends DisplayDefinition {

    public static function getFields() : array
    {
        return [];
    }


    public function getValues() : array
    {
        return [];
    }


    public static function getValueFromPost(string $index)
    {
        return new NumericEditorDisplayDefinition();
    }


    public static function deserialize($data)
    {
        return new NumericEditorDisplayDefinition();
    }
}