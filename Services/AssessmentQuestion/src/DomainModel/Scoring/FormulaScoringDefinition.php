<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqTableInputFieldDefinition;
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
    const VAR_FORMULA = 'fsd_formula';
    const VAR_UNIT = 'fsd_unit';
    const VAR_POINTS = 'fsd_points';
    
    /**
     * @var string
     */
    protected $formula;
    
    /**
     * @var string
     */
    protected $unit;
    
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
    public function __construct(string $formula, string $unit, int $points) {
        $this->formula = $formula;
        $this->unit = $unit;
        $this->points = $points;
    }
    
    
    
    /**
     * @return string
     */
    public function getFormula()
    {
        return $this->formula;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }

    public static function getFields(QuestionPlayConfiguration $play): array
    {
        global $DIC;
        
        $fields = [];
        
        $fields[] = new AsqTableInputFieldDefinition(
            $DIC->language()->txt('asq_formula'),
            AsqTableInputFieldDefinition::TYPE_TEXT,
            self::VAR_FORMULA);
 
        $fields[] = new AsqTableInputFieldDefinition(
            $DIC->language()->txt('asq_label_unit'),
            AsqTableInputFieldDefinition::TYPE_TEXT,
            self::VAR_UNIT);
        
        $fields[] = new AsqTableInputFieldDefinition(
            $DIC->language()->txt('asq_label_points'),
            AsqTableInputFieldDefinition::TYPE_TEXT,
            self::VAR_POINTS);
        
        return $fields;
    }

    public function getValues(): array
    {
        return [
            self::VAR_FORMULA => $this->formula,
            self::VAR_UNIT => $this->unit,
            self::VAR_POINTS => $this->points
        ];
    }

    public static function getValueFromPost(string $index)
    {          
        return new FormulaScoringDefinition(ilAsqHtmlPurifier::getInstance()->purify($_POST[$index . self::VAR_FORMULA]),
                                            ilAsqHtmlPurifier::getInstance()->purify($_POST[$index . self::VAR_UNIT]), 
                                            intval($_POST[$index . self::VAR_POINTS]));            
    }

    public static function deserialize(stdClass $data)
    {
        return new FormulaScoringDefinition($data->formula, 
                                            $data->unit,
                                            $data->points);
    }
}