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
    
    const VAR_TSSD_POINTS = 'tssd_points';
    const VAR_TSSD_TEXT = 'tsdd_text' ;
    
    /**
     * @var int
     */
    protected $points;
    /**
     * @var string
     */
    protected $text;
    
    /**
     * TextSubsetScoringDefinition constructor.
     *
     * @param int $points
     */
    public function __construct(int $points, ?string $text)
    {
        $this->points = $points;
        $this->text = $text;
    }  
    
    /**
     * @return int
     */
    public function getPoints(): int {
        return $this->points;
    }
    
    /**
     * @return string
     */
    public function getText(): string {
        return $this->text;
    }
    
    public static function getFields(): array {
        global $DIC;
        
        $fields = [];
        $fields[] = new AnswerOptionFormFieldDefinition(
            $DIC->language()->txt('asq_label_answer_text'),
            AnswerOptionFormFieldDefinition::TYPE_TEXT,
            self::VAR_TSSD_TEXT
            );
        
        $fields[] = new AnswerOptionFormFieldDefinition(
            $DIC->language()->txt('asq_label_points'),
            AnswerOptionFormFieldDefinition::TYPE_NUMBER,
            self::VAR_TSSD_POINTS
            );
        
        return $fields;
    }
    
    public static function getValueFromPost(string $index) {
        return new TextSubsetScoringDefinition(intval($_POST[$index . self::VAR_TSSD_POINTS]),
                                               $_POST[$index . self::VAR_TSSD_TEXT]);
    }
    
    public function getValues(): array {
        return [self::VAR_TSSD_POINTS => $this->points,
                self::VAR_TSSD_TEXT => $this->text
        ];
    }
    
    
    public static function deserialize(stdClass $data) {
        return new TextSubsetScoringDefinition(
            $data->points, $data->text);
    }
}