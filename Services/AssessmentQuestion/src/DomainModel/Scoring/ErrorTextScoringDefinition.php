<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\ScoringDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;
use stdClass;

/**
 * Class ErrorTextScoringDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ErrorTextScoringDefinition extends ScoringDefinition {
    
    const VAR_WORD_INDEX = 'etsd_word_index';
    const VAR_CORRECT_TEXT = 'etsd_correct_text' ;
    const VAR_POINTS = 'etsd_points';
    
    /**
     * @var int
     */
    protected $wrong_word_index;
    /**
     * @var string
     */
    protected $correct_text;
    /**
     * @var int
     */
    protected $points;
    
    /**
     * ErrorTextScoringDefinition constructor.
     *
     * @param int $points
     */
    public function __construct(int $wrong_word_index, ?string $correct_text, int $points)
    {
        $this->wrong_word_index = $wrong_word_index;
        $this->correct_text = $correct_text;
        $this->points = $points;
    }
    
    /**
     * @return number
     */
    public function getWrongWordIndex()
    {
        return $this->wrong_word_index;
    }
    
    /**
     * @return string
     */
    public function getCorrectText()
    {
        return $this->correct_text;
    }
    
    /**
     * @return number
     */
    public function getPoints()
    {
        return $this->points;
    }
    
    public static function getFields(): array {
        $fields = [];
        $fields[] = new AnswerOptionFormFieldDefinition(
            'Wrong Text',
            AnswerOptionFormFieldDefinition::TYPE_TEXT,
            self::VAR_WORD_INDEX);
        
        $fields[] = new AnswerOptionFormFieldDefinition(
            'Correct Text',
            AnswerOptionFormFieldDefinition::TYPE_TEXT,
            self::VAR_CORRECT_TEXT);
        
        $fields[] = new AnswerOptionFormFieldDefinition(
            'Points',
            AnswerOptionFormFieldDefinition::TYPE_NUMBER,
            self::VAR_POINTS);
        
        return $fields;
    }
    
    public static function getValueFromPost(string $index) {
        return new ErrorTextScoringDefinition(
            intval($_POST[$index . self::VAR_WORD_INDEX]),
            $_POST[$index . self::VAR_CORRECT_TEXT],
            intval($_POST[$index]));
    }
    
    public function getValues(): array {
        return [self::VAR_WORD_INDEX => $this->wrong_word_index,
                self::VAR_CORRECT_TEXT => $this->correct_text,
                self::VAR_POINTS => $this->points
        ];
    }
    
    
    public static function deserialize(stdClass $data) {
        return new ErrorTextScoringDefinition(
            $data->wrong_word_index, 
            $data->correct_text,
            $data->points);
    }
}