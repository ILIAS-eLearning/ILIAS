<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\ScoringDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;
use stdClass;

/**
 * Class TextSubsetScoringDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class TextSubsetScoringDefinition extends ScoringDefinition {
    
    const VAR_TSSD_POINTS = 'mcsd_selected';
    
    /**
     * @var int
     */
    protected $points;
    
    /**
     * TextSubsetScoringDefinition constructor.
     *
     * @param int $points
     */
    public function __construct(int $points)
    {
        $this->points = $points;
    }  
    
    /**
     * @return int
     */
    public function getPoints(): int {
        return $this->points;
    }
    
    public static function getFields(): array {
        $fields = [];
        $fields[] = new AnswerOptionFormFieldDefinition(
            'Points',
            AnswerOptionFormFieldDefinition::TYPE_NUMBER,
            self::VAR_TSSD_POINTS
            );
        
        return $fields;
    }
    
    public static function getValueFromPost(string $index) {
        return new TextSubsetScoringDefinition(intval($_POST[$index . self::VAR_TSSD_POINTS]));
    }
    
    public function getValues(): array {
        return [self::VAR_TSSD_POINTS => $this->points];
    }
    
    
    public static function deserialize(stdClass $data) {
        return new TextSubsetScoringDefinition(
            $data->points);
    }
}