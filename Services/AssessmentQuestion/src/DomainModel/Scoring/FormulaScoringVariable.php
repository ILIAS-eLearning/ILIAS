<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;

/**
 * Class FormulaScoring
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class FormulaScoringVariable extends AbstractValueObject {
    const VAR_MIN = 'fsv_min';
    const VAR_MAX = 'fsv_max';
    const VAR_UNIT = 'fsv_unit';
    const VAR_MULTIPLE_OF = 'fsv_multiple_of';

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
     * @var float
     */
    protected $multiple_of;
    
    /**
     * @param float $min
     * @param float $max
     * @param string $unit
     * @param float $divisor
     * @return FormulaScoringVariable
     */
    public static function create(float $min,
                                  float $max,
                                  string $unit,
                                  float $multiple_of) : 
                                  FormulaScoringVariable {
        $object = new FormulaScoringVariable();
        $object->min = $min;
        $object->max = $max;
        $object->unit = $unit;
        $object->multiple_of = $multiple_of;
        return $object;
    }
    
    /**
     * @return float
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @return float
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
     * @return float
     */
    public function getMultipleOf()
    {
        return $this->multiple_of;
    }

    public function getAsArray(): array {
        return [
            self::VAR_MIN => $this->min,
            self::VAR_MAX => $this->max,
            self::VAR_UNIT => $this->unit,
            self::VAR_MULTIPLE_OF => $this->multiple_of
        ];
    }
    
    public function equals(AbstractValueObject $other): bool
    {
        /** @var FormulaScoringVariable $other */
        return get_class($this) === get_class($other) &&
               $this->min === $other->min &&
               $this->max === $other->max &&
               $this->unit === $other->unit &&
               $this->multiple_of === $other->multiple_of;
    }
}