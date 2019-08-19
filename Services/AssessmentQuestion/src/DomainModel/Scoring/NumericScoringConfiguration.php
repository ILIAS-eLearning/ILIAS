<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;


use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class NumericScoringConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class NumericScoringConfiguration extends AbstractConfiguration {
    /**
     * @var int
     */
    protected $points;
    /**
     * @var float
     */
    protected $lower_bound;
    /**
     * @var float
     */
    protected $upper_bound;


    static function create(int $points, float $lower_bound , float $upper_bound) : NumericScoringConfiguration
    {
        $object = new NumericScoringConfiguration();
        $object->points = $points;
        $object->lower_bound = $lower_bound;
        $object->upper_bound = $upper_bound;
        return $object;
    }

    /**
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }


    /**
     * @return float
     */
    public function getLowerBound()
    {
        return $this->lower_bound;
    }


    /**
     * @return float
     */
    public function getUpperBound()
    {
        return $this->upper_bound;
    }

    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject::equals()
     */
    public function equals(AbstractValueObject $other): bool
    {
        /** @var NumericScoringConfiguration $other */
        return get_class($this) === get_class($other) &&
               $this->lower_bound === $other->lower_bound &&
               $this->upper_bound === $other->upper_bound &&
               $this->points === $other->points;
    }
}