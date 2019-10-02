<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class KprimChoiceScoringConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class FormulaScoringConfiguration extends AbstractConfiguration {
    /**
     * @var string
     */
    protected $formula;
    
    /**
     * @var string
     */
    protected $units;
    
    /**
     * @var int
     */
    protected $precision;
    
    /**
     * @var float
     */
    protected $tolerance;
    
    /**
     * @var int
     */
    protected $result_type;
    
    const TYPE_ALL = 1;
    const TYPE_DECIMAL = 2;
    const TYPE_FRACTION = 3;
    const TYPE_COPRIME_FRACTION = 4;
    
    public static function create(string $formula,
                                  string $units,
                                  int $precision,
                                  float $tolerance,
                                  int $result_type) : FormulaScoringConfiguration {
        
        $object = new FormulaScoringConfiguration();
        
        $object->formula = $formula;
        $object->units = $units;
        $object->precision = $precision;
        $object->tolerance = $tolerance;
        $object->result_type = $result_type;
        
        return $object;
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
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * @return int
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * @return float
     */
    public function getTolerance()
    {
        return $this->tolerance;
    }

    /**
     * @return int
     */
    public function getResultType()
    {
        return $this->result_type;
    }

    public function equals(AbstractValueObject $other): bool
    {
        /** @var FormulaScoringConfiguration $other */
        return get_class($this) === get_class($other) &&
               $this->formula === $other->formula &&
               $this->units === $other->units &&
               $this->precision === $other->precision &&
               $this->tolerance === $other->tolerance &&
               $this->result_type === $other->result_type;
    }
}