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
    
    /**
     * @var FormulaScoringVariable[]
     */
    protected $variables = [];
    
    const TYPE_ALL = 1;
    const TYPE_DECIMAL = 2;
    const TYPE_FRACTION = 3;
    const TYPE_COPRIME_FRACTION = 4;
    
    public static function create(string $units,
                                  int $precision,
                                  float $tolerance,
                                  int $result_type,
                                  array $variables) : FormulaScoringConfiguration {
        
        $object = new FormulaScoringConfiguration();
        
        $object->units = $units;
        $object->precision = $precision;
        $object->tolerance = $tolerance;
        $object->result_type = $result_type;
        $object->variables = $variables;
        
        return $object;
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
    
    public function getVariables() {
        return $this->variables;
    }

    /**
     * @return array
     */
    public function getVariablesArray(): array {
        $var_array = [];
        
        foreach($this->variables as $variable) {
            $var_array[] = $variable->getAsArray();
        }
        
        return $var_array;
    }
    
    public function equals(AbstractValueObject $other): bool
    {
        /** @var FormulaScoringConfiguration $other */
        return get_class($this) === get_class($other) &&
               $this->units === $other->units &&
               $this->precision === $other->precision &&
               $this->tolerance === $other->tolerance &&
               $this->result_type === $other->result_type &&
               $this->variablesMatch($other->getVariables());
    }
    
    /**
     * @param FormulaScoringVariable[] $other_variables
     * @return bool
     */
    private function variablesMatch(array $other_variables) : bool {
        if (count($this->variables) !== count($other_variables)) {
            return false;
        }
        
        $i = 0;
        for ($i; i < count($this->variables); $i += 1) {
            if($this->variables[i].equals($other_variables[i])) {
                return false;
            }
        }
        
        return true;
    }
}