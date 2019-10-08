<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;
use stdClass;

/**
 * Class FormulaScoringDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class FormulaScoringDefinition extends AnswerDefinition {
    const VAR_TYPE = 'fsd_type';
    const VAR_MIN = 'fsd_min';
    const VAR_MAX = 'fsd_max';
    const VAR_UNIT = 'fsd_unit';
    const VAR_MULTIPLE_OF = 'fsd_multiple_of';
    const VAR_POINTS = 'fsd_points';
    
    /**
     * @var int
     */
    protected $type;
    const TYPE_VARIABLE = 1;
    const TYPE_RESULT = 2;
    
    /**
     * @var float
     */
    protected $min;
    
    /**
     * @var float
     */
    protected $max;
    
    /**
     * @var string
     */
    protected $unit;
    
    /**
     * @var ?float
     */
    protected $multiple_of;
    
    /**
     * @var ?int
     */
    protected $points;
    
    /**
     * @param int $type
     * @param float $min
     * @param float $max
     * @param string $unit
     * @param float $multiple_of
     * @param int $points
     */
    public function __construct(int $type, string $unit, float $min, float $max, ?float $multiple_of = null, ?int $points = null) {
        $this->type = $type;
        $this->min = $min;
        $this->max = $max;
        $this->unit = $unit;
        $this->multiple_of = $multiple_of;
        $this->points = $points;
    }
    
    
    
    /**
     * @return number
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return number
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @return number
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @return \ILIAS\AssessmentQuestion\DomainModel\Scoring\?float
     */
    public function getMultipleOf()
    {
        return $this->multiple_of;
    }

    /**
     * @return \ILIAS\AssessmentQuestion\DomainModel\Scoring\?int
     */
    public function getPoints()
    {
        return $this->points;
    }

    public static function getFields(QuestionPlayConfiguration $play): array
    {
        global $DIC;
        
        $fields = [];
        
        $fields[] = new AnswerOptionFormFieldDefinition(
            $DIC->language()->txt('asq_label_type'),
            AnswerOptionFormFieldDefinition::TYPE_RADIO,
            self::VAR_TYPE,
            [
                'VAR' => self::TYPE_VARIABLE,
                'RESULT' => self::TYPE_RESULT
            ]);
 
        $fields[] = new AnswerOptionFormFieldDefinition(
            $DIC->language()->txt('asq_label_unit'),
            AnswerOptionFormFieldDefinition::TYPE_TEXT,
            self::VAR_UNIT);
        
        $fields[] = new AnswerOptionFormFieldDefinition(
            $DIC->language()->txt('asq_label_min'),
            AnswerOptionFormFieldDefinition::TYPE_TEXT,
            self::VAR_MIN);
        
        $fields[] = new AnswerOptionFormFieldDefinition(
            $DIC->language()->txt('asq_label_max'),
            AnswerOptionFormFieldDefinition::TYPE_TEXT,
            self::VAR_MAX);
        
        $fields[] = new AnswerOptionFormFieldDefinition(
            $DIC->language()->txt('asq_label_multiple_of'),
            AnswerOptionFormFieldDefinition::TYPE_TEXT,
            self::VAR_MULTIPLE_OF);
        
        $fields[] = new AnswerOptionFormFieldDefinition(
            $DIC->language()->txt('asq_label_points'),
            AnswerOptionFormFieldDefinition::TYPE_TEXT,
            self::VAR_POINTS);
        
        return $fields;
    }

    public function getValues(): array
    {
        return [
            self::VAR_TYPE => $this->type,
            self::VAR_MIN => $this->min,
            self::VAR_MAX => $this->max,
            self::VAR_UNIT => $this->unit,
            self::VAR_MULTIPLE_OF => $this->multiple_of,
            self::VAR_POINTS => $this->points
        ];
    }

    public static function getValueFromPost(string $index)
    {
        $type = intval($_POST[$index . self::VAR_TYPE]);
        
        if ($type === self::TYPE_VARIABLE) {
            if (!empty($_POST[$index . self::VAR_MULTIPLE_OF])) {
                $multiple_of = floatval($_POST[$index . self::VAR_MULTIPLE_OF]);
            }
        }
        else if ($type === self::TYPE_RESULT) {
            $points = intval($_POST[$index . self::VAR_POINTS]);
        }
        
        return new FormulaScoringDefinition($type,
                                            ilAsqHtmlPurifier::getInstance()->purify($_POST[$index . self::VAR_UNIT]), 
                                            floatval($_POST[$index . self::VAR_MIN]), 
                                            floatval($_POST[$index . self::VAR_MAX]),
                                            $multiple_of,
                                            $points);            
    }

    public static function deserialize(stdClass $data)
    {
        return new FormulaScoringDefinition($data->type, 
                                            $data->unit, 
                                            $data->min, 
                                            $data->max, 
                                            $data->multiple_of, 
                                            $data->points);
    }
}