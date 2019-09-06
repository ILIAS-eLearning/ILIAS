<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\ScoringDefinition;

/**
 * Class EmptyScoringDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class EmptyScoringDefinition extends ScoringDefinition {
    
    public static function getFields(): array {
        return [];
    }
    
    public static function getValueFromPost($index) {
        return new EmptyDisplayDefinition();
    }
    
    public function getValues(): array {
        return [];
    }
    
    
    public static function deserialize($data) {
        return new EmptyDisplayDefinition();
    }
}