<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerDefinition;
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
class ErrorTextScoringDefinition extends AnswerDefinition {
    const VAR_WRONG_TEXT = 'etsd_wrong_text';
    const VAR_WORD_INDEX = 'etsd_word_index';
    const VAR_WORD_LENGTH = 'etsd_word_length';
    const VAR_CORRECT_TEXT = 'etsd_correct_text' ;
    const VAR_POINTS = 'etsd_points';
    
    /**
     * @var int
     */
    protected $wrong_word_index;
    /**
     * @var int
     */
    protected $wrong_word_length;
    /**
     * @var string
     */
    protected $correct_text;
    /**
     * @var int
     */
    protected $points;
    
    /**
     * @var array
     */
    private static $error_text_words;
    
    /**
     * ErrorTextScoringDefinition constructor.
     *
     * @param int $points
     */
    public function __construct(int $wrong_word_index, int $wrong_word_length, ?string $correct_text, int $points)
    {
        $this->wrong_word_index = $wrong_word_index;
        $this->wrong_word_length = $wrong_word_length;
        $this->correct_text = $correct_text;
        $this->points = $points;
    }
    
    /**
     * @return int
     */
    public function getWrongWordIndex()
    {
        return $this->wrong_word_index;
    }
    
    /**
     * @return int
     */
    public function getWrongWordLength()
    {
        return $this->wrong_word_length;
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
    
    public static function getFields(QuestionPlayConfiguration $play): array {
        global $DIC;
        
        self::$error_text_words = explode(' ', $play->getEditorConfiguration()->getSanitizedErrorText());
        
        $fields = [];
        $fields[] = new AnswerOptionFormFieldDefinition(
            $DIC->language()->txt('asq_label_wrong_text'),
            AnswerOptionFormFieldDefinition::TYPE_LABEL,
            self::VAR_WRONG_TEXT);
        
        $fields[] = new AnswerOptionFormFieldDefinition(
            '',
            AnswerOptionFormFieldDefinition::TYPE_HIDDEN,
            self::VAR_WORD_INDEX);
        
        $fields[] = new AnswerOptionFormFieldDefinition(
            '',
            AnswerOptionFormFieldDefinition::TYPE_HIDDEN,
            self::VAR_WORD_LENGTH);
            
        $fields[] = new AnswerOptionFormFieldDefinition(
            $DIC->language()->txt('asq_label_correct_text'),
            AnswerOptionFormFieldDefinition::TYPE_TEXT,
            self::VAR_CORRECT_TEXT);
        
        $fields[] = new AnswerOptionFormFieldDefinition(
            $DIC->language()->txt('asq_label_points'),
            AnswerOptionFormFieldDefinition::TYPE_NUMBER,
            self::VAR_POINTS);
        
        return $fields;
    }
    
    private static function storeErrorText(?string $error_text) {
        if (is_null($error_text)) {
            return;
        }
        
        $error_text = str_replace('#', '', $error_text);
        $error_text = str_replace('((', '', $error_text);
        $error_text = str_replace('))', '', $error_text);
        self::$error_text_words = explode(' ', $error_text);
    }
    
    public static function getValueFromPost(string $index) {
        return new ErrorTextScoringDefinition(
            intval($_POST[$index . self::VAR_WORD_INDEX]),
            intval($_POST[$index . self::VAR_WORD_LENGTH]),
            ilAsqHtmlPurifier::getInstance()->purify($_POST[$index . self::VAR_CORRECT_TEXT]),
            intval($_POST[$index . self::VAR_POINTS]));
    }
    
    public function getValues(): array {
        return [self::VAR_WRONG_TEXT => $this->calculateErrorText($this->wrong_word_index, 
                                                                  $this->wrong_word_length),
                self::VAR_WORD_INDEX => $this->wrong_word_index,
                self::VAR_WORD_LENGTH => $this->wrong_word_length,
                self::VAR_CORRECT_TEXT => $this->correct_text,
                self::VAR_POINTS => $this->points
        ];
    }
    
    private function calculateErrorText(int $index, int $length) {
        $text = '';
        
        for ($i = $index; $i < $index + $length; $i++) {
            $text .= self::$error_text_words[$i] . ' ';
        }
        
        return $text;
    }
    
    
    public static function deserialize(stdClass $data) {
        return new ErrorTextScoringDefinition(
            $data->wrong_word_index,
            $data->wrong_word_length,
            $data->correct_text,
            $data->points);
    }
}